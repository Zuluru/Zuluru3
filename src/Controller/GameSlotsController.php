<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Database\Query;
use Cake\I18n\FrozenDate;
use Cake\ORM\TableRegistry;

/**
 * GameSlots Controller
 *
 * @property \App\Model\Table\GameSlotsTable $GameSlots
 */
class GameSlotsController extends AppController {

	/**
	 * isAuthorized method
	 *
	 * @return bool true if access allowed
	 */
	public function isAuthorized() {
		try {
			if ($this->UserCache->read('Person.status') == 'locked') {
				return false;
			}

			if (Configure::read('Perm.is_manager')) {
				// Managers can perform these operations in affiliates they manage
				if (in_array($this->request->params['action'], [
					'add',
				])) {
					// If an affiliate id is specified, check if we're a manager of that affiliate
					$affiliate = $this->request->query('affiliate');
					if ($affiliate && in_array($affiliate, $this->UserCache->read('ManagedAffiliateIDs'))) {
						return true;
					} else {
						Configure::write('Perm.is_manager', false);
					}

					// If a field id is specified, check if we're a manager of that field
					$field = $this->request->query('field');
					if ($field) {
						if (in_array($this->GameSlots->Fields->affiliate($field), $this->UserCache->read('ManagedAffiliateIDs'))) {
							return true;
						} else {
							Configure::write('Perm.is_manager', false);
						}
					}
				}

				// Managers can perform these operations in affiliates they manage
				if (in_array($this->request->params['action'], [
					'edit',
					'view',
					'delete',
					'submit_score',
				])) {
					// If a game slot id is specified, check if we're a manager of that game slot's affiliate
					$slot = $this->request->query('slot');
					if ($slot) {
						if (in_array($this->GameSlots->affiliate($slot), $this->UserCache->read('ManagedAffiliateIDs'))) {
							return true;
						} else {
							Configure::write('Perm.is_manager', false);
						}
					}
				}
			}
		} catch (RecordNotFoundException $ex) {
		} catch (InvalidPrimaryKeyException $ex) {
		}

		return false;
	}

	// TODO: Eliminate this if we can find a way around black-holing caused by Ajax field adds
	public function beforeFilter(\Cake\Event\Event $event) {
		parent::beforeFilter($event);
		$this->Security->config('unlockedActions', ['add']);
	}

