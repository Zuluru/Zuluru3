<?php
namespace App\Controller;

use App\Authorization\ContextResource;
use Authorization\Exception\MissingIdentityException;
use Cake\Cache\Cache;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Exception\GoneException;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\ORM\Query;
use App\PasswordHasher\HasherTrait;
use App\Model\Entity\Allstar;
use App\Model\Entity\Game;
use App\Model\Entity\Team;

/**
 * Games Controller
 *
 * @property \App\Model\Table\GamesTable $Games
 */
class GamesController extends AppController {

	use HasherTrait;

	/**
	 * _noAuthenticationActions method
	 *
	 * @return array of actions that can be taken even by visitors that are not logged in.
	 */
	protected function _noAuthenticationActions() {
		$actions = ['view', 'tooltip', 'ical', 'results',
			// Attendance updates may come from emailed links; people might not be logged in
			'attendance_change',
		];
		if (Configure::read('feature.public')) {
			$actions[] = 'stats';
		}
		return $actions;
	}

	/**
	 * _noAuthenticationJsonActions method
	 *
	 * @return array of JSON actions that can be taken even by visitors that are not logged in.
	 */
	protected function _noAuthenticationJsonActions() {
		return ['results'];
	}

	// TODO: Eliminate this if we can find a way around black-holing caused by Ajax field adds
	public function beforeFilter(\Cake\Event\Event $event) {
		parent::beforeFilter($event);
		if (isset($this->Security)) {
			$this->Security->config('unlockedActions', ['edit_boxscore']);
		}
	}

