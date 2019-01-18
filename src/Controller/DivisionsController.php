<?php
namespace App\Controller;

use App\Authorization\ContextResource;
use App\Model\Entity\Game;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Database\Type\DateType;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use App\Exception\ScheduleException;
use App\Model\Entity\DivisionsPerson;
use App\Model\Results\Comparison;
use App\Model\Table\GamesTable;

/**
 * Divisions Controller
 *
 * @property \App\Model\Table\DivisionsTable $Divisions
 */
class DivisionsController extends AppController {

	/**
	 * _noAuthenticationActions method
	 *
	 * @return array of actions that can be taken even by visitors that are not logged in.
	 */
	protected function _noAuthenticationActions() {
		$actions = ['view', 'schedule', 'standings', 'tooltip'];
		if (Configure::read('feature.public')) {
			$actions[] = 'scores';
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
		return ['view', 'schedule', 'standings'];
	}

	/**
	 * View method
	 *
	 * @return void|\Cake\Network\Response
	 */
	public function view() {
		$id = $this->request->getQuery('division');
		try {
			$division = $this->Divisions->get($id, [
				'contain' => [
					'Leagues',
				]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		}

		$this->Configuration->loadAffiliate($division->league->affiliate_id);
		$this->Divisions->prepForView($division);
		$league_obj = $this->moduleRegistry->load("LeagueType:{$division->schedule_type}");

		$this->set(compact('division', 'league_obj'));
		$this->set('_serialize', ['division']);
	}

	/**
	 * Tooltip function
	 *
	 * @return void|\Cake\Network\Response
	 */
	public function tooltip() {
		$this->request->allowMethod('ajax');

		$id = $this->request->getQuery('division');
		try {
			$division = $this->Divisions->get($id, [
				'contain' => [
					'People',
					'Days' => [
						'queryBuilder' => function (Query $q) {
							return $q->order(['day_id']);
						},
					],
					'Teams',
					'Leagues',
				]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		}

		$this->Configuration->loadAffiliate($division->league->affiliate_id);
		$this->set(compact('division'));
	}

	/**
	 * Stats method. Displays stats for all players in the division.
	 *
	 * @return \Cake\Network\Response|void
	 */
	public function stats() {
		$id = intval($this->request->getQuery('division'));
		try {
			$division = $this->Divisions->get($id, [
				'contain' => [
					'Leagues' => [
						'StatTypes' => [
							'queryBuilder' => function (Query $q) {
								return $q->where(['StatTypes.type IN' => Configure::read('stat_types.team')]);
							},
						],
					],
					'Days',
					'Teams',
				]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		}

		$this->Authorization->authorize($division->league);

		$this->Configuration->loadAffiliate($division->league->affiliate_id);
		$sport_obj = $this->moduleRegistry->load("Sport:{$division->league->sport}");

		// Hopefully, everything we need is already cached
		$stats = Cache::remember("division/{$id}/stats", function () use ($division, $sport_obj) {
			if (empty($division->teams)) {
				return ['people' => [], 'calculated_stats' => []];
			}

			// Calculate some stats.
			$team_ids = collection($division->teams)->extract('id')->toArray();
			$stats = $this->Divisions->Teams->Stats->find()
				->where(['team_id IN' => $team_ids])
				->toArray();

			return [
				'calculated_stats' => $sport_obj->calculateStats($stats, $division->league->stat_types),
				'people' => $this->Divisions->People->find()
					->matching('Teams', function (Query $q) use ($team_ids) {
						return $q->where(['Teams.id IN' => $team_ids]);
					})
					->where([
						'TeamsPeople.role IN' => Configure::read('extended_playing_roster_roles'),
						'TeamsPeople.status' => ROSTER_APPROVED,
					])
					->order(['People.' . Configure::read('gender.column') => Configure::read('gender.order'), 'People.last_name', 'People.first_name'])
					->indexBy('id')
					->toArray(),
			];
		}, 'long_term');

		$division->people = $stats['people'];
		$division->calculated_stats = $stats['calculated_stats'];

		$this->set(compact('division', 'sport_obj'));

		if ($this->request->is('csv')) {
			$this->response->download("Stats - {$division->name}.csv");
		}
	}

	/**
	 * Add method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$league_id = $this->request->getQuery('league');
		try {
			$league = $this->Divisions->Leagues->get($league_id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid league.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid league.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		}

		$this->Authorization->authorize($league, 'add_division');
		$this->Configuration->loadAffiliate($league->affiliate_id);

		$division = $this->Divisions->newEntity();

		if ($this->request->is('post')) {
			$division = $this->Divisions->patchEntity($division, $this->request->data, ['validateDays' => true]);
			if ($this->Divisions->save($division)) {
				$this->Flash->success(__('The division has been saved.'));
				return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
			}
			$this->Flash->warning(__('The division could not be saved. Please correct the errors below and try again.'));
		} else if ($this->request->getQuery('division')) {
			// To clone a division, read the old one and remove the id
			try {
				$division = $this->Divisions->cloneWithoutIds($this->request->getQuery('division'), [
					'contain' => ['Days'],
				]);
			} catch (RecordNotFoundException $ex) {
				$this->Flash->info(__('Invalid division.'));
				return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
			} catch (InvalidPrimaryKeyException $ex) {
				$this->Flash->info(__('Invalid division.'));
				return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
			}
		}
		$division->league_id = $league_id;
		$division->league = $league;

		$this->set(compact('division'));
		$this->set('days', $this->Divisions->Days->find('list')->toArray());
		if (isset($division->schedule_type)) {
			$this->set('league_obj', $this->moduleRegistry->load("LeagueType:{$division->schedule_type}"));
		}

		$this->render('edit');
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful edit, renders view otherwise.
	 */
	public function edit() {
		$id = intval($this->request->getQuery('division'));
		try {
			$division = $this->Divisions->get($id, [
				'contain' => ['Leagues', 'Days'],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		}

		$this->Authorization->authorize($division);
		$this->Configuration->loadAffiliate($division->league->affiliate_id);

		if ($this->request->is(['patch', 'post', 'put'])) {
			$division = $this->Divisions->patchEntity($division, $this->request->data, ['validateDays' => true]);

			// This recalculation will save all changes including any modified division data
			$rating_obj = $this->moduleRegistry->load("Ratings:{$division->rating_calculator}");
			if ($rating_obj->recalculateRatings($division)) {
				$this->Flash->success(__('The division has been saved.'));
				return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
			} else {
				$this->Flash->warning(__('The division could not be saved. Please correct the errors below and try again.'));
			}
		}

		$this->set(compact('division'));
		$this->set('days', $this->Divisions->Days->find('list')->toArray());
		$this->set('league_obj', $this->moduleRegistry->load("LeagueType:{$division->schedule_type}"));
	}

	/**
	 * Scheduling Fields method. Generates form elements related to the selected schedule type.
	 *
	 * @return void Renders view, though that view may be empty.
	 */
	public function scheduling_fields() {
		$this->request->allowMethod('ajax');
		$this->Authorization->authorize($this);

		if (array_key_exists('divisions', $this->request->data)) {
			$index = current(array_keys($this->request->data['divisions']));
			$type = $this->request->data['divisions'][$index]['schedule_type'];
		} else {
			$type = $this->request->data['schedule_type'];
		}
		$this->set('league_obj', $this->moduleRegistry->load("LeagueType:{$type}"));
		$this->set(compact('index'));
	}

	/**
	 * Add coordinator method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful add, renders view otherwise
	 */
	public function add_coordinator() {
		$id = $this->request->getQuery('division');
		try {
			$division = $this->Divisions->get($id, [
				'contain' => [
					'People' => [
						'queryBuilder' => function (Query $q) {
							return $q->where(['DivisionsPeople.position' => 'coordinator']);
						},
					],
					'Leagues',
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		}

		$this->Authorization->authorize($division);
		$this->Configuration->loadAffiliate($division->league->affiliate_id);

		$this->set(compact('division'));

		$person_id = $this->request->getQuery('person');
		if ($person_id != null) {
			try {
				$person = $this->Divisions->People->get($person_id, [
					'contain' => [
						'Divisions' => [
							'queryBuilder' => function (Query $q) use ($id) {
								return $q->where(['Divisions.id' => $id]);
							},
						],
					],
				]);
			} catch (RecordNotFoundException $ex) {
				$this->Flash->info(__('Invalid person.'));
				return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
			} catch (InvalidPrimaryKeyException $ex) {
				$this->Flash->info(__('Invalid person.'));
				return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
			}

			if (!empty($person->divisions)) {
				$this->Flash->info(__('{0} is already a coordinator of this division.', $person->full_name));
				return $this->redirect(['action' => 'add_coordinator', 'division' => $id]);
			} else {
				$person->_joinData = new DivisionsPerson([
					'position' => 'coordinator',
				]);
				if ($this->Divisions->People->link($division, [$person])) {
					$this->UserCache->clear('Divisions', $person_id);
					$this->UserCache->clear('DivisionIDs', $person_id);
					$this->Flash->success(__('Added {0} as coordinator.', $person->full_name));
					return $this->redirect(['action' => 'view', 'division' => $id]);
				} else {
					$this->Flash->warning(__('Failed to add {0} as coordinator.', $person->full_name));
					return $this->redirect(['action' => 'add_coordinator', 'division' => $id]);
				}
			}
		}

		$this->_handlePersonSearch(['division', 'person'], ['group_id IN' => [GROUP_VOLUNTEER,GROUP_OFFICIAL,GROUP_MANAGER,GROUP_ADMIN]]);
	}

	/**
	 * Remove coordinator method
	 *
	 * @return void|\Cake\Network\Response Redirects to view.
	 */
	public function remove_coordinator() {
		$this->request->allowMethod(['post']);

		$id = $this->request->getQuery('division');
		$person_id = $this->request->getQuery('person');
		try {
			$division = $this->Divisions->get($id, [
				'contain' => [
					'People' => [
						'queryBuilder' => function (Query $q) use ($person_id) {
							return $q->where([
								'People.id' => $person_id,
								'DivisionsPeople.position' => 'coordinator',
							]);
						},
					],
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($division);

		if (empty($division->people)) {
			$this->Flash->warning(__('That person is not a coordinator of this division!'));
			return $this->redirect(['action' => 'view', 'division' => $id]);
		}

		$this->Divisions->People->unlink($division, $division->people, false);
		$this->UserCache->clear('Divisions', $person_id);
		$this->UserCache->clear('DivisionIDs', $person_id);
		$this->Flash->success(__('Successfully removed coordinator.'));

		return $this->redirect(['action' => 'view', 'division' => $id]);
	}

	/**
	 * Add teams method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful add, renders view otherwise
	 */
	public function add_teams() {
		$id = $this->request->getQuery('division');
		try {
			$division = $this->Divisions->get($id, [
				'contain' => [
					'People',
					'Leagues',
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		}

		$this->Authorization->authorize($division);
		$this->Configuration->loadAffiliate($division->league->affiliate_id);

		if ($this->request->is(['patch', 'post', 'put'])) {
			$division->teams = [];
			$default = $this->request->data['teams'][0];
			foreach ($this->request->data['teams'] as $key => $team) {
				if (!empty($team['name'])) {
					$division->teams[$key] = $this->Divisions->Teams->newEntity(array_merge($default, $team));
				}
			}
			if (!empty($division->teams)) {
				$division->dirty('teams', true);
				if ($this->Divisions->save($division)) {
					$this->Flash->success(__('The teams have been saved.'));
					return $this->redirect(['action' => 'view', 'division' => $id]);
				} else {
					$this->Flash->warning(__('The teams could not be saved. Please correct the errors below and try again.'));
				}
			}
		}

		$this->set(compact('division'));
	}

	/**
	 * Ratings method. Adjust team ratings.
	 *
	 * @return void|\Cake\Network\Response Redirects on successful save, renders view otherwise
	 */
	public function ratings() {
		$id = intval($this->request->getQuery('division'));
		try {
			$division = $this->Divisions->get($id, [
				'contain' => [
					'Days' => [
						'queryBuilder' => function (Query $q) {
							return $q->order(['day_id']);
						},
					],
					'Teams' => [
						'queryBuilder' => function (Query $q) {
							return $q->order(['rating' => 'DESC']);
						},
						'People' => ['Skills'],
					],
					'Leagues',
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		}

		$this->Authorization->authorize($division, 'edit_schedule');

		if (empty($division->teams)) {
			$this->Flash->info(__('Cannot adjust ratings for a division with no teams.'));
			return $this->redirect(['action' => 'view', 'division' => $id]);
		}

		$this->Configuration->loadAffiliate($division->league->affiliate_id);

		if ($this->request->is(['patch', 'post', 'put'])) {
			$division = $this->Divisions->patchEntity($division, $this->request->data);
			$rating_obj = $this->moduleRegistry->load("Ratings:{$division->rating_calculator}");

			// This recalculation will save all changes including initial rating adjustments
			if ($rating_obj->recalculateRatings($division)) {
				$this->Flash->success(__('The ratings have been saved.'));
				return $this->redirect(['action' => 'view', 'division' => $id]);
			} else {
				$this->Flash->warning(__('The ratings could not be saved. Please correct the errors below and try again.'));
			}
		}

		$this->set(compact('division'));
	}

	/**
	 * Seeds method. Adjust initial team seeding.
	 *
	 * @return void|\Cake\Network\Response Redirects on successful save, renders view otherwise
	 */
	public function seeds() {
		$id = intval($this->request->getQuery('division'));
		try {
			$division = $this->Divisions->get($id, [
				'contain' => [
					'Days' => [
						'queryBuilder' => function (Query $q) {
							return $q->order(['day_id']);
						},
					],
					'Teams' => [
						'queryBuilder' => function (Query $q) {
							return $q->order(['initial_seed', 'seed', 'name']);
						},
						'People' => ['Skills'],
					],
					'Leagues',
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		}

		$this->Authorization->authorize($division, 'edit_schedule');

		if (empty($division->teams)) {
			$this->Flash->info(__('Cannot adjust seeds for a division with no teams.'));
			return $this->redirect(['action' => 'view', 'division' => $id]);
		}

		$this->Configuration->loadAffiliate($division->league->affiliate_id);

		if ($this->request->is(['patch', 'post', 'put'])) {
			$division = $this->Divisions->patchEntity($division, $this->request->data);

			$seeds = collection($this->request->data['teams'])->extract('initial_seed')->toArray();
			if (count($this->request->data['teams']) != count(array_unique($seeds))) {
				$this->Flash->warning(__('Each team must have a unique initial seed.'));
			} else if (min($seeds) != 1 || count($this->request->data['teams']) != max($seeds)) {
				$this->Flash->warning(__('Initial seeds must start at 1 and not skip any.'));
			} else {
				foreach ($division->teams as $team) {
					$team->seed = $team->initial_seed;
				}
				if ($this->Divisions->save($division)) {
					$this->Flash->success(__('The seeds have been saved.'));
					return $this->redirect(['action' => 'view', 'division' => $id]);
				} else {
					$this->Flash->warning(__('The seeds could not be saved. Please correct the errors below and try again.'));
				}
			}
		}

		$this->set(compact('division'));
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Network\Response Redirects to index.
	 */
	public function delete() {
		$this->request->allowMethod(['post', 'delete']);

		$id = $this->request->getQuery('division');
		try {
			$division = $this->Divisions->get($id, [
				'contain' => ['Leagues' => ['Divisions']],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		}

		$this->Authorization->authorize($division);

		$dependencies = $this->Divisions->dependencies($id, ['Days', 'People']);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this division, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		}

		if ($this->Divisions->delete($division)) {
			$this->Flash->success(__('The division has been deleted.'));
		} else if ($division->errors('delete')) {
			$this->Flash->warning(current($division->errors('delete')));
		} else {
			$this->Flash->warning(__('The division could not be deleted. Please, try again.'));
		}
		return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
	}

	/**
	 * Schedule method. Display and optionally edit schedules.
	 *
	 * @return void|\Cake\Network\Response Redirects on error, renders view otherwise
	 */
	public function schedule() {
		$id = intval($this->request->getQuery('division'));

		// Hopefully, everything we need is already cached
		$division = Cache::remember("division/{$id}/schedule", function () use ($id) {
			try {
				$division = $this->Divisions->get($id, [
					'contain' => [
						'Days' => [
							'queryBuilder' => function (Query $q) {
								return $q->order(['day_id']);
							},
						],
						'Teams',
						'Leagues' => [
							'StatTypes' => [
								'queryBuilder' => function (Query $q) {
									return $q->where(['StatTypes.type' => 'entered']);
								},
							],
						],
						'Games' => [
							'queryBuilder' => function (Query $q) {
								return $q->where([
									'OR' => [
										'Games.home_dependency_type !=' => 'copy',
										'Games.home_dependency_type IS' => null,
									],
								]);
							},
							'GameSlots' => ['Fields' => ['Facilities']],
							'ScoreEntries',
							'HomeTeam',
							'HomePoolTeam' => ['DependencyPool'],
							'AwayTeam',
							'AwayPoolTeam' => ['DependencyPool'],
							'Pools',
						],
					],
				]);
			} catch (RecordNotFoundException $ex) {
				$this->Flash->info(__('Invalid division.'));
				return null;
			} catch (InvalidPrimaryKeyException $ex) {
				$this->Flash->info(__('Invalid division.'));
				return null;
			}

			if (empty($division->games)) {
				$this->Flash->info(__('This division has no games scheduled yet.'));
				return null;
			}

			// Sort games by date, time and field
			usort($division->games, ['App\Model\Table\GamesTable', 'compareDateAndField']);

			return $division;
		}, 'long_term');

		if (!$division) {
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		}

		$this->Configuration->loadAffiliate($division->league->affiliate_id);

		if ($this->Authorization->can($division, 'edit_schedule')) {
			$edit_date = $this->request->getQuery('edit_date');
		} else {
			$edit_date = null;
		}

		$multi_day = $division->schedule_type != 'tournament' && count($division->days) > 1;

		if ($edit_date) {
			$is_tournament = collection($division->games)->some(function ($game) use ($edit_date) {
				return $game->type != SEASON_GAME && $game->game_slot->game_date == $edit_date;
			});
			$game_slots = $this->Divisions->GameSlots->find('available', [
				'divisions' => [$id],
				'date' => $edit_date,
				'is_tournament' => $is_tournament,
				'double_booking' => $division->double_booking,
				'multi_day' => $multi_day,
			])->toArray();
		} else {
			$is_tournament = collection($division->games)->some(function ($game) use ($edit_date) {
				return $game->type != SEASON_GAME;
			});
		}

		// Save posted data
		if ($this->request->is(['patch', 'post', 'put']) && $this->Authorization->can($division, 'edit_schedule')) {
			$this->loadComponent('Lock');

			if ($this->Lock->lock('scheduling', $this->Divisions->affiliate($id), 'schedule creation or edit')) {
				try {
					$edit_games = $this->Divisions->Games->patchEntities($division->games, $this->request->data['games'],
						array_merge($this->request->data['options'], ['validate' => 'scheduleEdit'])
					);

					$edit_ids = collection($edit_games)->extract('id')->toArray();
					$other_games = collection($division->games)->reject(function ($game) use ($edit_ids) {
						return in_array($game->id, $edit_ids);
					})->toArray();
					$division->games = array_merge($edit_games, $other_games);
					usort($division->games, ['App\Model\Table\GamesTable', 'compareDateAndField']);

					if ($this->Divisions->Games->connection()->transactional(function () use ($division, $edit_games, $game_slots) {
						$success = true;
						$options = array_merge($this->request->data['options'], [
							'games' => $edit_games,
							'game_slots' => $game_slots,
							'validate' => 'scheduleEdit',
						]);
						// We intentionally do not use saveMany here; it returns immediately when one save fails,
						// whereas this method will generate error messages for everything applicable.
						foreach ($division->games as $game) {
							if (!$this->Divisions->Games->save($game, $options)) {
								$success = false;
							}
						}
						return $success;
					})) {
						$this->Flash->success(__('Schedule changes saved!'));
						return $this->redirect(['action' => 'schedule', 'division' => $id]);
					}

					$this->Flash->warning(__('The games could not be saved. Please correct the errors below and try again.'));
				} catch (ScheduleException $ex) {
					$this->Flash->html($ex->getMessages(), ['params' => $ex->getAttributes()]);
				}
			}
		}

		$division->games = collection($division->games)->indexBy('id')->toArray();
		$this->set(compact('id', 'division', 'edit_date', 'game_slots', 'is_tournament', 'multi_day'));
		$this->set('_serialize', ['division']);
	}

	/**
	 * Standings method
	 *
	 * @return void|\Cake\Network\Response Redirects on error, renders view otherwise
	 */
	public function standings() {
		$id = intval($this->request->getQuery('division'));
		$team_id = $this->request->getQuery('team');
		$show_all = $this->request->getQuery('full') || $this->request->is('json');

		try {
			$division = $this->Divisions->get($id, [
				'contain' => [
					'Days' => [
						'queryBuilder' => function (Query $q) {
							return $q->order(['day_id']);
						},
					],
					'Leagues',
				]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		}

		$this->Configuration->loadAffiliate($division->league->affiliate_id);
		$league_obj = $this->moduleRegistry->load("LeagueType:{$division->schedule_type}");
		$spirit_obj = $division->league->hasSpirit() ? $this->moduleRegistry->load("Spirit:{$division->league->sotg_questions}") : null;
		if (!$league_obj->addResults($division, $spirit_obj)) {
			$this->Flash->info(__('Cannot generate standings for a division with no schedule.'));
			return $this->redirect(['action' => 'view', 'division' => $id]);
		}

		// If we're asking for "team" standings, only show the 5 teams above and 5 teams below this team.
		// Don't bother if there are 24 teams or less (24 is probably the largest fall division size).
		// If $show_all is set, don't remove teams.
		if (!$show_all && $team_id != null && count($division->teams) > 24) {
			$index_of_this_team = false;
			foreach (array_values($division->teams) as $i => $team) {
				if ($team->id == $team_id) {
					$index_of_this_team = $i;
					break;
				}
			}

			$first = $index_of_this_team - 5;
			if ($first <= 0) {
				$first = 0;
			} else {
				$more_before = $first; // need to add this to the first seed
			}
			$last = $index_of_this_team + 5;
			if ($last < count($division->teams) - 1) {
				$more_after = true; // we never need to know how many after
			}

			$show_teams = array_slice ($division->teams, $first, $last + 1 - $first);
		} else {
			$show_teams = $division->teams;
		}
		$this->set(compact('division', 'league_obj', 'spirit_obj', 'team_id', 'show_teams', 'more_before', 'more_after'));
		$this->set('_serialize', ['division']);
	}

	/**
	 * Scores method. Shows a matrix of scores from games between team pairings.
	 *
	 * @return void|\Cake\Network\Response Redirects on error, renders view otherwise
	 */
	public function scores() {
		$id = $this->request->getQuery('division');
		try {
			$division = $this->Divisions->get($id, [
				'contain' => [
					'Days' => [
						'queryBuilder' => function (Query $q) {
							return $q->order(['day_id']);
						},
					],
					'Teams',
					'Leagues',
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		}

		$this->Authorization->authorize($division);
		$this->Configuration->loadAffiliate($division->league->affiliate_id);

		// Find all games played by teams that are currently in this division,
		// or tournament games for this division
		$team_ids = collection($division->teams)->extract('id')->toArray();
		$division->games = TableRegistry::get('Games')
			->find('schedule', ['teams' => $team_ids, 'playoff_division' => $id])
			->find('played')
			->where([
				'OR' => [
					'Games.home_dependency_type !=' => 'copy',
					'Games.home_dependency_type IS' => null,
				],
			])
			->order(['GameSlots.game_date', 'GameSlots.game_start'])
			->toArray();

		if (empty($division->games)) {
			$this->Flash->info(__('This division has no games scheduled yet.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		}

		// Sort games by date, time and field
		usort($division->games, ['App\Model\Table\GamesTable', 'compareDateAndField']);
		GamesTable::adjustEntryIndices($division->games);
		$league_obj = $this->moduleRegistry->load("LeagueType:{$division->schedule_type}");
		$spirit_obj = $division->league->hasSpirit() ? $this->moduleRegistry->load("Spirit:{$division->league->sotg_questions}") : null;
		$league_obj->sort($division, $division->league, $division->games, $spirit_obj, false);

		// Move the teams into an array indexed by team id, for easier use in the view
		$division->teams = collection($division->teams)->indexBy('id')->toArray();

		$this->set(compact('division'));
	}

	/**
	 * Fields method. Displays field distribution report.
	 *
	 * @return void|\Cake\Network\Response Redirects on error, renders view otherwise
	 */
	public function fields() {
		$id = $this->request->getQuery('division');

		$conditions = [
			'OR' => [
				'Games.home_dependency_type !=' => 'copy',
				'Games.home_dependency_type IS' => null,
			],
		];

		if ($this->request->getQuery('published')) {
			$conditions['Games.published'] = true;
			$this->set('published', true);
		}

		try {
			$division = $this->Divisions->get($id, [
				'contain' => [
					'Teams' => [
						'Fields' => ['Facilities'],
						'Regions',
					],
					'Leagues',
					'Games' => [
						'queryBuilder' => function (Query $q) use ($conditions) {
							return $q->find('played')->where($conditions);
						},
						'GameSlots' => ['Fields' => ['Facilities']],
						'HomeTeam',
						'HomePoolTeam' => ['Pools'],
						'AwayTeam',
						'AwayPoolTeam' => ['Pools'],
					],
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		}

		$this->Authorization->authorize($division, 'edit_schedule');

		if (empty($division->games)) {
			$this->Flash->info(__('This division has no games scheduled yet.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		}

		$this->Configuration->loadAffiliate($division->league->affiliate_id);
		$league_obj = $this->moduleRegistry->load("LeagueType:{$division->schedule_type}");
		$spirit_obj = $division->league->hasSpirit() ? $this->moduleRegistry->load("Spirit:{$division->league->sotg_questions}") : null;
		$league_obj->sort($division, $division->league, $division->games, $spirit_obj, false);

		// Gather all possible facility/time slot combinations this division can use
		$game_slots = $this->Divisions->GameSlots->find()
			->distinct(['Facilities.id', 'GameSlots.game_start'])
			->contain([
				'Fields' => ['Facilities' => ['Regions']],
			])
			->matching('Divisions', function (Query $q) use ($id) {
				return $q->where(['Divisions.id' => $id]);
			})
			->order(['Regions.id', 'Facilities.code', 'GameSlots.game_start'])
			->toArray();

		$this->set(compact('division', 'league_obj', 'game_slots'));
	}

	/**
	 * Slots method. Shows game slots available to the division on selected date.
	 *
	 * @return void|\Cake\Network\Response Redirects on error, renders view otherwise
	 */
	public function slots() {
		$id = $this->request->getQuery('division');
		try {
			$division = $this->Divisions->get($id, [
				'contain' => ['Leagues'],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		}

		$this->Authorization->authorize($division, 'edit_schedule');
		$this->Configuration->loadAffiliate($division->league->affiliate_id);

		// Find all the dates that this division has game slots on
		$dates = $this->Divisions->GameSlots->find()
			->hydrate(false)
			->select(['GameSlots.game_date'])
			->distinct(['GameSlots.game_date'])
			->matching('Divisions', function (Query $q) use ($id) {
				return $q->where(['Divisions.id' => $id]);
			})
			->order(['GameSlots.game_date'])
			->extract('game_date')
			->toArray();

		$date = $this->request->getQuery('date');
		if ($this->request->is('post') && array_key_exists('date', $this->request->data)) {
			$date = $this->request->data['date'];
			// TODO: Is there a way to make the Ajax form submitter not send the string literal "null"?
			if (empty($date) || $date == 'null') {
				$this->Flash->info(__('You must select a date.'));
				return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
			}
		}
		if (!empty($date)) {
			$slots = $this->Divisions->GameSlots->find()
				->contain([
					'Games' => [
						'queryBuilder' => function (Query $q) {
							return $q->where([
								'OR' => [
									'Games.home_dependency_type !=' => 'copy',
									'Games.home_dependency_type IS' => null,
								],
							]);
						},
						'Divisions' => ['Leagues'],
						'Pools',
						'HomeTeam' => [
							'Fields' => ['Facilities'],
							'Regions',
						],
						'HomePoolTeam' => ['DependencyPool'],
						'AwayTeam' => [
							'Fields' => ['Facilities'],
							'Regions',
						],
						'AwayPoolTeam' => ['DependencyPool'],
					],
					'Fields' => [
						'Facilities' => ['Regions'],
					],
				])
				->matching('Divisions', function (Query $q) use ($id) {
					return $q->where(['Divisions.id' => $id]);
				})
				->where(['GameSlots.game_date' => $date])
				->order(['GameSlots.game_start', 'Fields.id'])
				->toArray();

			$is_tournament = collection($slots)->some(function ($slot) {
				return collection($slot->games)->some(function ($game) {
					return $game->type != SEASON_GAME;
				});
			});
		}

		$this->set(compact('division', 'dates', 'date', 'slots', 'is_tournament'));
	}

	/**
	 * Status method. Generates division status report.
	 *
	 * @return void|\Cake\Network\Response Redirects on error, renders view otherwise
	 */
	public function status() {
		$id = $this->request->getQuery('division');
		try {
			$division = $this->Divisions->get($id, [
				'contain' => [
					'Teams' => [
						'queryBuilder' => function (Query $q) {
							return $q->order(['Teams.name']);
						},
					],
					'Leagues',
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		}

		$this->Authorization->authorize($division, 'edit_schedule');

		if (empty($division->teams)) {
			$this->Flash->info(__('Cannot generate status report for a division with no teams.'));
			return $this->redirect(['action' => 'view', 'division' => $id]);
		}
		// TODO: Use the league_obj to determine what functions are applicable instead of hard-coding the list
		if (!in_array($division->schedule_type, ['roundrobin', 'ratings_ladder'])) {
			$this->Flash->info(__('Cannot generate status report for a division with this schedule type.'));
			return $this->redirect(['action' => 'view', 'division' => $id]);
		}

		$this->Configuration->loadAffiliate($division->league->affiliate_id);
		$league_obj = $this->moduleRegistry->load("LeagueType:{$division->schedule_type}");

		// Find all games played by teams that are currently in this division.
		$division->teams = collection($division->teams)->indexBy('id')->toArray();
		$team_ids = array_keys($division->teams);

		$division->games = $this->Divisions->Games->find('played')
			->contain([
				'GameSlots',
				'ScoreEntries',
				'SpiritEntries',
			])
			->where([
				'OR' => [
					'Games.home_team_id IN' => $team_ids,
					'Games.away_team_id IN' => $team_ids,
				],
			])
			->toArray();

		if (empty($division->games)) {
			$this->Flash->info(__('Cannot generate status report for a division with no schedule.'));
			return $this->redirect(['action' => 'view', 'division' => $id]);
		}

		$regions = TableRegistry::get('Regions')->find()
			->hydrate(false)
			->where(['affiliate_id' => $division->league->affiliate_id])
			->combine('id', 'name')
			->toArray();

		$fields = TableRegistry::get('Fields')->find()
			->contain(['Facilities'])
			->where(['Facilities.region_id IN' => array_keys($regions)])
			->indexBy('id')
			->toArray();

		$stats = array_fill_keys($team_ids, [
			'games' => 0,
			'season_games' => 0,
			'home_games' => 0,
			'field_rank' => 0,
			'region_games' => [],
			'opponents' => [],
		]);
		$regions_used = [];
		$playoffs_included = false;

		foreach ($division->games as $game) {
			$home_team_id_id = $game['home_team_id'];
			$away_team_id_id = $game['away_team_id'];

			// Only count regular-season games
			if ($game['type'] == SEASON_GAME) {
				++ $stats[$home_team_id_id]['games'];
				++ $stats[$home_team_id_id]['home_games'];
				if ($game['home_field_rank'] != NULL) {
					$stats[$home_team_id_id]['field_rank'] += 1 / $game['home_field_rank'];
				} else {
					// A NULL home rank means that the home team had no preference at that time,
					// which means we count it as being 100% satisfied.
					++ $stats[$home_team_id_id]['field_rank'];
				}

				++ $stats[$away_team_id_id]['games'];
				if ($game['away_field_rank'] != NULL) {
					$stats[$away_team_id_id]['field_rank'] += 1 / $game['away_field_rank'];
				}

				$region_id = $fields[$game['game_slot']['field_id']]['facility']['region_id'];
				$regions_used[$region_id] = true;

				if (!array_key_exists($region_id, $stats[$home_team_id_id]['region_games'])) {
					$stats[$home_team_id_id]['region_games'][$region_id] = 1;
				} else {
					++ $stats[$home_team_id_id]['region_games'][$region_id];
				}
				if (!array_key_exists($region_id, $stats[$away_team_id_id]['region_games'])) {
					$stats[$away_team_id_id]['region_games'][$region_id] = 1;
				} else {
					++ $stats[$away_team_id_id]['region_games'][$region_id];
				}

				if (!array_key_exists($away_team_id_id, $stats[$home_team_id_id]['opponents'])) {
					$stats[$home_team_id_id]['opponents'][$away_team_id_id] = 1;
				} else {
					++ $stats[$home_team_id_id]['opponents'][$away_team_id_id];
				}
				if (!array_key_exists($home_team_id_id, $stats[$away_team_id_id]['opponents'])) {
					$stats[$away_team_id_id]['opponents'][$home_team_id_id] = 1;
				} else {
					++ $stats[$away_team_id_id]['opponents'][$home_team_id_id];
				}
			} else {
				$playoffs_included = true;
			}
		}

		// Skip the region column if there is only one
		if (count($regions_used) == 1) {
			$regions_used = [];
		}

		$this->set(compact('division', 'regions', 'regions_used', 'fields', 'stats', 'playoffs_included', 'league_obj'));
	}

	/**
	 * Allstars method. Generates report on all star nominations.
	 *
	 * @return void|\Cake\Network\Response Redirects on error, renders view otherwise
	 */
	public function allstars() {
		$id = $this->request->getQuery('division');
		try {
			$division = $this->Divisions->get($id, [
				'contain' => [
					'Leagues',
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		}

		$this->Authorization->authorize($division);
		$this->Configuration->loadAffiliate($division->league->affiliate_id);

		$min = $this->request->getQuery('min');
		if (!$min) {
			$min = 2;
		}
		$allstars_table = TableRegistry::get('GamesAllstars');
		$allstars = $allstars_table->find()
			->select(['count' => 'COUNT(GamesAllstars.score_entry_id)'])
			->select($allstars_table->People)
			->contain([
				'ScoreEntries' => ['Games'],
				'People',
			])
			->where(['Games.division_id' => $id])
			->group(['GamesAllstars.person_id'])
			->having(['count >=' => $min])
			->order(['People.' . Configure::read('gender.column') => Configure::read('gender.order'), 'count' => 'DESC', 'People.last_name', 'People.first_name'])
			->toArray();

		$this->set(compact('division', 'allstars', 'min'));
	}

	/**
	 * Emails method. Provides list of captain emails, and mailto link.
	 *
	 * @return void|\Cake\Network\Response Redirects on error, renders view otherwise
	 */
	public function emails() {
		$id = $this->request->getQuery('division');
		try {
			$division = $this->Divisions->get($id, [
				'contain' => [
					'Teams' => [
						'People' => [
							'queryBuilder' => function (Query $q) {
								return $q->where([
									'TeamsPeople.role IN' => Configure::read('privileged_roster_roles'),
									'TeamsPeople.status' => ROSTER_APPROVED,
								]);
							},
							Configure::read('Security.authModel'),
						],
					],
					'Leagues',
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		}

		$this->Authorization->authorize($division);
		$this->Configuration->loadAffiliate($division->league->affiliate_id);
		$this->set(compact('division'));
	}

	/**
	 * Spirit method. Generates spirit report.
	 *
	 * @return void|\Cake\Network\Response Redirects on error, renders view otherwise
	 */
	public function spirit() {
		$id = $this->request->getQuery('division');
		try {
			$division = $this->Divisions->get($id, [
				'contain' => [
					'Teams',
					'Leagues',
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		}

		$this->Authorization->authorize(new ContextResource($division, ['league' => $division->league]));
		$this->Configuration->loadAffiliate($division->league->affiliate_id);

		// We need to find all games involving teams that have ever been in this division
		$team_ids = collection($division->teams)->extract('id')->toArray();
		$division->games = TableRegistry::get('Games')
			->find('schedule', ['teams' => $team_ids, 'division' => $id])
			->find('played')
			->contain([
				'SpiritEntries' => ['MostSpirited'],
				'ScoreEntries',
				'Incidents',
			])
			->where([
				['OR' => [
					'Games.home_dependency_type !=' => 'copy',
					'Games.home_dependency_type IS' => null,
				]],
			])
			->order(['Games.id'])
			->filter(function (Game $game) {
				return !empty($game->spirit_entries);
			})
			->toArray();

		if (empty($division->games)) {
			$this->Flash->info(__('This division has no games scheduled yet.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		}

		$spirit_obj = $this->moduleRegistry->load("Spirit:{$division->league->sotg_questions}");

		usort($division->games, ['App\Model\Table\GamesTable', 'compareDateAndField']);
		$this->set(compact('division', 'spirit_obj'));

		if ($this->request->is('csv')) {
			$this->response->download("Spirit - {$division->full_league_name}.csv");
		}
	}

	/**
	 * Approve scores method. Gives list of games that are not yet finalized.
	 *
	 * @return void|\Cake\Network\Response Redirects on error, renders view otherwise
	 */
	public function approve_scores() {
		$id = $this->request->getQuery('division');
		$roles = Configure::read('privileged_roster_roles');
		try {
			$division = $this->Divisions->get($id, [
				'contain' => [
					'Leagues',
					'Games' => [
						'queryBuilder' => function (Query $q) {
							return $q->where([
								'Games.approved_by_id IS' => null,
								'OR' => [
									'GameSlots.game_date <' => FrozenDate::now(),
									[
										'GameSlots.game_date' => FrozenDate::now(),
										'GameSlots.game_end <' => FrozenTime::now(),
									],
								],
							])
							->order(['GameSlots.game_date', 'GameSlots.game_start', 'Games.id']);
						},
						// Get the list of captains for each team, for building the email link
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
								'queryBuilder' => function (Query $q) use ($roles) {
									return $q->where([
										'TeamsPeople.role IN' => $roles,
										'TeamsPeople.status' => ROSTER_APPROVED,
									]);
								},
								Configure::read('Security.authModel'),
							],
						],
						'AwayPoolTeam' => ['DependencyPool'],
						'GameSlots',
						'ScoreEntries',
					],
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		}

		$this->Authorization->authorize($division);
		$this->Configuration->loadAffiliate($division->league->affiliate_id);

		if ($division->schedule_type == 'competition') {
			$this->Flash->info(__('This division does not support this report.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		}

		if (empty($division->games)) {
			$this->Flash->info(__('There are currently no games to approve in this division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		}
		GamesTable::adjustEntryIndices($division->games);

		$this->set(compact('division'));
	}

	/**
	 * Initialize ratings method. Initializes ratings for playoff teams based on their regular season records.
	 *
	 * @return \Cake\Network\Response Redirects to division view
	 */
	public function initialize_ratings() {
		$id = intval($this->request->getQuery('division'));
		try {
			$division = $this->Divisions->get($id, [
				'contain' => [
					'Teams',
					'Leagues',
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		}

		$this->Authorization->authorize($division);
		$this->Configuration->loadAffiliate($division->league->affiliate_id);

		if (!$division->is_playoff) {
			$this->Flash->info(__('Only playoff divisions can be initialized.'));
			return $this->redirect(['action' => 'view', 'division' => $id]);
		}

		// Initialize all teams ratings with their regular season ratings
		foreach ($division->teams as $team) {
			$affiliated_team = $team->_getAffiliatedTeam($division);
			if (!$affiliated_team) {
				$this->Flash->warning(__('{0} does not have a unique affiliated team in the correct division.', $team->name));
				return $this->redirect(['action' => 'view', 'division' => $id]);
			}
			$team->rating = $team->initial_rating = $affiliated_team->rating;
		}
		$division->dirty('teams', true);

		if ($this->Divisions->save($division)) {
			$this->Flash->success(__('Team ratings have been initialized.'));
		} else {
			$this->Flash->warning(__('Failed to initialize team ratings.'));
		}

		return $this->redirect(['action' => 'view', 'division' => $id]);
	}

	/**
	 * Initialize dependencies method. Initializes seed-based tournament dependencies based on current standings.
	 *
	 * @return \Cake\Network\Response Redirects to division schedule
	 */
	public function initialize_dependencies() {
		$id = intval($this->request->getQuery('division'));
		try {
			$division = $this->Divisions->get($id, [
				'contain' => [
					'Days' => [
						'queryBuilder' => function (Query $q) {
							return $q->order(['day_id']);
						},
					],
					'Teams',
					'Leagues',
					// We may need all of the games, as some league types use game results
					// to determine sort order.
					'Games' => [
						'queryBuilder' => function (Query $q) {
							return $q
								->find('played')
								->order(['Games.id']);	// need to ensure that "copy" games come after the ones they're copied from
						},
						'GameSlots',
						'HomePoolTeam' => ['Pools', 'DependencyPool'],
						'AwayPoolTeam' => ['Pools', 'DependencyPool'],
					],
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		}

		$this->Authorization->authorize($division);
		$this->Configuration->loadAffiliate($division->league->affiliate_id);

		$conditions = [
			'Games.division_id' => $id,
			'Games.type !=' => SEASON_GAME,
			'Games.approved_by_id IS' => null,
			'OR' => [
				'HomePoolTeam.dependency_type IN' => ['seed', 'pool', 'ordinal', 'copy'],
				'AwayPoolTeam.dependency_type IN' => ['seed', 'pool', 'ordinal', 'copy'],
			],
		];

		// If there are tournament pools with finalized games in them, we do not want to
		// initialize any games in those pools.
		$finalized_pools = array_unique(collection($division->games)
			->filter(function ($game) {
				return $game->home_dependency_type != 'copy' && $game->isFinalized() && !empty($game->pool_id);
			})
			->extract('pool_id')
			->toArray()
		);
		if (!empty($finalized_pools)) {
			$conditions['NOT'] = ['Games.pool_id IN' => $finalized_pools];
		}

		$pool = $this->request->getQuery('pool');
		if ($pool) {
			if (in_array($pool, $finalized_pools)) {
				$this->Flash->warning(__('There are already games finalized in this pool. Unable to proceed.'));
				return $this->redirect(['action' => 'schedule', 'division' => $id]);
			}
			$conditions['Games.pool_id'] = $pool;
		}

		$games = $this->Divisions->Games->find()
			->contain([
				'HomePoolTeam',
				'AwayPoolTeam',
				'GameSlots',
			])
			->where($conditions)
			->toArray();

		$date = $this->request->getQuery('date');
		if ($date) {
			$date = new FrozenDate($date);
			$multi_day = ($division->schedule_type != 'tournament' && count($division->days) > 1);
			if ($multi_day) {
				$end = $date->next(Configure::read('organization.first_day'))->subDay();
				$games = collection($games)->filter(function ($game) use ($date, $end) {
					return $game->game_slot->game_date >= $date && $game->game_slot->game_date < $end;
				});
			} else {
				$games = collection($games)->filter(function ($game) use ($date) {
					return $game->game_slot->game_date == $date;
				});
			}
		}
		if (empty($games)) {
			$this->Flash->warning(__('There are currently no dependencies to initialize in this division.'));
			return $this->redirect(['action' => 'schedule', 'division' => $id]);
		}

		$pools = array_unique(collection($games)->extract('pool_id')->toArray());

		if ($division->schedule_type == 'tournament') {
			$seeds = array_unique(collection($division->teams)->extract('initial_seed')->toArray());
			if (count($division->teams) != count($seeds)) {
				$msg = __('Each team must have a unique initial seed.');
				if ($division->is_playoff) {
					$this->Flash->html([$msg, __('Perhaps you need to {0}?')], [
						'params' => [
							'class' => 'warning',
							'replacements' => [
								[
									'type' => 'link',
									'link' => __('initialize division ratings'),
									'target' => ['action' => 'initialize_ratings', 'division' => $id],
								],
							],
						],
					]);
				} else {
					$this->Flash->warning($msg);
				}
				return $this->redirect(['action' => 'seeds', 'division' => $id]);
			}
		}

		$league_obj = $this->moduleRegistry->load("LeagueType:{$division->schedule_type}");
		$spirit_obj = $division->league->hasSpirit() ? $this->moduleRegistry->load("Spirit:{$division->league->sotg_questions}") : null;
		$league_obj->sort($division, $division->league, $division->games, $spirit_obj, false);
		$reset = $this->request->getQuery('reset');
		$operation = ($reset ? __('reset') : __('update'));

		// Go through all games, updating seed dependencies
		foreach ($division->games as $game) {
			if (!in_array($game->pool_id, $pools)) {
				continue;
			}
			if ($game->isFinalized()) {
				continue;
			}

			foreach (['home', 'away'] as $type) {
				$pool_team = "{$type}_pool_team";
				if ($game->has($pool_team)) {
					$field = "{$type}_team_id";

					if ($reset) {
						$team_id = null;
					} else {
						switch ($game->$pool_team->dependency_type) {
							case 'seed':
								$seed = $game->$pool_team->dependency_id;
								if ($seed > count($division->teams)) {
									$this->Flash->warning(__('Not enough teams in the division to fulfill all scheduled seeds.'));
									return $this->redirect(['action' => 'schedule', 'division' => $id]);
								}
								// The sort call above leaves the teams array indexed by id, but we don't want that here
								$teams = array_values($division->teams);
								$team_id = $teams[$seed - 1]->id;
								break;

							case 'pool':
								$stage_id = $game->$pool_team->dependency_pool->stage;
								$pool_id = $game->$pool_team->dependency_pool_id;
								$seed = $game->$pool_team->dependency_id;
								$pool = $division->_results->pools[$stage_id][$pool_id];
								if (!$pool->has('teams')) {
									$aliases = collection($pool->games)->combine('home_pool_team.alias', 'home_pool_team.team_id')->toArray() + collection($pool->games)->combine('away_pool_team.alias', 'away_pool_team.team_id')->toArray();
									$pool->teams = [];
									foreach ($aliases as $alias => $team_id) {
										$pool->teams[] = $division->teams[$team_id];
									}
									$sort_context = ['results' => 'pool', 'stage' => $stage_id, 'pool' => $pool_id, 'tie_breaker' => $division->league->tie_breakers];
									\App\Lib\context_usort($pool->teams, ['App\Model\Results\Comparison', 'compareTeamsResults'], $sort_context);
									Comparison::detectAndResolveTies($pool->teams, ['App\Model\Results\Comparison', 'compareTeamsResults'], $sort_context);
								}
								$team_id = $pool->teams[$seed - 1]->id;
								break;

							case 'ordinal':
								// The stage we're looking at for these results might be the
								// one before this one, or it might be two stages ago, if
								// the previous stage was crossover games.
								$stage_id = $game->$pool_team->pool->stage - 1;
								$pool_id = current(array_keys($division->_results->pools[$stage_id]));
								if ($division->_results->pools[$stage_id][$pool_id]->games[0]->home_pool_team->pool->type == 'crossover') {
									-- $stage_id;
								}

								$ordinal = $game->$pool_team->dependency_ordinal;
								$teams = [];
								foreach ($division->_results->pools[$stage_id] as $pool_id => $pool) {
									if (!$pool->has('teams')) {
										$aliases = collection($pool->games)->combine('home_pool_team.alias', 'home_pool_team.team_id')->toArray() + collection($pool->games)->combine('away_pool_team.alias', 'away_pool_team.team_id')->toArray();
										$pool->teams = [];
										foreach ($aliases as $alias => $team_id) {
											$pool->teams[] = $division->teams[$team_id];
										}
										$sort_context = ['results' => 'pool', 'stage' => $stage_id, 'pool' => $pool_id];
										\App\Lib\context_usort($pool->teams, ['App\Model\Results\Comparison', 'compareTeamsTournamentResults'], $sort_context);
										Comparison::detectAndResolveTies($pool->teams, ['App\Model\Results\Comparison', 'compareTeamsTournamentResults'], $sort_context);
									}
									$teams[] = $pool->teams[$ordinal - 1];
								}
								$sort_context = ['results' => 'stage', 'stage' => $stage_id];
								\App\Lib\context_usort($teams, ['App\Model\Results\Comparison', 'compareTeamsResultsCrossPool'], $sort_context);
								$seed = $game->$pool_team->dependency_id;
								$team_id = $teams[$seed - 1]->id;
								break;
						}
					}

					$game->$field = $game->$pool_team->team_id = $team_id;
					$game->dirty($pool_team, true);
				}
			}

			// Handle any carried-forward results
			if ($game->home_dependency_type == 'copy') {
				if ($reset) {
					$game = $this->Divisions->Games->patchEntity($game, [
						'home_score' => null,
						'away_score' => null,
						'approved_by_id' => null,
						'status' => 'normal',
					]);
				} else {
					$copy = collection($division->games)->firstMatch([
						'home_team_id' => $game->home_team_id,
						'away_team_id' => $game->away_team_id,
						'pool_id' => $game->home_pool_team->dependency_pool_id,
					]);
					if (empty($copy)) {
						$copy = collection($division->games)->firstMatch([
							'home_team_id' => $game->away_team_id,
							'away_team_id' => $game->home_team_id,
							'pool_id' => $game->home_pool_team->dependency_pool_id,
						]);
						$home = 'away_score';
						$away = 'home_score';
					} else {
						$home = 'home_score';
						$away = 'away_score';
					}
					if (empty($copy)) {
						$this->Flash->warning(__('Failed to {0} game dependency.', __('locate')));
						return $this->redirect(['action' => 'schedule', 'division' => $id]);
					}
					$game = $this->Divisions->Games->patchEntity($game, [
						'home_score' => $copy->$home,
						'away_score' => $copy->$away,
						'approved_by_id' => $copy->approved_by_id,
						'status' => $copy->status,
						'modified' => $copy->modified,
					]);
				}
			}

			if (!$this->Divisions->Games->save($game)) {
				$this->Flash->warning(__('Failed to {0} game dependency.', __($operation)));
				return $this->redirect(['action' => 'schedule', 'division' => $id]);
			}
		}
		$this->Flash->success(__('Dependencies have been {0}.', $reset ? __('reset') : __('resolved')));
		$this->Divisions->clearCache($division, ['schedule', 'standings']);

		return $this->redirect(['action' => 'schedule', 'division' => $id]);
	}

	/**
	 * Delete stage method. Deletes a tournament stage in cases where it needs to be re-done.
	 *
	 * @return \Cake\Network\Response Redirects to "schedule add" page
	 */
	public function delete_stage() {
		$id = intval($this->request->getQuery('division'));
		$stage = $this->request->getQuery('stage');
		try {
			$division = $this->Divisions->get($id, [
				'contain' => [
					'Leagues',
					'Pools' => [
						'queryBuilder' => function (Query $q) use ($stage) {
							return $q->where(['Pools.stage' => $stage]);
						},
					],
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		}

		$this->Authorization->authorize($division);
		$this->Configuration->loadAffiliate($division->league->affiliate_id);

		if (empty($division->pools)) {
			$this->Flash->warning(__('There are currently no pools to delete in this stage.'));
			return $this->redirect(['controller' => 'Schedules', 'action' => 'add', 'division' => $id]);
		}

		if ($this->Divisions->Pools->connection()->transactional(function () use ($division, $stage) {
			foreach ($division->pools as $pool) {
				$this->Divisions->Pools->delete($pool);
			}

			$this->Divisions->clearCache($division, ['schedule', 'standings']);
			return true;
		})) {
			$this->Flash->success(__('The pools in this stage have been deleted.'));
		} else {
			$this->Flash->warning(__('Pools in this stage were not deleted.'));
		}

		return $this->redirect(['controller' => 'Schedules', 'action' => 'add', 'division' => $id]);
	}

	/**
	 * Override the redirect function; if it's a view and there's only one division, view the league instead
	 */
	public function redirect($url = null, $status = 302) {
		if (is_array($url) && in_array($url['action'], ['edit', 'view']) && (!array_key_exists('controller', $url) || $url['controller'] == 'Divisions')) {
			$league = $this->Divisions->league($url['division']);
			if ($this->Divisions->find('byLeague', compact('league'))->count() == 1) {
				return parent::redirect(['controller' => 'Leagues', 'action' => $url['action'], 'league' => $league], $status);
			}
		}
		return parent::redirect($url, $status);
	}

	public function select() {
		$this->Authorization->authorize($this);
		$this->request->allowMethod('ajax');

		$date = new DateType();
		$date = $date->marshal($this->request->data['game_date']);

		$query = $this->Divisions->find('open')
			->find('date', ['date' => $date])
			->contain(['Leagues'])
			->where(['Leagues.affiliate_id' => $this->request->getQuery('affiliate')]);

		$sport = $this->request->data['sport'];
		if ($sport) {
			$query->where(['Leagues.sport' => $sport]);
		}

		$this->set('divisions', $query->combine('id', 'full_league_name')->toArray());
	}

}