	/**
	 * View method
	 *
	 * @return void|\Cake\Network\Response
	 */
	public function view() {
		$id = $this->request->query('slot');
		try {
			$game_slot = $this->GameSlots->get($id, [
				'contain' => [
					'Games' => [
						'HomeTeam',
						'HomePoolTeam' => ['DependencyPool'],
						'AwayTeam',
						'AwayPoolTeam' => ['DependencyPool'],
						'Divisions' => ['Leagues'],
					],
					'Fields' => ['Facilities' => ['Regions']],
					'Divisions' => ['Leagues'],
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid game slot.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid game slot.'));
			return $this->redirect('/');
		}
		$this->Configuration->loadAffiliate($game_slot->field->facility->region->affiliate_id);

		$this->set(compact('game_slot'));
		$this->set('_serialize', true);
	}

	/**
	 * Add method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$field = $this->request->query('field');
		if (Configure::read('feature.affiliates')) {
			$affiliate = $this->request->query('affiliate');
			if (!$affiliate && !$field) {
				$this->Flash->info(__('Invalid affiliate.'));
				return $this->redirect('/');
			}
		} else {
			$affiliate = 1;
		}

		// The entity should allow the extra fields that are used for bulk creation
		$game_slot = $this->GameSlots->newEntity();
		$game_slot->accessible(['sport', 'length', 'buffer', 'weeks', 'fields', 'facilities', 'game_slots'], true);

		if ($field) {
			try {
				$field = $this->GameSlots->Fields->get($field, [
					'contain' => ['Facilities' => ['Regions']],
				]);
			} catch (RecordNotFoundException $ex) {
				$this->Flash->info(__('Invalid {0}.', __(Configure::read('UI.field'))));
				return $this->redirect('/');
			} catch (InvalidPrimaryKeyException $ex) {
				$this->Flash->info(__('Invalid {0}.', __(Configure::read('UI.field'))));
				return $this->redirect('/');
			}
			$affiliate = $field->facility->region->affiliate_id;
			$this->set(compact('field'));
		} else {
			$regions = $this->GameSlots->Fields->Facilities->Regions->find()
				->contain([
					'Facilities' => [
						'queryBuilder' => function (Query $q) {
							return $q->where(['Facilities.is_open' => true])->order('Facilities.name');
						},
						'Fields' => [
							'queryBuilder' => function (Query $q) {
								return $q->where(['Fields.is_open' => true])->order('Fields.num');
							},
						],
					],
				])
				->where(['Regions.affiliate_id' => $affiliate])
				->order('Regions.id')
				->toArray();
			$this->set(compact('regions'));
		}
		$this->Configuration->loadAffiliate($affiliate);

		if ($this->request->is(['patch', 'post', 'put'])) {
			if ($field && $this->request->data['length'] == 0 && $this->request->data['weeks'] == 1) {
				// Deal with a single game slot being added
				$game_slot = $this->GameSlots->newEntity(array_merge($this->request->data, [
					'field_id' => $field->id,
				]), ['associated' => 'Divisions', 'divisions' => true]);

				// Try to save
				if ($this->GameSlots->save($game_slot, ['single' => true])) {
					$this->Flash->success(__('The game slot has been saved.'));
					// We intentionally don't redirect here, leaving the user back on the
					// original "add" form, with the last game date/start/end/weeks options
					// already selected.
				} else {
					$this->Flash->warning(__('The game slot could not be saved. Please correct the errors below and try again.'));
				}
			} else {
				// Find the list of holidays to avoid
				$this->loadModel('Holidays');
				$holidays = $this->Holidays->find()
					->where(['affiliate_id' => $affiliate])
					->combine('date_string', 'name')
					->toArray();

				$game_slot = $this->GameSlots->patchEntity($game_slot, $this->request->data, [
					'validate' => 'bulk',
					'associated' => ['Divisions'],
					'accessibleFields' => ['length' => true, 'buffer' => true],
					'divisions' => true,
				]);
				// We have to do complete rule checking here, not just validation, so that we can
				// report any errors before proceeding to the confirmation page.
				$this->GameSlots->checkRules($game_slot);

				// Build the list of times to re-use
				$times = [];
				// Create games from start time through end time
				$start = $game_slot->start_time;
				if ($game_slot->length > 0) {
					$end = $game_slot->end_time;
					while ($start->addMinutes($game_slot->length) <= $end) {
						$times[] = $start;
						$start = $start->addMinutes($game_slot->length);
					}
				} else {
					$times[] = $start;
				}

				// Build the list of dates to re-use
				$weeks = $skipped = [];
				$date = $game_slot->game_date;
				while (count($weeks) < $game_slot->weeks) {
					$key = $date->toDateString();
					if (!array_key_exists($key, $holidays)) {
						$weeks[] = $date;
					} else {
						$skipped[$key] = $holidays[$key];
					}
					$date = $date->addWeek();
				}

				if ($game_slot->errors()) {
					$this->Flash->warning(__('The game slots could not be saved. Please correct any issues below and try again.'));

					// Some validation errors may need to be displayed as flash messages
					$errors = $game_slot->errors('fields');
					if (!empty($errors)) {
						$this->Flash->info(current($errors));
					}
				} else if (array_key_exists('confirm', $this->request->data)) {
					if (empty($this->request->data['game_slots'])) {
						$this->Flash->info(__('You must select at least one game slot!'));
						$this->viewBuilder()->template('confirm');
					} else {
						if ($this->GameSlots->connection()->transactional(function () use ($game_slot, $holidays, $times, $weeks) {
							$division_ids = collection($game_slot->divisions)->extract('id')->toArray();

							foreach ($this->request->data['game_slots'] as $field_id => $field_dates) {
								foreach ($field_dates as $date => $field_times) {
									$week = $weeks[$date];
									foreach (array_keys($field_times) as $time) {
										$game_start = $times[$time];
										if ($game_slot->length > 0) {
											$game_end = $game_start->addMinutes($game_slot->length - $game_slot->buffer);
										} else if (empty($game_slot->game_end)) {
											$game_end = null;
										} else {
											$game_end = $game_slot->game_end;
										}

										$slot = $this->GameSlots->newEntity(array_merge($this->request->data, [
											'field_id' => $field_id,
											'game_date' => $week,
											'game_start' => $game_start,
											'game_end' => $game_end,
											'divisions' => ['_ids' => $division_ids],
										]), ['associated' => 'Divisions']);

										// Try to save
										if (!$this->GameSlots->save($slot)) {
											$this->Flash->warning(__('The game slots could not be saved. Please correct any issues below and try again.'));
											$this->Flash->info(implode(' ', \Cake\Utility\Hash::flatten($slot->errors())));
											return false;
										}
									}
								}
							}

							return true;
						})
						) {
							$this->Flash->success(__('The game slots have been saved.'));
							// We intentionally don't redirect here, leaving the user back on the
							// original "add" form, with the last game date/start/end/weeks options
							// already selected.
						} else {
							$this->viewBuilder()->template('add');
						}
					}
				} else {
					$this->viewBuilder()->template('confirm');
				}
			}
		} else {
			// Not a post, set some defaults
			if (isset($field)) {
				$sport = $field->sport;
			} else {
				$sport = current(array_keys(Configure::read('options.sport')));
			}
			$game_slot = $this->GameSlots->patchEntity($game_slot, [
				'game_date' => FrozenDate::now(),
				'sport' => $sport,
			], ['validate' => 'common']);
		}

		$divisions_table = TableRegistry::get('Divisions');
		$divisions = $divisions_table->find('open')
			->find('date', ['date' => $game_slot->game_date])
			->contain(['Leagues'])
			->where(['Leagues.affiliate_id' => $affiliate])
			->where(['Leagues.sport' => ($field ? $field->sport : $game_slot->sport)])
			->order(['Divisions.id'])
			->combine('id', 'full_league_name')
			->toArray();

		$this->set(compact('game_slot', 'affiliate', 'divisions', 'holidays', 'times', 'weeks', 'skipped'));
		$this->set('days', $divisions_table->Days->find('list'));

		$this->set('_serialize', true);
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful edit, renders view otherwise.
	 */
	public function edit() {
		$id = $this->request->query('slot');
		try {
			$game_slot = $this->GameSlots->get($id, [
				'contain' => ['Divisions', 'Fields']
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid game slot.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid game slot.'));
			return $this->redirect('/');
		}
		$affiliate = $this->GameSlots->affiliate($id);
		$this->Configuration->loadAffiliate($affiliate);

		if ($this->request->is(['patch', 'post', 'put'])) {
			$game_slot = $this->GameSlots->patchEntity($game_slot, $this->request->data, [
				'associated' => ['Divisions'],
				'divisions' => true,
			]);

			if ($this->GameSlots->save($game_slot, ['single' => true])) {
				$this->Flash->success(__('The game slot has been saved.'));
				return $this->redirect(['action' => 'view', 'slot' => $id]);
			} else {
				$this->Flash->warning(__('The game slot could not be saved. Please correct the errors below and try again.'));
			}
		}

		$divisions = $this->GameSlots->Games->Divisions->find('open')
			->find('date', ['date' => $game_slot->game_date])
			->contain(['Leagues'])
			->where(['Leagues.affiliate_id' => $affiliate])
			->where(['Leagues.sport' => $this->GameSlots->sport($id)])
			->order(['Divisions.id'])
			->combine('id', 'full_league_name')
			->toArray();

		$this->set(compact('game_slot', 'affiliate', 'divisions'));

		$this->set('_serialize', true);
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Network\Response Redirects to index.
	 */
	public function delete() {
		$this->request->allowMethod(['post', 'delete']);

		try {
			$game_slot = $this->GameSlots->get($this->request->query('slot'));
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid game slot.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid game slot.'));
			return $this->redirect('/');
		}

		if ($this->GameSlots->delete($game_slot)) {
			$this->Flash->success(__('The game slot has been deleted.'));
		} else if ($game_slot->errors('delete')) {
			$this->Flash->warning(current($game_slot->errors('delete')));
		} else {
			$this->Flash->warning(__('The game slot could not be deleted. Please, try again.'));
		}

		return $this->redirect('/');
	}

	public function TODOLATER_submit_score() {
		$id = $this->request->query('slot');
		$this->GameSlot->contain([
				'Game' => [
					'HomeTeam',
					'HomePoolTeam' => 'DependencyPool',
					'Division' => 'League',
				],
				'Field' => ['Facility' => 'Region'],
		]);
		$game_slot = $this->GameSlot->read(null, $id);
		if (!$game_slot) {
			$this->Flash->info(__('Invalid game slot.'));
			return $this->redirect('/');
		}
		if (empty($game_slot['Game'])) {
			$this->Flash->info(__('This game slot has no games associated with it.'));
			return $this->redirect('/');
		}
		$game = $game_slot['Game'][0];
		if ($game['Division']['schedule_type'] != 'competition') {
			$this->Flash->info(__('You can only enter scores for multiple games in "competition" divisions.'));
			return $this->redirect('/');
		}
		if ($game->isFinalized()) {
			$this->Flash->info(__('Games in this slot have already been finalized.'));
			return $this->redirect('/');
		}
		if ($game_slot->end_time->subHour()->isFuture()) {
			$this->Flash->info(__('That game has not yet occurred!'));
			return $this->redirect('/');
		}

		$this->Configuration->loadAffiliate($game_slot['Field']['Facility']['Region']['affiliate_id']);
		$ratings_obj = $this->moduleRegistry->load("Ratings:{$game['Division']['rating_calculator']}");

		$this->set(compact('game_slot'));

		if ($this->request->is('post')) {
			$teams = $games = $incidents = $errors = [];

			$unplayed = in_array($this->request->data['Game']['status'], Configure::read('unplayed_status'));

			// We could put these as hidden fields in the form, but we'd need to
			// validate them against the values from the URL anyway, so it's
			// easier to just set them directly here.
			// We use the team_id as the array index, here and in the views,
			// because order matters, and this is a good way to ensure that
			// the correct data gets into the correct form.
			foreach ($game_slot['Game'] as $i => $game) {
				if (!array_key_exists($game['home_team_id'], $this->request->data['Game'])) {
					$errors[$game['home_team_id']]['home_score'] = 'Scores must be entered for all teams.';
				} else {
					$details = $this->request->data['Game'][$game['home_team_id']];
					if ($unplayed) {
						$score = $rating = null;
					} else {
						$score = $details['home_score'];
						$rating = $ratings_obj->calculateRatingsChange($details['home_score']);
						$teams[$game['home_team_id']] = [
								'id' => $game['home_team_id'],
								'rating' => $game['HomeTeam']['rating'] + $rating,
								// Any time that this is called, the division seeding might change.
								// We just reset it here, and it will be recalculated as required elsewhere.
								'seed' => 0,
						];
					}
					$games[$game['home_team_id']] = [
							'id' => $game['id'],
							'status' => $this->request->data['Game']['status'],
							'home_score' => $score,
							'rating_points' => $rating,
							'approved_by_id' => $this->UserCache->currentId(),
					];
					if ($details['incident']) {
						$incidents[$game['home_team_id']] = [
								'game_id' => $game['id'],
								'team_id' => $game['home_team_id'],
								'type' => $details['type'],
								'details' => $details['details'],
								'game' => $game,
						];
					}
				}
			}

			if (!empty($errors)) {
				$this->GameSlot->Game->validationErrors = $errors;
			} else {
				$transaction = new DatabaseTransaction($this->GameSlot);
				if ($this->GameSlot->Game->saveAll($games, ['validate' => 'first'])) {
					if (Configure::read('scoring.incident_reports') && !empty($incidents)) {
						if (!$this->GameSlot->Game->Incident->saveAll($incidents, ['validate' => 'first'])) {
							$this->Flash->warning(__('The incident data could not be saved. Please correct the errors below and try again.'));
							return;
						}
					}

					// TODO: Replace this with a call to $game->adjustScoreAndRatings, which will need
					// to be adjusted to handle competition differences.
					// For now, all that function does that this doesn't is stats, and we have no idea how
					// stats might play out in competition divisions, so this will suffice.
					$this->GameSlot->Game->Division->Team->saveAll($teams);

					$transaction->commit();

					$resultMessage = __('Scores have been saved and game results posted.');
					$resultClass = 'success';

					if ($resultMessage) {
						$this->Flash->{$resultClass}($resultMessage);
					}

					return $this->redirect('/');
				} else {
					$this->Flash->warning(__('The scores could not be saved. Please correct the errors below and try again.'));
				}
			}
		}
	}

}