	/**
	 * View method
	 *
	 * @return void|\Cake\Network\Response
	 */
	public function view() {
		$id = $this->request->getQuery('game');
		try {
			$game = $this->Games->get($id, [
				'contain' => [
					'Divisions' => ['Leagues'],
					'GameSlots' => ['Fields' => ['Facilities']],
					// Get the list of captains for each team, we may need to email them
					'HomeTeam' => [
						'People' => [
							Configure::read('Security.authModel'),
							'queryBuilder' => function (Query $q) {
								return $q->where([
									'TeamsPeople.role IN' => Configure::read('privileged_roster_roles'),
									'TeamsPeople.status' => ROSTER_APPROVED,
								]);
							},
						],
					],
					'HomePoolTeam' => ['DependencyPool'],
					'AwayTeam' => [
						'People' => [
							Configure::read('Security.authModel'),
							'queryBuilder' => function (Query $q) {
								return $q->where([
									'TeamsPeople.role IN' => Configure::read('privileged_roster_roles'),
									'TeamsPeople.status' => ROSTER_APPROVED,
								]);
							},
						],
					],
					'AwayPoolTeam' => ['DependencyPool'],
					'ApprovedBy',
					'ScoreEntries' => [
						'People',
						'Allstars',
					],
					'ScoreDetails' => [
						'ScoreDetailStats' => ['People', 'StatTypes'],
						'queryBuilder' => function (Query $q) {
							return $q->order(['ScoreDetails.created', 'ScoreDetails.id']);
						},
					],
					'SpiritEntries' => ['MostSpirited'],
					'Incidents',
					'Stats' => [
						'queryBuilder' => function (Query $q) {
							// We just need something to differentiate between games that have stats and those that don't
							return $q->limit(1);
						}
					],
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid game.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid game.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($game);

		$identity = $this->Authentication->getIdentity();
		if ($identity && $identity->isLoggedIn() && Configure::read('feature.annotations')) {
			$visibility = [VISIBILITY_PUBLIC];

			if ($identity->isManagerOf($game)) {
				$visibility[] = VISIBILITY_ADMIN;
				$visibility[] = VISIBILITY_COORDINATOR;
			} else if ($identity->isCoordinatorOf($game)) {
				$visibility[] = VISIBILITY_COORDINATOR;
			}

			$conditions = [
				'Notes.visibility IN' => $visibility,
				'Notes.created_person_id' => $this->UserCache->currentId(),
			];

			$teams = $this->UserCache->read('AllOwnedTeamIDs');
			if (!empty($teams)) {
				$conditions[] = [
					'Notes.visibility' => VISIBILITY_CAPTAINS,
					'Notes.created_team_id IN' => $teams,
				];
			}

			$teams = $this->UserCache->read('AllTeamIDs');
			if (!empty($teams)) {
				$conditions[] = [
					'Notes.visibility' => VISIBILITY_TEAM,
					'Notes.created_team_id IN' => $teams,
				];
			}

			$contain = [
				'CreatedPerson',
				'queryBuilder' => function (Query $q) use ($conditions) {
					return $q->where(['OR' => $conditions]);
				},
			];

			$this->Games->loadInto($game, ['Notes' => $contain]);
		}

		$this->Configuration->loadAffiliate($game->division->league->affiliate_id);
		$this->Games->adjustEntryIndices($game);
		$game->readDependencies();

		$this->set('game', $game);
		$this->set('spirit_obj', $game->division->league->hasSpirit() ? $this->moduleRegistry->load("Spirit:{$game->division->league->sotg_questions}") : null);
		$this->set('league_obj', $this->moduleRegistry->load("LeagueType:{$game->division->schedule_type}"));
		$this->set('ratings_obj', $this->moduleRegistry->load("Ratings:{$game->division->rating_calculator}"));
		$this->set('_serialize', ['game']);
	}

	/**
	 * Tooltip method
	 *
	 * @return void
	 */
	public function tooltip() {
		$this->request->allowMethod('ajax');

		$id = $this->request->getQuery('game');
		try {
			$game = $this->Games->get($id, [
				'contain' => [
					'HomeTeam',
					'AwayTeam',
					'GameSlots' => ['Fields' => ['Facilities' => ['Regions']]],
					'Divisions' => ['Leagues'],
				]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid game.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid game.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($game, 'view');

		$this->Configuration->loadAffiliate($game->game_slot->field->facility->region->affiliate_id);
		$this->set(compact('game'));
	}

	public function ratings_table() {
		$id = $this->request->getQuery('game');
		if ($this->request->is(['patch', 'post', 'put'])) {
			$this->set('rating_home', $this->request->data['rating_home']);
			$this->set('rating_away', $this->request->data['rating_away']);
		}

		try {
			$game = $this->Games->get($id, [
				'contain' => [
					'Divisions' => ['Leagues'],
					'HomeTeam',
					'AwayTeam',
				]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid game.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid game.'));
			return $this->redirect('/');
		}

		$ratings_obj = $this->moduleRegistry->load("Ratings:{$game->division->rating_calculator}");
		$this->Authorization->authorize(new ContextResource($game, ['division' => $game->division, 'ratings_obj' => $ratings_obj]));
		$this->Configuration->loadAffiliate($game->division->league->affiliate_id);

		$max_score = $game->division->league->expected_max_score;
		$this->set(compact('game', 'ratings_obj', 'max_score'));
	}

	// This function takes the parameters the old-fashioned way, to try to be more third-party friendly
	public function ical($game_id, $team_id) {
		$this->viewBuilder()->layout('ical');
		$game_id = intval($game_id);
		$team_id = intval($team_id);
		if (!$game_id || !$team_id) {
			return;
		}

		try {
			$game = $this->Games->get($game_id, [
				'contain' => [
					'HomeTeam',
					'AwayTeam',
					'GameSlots' => ['Fields' => ['Facilities' => ['Regions']]],
					'Divisions',
				]
			]);
		} catch (RecordNotFoundException $ex) {
			throw new GoneException();
		} catch (InvalidPrimaryKeyException $ex) {
			throw new GoneException();
		}

		$this->Authorization->authorize(new ContextResource($game, ['division' => $game->division, 'team_id' => $team_id]));
		$this->Configuration->loadAffiliate($game->game_slot->field->facility->region->affiliate_id);

		$this->set('calendar_type', 'Game');
		$this->set('calendar_name', 'Game');
		$this->response->download("$game_id.ics");
		$this->set(compact('game', 'team_id'));
		$this->RequestHandler->ext = 'ics';
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful edit, renders view otherwise.
	 */
	public function edit() {
		$id = $this->request->getQuery('game');
		// We need some basic game information right off. Much of the
		// data we display here doesn't come from the form, so we have
		// to read the whole thing.
		try {
			$game = $this->Games->get($id, [
				'contain' => [
					'Divisions' => [
						'People' => [
							Configure::read('Security.authModel'),
						],
						'Leagues',
					],
					'GameSlots' => ['Fields' => ['Facilities']],
					'HomeTeam' => [
						'People' => [
							Configure::read('Security.authModel'),
							'queryBuilder' => function (Query $q) {
								// This will be used for the allstar options, so we only want player roles
								return $q->where([
									'TeamsPeople.role IN' => Configure::read('extended_playing_roster_roles'),
									'TeamsPeople.status' => ROSTER_APPROVED,
								]);
							},
						],
					],
					'HomePoolTeam' => ['DependencyPool'],
					'AwayTeam' => [
						'People' => [
							Configure::read('Security.authModel'),
							'queryBuilder' => function (Query $q) {
								return $q->where([
									'TeamsPeople.role IN' => Configure::read('extended_playing_roster_roles'),
									'TeamsPeople.status' => ROSTER_APPROVED,
								]);
							},
						],
					],
					'AwayPoolTeam' => ['DependencyPool'],
					'ApprovedBy',
					'ScoreEntries' => [
						'People',
						'Allstars',
					],
					'SpiritEntries' => ['MostSpirited'],
					'Incidents',
				]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid game.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid game.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($game);
		$this->Configuration->loadAffiliate($game->division->league->affiliate_id);
		$game->readDependencies();
		$game->resetEntryIndices();

		// Spirit score entry validation comes from the spirit module
		if ($game->division->league->hasSpirit()) {
			$spirit_obj = $game->division->league->hasSpirit() ? $this->moduleRegistry->load("Spirit:{$game->division->league->sotg_questions}") : null;
			$this->Games->SpiritEntries->addValidation($spirit_obj, $game->division->league);
		}

		if ($this->request->is(['patch', 'post', 'put'])) {
			$this->request->data['approved_by_id'] = $this->UserCache->currentId();

			$game = $this->Games->patchEntity($game, $this->request->data, [
				'associated' => ['ScoreEntries', 'ScoreEntries.Allstars', 'SpiritEntries'],
			]);
			$game->resetEntryIndices();

			// If the allstar submissions come from the submitting team, then the home allstars
			// are recorded under the home team's score submission, array index 0, and the away
			// allstars are recorded under the away team's score submission, array index 1.
			// Otherwise, it's the other way around.
			if ($game->division->allstars_from == 'submitter') {
				$team_id = [$game->home_team_id, $game->away_team_id];
			} else {
				$team_id = [$game->away_team_id, $game->home_team_id];
			}

			// Add in join data that won't be there otherwise
			foreach ($game->score_entries as $key => $entry) {
				if (!empty($entry->allstars)) {
					foreach ($entry->allstars as $allstar) {
						$allstar->_joinData = new Allstar(['team_id' => $team_id[$key]]);
					}
				}
			}

			// We don't actually want to update the "modified" column in the score entries or people tables here
			if ($this->Games->ScoreEntries->hasBehavior('Timestamp')) {
				$this->Games->ScoreEntries->removeBehavior('Timestamp');
			}
			if ($this->Games->ScoreEntries->Allstars->hasBehavior('Timestamp')) {
				$this->Games->ScoreEntries->Allstars->removeBehavior('Timestamp');
			}
			// Or the current user on the score or spirit entries
			if ($this->Games->ScoreEntries->hasBehavior('Footprint')) {
				$this->Games->ScoreEntries->removeBehavior('Footprint');
			}
			if ($this->Games->SpiritEntries->hasBehavior('Footprint')) {
				$this->Games->SpiritEntries->removeBehavior('Footprint');
			}

			if ($this->Games->save($game, compact('game'))) {
				$this->Flash->success(__('The game has been saved.'));

				if ($this->request->getQuery('stats')) {
					return $this->redirect(['action' => 'submit_stats', 'game' => $id]);
				} else {
					return $this->redirect(['action' => 'view', 'game' => $id]);
				}
			} else {
				$this->Flash->warning(__('The game could not be saved. Please correct the errors below and try again.'));
			}
		}

		$this->set(compact(['game', 'spirit_obj']));
	}

	public function edit_boxscore() {
		try {
			$game = $this->Games->get($this->request->getQuery('game'), [
				'contain' => [
					'Divisions' => [
						'Leagues' => [
							'StatTypes' => [
								'queryBuilder' => function (Query $q) {
									return $q->where(['type' => 'entered']);
								},
							],
						],
					],
					'GameSlots',
					'HomeTeam' => [
						'People' => [
							'queryBuilder' => function (Query $q) {
								return $q->where(['role IN' => Configure::read('extended_playing_roster_roles')]);
							},
						],
					],
					'HomePoolTeam' => ['DependencyPool'],
					'AwayTeam' => [
						'People' => [
							'queryBuilder' => function (Query $q) {
								return $q->where(['role IN' => Configure::read('extended_playing_roster_roles')]);
							},
						],
					],
					'AwayPoolTeam' => ['DependencyPool'],
					'ScoreDetails' => [
						'queryBuilder' => function (Query $q) {
							return $q->order(['created', 'id']);
						},
						'ScoreDetailStats',
					],
				]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid game.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid game.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($game);
		$this->Configuration->loadAffiliate($game->division->league->affiliate_id);
		$game->readDependencies();

		if ($this->request->is(['patch', 'post', 'put'])) {
			$game = $this->Games->patchEntity($game, $this->request->data, [
				'associated' => ['ScoreDetails', 'ScoreDetails.ScoreDetailStats'],
			]);

			// Eliminate any detail stats where no person was selected
			foreach ($game->score_details as $detail) {
				$detail->score_detail_stats = collection($detail->score_detail_stats)->filter(function ($stats) {
					return !empty($stats->person_id);
				})->toArray();
			}

			// We don't actually want to update the "modified" column in the games table here
			if ($this->Games->hasBehavior('Timestamp')) {
				$this->Games->removeBehavior('Timestamp');
			}

			if ($this->Games->save($game)) {
				$this->Flash->success(__('The score details have been saved.'));
				return $this->redirect(['action' => 'view', 'game' => $game->id]);
			} else {
				$this->Flash->warning(__('The score details could not be saved. Please correct the errors below and try again.'));
			}
		}

		$this->set(compact('game'));
	}

	public function delete_score() {
		$this->request->allowMethod('ajax');

		$id = $this->request->getQuery('detail');
		$game_id = $this->request->getQuery('game');
		try {
			$detail = $this->Games->ScoreDetails->get($id, ['contain' => ['Games']]);
			if ($detail->game_id != $game_id) {
				throw new InvalidPrimaryKeyException('Invalid game id');
			}
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid score detail.'));
			return $this->redirect(['action' => 'edit_boxscore', 'game' => $game_id]);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid score detail.'));
			return $this->redirect(['action' => 'edit_boxscore', 'game' => $game_id]);
		}

		$this->Authorization->authorize($detail->game);

		if (!$this->Games->ScoreDetails->delete($detail)) {
			$this->Flash->warning(__('The score detail could not be deleted. Please, try again.'));
		}
	}

	public function add_score() {
		$this->request->allowMethod('ajax');

		$game_id = $this->request->getQuery('game');

		try {
			$game = $this->Games->get($game_id, [
				'contain' => [
					'Divisions' => [
						'Leagues' => [
							'StatTypes' => [
								'queryBuilder' => function (Query $q) {
									return $q->where(['type' => 'entered']);
								},
							],
						],
					],
					'HomeTeam' => [
						'People' => [
							'queryBuilder' => function (Query $q) {
								return $q->where(['role IN' => Configure::read('extended_playing_roster_roles')]);
							},
						],
					],
					'AwayTeam' => [
						'People' => [
							'queryBuilder' => function (Query $q) {
								return $q->where(['role IN' => Configure::read('extended_playing_roster_roles')]);
							},
						],
					],
				]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid game.'));
			return $this->redirect(['action' => 'edit_boxscore', 'game' => $game_id]);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid game.'));
			return $this->redirect(['action' => 'edit_boxscore', 'game' => $game_id]);
		}

		$this->Authorization->authorize($game);
		$this->Configuration->loadAffiliate($game->division->league->affiliate_id);
		$game->readDependencies();

		$detail = $this->Games->ScoreDetails->newEntity(array_merge($this->request->data['add_detail'], compact('game_id')));
		$detail->game = $game;

		if (!$this->Games->ScoreDetails->save($detail)) {
			$this->Flash->warning(__('The score details could not be saved. Please correct the errors below and try again.'));
			return $this->redirect(['action' => 'edit_boxscore', 'game' => $game_id]);
		}

		$this->set(compact('game', 'detail'));
	}

	public function note() {
		$game_id = $this->request->getQuery('game');
		$note_id = $this->request->getQuery('note');

		try {
			$game = $this->Games->get($game_id, [
				'contain' => [
					'Divisions' => ['Leagues'],
					'HomeTeam',
					'AwayTeam',
					'GameSlots' => ['Fields' => ['Facilities']],
				]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid game.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid game.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize(new ContextResource($game, ['home_team' => $game->home_team, 'away_team' => $game->away_team]));
		$this->Configuration->loadAffiliate($game->division->league->affiliate_id);

		if ($note_id) {
			try {
				$note = $this->Games->Notes->get($note_id);
				if ($note->game_id != $game_id) {
					throw new InvalidPrimaryKeyException('Invalid note id');
				}

				$this->Authorization->authorize($note, 'edit_game');
			} catch (RecordNotFoundException $ex) {
				$this->Flash->info(__('Invalid note.'));
				return $this->redirect(['action' => 'view', 'game' => $game_id]);
			} catch (InvalidPrimaryKeyException $ex) {
				$this->Flash->info(__('Invalid note.'));
				return $this->redirect(['action' => 'view', 'game' => $game_id]);
			}
		} else {
			$note = $this->Games->Notes->newEntity();
		}

		if ($this->request->is(['patch', 'post', 'put'])) {
			$note = $this->Games->Notes->patchEntity($note, $this->request->data);

			$is_new = $note->isNew();
			if (empty($note->note)) {
				if ($note->isNew()) {
					$this->Flash->warning(__('You entered no text, so no note was added.'));
					return $this->redirect(['action' => 'view', 'game' => $game_id]);
				} else {
					if ($this->Games->Notes->delete($note)) {
						$this->Flash->success(__('The note has been deleted.'));
						return $this->redirect(['action' => 'view', 'game' => $game_id]);
					} else if ($note->errors('delete')) {
						$this->Flash->warning(current($note->errors('delete')));
					} else {
						$this->Flash->warning(__('The note could not be deleted. Please, try again.'));
					}
				}
			} else {
				// Send an email on new notes
				if ($is_new) {
					switch ($note->visibility) {
						case VISIBILITY_CAPTAINS:
							$roles = Configure::read('privileged_roster_roles');
							break;
						case VISIBILITY_TEAM:
							$roles = Configure::read('regular_roster_roles');
							break;
					}

					if (isset($roles)) {
						$identity = $this->Authentication->getIdentity();
						if ($identity->isPlayerOn($game->home_team_id)) {
							$note->created_team_id = $game->home_team_id;
							$opponent = $game->away_team;
						} else if ($identity->isPlayerOn($game->away_team_id)) {
							$note->created_team_id = $game->away_team_id;
							$opponent = $game->home_team;
						}
					}
				}

				if ($this->Games->Notes->save($note)) {
					// TODO: Move email sending to the afterSave event?
					if (isset($roles)) {
						$team = $this->Games->Divisions->Teams->get($note->created_team_id, [
							'contain' => [
								'People' => [
									'queryBuilder' => function (Query $q) use ($roles) {
										return $q->where([
											'TeamsPeople.role IN' => $roles,
											'TeamsPeople.status' => ROSTER_APPROVED,
											'TeamsPeople.person_id !=' => $this->UserCache->currentId(),
										]);
									},
									Configure::read('Security.authModel'),
								]
							]
						]);
						if (!empty($team->people)) {
							$person = $this->UserCache->read('Person');
							$this->_sendMail([
								'to' => $team->people,
								'replyTo' => $person,
								'subject' => __('{0} game note', $team->name),
								'template' => 'game_note',
								// Notes are entered as HTML
								'sendAs' => 'html',
								'viewVars' => compact('person', 'team', 'opponent', 'game', 'note'),
							]);
						}
					}

					$this->Flash->success(__('The note has been saved.'));
					return $this->redirect(['action' => 'view', 'game' => $game_id]);
				} else {
					$this->Flash->warning(__('The note could not be saved. Please correct the errors below and try again.'));
				}
			}
		}

		$this->set(compact('game', 'note'));
	}

	public function delete_note() {
		$this->request->allowMethod(['post', 'delete']);

		$note_id = $this->request->getQuery('note');

		try {
			$note = $this->Games->Notes->get($note_id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid note.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid note.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($note, 'delete_game');

		if ($this->Games->Notes->delete($note)) {
			$this->Flash->success(__('The note has been deleted.'));
		} else if ($note->errors('delete')) {
			$this->Flash->warning(current($note->errors('delete')));
		} else {
			$this->Flash->warning(__('The note could not be deleted. Please, try again.'));
		}
		return $this->redirect(['action' => 'view', 'game' => $note->game_id]);
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Network\Response Redirects to index.
	 */
	public function delete() {
		$this->request->allowMethod(['post', 'delete']);

		$id = $this->request->getQuery('game');
		try {
			$game = $this->Games->get($id, [
				'contain' => [
					'Divisions' => ['Leagues'],
					'GameSlots',
					'HomeTeam',
					'AwayTeam',
					'ScoreEntries',
				]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid game.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid game.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($game);
		$this->Configuration->loadAffiliate($game->division->league->affiliate_id);

		if (!$this->request->getQuery('force')) {
			if ($game->isFinalized()) {
				$msg = __('The score for that game has already been finalized.');
			}
			if (!empty($game->score_entries)) {
				$msg = __('A score has already been submitted for this game.');
			}
		}

		if (isset($msg)) {
			$this->Flash->html([$msg, __('If you are absolutely sure that you want to delete it anyway, {0}. <b>This cannot be undone!</b>')], [
				'params' => [
					'class' => 'warning',
					'replacements' => [
						[
							'type' => 'postLink',
							'link' => __('click here'),
							'target' => ['action' => 'delete', 'game' => $id, 'force' => true],
						],
					],
				],
			]);
			return $this->redirect(['action' => 'view', 'game' => $id]);
		}

		// If the game isn't finalized, and there's no score entry, then there won't
		// be any other related records either, and it's safe to delete it.
		// Wrap the whole thing in a transaction, for safety.
		if ($this->Games->delete($game)) {
			$this->Flash->success(__('The game has been deleted.'));
			return $this->redirect(['controller' => 'Divisions', 'action' => 'schedule', 'division' => $game->division_id]);
		} else if ($game->errors('delete')) {
			$this->Flash->warning(current($game->errors('delete')));
			return $this->redirect(['action' => 'view', 'game' => $id]);
		} else {
			$this->Flash->warning(__('The game could not be deleted. Please, try again.'));
			return $this->redirect(['action' => 'view', 'game' => $id]);
		}
	}

	public function attendance() {
		$id = $this->request->getQuery('game');
		$team_id = $this->request->getQuery('team');
		try {
			$game = $this->Games->get($id, [
				'contain' => [
					'Divisions' => [
						'Days',
						'Leagues' => [
							'StatTypes' => [
								'queryBuilder' => function (Query $q) {
									return $q->where(['StatTypes.type' => 'entered']);
								},
							],
						],
					],
					'HomeTeam',
					'AwayTeam',
					'GameSlots' => ['Fields' => ['Facilities']],
				]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid game.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid game.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize(new ContextResource($game, ['home_team' => $game->home_team, 'away_team' => $game->away_team]));
		$this->Configuration->loadAffiliate($game->division->league->affiliate_id);
		if ($team_id && $game->home_team_id == $team_id) {
			$team = $game->home_team;
			$opponent = $game->away_team;
		} else if ($team_id && $game->away_team_id == $team_id) {
			$team = $game->away_team;
			$opponent = $game->home_team;
		} else {
			$this->Flash->info(__('That team is not playing in this game.'));
			return $this->redirect('/');
		}
		$this->Authorization->authorize($team);

		$attendance = $this->Games->readAttendance($team_id, collection($game->division->days)->extract('id')->toArray(), $id);
		$this->set(compact('game', 'team', 'opponent', 'attendance'));
		$this->set('_serialize', ['game', 'team', 'opponent', 'attendance']);
	}

	public function TODOLATER_add_sub() {
	}

	public function attendance_change() {
		$id = $this->request->getQuery('game');
		$game_date = $this->request->getQuery('date');
		if (!$id && !$game_date) {
			$this->Flash->info(__('Invalid game.'));
			return $this->redirect('/');
		}

		$team_id = $this->request->getQuery('team');
		if (!$team_id) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect('/');
		}

		$person_id = $this->request->getQuery('person') ?: $this->UserCache->currentId();
		if (!$person_id) {
			throw new MissingIdentityException();
		}

		$captains_contain = [
			'queryBuilder' => function (Query $q) {
				$q = $q->where([
					'TeamsPeople.role IN' => Configure::read('privileged_roster_roles'),
					'TeamsPeople.status' => ROSTER_APPROVED,
				]);
				$my_id = $this->UserCache->currentId();
				if ($my_id) {
					$q = $q->where(['TeamsPeople.person_id !=' => $my_id]);
				}
				return $q;
			},
			Configure::read('Security.authModel'),
		];

		if ($id) {
			try {
				$game = $this->Games->get($id, [
					'contain' => [
						// Get the list of captains for each team, we may need to email them
						'HomeTeam' => ['People' => $captains_contain],
						'AwayTeam' => ['People' => $captains_contain],
						'GameSlots' => ['Fields' => ['Facilities' => ['Regions']]],
						// We need to specify the team id here, in case the person is on both teams in this game
						'Attendances' => [
							'queryBuilder' => function (Query $q) use ($team_id, $person_id) {
								return $q->where(compact('team_id', 'person_id'));
							},
							'People' => [
								Configure::read('Security.authModel'),
								'Teams' => [
									'queryBuilder' => function (Query $q) use ($team_id) {
										return $q->where(compact('team_id'));
									},
								],
							],
						],
					]
				]);
			} catch (RecordNotFoundException $ex) {
				$this->Flash->info(__('Invalid game.'));
				return $this->redirect('/');
			} catch (InvalidPrimaryKeyException $ex) {
				$this->Flash->info(__('Invalid game.'));
				return $this->redirect('/');
			}

			$this->Configuration->loadAffiliate($game->game_slot->field->facility->region->affiliate_id);
			$game_date = $game->game_slot->game_date;
			$past = $game->game_slot->start_time->isPast();

			if ($game->home_team_id == $team_id) {
				$team = $game->home_team;
				$opponent = $game->away_team;
			} else if ($game->away_team_id == $team_id) {
				$team = $game->away_team;
				$opponent = $game->home_team;
			} else {
				$this->Flash->info(__('That team is not playing in this game.'));
				return $this->redirect('/');
			}

			if (!empty($game->attendances)) {
				$attendance = $game->attendances[0];
			} else {
				$attendance = null;
			}
		} else {
			$game_date = new FrozenDate($game_date);
			$game = $this->Games->newEntity();
			$opponent = $this->Games->HomeTeam->newEntity();

			$attendance = $this->Games->Attendances->find()
				->contain([
					'People' => [
						Configure::read('Security.authModel'),
						'Teams' => [
							'queryBuilder' => function (Query $q) use ($team_id) {
								return $q->where(compact('team_id'));
							},
							'People' => $captains_contain,
						],
					],
				])
				->where([
					'person_id' => $person_id,
					'team_id' => $team_id,
					'game_date' => $game_date,
				])
				->first();

			if (!empty($attendance) && !empty($attendance->person->teams[0])) {
				$past = false;
				$team = $attendance->person->teams[0];
			}
		}

		$code = $this->request->getQuery('code');
		// After authorization, the context will also include an indication of whether it's a player or captain
		$context = new ContextResource($team, compact('attendance', 'code', 'game', 'game_date'));
		$this->Authorization->authorize($context);

		$identity = $this->Authentication->getIdentity();
		$is_me = $context->is_player || ($identity && ($identity->isMe($attendance) || $identity->isRelative($attendance)));
		$is_captain = $context->is_captain || ($identity && $identity->isCaptainOf($attendance));

		if ($code) {
			// Fake the posted data array with the status from the URL
			$this->request->data = ['status' => $this->request->getQuery('status')];
		}

		$role = $attendance->person->teams[0]->_joinData->role;
		$attendance_options = $this->Games->attendanceOptions($role, $attendance->status, $past, $is_captain);

		if ($code || $this->request->is(['patch', 'post', 'put'])) {
			// Future dates give a negative diff; a positive number is more logical here.
			$days_to_game = - $game_date->diffInDays(null, false);

			if (array_key_exists('status', $this->request->data) && $this->request->data['status'] == 'comment') {
				// Comments that come via Ajax will have the status set to comment, which is not useful.
				unset($this->request->data['status']);
				$result = $this->_updateAttendanceComment($attendance, $game, $game_date, $team, $opponent, $is_me, $days_to_game, $past);
			} else {
				$result = $this->_updateAttendanceStatus($attendance, $game, $game_date, $team, $opponent, $is_captain, $is_me, $days_to_game, $past, $attendance_options);
			}

			// Where do we go from here? It depends...
			if (!$result) {
				if ($code) {
					return $this->redirect('/');
				}
			} else {
				if ($this->request->is('ajax')) {
					$this->set('dedicated', $this->request->getQuery('dedicated'));
				} else if (!$this->Authorization->can($team, 'attendance')) {
					return $this->redirect(['controller' => 'Teams', 'action' => 'view', 'team' => $team_id]);
				} else if ($id) {
					return $this->redirect(['controller' => 'Games', 'action' => 'attendance', 'team' => $team_id, 'game' => $id]);
				} else {
					return $this->redirect(['controller' => 'Teams', 'action' => 'attendance', 'team' => $team_id]);
				}
			}
		}

		$this->set(compact('attendance', 'game', 'game_date', 'team', 'opponent', 'attendance_options', 'is_captain', 'is_me'));
	}

	protected function _updateAttendanceStatus($attendance, $game, $date, $team, $opponent, $is_captain, $is_me, $days_to_game, $past, $attendance_options) {
		if (!array_key_exists($this->request->data['status'], $attendance_options)) {
			$this->Flash->info(__('That is not currently a valid attendance status for this person for this game.'));
			return false;
		}

		$attendance = $this->Games->Attendances->patchEntity($attendance, $this->request->data);
		if (!$attendance->dirty('status') && !$attendance->dirty('comment') && !$attendance->dirty('note')) {
			return true;
		}

		if (!$this->Games->Attendances->save($attendance)) {
			$this->Flash->warning(__('Failed to update the attendance status!'));
			return false;
		}

		if (!$this->request->is('ajax')) {
			$this->Flash->success(__('Attendance has been updated to {0}.', $attendance_options[$attendance->status]));
		}

		// Maybe send some emails, only if the game is in the future
		if ($past) {
			return true;
		}

		$role = $attendance->person->teams[0]->_joinData->role;

		// Send email from the player to the captain(s) if it's within the configured date range
		if ($is_me && $team->attendance_notification >= $days_to_game) {
			if (!empty($team->people)) {
				$this->_sendMail([
					'to' => $team->people,
					'replyTo' => $attendance->person,
					'subject' => __('{0} attendance change', $team->name),
					'template' => 'attendance_captain_notification',
					'sendAs' => 'both',
					'viewVars' => array_merge([
						'captains' => implode(', ', collection($team->people)->extract('first_name')->toArray()),
						'person' => $attendance->person,
						'code' => $this->_makeHash([$attendance->id, $attendance->team_id, $attendance->game_id, $attendance->person_id, $attendance->created, 'captain']),
					], compact('attendance', 'game', 'date', 'team', 'opponent')),
				]);
			}
		}
		// Always send an email from the captain to substitute players. It will likely
		// be an invitation to play or a response to a request or cancelling attendance
		// if another player is available. Regardless, we need to communicate this.
		else if ($is_captain && !in_array($role, Configure::read('playing_roster_roles'))) {
			$captain = $this->UserCache->read('Person.full_name');
			$this->_sendMail([
				'to' => $attendance->person,
				'replyTo' => $this->UserCache->read('Person'),
				'subject' => __('{0} attendance change for {1} on {2}', $team->name, __('game'), $date),
				'template' => 'attendance_substitute_notification',
				'sendAs' => 'both',
				'viewVars' => array_merge([
					'captain' => $captain ? $captain : __('A coach or captain'),
					'person' => $attendance->person,
					'code' => $this->_makeHash([$attendance->id, $attendance->team_id, $attendance->game_id, $attendance->person_id, $attendance->created]),
					'player_options' => $this->Games->attendanceOptions($role, $attendance->status, $past, false),
				], compact('attendance', 'game', 'date', 'team', 'opponent')),
			]);
		}

		return true;
	}

	protected function _updateAttendanceComment($attendance, $game, $date, $team, $opponent, $is_me, $days_to_game, $past) {
		$attendance = $this->Games->Attendances->patchEntity($attendance, $this->request->data);
		if (!$attendance->dirty('comment')) {
			return true;
		}

		if (!$this->Games->Attendances->save($attendance)) {
			$this->Flash->warning(__('Failed to update the attendance comment!'));
			return false;
		}

		if (!$this->request->is('ajax')) {
			$this->Flash->success(__('Attendance comment has been updated.'));
		}

		// Maybe send some emails, only if the game is in the future
		if ($past) {
			return true;
		}

		// Send email from the player to the captain(s) if it's within the configured date range
		if ($is_me && $team->attendance_notification >= $days_to_game) {
			if (!empty($team->people)) {
				$this->_sendMail([
					'to' => $team->people,
					'replyTo' => $attendance->person,
					'subject' => __('{0} attendance comment', $team->name),
					'template' => 'attendance_comment_captain_notification',
					'sendAs' => 'both',
					'viewVars' => array_merge([
						'captains' => implode(', ', collection($team->people)->extract('first_name')->toArray()),
						'person' => $attendance->person,
					], compact('attendance', 'game', 'date', 'team', 'opponent')),
				]);
			}
		}

		return true;
	}

	public function stat_sheet() {
		$id = $this->request->getQuery('game');
		$team_id = $this->request->getQuery('team');
		try {
			$game = $this->Games->get($id, [
				'contain' => [
					'Divisions' => [
						'Leagues' => [
							'StatTypes' => [
								'queryBuilder' => function (Query $q) {
									return $q->where(['StatTypes.type' => 'entered']);
								},
							],
						],
						'Days',
					],
					'HomeTeam',
					'HomePoolTeam' => ['DependencyPool'],
					'AwayTeam',
					'AwayPoolTeam' => ['DependencyPool'],
					'GameSlots' => ['Fields' => ['Facilities']],
				]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid game.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid game.'));
			return $this->redirect('/');
		}

		$game->readDependencies();
		if ($team_id && $game->home_team_id == $team_id) {
			$team = $game->home_team;
			if ($game->away_team_id === null) {
				$opponent = new Team(['name' => $game->away_dependency]);
			} else {
				$opponent = $game->away_team;
			}
		} else if ($team_id && $game->away_team_id == $team_id) {
			$team = $game->away_team;
			if ($game->home_team_id === null) {
				$opponent = new Team(['name' => $game->home_dependency]);
			} else {
				$opponent = $game->home_team;
			}
		} else {
			$this->Flash->info(__('That team is not playing in this game.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize(new ContextResource($team, ['league' => $game->division->league, 'stat_types' => $game->division->league->stat_types]));
		$this->Configuration->loadAffiliate($game->division->league->affiliate_id);

		$attendance = $this->Games->readAttendance($team_id, collection($game->division->days)->extract('id')->toArray(), $id);
		$this->set(compact('game', 'team', 'opponent', 'attendance'));
		$this->set('is_captain', in_array($team_id, $this->UserCache->read('OwnedTeamIDs')));
	}

	public function TODOLATER_live_score() {
		$this->viewBuilder()->layout('bare');

		$id = $this->request->getQuery('game');
		$team_id = $this->request->getQuery('team');
		$contain = [
			'Division' => [
				'League' => [
					'StatType' => ['conditions' => ['StatType.type' => 'entered']],
				],
			],
			'GameSlot' => ['Field' => 'Facility'],
			'ScoreEntry',
			'ScoreDetail',
			// We need roster details for potential stat tracking.
			'HomeTeam' => [
				'Person' => [
					'conditions' => [
						'TeamsPeople.role IN' => Configure::read('extended_playing_roster_roles'),
						'TeamsPeople.status' => ROSTER_APPROVED,
					],
					'fields' => [
						'People.id', 'People.first_name', 'People.last_name', 'People.' . Configure::read('gender.column') => Configure::read('gender.order'),
					],
				],
			],
			'AwayTeam' => [
				'Person' => [
					'conditions' => [
						'TeamsPeople.role IN' => Configure::read('extended_playing_roster_roles'),
						'TeamsPeople.status' => ROSTER_APPROVED,
					],
					'fields' => [
						'People.id', 'People.first_name', 'People.last_name', 'People.' . Configure::read('gender.column') => Configure::read('gender.order'),
					],
				],
			],
		];
		if ($team_id) {
			$contain['ScoreEntry']['conditions'] = ['ScoreEntry.team_id' => $team_id];
		}

		$this->Game->contain($contain);
		$game = $this->Game->read(null, $id);
		if (!$game) {
			$this->Flash->info(__('Invalid game.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize(new ContextResource($game, ['team' => $team_id]));
		$this->Game->adjustEntryIndices($game);

		if (!$game->home_team_id || !$game->away_team_id) {
			$this->Flash->info(__('Dependencies for that game have not yet been resolved!'));
			return $this->redirect(['action' => 'view', 'game' => $id]);
		}

		if ($team_id && $team_id != $game->home_team_id && $team_id != $game->away_team_id) {
			$this->Flash->info(__('That team did not play in that game!'));
			return $this->redirect(['action' => 'view', 'game' => $id]);
		}

		if ($game->isFinalized()) {
			$this->Flash->info(__('The score for that game has already been finalized.'));
			return $this->redirect(['action' => 'view', 'game' => $id]);
		}

		if ($team_id && array_key_exists($team_id, $game['ScoreEntry']) && $game['ScoreEntry'][$team_id]['status'] != 'in_progress') {
			$this->Flash->info(__('That team has already submitted a score for that game.'));
			return $this->redirect(['action' => 'view', 'game' => $id]);
		}

		$this->Configuration->loadAffiliate($game->division->league['affiliate_id']);
		if ($game->home_team_id == $team_id || $team_id === null) {
			$team = $game->home_team;
			$opponent = $game->away_team;
		} else if ($game->away_team_id == $team_id) {
			$team = $game->away_team;
			$opponent = $game->home_team;
		} else {
			$this->Flash->info(__('That team is not playing in this game.'));
			return $this->redirect(['action' => 'view', 'game' => $id]);
		}

		$this->set(compact('game', 'team', 'opponent'));
		$this->set(['submitter' => $team_id]);
	}

	public function TODOLATER_score_up() {
		$this->request->allowMethod('ajax');

		$id = $this->request->getQuery('game');
		if (!$id) {
			$this->set('message', __('Invalid game.'));
			return;
		}

		if (!$this->request->data['team_id']) {
			$this->set('message', __('Invalid team.'));
			return;
		}

		$submitter = $this->request->getQuery('team');

		// Lock all of this to prevent multiple simultaneous score updates
		// TODO: Handle both teams updating at the same time, one with details and one without
		$this->loadComponent('Lock');
		if (!$this->Lock->lock("live_scoring $id", null, null, false)) {
			$this->set('message', __('Someone else is currently updating the score for this game!\n\nIt\'s probably your opponent, try again right away.'));
			return;
		}

		$this->Game->contain([
			'Division' => [
				'League',
			],
			'HomeTeam',
			'AwayTeam',
			'ScoreEntry' => ['conditions' => ['ScoreEntry.team_id' => $submitter]],
			'ScoreDetail' => ['conditions' => [
				'ScoreDetail.team_id' => $this->request->data['team_id'],
				'ScoreDetail.score_from' => $this->request->data['score_from'],
				'ScoreDetail.play' => $this->request->data['play'],
			]],
		]);
		$game = $this->Game->read(null, $id);
		if (!$game) {
			$this->set('message', __('Invalid game.'));
			return;
		}

		$this->Authorization->authorize(new ContextResource($game, ['team' => $submitter]));

		if ($this->request->data['team_id'] != $game->home_team_id && $this->request->data['team_id'] != $game->away_team_id) {
			$this->set('message', __('That team did not play in that game!'));
			return;
		}

		if ($game->isFinalized()) {
			$this->set('message', __('The score for that game has already been finalized.'));
			return;
		}

		// This will handle either the home team or a third-party submitting the score as "for"
		if (($submitter === null && $this->request->data['team_id'] == $game->home_team_id) || $submitter == $this->request->data['team_id']) {
			$team_score_field = 'score_for';
			$opponent_score_field = 'score_against';
		} else {
			$team_score_field = 'score_against';
			$opponent_score_field = 'score_for';
		}

		if (!empty($game['ScoreEntry'])) {
			$entry = current($game['ScoreEntry']);
			if ($entry['status'] != 'in_progress') {
				$this->set('message', __('That team has already submitted a score for that game.'));
				return;
			}
			unset($entry['created']);
			unset($entry['modified']);
			unset($entry['person_id']);
			$team_score = $entry[$team_score_field];
			$opponent_score = $entry[$opponent_score_field];
		} else {
			$entry = [
				'team_id' => $submitter,
				'game_id' => $id,
				'status' => 'in_progress',
			];
			$team_score = $opponent_score = 0;
		}

		if ($team_score != $this->request->data['score_from']) {
			$this->set('message', __('The saved score does not match yours.\nSomeone else may have updated the score in the meantime.\n\nPlease refresh the page and try again.'));
			return;
		}

		$this->Configuration->loadAffiliate($game->division->league['affiliate_id']);

		if (empty($this->request->data['play'])) {
			$this->set('message', __('You must indicate the scoring play so that the new score can be calculated.'));
			return;
		}
		$points = Configure::read("sports.{$game->division->league->sport}.score_points.{$this->request->data['play']}");
		if (!$points) {
			$this->set('message', __('Invalid scoring play!'));
			return;
		}
		$team_score += $points;
		$entry[$team_score_field] = $team_score;

		$transaction = new DatabaseTransaction($this->Game);

		if (!$this->Game->ScoreEntry->save($entry)) {
			$this->set('message', __('There was an error updating the score.\nPlease try again.'));
			return;
		} else {
			$this->Game->updateAll(['Game.modified' => 'NOW()'], ['Game.id' => $id]);
		}

		// Check if there's already a matching score detail record (presumably from the other team).
		// If so, we may want to update it.
		if (!empty($game['ScoreDetail'])) {
			$this->Game->ScoreDetail->id = $game['ScoreDetail'][0]->id;
		}
		if (!$this->Game->ScoreDetail->save(array_merge($this->request->data, [
				'game_id' => $id,
				'created_team_id' => $submitter,
				'points' => $points,
		])))
		{
			$this->set('message', __('There was an error updating the box score.\nPlease try again.'));
			return;
		}

		// Save stat details
		if (!empty($this->request->data['Stat'])) {
			foreach ($this->request->data['Stat'] as $stat_type_id => $person_id) {
				if (!empty($person_id)) {
					$this->Game->ScoreDetail->ScoreDetailStat->create();
					$this->Game->ScoreDetail->ScoreDetailStat->save([
							'score_detail_id' => $this->Game->ScoreDetail->id,
							'stat_type_id' => $stat_type_id,
							'person_id' => $person_id,
					]);
				}
			}
		}

		$transaction->commit();

		// TODO: Would be nice if there was a Cache method that could help with this
		$cache_file = CACHE . 'queries' . DS . "cake_division_{$game->division_id}_standings";
		if (file_exists($cache_file) && (new FrozenTime(filemtime($cache_file)))->addMinutes(5)->isPast()) {
			unlink($cache_file);
		}
		$cache_file = CACHE . 'queries' . DS . "cake_division_{$game->division_id}_schedule";
		if (file_exists($cache_file) && (new FrozenTime(filemtime($cache_file)))->addMinutes(5)->isPast()) {
			unlink($cache_file);
		}

		if ($game->home_team_id == $this->request->data['team_id'] || $this->request->data['team_id'] === null) {
			$team = $game->home_team;
			$opponent = $game->away_team;
		} else if ($game->away_team_id == $this->request->data['team_id']) {
			$team = $game->away_team;
			$opponent = $game->home_team;
		}

		// Do some fun analysis on scores
		$twitter = "Score update #{$game->division['name']}: ";
		if ($team_score == 1 && $opponent_score == 0) {
			$twitter .= $team->twitterName() . ' opens the scoring against ' . $opponent->twitterName() . '.';
		} else if ($team_score >= $game->division->league['expected_max_score']) {
			$twitter .= $team->twitterName() . " wins $team_score-$opponent_score against " . $opponent->twitterName();
		} else if ($team_score == ceil($game->division->league['expected_max_score'] / 2) && $team_score > $opponent_score) {
			$twitter .= $team->twitterName() . " takes half $team_score-$opponent_score against " . $opponent->twitterName();
		} else if ($team_score == $opponent_score) {
			$twitter .= $team->twitterName() . ' scores to tie ' . $opponent->twitterName() . " at $team_score-$opponent_score";
			if ($team_score == $game->division->league['expected_max_score'] - 1) {
				$twitter .= ', heading to overtime!';
			}
		} else if ($team_score == $opponent_score + 1) {
			$twitter .= $team->twitterName() . " takes the lead $team_score-$opponent_score against " . $opponent->twitterName();
		} else if ($team_score == $opponent_score - 1) {
			$twitter .= $team->twitterName() . " pulls within one, down $opponent_score-$team_score against " . $opponent->twitterName();
		} else if ($team_score == $opponent_score + 5) {
			$twitter .= $team->twitterName() . ' opens up a five-point lead on ' . $opponent->twitterName() . ', score now ' . Game::twitterScore($team, $team_score, $opponent, $opponent_score);
		} else {
			$twitter .= Game::twitterScore($team, $team_score, $opponent, $opponent_score);
		}

		$this->set(compact('team_score'));
		$this->set('twitter', addslashes($twitter));
	}

	public function TODOLATER_score_down() {
		$this->request->allowMethod('ajax');

		$id = $this->request->getQuery('game');
		if (!$id) {
			$this->set('message', __('Invalid game.'));
			return;
		}

		if (!$this->request->data['team_id']) {
			$this->set('message', __('Invalid team.'));
			return;
		}

		$submitter = $this->request->getQuery('team');

		// Lock all of this to prevent multiple simultaneous score updates
		// TODO: Handle both teams updating at the same time, one with details and one without
		$this->loadComponent('Lock');
		if (!$this->Lock->lock("live_scoring $id", null, null, false)) {
			$this->set('message', __('Someone else is currently updating the score for this game!\n\nIt\'s probably your opponent, try again right away.'));
			return;
		}

		$this->Game->contain([
			'Division' => [
				'League',
			],
			'HomeTeam',
			'AwayTeam',
			'ScoreEntry' => ['conditions' => ['ScoreEntry.team_id' => $submitter]],
			'ScoreDetail' => [
				'conditions' => [
					'ScoreDetail.team_id' => $this->request->data['team_id'],
					'ScoreDetail.points IS NOT' => null,
				],
				'order' => ['ScoreDetail.score_from' => 'DESC'],
			],
		]);
		$game = $this->Game->read(null, $id);
		if (!$game) {
			$this->set('message', __('Invalid game.'));
			return;
		}

		$this->Authorization->authorize(new ContextResource($game, ['team' => $submitter]));
		$this->Game->adjustEntryIndices($game);

		if ($this->request->data['team_id'] != $game->home_team_id && $this->request->data['team_id'] != $game->away_team_id) {
			$this->set('message', __('That team did not play in that game!'));
			return;
		}

		if ($game->isFinalized()) {
			$this->set('message', __('The score for that game has already been finalized.'));
			return;
		}

		// This will handle either the home team or a third-party submitting the score as "for"
		if (($submitter === null && $this->request->data['team_id'] == $game->home_team_id) || $submitter == $this->request->data['team_id']) {
			$team_score_field = 'score_for';
			$opponent_score_field = 'score_against';
		} else {
			$team_score_field = 'score_against';
			$opponent_score_field = 'score_for';
		}

		if (empty($game['ScoreEntry'])) {
			$this->set('message', __('You can\'t decrease the score below zero.'));
			return;
		}
		$entry = current($game['ScoreEntry']);
		if ($entry['status'] != 'in_progress') {
			$this->set('message', __('That team has already submitted a score for that game.'));
			return;
		}
		unset($entry['created']);
		unset($entry['modified']);
		unset($entry['person_id']);
		$team_score = $entry[$team_score_field];
		$opponent_score = $entry[$opponent_score_field];

		if ($team_score != $this->request->data['score_from']) {
			$this->set('message', __('The saved score does not match yours.\nSomeone else may have updated the score in the meantime.\n\nPlease refresh the page and try again.'));
			return;
		}

		$this->Configuration->loadAffiliate($game->division->league['affiliate_id']);

		$detail = current($game['ScoreDetail']);
		$team_score -= $detail['points'];
		$entry[$team_score_field] = $team_score;

		$transaction = new DatabaseTransaction($this->Game);

		if (!$this->Game->ScoreEntry->save($entry)) {
			$this->set('message', __('There was an error updating the score.\nPlease try again.'));
			return;
		} else {
			$this->Game->updateAll(['Game.modified' => 'NOW()'], ['Game.id' => $id]);
		}

		// Delete the matching score detail record, if it's got details from our team.
		// TODO: If the other team isn't keeping stats, there might be ScoreDetail records to remove when the score is finalized.
		if (($submitter === null || $detail->team_id == $submitter) && !$this->Game->ScoreDetail->delete($detail->id)) {
			$this->set('message', __('There was an error updating the box score.\nPlease try again.'));
			return;
		}
		$transaction->commit();

		// TODO: Would be nice if there was a Cache method that could help with this
		$cache_file = CACHE . 'queries' . DS . "cake_division_{$game->division_id}_standings";
		if (file_exists($cache_file) && time()-filemtime($cache_file) > 5 * MINUTE) {
			unlink($cache_file);
		}
		$cache_file = CACHE . 'queries' . DS . "cake_division_{$game->division_id}_schedule";
		if (file_exists($cache_file) && time()-filemtime($cache_file) > 5 * MINUTE) {
			unlink($cache_file);
		}

		if ($game->home_team_id == $this->request->data['team_id'] || $this->request->data['team_id'] === null) {
			$team = $game->home_team;
			$opponent = $game->away_team;
		} else if ($game->away_team_id == $this->request->data['team_id']) {
			$team = $game->away_team;
			$opponent = $game->home_team;
		}

		$twitter = "Score update #{$game->division['name']}: " . Game::twitterScore($team, $team_score, $opponent, $opponent_score);
		$this->set(compact('team_score'));
		$this->set('twitter', addslashes($twitter));
	}

	public function TODOLATER_timeout() {
		$this->request->allowMethod('ajax');

		$id = $this->request->getQuery('game');
		if (!$id) {
			$this->set('message', __('Invalid game.'));
			return;
		}

		if (!$this->request->data['team_id']) {
			$this->set('message', __('Invalid team.'));
			return;
		}

		$submitter = $this->request->getQuery('team');

		// Lock all of this to prevent multiple simultaneous score updates
		$this->loadComponent('Lock');
		if (!$this->Lock->lock("live_scoring $id", null, null, false)) {
			$this->set('message', __('Someone else is currently updating the score for this game!\n\nIt\'s probably your opponent, try again right away.'));
			return;
		}

		$this->Game->contain([
			'Division' => [
				'League',
			],
			'HomeTeam',
			'AwayTeam',
			'ScoreEntry' => ['conditions' => ['ScoreEntry.team_id' => $submitter]],
			'ScoreDetail' => ['conditions' => [
				'ScoreDetail.team_id' => $this->request->data['team_id'],
				'ScoreDetail.play' => 'Timeout',
			]],
		]);
		$game = $this->Game->read(null, $id);
		if (!$game) {
			$this->set('message', __('Invalid game.'));
			return;
		}

		$this->Authorization->authorize(new ContextResource($game, ['team' => $submitter]));
		$this->Game->adjustEntryIndices($game);

		if ($this->request->data['team_id'] != $game->home_team_id && $this->request->data['team_id'] != $game->away_team_id) {
			$this->set('message', __('That team did not play in that game!'));
			return;
		}

		if ($game->isFinalized()) {
			$this->set('message', __('The score for that game has already been finalized.'));
			return;
		}

		// This will handle either the home team or a third-party submitting the timeout as "for"
		if (($submitter === null && $this->request->data['team_id'] == $game->home_team_id) || $submitter == $this->request->data['team_id']) {
			$team_score_field = 'score_for';
			$opponent_score_field = 'score_against';
		} else {
			$team_score_field = 'score_against';
			$opponent_score_field = 'score_for';
		}

		if (empty($game['ScoreEntry'])) {
			$team_score = $opponent_score = 0;
		} else {
			$entry = current($game['ScoreEntry']);
			if ($entry['status'] != 'in_progress') {
				$this->set('message', __('That team has already submitted a score for that game.'));
				return;
			}
			$team_score = $entry[$team_score_field];
			$opponent_score = $entry[$opponent_score_field];
		}
		if ($team_score != $this->request->data['score_from']) {
			$this->set('message', __('The saved score does not match yours.\nSomeone else may have updated the score in the meantime.\n\nPlease refresh the page and try again.'));
			return;
		}

		$this->Configuration->loadAffiliate($game->division->league['affiliate_id']);

		if ($game->home_team_id == $this->request->data['team_id'] || $this->request->data['team_id'] === null) {
			$team = $game->home_team;
			$opponent = $game->away_team;
		} else if ($game->away_team_id == $this->request->data['team_id']) {
			$team = $game->away_team;
			$opponent = $game->home_team;
		}

		$twitter = "Game update #{$game->division['name']}: timeout called by " . $team->twitterName() . ' with the score ' . Game::twitterScore($team, $team_score, $opponent, $opponent_score);

		// Check if there's already a score detail record from the other team that this is likely a duplicate of.
		// If so, we want to disregard it.
		foreach ($game->score_details as $detail) {
			if ($detail->play == 'Timeout' &&
				$detail->created_team_id != $submitter &&
				$detail->score_from == $this->request->data['score_from'] &&
				$detail->created >= FrozenTime::now()->subMinutes(2))
			{
				$this->set('taken', count($game->score_details));
				$this->set('twitter', addslashes($twitter));
				return;
			}
		}

		if (!$this->Game->ScoreDetail->save(array_merge($this->request->data, [
				'game_id' => $id,
				'created_team_id' => $submitter,
				'play' => 'Timeout',
		])))
		{
			$this->set('message', __('There was an error updating the box score.\nPlease try again.'));
			return;
		}

		$this->set('taken', count($game['ScoreDetail']) + 1);
		$this->set('twitter', addslashes($twitter));
	}

	public function TODOLATER_play() {
		$this->request->allowMethod('ajax');

		$id = $this->request->getQuery('game');
		if (!$id) {
			$this->set('message', __('Invalid game.'));
			return;
		}

		if (!$this->request->data['team_id']) {
			$this->set('message', __('Invalid team.'));
			return;
		}

		$submitter = $this->request->getQuery('team');

		// Lock all of this to prevent multiple simultaneous score updates
		$this->loadComponent('Lock');
		if (!$this->Lock->lock("live_scoring $id", null, null, false)) {
			$this->set('message', __('Someone else is currently updating the score for this game!\n\nIt\'s probably your opponent, try again right away.'));
			return;
		}

		$this->Game->contain([
			'Division' => [
				'League',
			],
			'HomeTeam',
			'AwayTeam',
			'ScoreEntry' => ['conditions' => ['ScoreEntry.team_id' => $submitter]],
			'ScoreDetail',
		]);
		$game = $this->Game->read(null, $id);
		if (!$game) {
			$this->set('message', __('Invalid game.'));
			return;
		}

		$this->Authorization->authorize(new ContextResource($game, ['team' => $submitter]));
		$this->Game->adjustEntryIndices($game);

		if ($this->request->data['team_id'] != $game->home_team_id && $this->request->data['team_id'] != $game->away_team_id) {
			$this->set('message', __('That team did not play in that game!'));
			return;
		}

		if ($game->isFinalized()) {
			$this->set('message', __('The score for that game has already been finalized.'));
			return;
		}

		// This will handle either the home team or a third-party submitting the score as "for"
		if (($submitter === null && $this->request->data['team_id'] == $game->home_team_id) || $submitter == $this->request->data['team_id']) {
			$team_score_field = 'score_for';
			$opponent_score_field = 'score_against';
		} else {
			$team_score_field = 'score_against';
			$opponent_score_field = 'score_for';
		}

		if (empty($game['ScoreEntry'])) {
			$team_score = $opponent_score = 0;
		} else {
			$entry = current($game['ScoreEntry']);
			if ($entry['status'] != 'in_progress') {
				$this->set('message', __('That team has already submitted a score for that game.'));
				return;
			}
			$team_score = $entry[$team_score_field];
			$opponent_score = $entry[$opponent_score_field];
		}

		$this->Configuration->loadAffiliate($game->division->league['affiliate_id']);
		$sport_obj = $this->moduleRegistry->load("Sport:{$game->division->league->sport}");

		if (empty($this->request->data['play'])) {
			$this->set('message', __('You must indicate the play so that the box score will be accurate.'));
			return;
		}
		if ($this->request->data['play'] != 'Start' && !Configure::read("sports.{$game->division->league->sport}.other_options.{$this->request->data['play']}")) {
			$this->set('message', __('Invalid play!'));
			return;
		}

		if ($game->home_team_id == $this->request->data['team_id'] || $this->request->data['team_id'] === null) {
			$team = $game->home_team;
			$opponent = $game->away_team;
		} else if ($game->away_team_id == $this->request->data['team_id']) {
			$team = $game->away_team;
			$opponent = $game->home_team;
		}

		$valid = $sport_obj->validatePlay($this->request->data['team_id'], $this->request->data['play'], $this->request->data['score_from'], $game['ScoreDetail']);
		if ($valid !== true) {
			$this->set('message', addslashes($valid));
			$this->set('twitter', '');
			return;
		} else if ($this->request->data['play'] == 'Start') {
			$this->set('message', __('Game timer initialized.'));
			$twitter = __('Game update #{0}:', $game->division->name) . ' ';
			$text = Configure::read("sports.{$game->division->league->sport}.start.twitter");
			if ($text) {
				$twitter .= __($text, $team->twitterName(), $opponent->twitterName()) . ' ' . __('to start the game');
			} else {
				$twitter .= __('Game started');
			}
			$twitter .= '.';
			$this->set('twitter', addslashes($twitter));
		} else {
			$this->set('message', Configure::read("sports.{$game->division->league->sport}.other_options.{$this->request->data['play']}") . ' ' . __('recorded'));
			$twitter = "Game update #{$game->division['name']}: " . $team->twitterName() . ' ' . strtolower(Configure::read("sports.{$game->division->league->sport}.other_options.{$this->request->data['play']}")) . ' vs ' . $opponent->twitterName();
			$this->set('twitter', addslashes($twitter));
		}

		// Check if there's already a score detail record from the other team that this is likely a duplicate of.
		// If so, we want to disregard it.
		foreach ($game->score_details as $detail) {
			if ($detail->play == $this->request->data['play'] &&
				$detail->team_id == $this->request->data['team_id'] &&
				$detail->created_team_id != $submitter &&
				$detail->score_from == $this->request->data['score_from'] &&
				$detail->created >= FrozenTime::now()->subMinutes(2))
			{
				return;
			}
		}

		if (!$this->Game->ScoreDetail->save(array_merge($this->request->data, [
				'game_id' => $id,
				'created_team_id' => $submitter,
		])))
		{
			$this->set('message', __('There was an error updating the box score.\nPlease try again.'));
			return;
		}
	}

	public function TODOLATER_tweet() {
		$this->request->allowMethod('ajax');

		if (!App::import('Lib', 'twitter_api_exchange')) {
			$this->set('message', __('Failed to load the {0} library! Contact your system administrator.', 'Twitter API Exchange'));
			return;
		}
		$this->Game->HomeTeam->Person->contain();
		$person = $this->Game->HomeTeam->Person->read(['twitter_token', 'twitter_secret'], $this->UserCache->currentId());
		if (empty($person['Person']['twitter_token']) || empty($person['Person']['twitter_secret'])) {
			$this->set('message', __('You have not authorized this site to tweet on your behalf. Configure this in the Profile Preferences page.'));
			return;
		}
		$settings = [
				'consumer_key' => Configure::read('twitter.consumer_key'),
				'consumer_secret' => Configure::read('twitter.consumer_secret'),
				'oauth_access_token' => $person['Person']['twitter_token'],
				'oauth_access_token_secret' => $person['Person']['twitter_secret'],
		];
		$url = 'https://api.twitter.com/1.1/statuses/update.json';
		$postfields = [
				'status' => $this->request->data['Twitter']['message'],
				'lat' => $this->request->data['Twitter']['lat'],
				'long' => $this->request->data['Twitter']['long'],
		];
		$twitter = new TwitterAPIExchange($settings);
		$response = json_decode($twitter->buildOauth($url, 'POST')->setPostfields($postfields)->performRequest());
		if (!empty($response->id_str)) {
			$this->set('message', __('Your message has been tweeted.'));
		} else {
			$this->set('message', __('Failed to send the tweet.') . ' ' . $response->errors[0]->message);
		}
	}

	public function submit_score() {
		$id = $this->request->getQuery('game');
		$team_id = $this->request->getQuery('team');

		if (Configure::read('scoring.allstars') || Configure::read('scoring.most_spirited')) {
			// We need roster details for potential allstar nominations.
			// TODO: This isn't ideal.
			$roles = array_unique(array_merge(Configure::read('privileged_roster_roles'), Configure::read('extended_playing_roster_roles')));
		} else {
			$roles = Configure::read('privileged_roster_roles');
		}

		try {
			$game = $this->Games->get($id, [
				'contain' => [
					'Divisions' => [
						'People' => [Configure::read('Security.authModel')],
						'Leagues',
					],
					'GameSlots' => ['Fields' => ['Facilities']],
					'ScoreEntries' => [
						'queryBuilder' => function (Query $q) use ($team_id) {
							return $q->where(['ScoreEntries.team_id' => $team_id]);
						},
						'People',
						'Allstars' => [
							'queryBuilder' => function (Query $q) use ($team_id) {
								return $q->order(['Allstars.' . Configure::read('gender.column') => Configure::read('gender.order')]);
							},
						],
					],
					'SpiritEntries' => [
						'queryBuilder' => function (Query $q) use ($team_id) {
							return $q->where(['SpiritEntries.created_team_id' => $team_id]);
						},
						'MostSpirited',
					],
					'Incidents' => [
						'queryBuilder' => function (Query $q) use ($team_id) {
							return $q->where(['Incidents.team_id' => $team_id]);
						},
					],
					'HomeTeam' => [
						'People' => [
							'queryBuilder' => function (Query $q) use ($roles) {
								return $q->where([
									'TeamsPeople.role IN' => $roles,
									'TeamsPeople.status' => ROSTER_APPROVED,
								]);
							},
							Configure::read('Security.authModel'),
						],
					],
					'HomePoolTeam' => ['DependencyPool'],
					'AwayTeam' => [
						'People' => [
							Configure::read('Security.authModel'),
							'queryBuilder' => function (Query $q) use ($roles) {
								return $q->where([
									'TeamsPeople.role IN' => $roles,
									'TeamsPeople.status' => ROSTER_APPROVED,
								]);
							},
						],
					],
					'AwayPoolTeam' => ['DependencyPool'],
				]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid game.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid game.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($game);

		if (empty($game->home_team_id) || empty($game->away_team_id)) {
			$this->Flash->info(__('The opponent for that game has not been determined, so a score cannot yet be submitted.'));
			return $this->redirect(['action' => 'view', 'game' => $id]);
		}

		if ($team_id == $game->home_team_id) {
			$team = $game->home_team;
			$opponent = $game->away_team;
		} else if ($team_id == $game->away_team_id) {
			$team = $game->away_team;
			$opponent = $game->home_team;
		} else {
			$this->Flash->info(__('That team is not playing in this game.'));
			return $this->redirect(['action' => 'view', 'game' => $id]);
		}

		$this->Authorization->authorize($team);

		if ($game->isFinalized()) {
			$this->Flash->info(__('The score for that game has already been finalized.'));
			return $this->redirect(['action' => 'view', 'game' => $id]);
		}

		$this->Configuration->loadAffiliate($game->division->league->affiliate_id);

		if ($game->game_slot->end_time->subHour()->isFuture()) {
			$this->Flash->info(__('That game has not yet occurred!'));
			return $this->redirect(['action' => 'view', 'game' => $id]);
		}

		$game->readDependencies();

		// Initialize various checkboxes that aren't part of the saved record
		// TODO: These should be accessors?
		$game->has_incident = !empty($game->incidents);
		if (!empty($game->score_entries)) {
			$game->has_allstars = !empty($game->score_entries[0]->allstars);
		}
		if (!empty($game->spirit_entries)) {
			$game->has_most_spirited = !empty($game->spirit_entries[0]->most_spirited_id);
		}

		// Spirit score entry validation comes from the spirit module
		if ($game->division->league->hasSpirit()) {
			$spirit_obj = $game->division->league->hasSpirit() ? $this->moduleRegistry->load("Spirit:{$game->division->league->sotg_questions}") : null;
			$this->Games->SpiritEntries->addValidation($spirit_obj, $game->division->league);
		}

		$opponent_score = $game->getScoreEntry($opponent->id);

		if ($this->request->is(['patch', 'post', 'put'])) {
			// TODO: Move these checks to rules?
			if (!empty($game->score_entries) && !array_key_exists('id', $this->request->data['score_entries'][0])) {
				$this->Flash->warning(__('There is already a score submitted by your team for this game. To update this, use the "{0}" link.',
					__('Edit Score')));
				return $this->redirect(['action' => 'view', 'game' => $id]);
			}

			if (!empty($game->spirit_entries) && !array_key_exists('id', $this->request->data['spirit_entries'][0])) {
				$this->Flash->warning(__('There is already a spirit score submitted by your team for this game. To update this, use the "{0}" link.',
					__('Edit Score')));
				return $this->redirect(['action' => 'view', 'game' => $id]);
			}

			$game = $this->Games->patchEntity($game, $this->request->data, [
				'associated' => ['ScoreEntries', 'ScoreEntries.Allstars', 'SpiritEntries', 'Incidents'],
			]);

			// TODO: This is a bit of a hack. It should be handled by the Footprint plugin, except that
			// it doesn't run until after the beforeSave for the game entity has run, and that looks at
			// person_id fields to decide which score entries are "real" and which are not (e.g. a score
			// entry created by the game edit when one team didn't submit a score).
			$game->score_entries[0]->person_id = $this->UserCache->currentId();

			// We don't actually want to update the "modified" column in the games table here
			if ($this->Games->hasBehavior('Timestamp')) {
				$this->Games->removeBehavior('Timestamp');
			}

			if ($this->Games->save($game, ['game' => $game, 'team_id' => $team_id])) {
				if ($game->division->league->hasStats() && $this->request->data['collect_stats']) {
					return $this->redirect(['action' => 'submit_stats', 'game' => $id, 'team' => $team_id]);
				} else {
					return $this->redirect('/');
				}
			} else {
				$this->Flash->warning(__('The game results could not be saved. Please correct the errors below and try again.'));
			}
		} else {
			// Include any parameters from emailed confirmation link
			foreach (['status', 'score_for', 'score_against', 'home_carbon_flip'] as $field) {
				if ($this->request->getQuery($field)) {
					if (empty($game->score_entries)) {
						$game->score_entries = [$this->Games->ScoreEntries->newEntity()];
					}
					$game->score_entries[0]->$field = $this->request->getQuery($field);
				}
			}
		}

		$this->set(compact('game', 'team_id', 'opponent_score', 'spirit_obj'));
	}

	public function submit_stats() {
		$id = $this->request->getQuery('game');
		try {
			$game = $this->Games->get($id, [
				'contain' => [
					'Divisions' => [
						'Leagues' => [
							'StatTypes' => [
								'queryBuilder' => function (Query $q) {
									return $q->where(['type' => 'entered']);
								},
							],
						],
						'Days',
					],
					'GameSlots' => ['Fields' => ['Facilities']],
					'HomeTeam' => ['People'],
					'AwayTeam' => ['People'],
					'ScoreEntries',
					'ScoreDetails' => [
						'ScoreDetailStats',
					],
					'Stats',
				]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid game.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid game.'));
			return $this->redirect('/');
		}

		$this->Games->adjustEntryIndices($game);
		$team_id = $this->request->getQuery('team');
		$this->Authorization->authorize(new ContextResource($game, ['team_id' => $team_id, 'league' => $game->division->league, 'stat_types' => $game->division->league->stat_types]));
		$this->Configuration->loadAffiliate($game->division->league->affiliate_id);
		$sport_obj = $this->moduleRegistry->load("Sport:{$game->division->league->sport}");

		if ($this->request->is(['patch', 'post', 'put'])) {
			// Remove any empty or invalid stats. We DON'T remove '0' stats, as that's still a stat.
			$entry_stats = collection($game->division->league->stat_types)->extract('id')->toArray();
			$this->request->data['stats'] = collection($this->request->data['stats'])->filter(function ($datum) use ($entry_stats) {
				return ($datum['value'] !== '' && in_array($datum['stat_type_id'], $entry_stats));
			})->toArray();

			// TODOLATER: Eliminate places like this, where more than two tables are chained, and use TableRegistry instead, if it saves a constructor call
			$calc_stats = $this->Games->Divisions->Leagues->StatTypes->find()
				->where([
					'StatTypes.type' => 'game_calc',
					'StatTypes.sport' => $game->division->league->sport,
				]);
			$calc_stat_ids = $calc_stats->extract('id')->toArray();

			// Locate existing records that we want to delete
			$to_delete = collection($game->stats)
				->filter(function ($stat) use ($team_id, $calc_stat_ids) {
					return (!$team_id || $stat->team_id == $team_id) || in_array($stat->stat_type_id, $calc_stat_ids);
				})
				->extract('id')->toArray(false);
			$game = $this->Games->patchEntity($game, $this->request->data, ['associated' => ['Stats']]);
			if (!empty($to_delete)) {
				$to_delete = array_diff($to_delete, collection($game->stats)->reject(function ($stat) {
					return empty($stat->id);
				})->extract('id')->toArray(false));
			}

			// Add calculated stats. We have already arranged to delete any prior calculated stats.
			foreach ($calc_stats as $stat_type) {
				$func = "{$stat_type->handler}_game";
				if (method_exists($sport_obj, $func)) {
					$sport_obj->$func($stat_type, $game);
				} else {
					trigger_error("Game stat handler {$stat_type->handler} was not found in the {$game->division->league->sport} module!", E_USER_ERROR);
				}
			}

			// We don't actually want to update the "modified" column in the games table here
			if ($this->Games->hasBehavior('Timestamp')) {
				$this->Games->removeBehavior('Timestamp');
			}

			if ($this->Games->connection()->transactional(function () use ($game, $to_delete) {
				if (!empty($to_delete)) {
					if (!$this->Games->Stats->deleteAll(['id IN' => $to_delete])) {
						$this->Flash->warning(__('Failed to delete previously saved stats.'));
						return false;
					}
					if (empty($game->stats)) {
						$this->Flash->success(__('The previously saved stats have been removed.'));
					}
				}

				if (!empty($game->stats)) {
					if ($this->Games->save($game)) {
						$this->Flash->success(__('The stats have been saved.'));
					} else {
						$this->Flash->warning(__('The stats could not be saved. Please correct the errors below and try again.'));
						return false;
					}
				}

				return true;
			})) {
				if ($team_id) {
					Cache::delete("team/{$team_id}/stats", 'long_term');
				} else {
					Cache::delete("team/{$game->home_team_id}/stats", 'long_term');
					Cache::delete("team/{$game->away_team_id}/stats", 'long_term');
				}
				$this->Games->Divisions->clearCache($game->division, ['stats']);

				return $this->redirect(['action' => 'view', 'game' => $id]);
			}

			if (empty($this->request->data['stats'])) {
				$this->Flash->info(__('You did not submit any stats. You can return to complete this at any time.'));
				return $this->redirect(['action' => 'view', 'game' => $id]);
			}
		} else {
			// Extract counts of stats per player from the live scoring
			$live_stats = [];
			foreach ($game->score_details as $detail) {
				foreach ($detail->score_detail_stats as $stat) {
					$key = "{$stat->person_id}:{$stat->stat_type_id}";
					if (!array_key_exists($key, $live_stats)) {
						$live_stats[$key] = $this->Games->Stats->newEntity([
							'game_id' => $id,
							'team_id' => $detail->team_id,
							'person_id' => $stat->person_id,
							'stat_type_id' => $stat->stat_type_id,
							'value' => 1,
						]);
					} else {
						++ $live_stats[$key]->value;
					}
				}
			}
			// It's possible that there's already a saved value for a stat we're calculating here. In that case,
			// the saved one will be first in the resulting merged array, and we use firstMatch in the view, so
			// the saved one will be picked up instead of the calculated one. This is faster and easier than
			// checking for existence of those saved stats.
			$game->stats = array_merge($game->stats, array_values($live_stats));
		}

		$days = collection($game->division->days)->extract('id')->toArray();
		if ($team_id) {
			$attendance = $this->Games->readAttendance($team_id, $days, $id, null, true);
			usort($attendance->people, ['App\Model\Table\PeopleTable', 'comparePerson']);
		} else {
			$home_attendance = $this->Games->readAttendance($game->home_team_id, $days, $id, null, true);
			usort($home_attendance->people, ['App\Model\Table\PeopleTable', 'comparePerson']);
			$away_attendance = $this->Games->readAttendance($game->away_team_id, $days, $id, null, true);
			usort($away_attendance->people, ['App\Model\Table\PeopleTable', 'comparePerson']);
		}

		$this->set(compact('game', 'team_id', 'attendance', 'home_attendance', 'away_attendance', 'sport_obj'));
	}

	public function stats() {
		$id = $this->request->getQuery('game');
		$team_id = $this->request->getQuery('team');
		try {
			$game = $this->Games->get($id, [
				'contain' => [
					'Divisions' => [
						'Leagues' => [
							'StatTypes' => [
								'queryBuilder' => function (Query $q) {
									return $q->where(['StatTypes.type IN' => Configure::read('stat_types.game')]);
								},
							],
						],
					],
					'HomeTeam',
					'AwayTeam',
					'GameSlots' => ['Fields' => ['Facilities']],
					'ScoreEntries',
					'Stats' => [
						'queryBuilder' => function (Query $q) use ($team_id) {
							if ($team_id) {
								return $q->where(['Stats.team_id' => $team_id]);
							}

							return $q;
						},
					],
				]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid game.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid game.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize(new ContextResource($game, ['team_id' => $team_id, 'league' => $game->division->league, 'stat_types' => $game->division->league->stat_types]));
		$this->Configuration->loadAffiliate($game->division->league->affiliate_id);
		$sport_obj = $this->moduleRegistry->load("Sport:{$game->division->league->sport}");

		// Team rosters may have changed since the game was played, so use the list of people with stats instead
		foreach (['home_team', 'away_team'] as $key) {
			$people = array_unique(collection($game->stats)->match(['team_id' => $game->$key->id])->extract('person_id')->toArray());
			if (!empty($people)) {
				$game->$key->people = $this->Games->HomeTeam->People->find()
					->where(['id IN' => $people])
					->toArray();
				usort($game->$key->people, ['App\Model\Table\PeopleTable', 'comparePerson']);
			} else {
				$game->$key->people = [];
			}
		}

		if ($game->home_team_id == $team_id || $team_id === null) {
			$team = $game->home_team;
			$opponent = $game->away_team;
		} else {
			$team = $game->away_team;
			$opponent = $game->home_team;
		}

		$this->set(compact('game', 'team_id', 'team', 'opponent', 'sport_obj'));
		if ($this->request->is('csv')) {
			$this->response->download("Stats - Game {$game->id}.csv");
		}
	}

	public function future($limit = null) {
		$this->Authorization->authorize($this->UserCache->read('Person'));
		$team_ids = $this->UserCache->read('TeamIDs');
		if (empty($team_ids)) {
			$this->set([
				'games' => [],
			]);
			return;
		}

		if ($limit === null) {
			$limit = max(4, ceil(count(array_unique($team_ids)) * 1.5));
		}

		$games = $this->Games->find('schedule', ['teams' => $team_ids])
			->contain([
				'Divisions' => ['Days', 'Leagues'],
			])
			->where([
				'Games.published' => true,
				'GameSlots.game_date >=' => FrozenDate::now(),
				'GameSlots.game_date <' => FrozenDate::now()->addWeeks(2),
			])
			->order(['GameSlots.game_date', 'GameSlots.game_start'])
			->limit($limit)
			->toArray();

		$this->set(compact('games'));
		$this->set('_serialize', ['games']);
	}

	function results() {
		$games = $this->Games->find()
			->contain([
				'Divisions' => ['Leagues'],
				'GameSlots' => ['Fields' => ['Facilities']],
				'ScoreEntries',
				'HomeTeam',
				'HomePoolTeam' => ['DependencyPool'],
				'AwayTeam',
				'AwayPoolTeam' => ['DependencyPool'],
			])
			->where([
				'Games.published' => true,
				'OR' => [
					'GameSlots.game_date <' => FrozenDate::now(),
					[
						'GameSlots.game_date' => FrozenDate::now(),
						'GameSlots.game_start <' => FrozenTime::now()->format('H:i:s'),
					],
				],
			])
			->order(['Games.modified' => 'DESC'])
			->limit(10);

		$this->set(compact('games'));
	}

}
