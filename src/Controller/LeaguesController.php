<?php
namespace App\Controller;

use App\Model\Entity\League;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;
use App\Exception\ScheduleException;
use App\Model\Table\GamesTable;
use App\Model\Table\LeaguesTable;

/**
 * Leagues Controller
 *
 * @property \App\Model\Table\LeaguesTable $Leagues
 */
class LeaguesController extends AppController {

	/**
	 * _noAuthenticationActions method
	 *
	 * @return array of actions that can be taken even by visitors that are not logged in.
	 */
	protected function _noAuthenticationActions() {
		return ['index', 'view', 'schedule', 'standings', 'tooltip'];
	}

	/**
	 * _noAuthenticationJsonActions method
	 *
	 * @return array of JSON actions that can be taken even by visitors that are not logged in.
	 */
	protected function _noAuthenticationJsonActions() {
		return ['index', 'view', 'schedule', 'standings'];
	}

	/**
	 * _freeActions method
	 *
	 * @return array list of actions that people can perform even if the system wants them to do something else
	 */
	protected function _freeActions() {
		return ['index'];
	}

	// TODO: Eliminate this if we can find a way around black-holing caused by Ajax field adds
	public function beforeFilter(\Cake\Event\Event $event) {
		parent::beforeFilter($event);
		if (isset($this->Security)) {
			$this->Security->config('unlockedActions', ['add', 'edit']);
		}
	}

	/**
	 * Index method
	 *
	 * @return void|\Cake\Network\Response
	 */
	public function index() {
		$year = $this->request->getQuery('year');
		if ($year === null) {
			$conditions = ['OR' => [
				'Leagues.is_open' => true,
				'Leagues.open >' => FrozenDate::now(),
			]];
		} else {
			$conditions = ['YEAR(Leagues.open)' => $year];
		}

		$affiliate = $this->request->getQuery('affiliate');
		$affiliates = $this->Authentication->applicableAffiliateIDs();
		$conditions['Leagues.affiliate_id IN'] = $affiliates;

		$sport = $this->request->getQuery('sport');
		if ($sport) {
			$conditions['Leagues.sport'] = $sport;
		}

		$tournaments = $this->request->getParam('tournaments');

		$leagues = $this->Leagues->find()
			->contain([
				'Affiliates',
				'Divisions' => [
					'queryBuilder' => function (Query $q) use ($tournaments) {
						if ($tournaments) {
							return $q->where(['Divisions.schedule_type' => 'tournament']);
						} else {
							return $q->where(['Divisions.schedule_type !=' => 'tournament']);
						}
					},
					'Days',
				],
			])
			->where($conditions)
			->reject(function (League $league) {
				return empty($league->divisions);
			})
			->toArray();

		usort($leagues, [LeaguesTable::class, 'compareLeagueAndDivision']);
		$this->set(compact('leagues', 'affiliate', 'affiliates', 'sport', 'tournaments'));

		$open = $this->Leagues->find()
			->enableHydration(false)
			->select(['year' => 'DISTINCT YEAR(Leagues.open)'])
			->where([
				'YEAR(Leagues.open) !=' => 0,
				'Leagues.affiliate_id IN' => $affiliates,
			])
			->order(['year'])
			->toArray();
		$close = $this->Leagues->find()
			->enableHydration(false)
			->select(['year' => 'DISTINCT YEAR(Leagues.close)'])
			->where([
				'YEAR(Leagues.close) !=' => 0,
				'Leagues.affiliate_id IN' => $affiliates,
			])
			->order(['year'])
			->toArray();
		$years = array_unique(collection(array_merge($open, $close))->extract('year')->toArray());
		$this->set(compact('years'));
		$this->set('_serialize', ['league', 'years']);
	}

	public function summary() {
		$affiliates = $this->Authentication->applicableAffiliateIDs(true);
		$divisions = $this->Leagues->Divisions->find()
			->contain(['Leagues' => ['Affiliates', 'Categories'], 'Days'])
			->where([
				'OR' => [
					'Leagues.is_open' => true,
					'Leagues.open >' => FrozenDate::now(),
				],
				'Leagues.affiliate_id IN' => $affiliates,
			])
			->toArray();

		if (empty($divisions)) {
			$this->Flash->info(__('You have no current or upcoming leagues. '));
			return $this->redirect('/');
		}

		$this->Authorization->authorize(current($divisions)->league);
		usort($divisions, [LeaguesTable::class, 'compareLeagueAndDivision']);
		$this->set(compact('divisions', 'affiliates'));
		$this->set('categories', $this->Leagues->Categories->find('list')->where(['Categories.type' => 'Leagues'])->count());
	}

	/**
	 * View method
	 *
	 * @return void|\Cake\Network\Response
	 */
	public function view() {
		$id = $this->request->getQuery('league') ?: $this->request->getQuery('tournament');
		try {
			$league = $this->Leagues->get($id, [
				'contain' => [
					'Divisions',
					'Affiliates',
					'Categories',
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid league.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid league.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Configuration->loadAffiliate($league->affiliate_id);

		if (count($league->divisions) == 1) {
			$this->Leagues->Divisions->prepForView($league->divisions[0]);
			$league_obj = $this->moduleRegistry->load("LeagueType:{$league->divisions[0]->schedule_type}");
		} else {
			$league_obj = null;
		}

		$affiliates = $this->Authentication->applicableAffiliateIDs(true);

		$this->set(compact('league', 'league_obj', 'affiliates'));
		$this->set('_serialize', ['league']);
	}

	public function tooltip() {
		$this->request->allowMethod('ajax');

		$id = $this->request->getQuery('league') ?: $this->request->getQuery('tournament');
		try {
			$league = $this->Leagues->get($id, [
				'contain' => [
					'Divisions' => [
						'People',
						'Days' => [
							'queryBuilder' => function (Query $q) {
								return $q->order(['DivisionsDays.day_id']);
							},
						],
						'Teams',
					],
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid league.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid league.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Configuration->loadAffiliate($league->affiliate_id);
		$this->set(compact('league'));
	}

	public function participation() {
		$id = $this->request->getQuery('league') ?: $this->request->getQuery('tournament');
		$contain = [
			'Divisions' => [
				'Teams' => [
					'People' => [],
				],
			],
		];
		if ($this->request->is('csv')) {
			$contain['Divisions']['Teams']['People'] = [
				Configure::read('Security.authModel'),
				'Groups',
				'Related' => [Configure::read('Security.authModel')],
			];
			try {
				$sport = $this->Leagues->field('sport', ['Leagues.id' => $id]);
				$contain['Divisions']['Teams']['People']['Skills'] = [
					'queryBuilder' => function (Query $q) use ($sport) {
						return $q->where(['Skills.sport' => $sport]);
					},
				];
			} catch (RecordNotFoundException $ex) {
				// That's okay... We just won't get any skill records.
			}
		}

		try {
			$league = $this->Leagues->get($id, [
				'contain' => $contain,
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid league.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid league.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($league);
		$this->Configuration->loadAffiliate($league->affiliate_id);

		if ($this->request->is('csv')) {
			$this->response->download("Participation - {$league->full_name}.csv");
		}
		$this->set(compact('league'));
	}

	/**
	 * Add method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$this->Authorization->authorize($this);
		$league = $this->Leagues->newEntity();

		if ($this->request->is('post')) {
			$league = $this->Leagues->patchEntity($league, $this->request->getData(), [
				'associated' => ['StatTypes', 'Divisions' => ['validateDays' => true], 'Divisions.Days'],
			]);

			if ($this->Leagues->save($league, ['divisions' => $league->divisions])) {
				$this->Flash->success(__('The league has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The league could not be saved. Please correct the errors below and try again.'));
			}
		} else {
			$id = $this->request->getQuery('league') ?: $this->request->getQuery('tournament');
			if ($id) {
				// To clone a league, read the old one and remove the id
				try {
					$league = $this->Leagues->get($id, [
						'contain' => ['Categories', 'Divisions' => ['Days']]
					]);
				} catch (RecordNotFoundException $ex) {
					$this->Flash->info(__('Invalid league.'));
					return $this->redirect(['action' => 'index']);
				} catch (InvalidPrimaryKeyException $ex) {
					$this->Flash->info(__('Invalid league.'));
					return $this->redirect(['action' => 'index']);
				}

				$this->Authorization->authorize($league, 'edit');
				$league = $this->Leagues->cloneWithoutIds($league);
			}
		}

		$this->Configuration->loadAffiliate($league->affiliate_id);

		$sports = Configure::read('options.sport');
		if (count($sports) == 1) {
			$league->sport = current(array_keys($sports));
		}
		$this->set(compact('league'));
		$this->set('affiliates', $this->Authentication->applicableAffiliates(true));
		$this->set('days', $this->Leagues->Divisions->Days->find('list'));
		$this->set('categories', $this->Leagues->Categories->find('list')->where(['Categories.type' => 'Leagues'])->toArray());
		$this->set('stat_types', $this->Leagues->StatTypes->findBySport($league->sport));
		$this->render('edit');
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful edit, renders view otherwise.
	 */
	public function edit() {
		$id = $this->request->getQuery('league') ?: $this->request->getQuery('tournament');
		try {
			$league = $this->Leagues->get($id, [
				'contain' => [
					'Divisions' => [
						'People',
						'Days' => [
							'queryBuilder' => function (Query $q) {
								return $q->order('DivisionsDays.day_id');
							},
						],
					],
					'Categories',
					'StatTypes',
				]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid league.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid league.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($league);
		$this->Configuration->loadAffiliate($league->affiliate_id);

		if ($this->request->is(['patch', 'post', 'put'])) {
			$league = $this->Leagues->patchEntity($league, $this->request->getData(), [
				'associated' => ['Categories', 'StatTypes', 'Divisions' => ['validateDays' => true], 'Divisions.Days'],
			]);

			if ($this->Leagues->save($league, ['divisions' => $league->divisions])) {
				// Any time that this is called, the division seeding might change.
				// We just reset it here, and it will be recalculated as required elsewhere.
				$divisions = collection($league->divisions)->extract('id')->toList();
				$this->Leagues->Divisions->Teams->updateAll(['seed' => 0], ['division_id IN' => $divisions]);

				$this->Flash->success(__('The league has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The league could not be saved. Please correct the errors below and try again.'));
			}
		}

		$this->set(compact('league'));
		$this->set('affiliates', $this->Authentication->applicableAffiliates(true));
		$this->set('days', $this->Leagues->Divisions->Days->find('list'));
		$this->set('categories', $this->Leagues->Categories->find('list')->where(['Categories.type' => 'Leagues'])->toArray());
		$this->set('stat_types', $this->Leagues->StatTypes->findBySport($league->sport));

		if (count($league->divisions) == 1) {
			$this->set('league_obj', $this->moduleRegistry->load("LeagueType:{$league->divisions[0]->schedule_type}"));
		}
	}

	/**
	 * Add division function
	 *
	 * @return void Renders view, just an empty division block with a random index.
	 */
	public function add_division() {
		$this->request->allowMethod('ajax');
		$this->Authorization->authorize($this, 'add_division_fields');
		$league = $this->Leagues->newEntity();
		$this->set(compact('league'));
		// TODO: Do we need to take the league ID, if there is one, as a parameter,
		// and base this on the user's status in that league?
		$this->set('days', $this->Leagues->Divisions->Days->find('list'));
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Network\Response Redirects to index.
	 */
	public function delete() {
		$this->request->allowMethod(['post', 'delete']);

		$id = $this->request->getQuery('league') ?: $this->request->getQuery('tournament');
		try {
			$league = $this->Leagues->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid league.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid league.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($league);

		$dependencies = $this->Leagues->dependencies($id, [], ['Divisions' => ['Days', 'People']]);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this league, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['action' => 'index']);
		}

		if ($this->Leagues->delete($league)) {
			$this->Flash->success(__('The league has been deleted.'));
		} else if ($league->errors('delete')) {
			$this->Flash->warning(current($league->errors('delete')));
		} else {
			$this->Flash->warning(__('The league could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

	public function schedule() {
		$id = intval($this->request->getQuery('league') ?: $this->request->getQuery('tournament'));

		// Hopefully, everything we need is already cached
		$league = Cache::remember("league/{$id}/schedule", function () use ($id) {
			try {
				$league = $this->Leagues->get($id, [
					'finder' => 'translations',
					'contain' => [
						'Divisions' => [
							'queryBuilder' => function (Query $q) {
								return $q->find('translations');
							},
							'Days' => [
								'queryBuilder' => function (Query $q) {
									return $q->order(['DivisionsDays.day_id']);
								},
							],
							'Teams',
						],
					],
				]);
			} catch (RecordNotFoundException $ex) {
				$this->Flash->info(__('Invalid league.'));
				return null;
			} catch (InvalidPrimaryKeyException $ex) {
				$this->Flash->info(__('Invalid league.'));
				return null;
			}

			$divisions = $this->Leagues->Divisions->find()
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
						'GameSlots' => [
							'Fields' => [
								'queryBuilder' => function (Query $q) {
									return $q->find('translations');
								},
								'Facilities' => [
									'queryBuilder' => function (Query $q) {
										return $q->find('translations');
									},
								],
							],
						],
						'ScoreEntries',
						'HomeTeam',
						'HomePoolTeam' => [
							'DependencyPool' => [
								'queryBuilder' => function (Query $q) {
									return $q->find('translations');
								},
							],
						],
						'AwayTeam',
						'AwayPoolTeam' => [
							'DependencyPool' => [
								'queryBuilder' => function (Query $q) {
									return $q->find('translations');
								},
							],
						],
						'Pools' => [
							'queryBuilder' => function (Query $q) {
								return $q->find('translations');
							},
						],
					],
				])
				->where(['Divisions.id IN' => collection($league->divisions)->extract('id')->toArray()])
				->toArray();

			$league->games = [];
			foreach ($divisions as $division) {
				$raw_division = $division;
				$games = $division->games;
				unset($raw_division->games);
				foreach ($games as $game) {
					$game->division = $raw_division;
					$game->setDirty('division', false);
					$league->games[] = $game;
				}
			}
			if (empty($league->games)) {
				$this->Flash->info(__('This league has no games scheduled yet.'));
				return null;
			}

			// Sort games by date, time and field
			usort($league->games, [GamesTable::class, 'compareDateAndField']);

			return $league;
		}, 'long_term');

		if (!$league) {
			return $this->redirect(['action' => 'index']);
		}

		$this->Configuration->loadAffiliate($league->affiliate_id);

		$can_edit = $this->Authorization->can($league, 'edit_schedule');
		if ($can_edit) {
			$edit_date = $this->request->getQuery('edit_date');
		} else {
			$edit_date = null;
		}

		$multi_day = (count(array_unique(collection($league->divisions)->filter(function ($division) {
				return $division->schedule_type != 'tournament';
			})->extract('days.{*}.id')->toArray())) > 1);

		if ($edit_date) {
			$is_tournament = collection($league->games)->some(function ($game) use ($edit_date) {
				return $game->type != SEASON_GAME && $game->game_slot->game_date == $edit_date;
			});
			$divisions = [];
			$double_booking = false;
			foreach ($league->divisions as $division) {
				if ($this->Authorization->can($division, 'edit_schedule')) {
					$divisions[] = $division->id;
					$double_booking |= $division->double_booking;
				}
			}
			$game_slots = $this->Leagues->Divisions->GameSlots->find('available', [
				'divisions' => $divisions,
				'date' => $edit_date,
				'is_tournament' => $is_tournament,
				'double_booking' => $double_booking,
				'multi_day' => $multi_day,
			])->toArray();
		} else {
			$is_tournament = collection($league->games)->some(function ($game) {
				return $game->type != SEASON_GAME;
			});
			$game_slots = [];
		}

		// Save posted data
		if ($this->request->is(['patch', 'post', 'put']) && $can_edit) {
			$this->loadComponent('Lock');

			if ($this->Lock->lock('scheduling', $this->Leagues->affiliate($league->id), 'schedule creation or edit')) {
				try {
					$edit_games = $this->Leagues->Divisions->Games->patchEntities($league->games, $this->request->getData('games'),
						array_merge($this->request->getData('options'), ['validate' => 'scheduleEdit'])
					);

					$edit_ids = collection($edit_games)->extract('id')->toArray();
					$other_games = collection($league->games)->reject(function ($game) use ($edit_ids) {
						return in_array($game->id, $edit_ids);
					})->toArray();
					$league->games = array_merge($edit_games, $other_games);
					usort($league->games, [GamesTable::class, 'compareDateAndField']);

					if ($this->Leagues->Divisions->Games->getConnection()->transactional(function () use ($edit_games, $game_slots) {
						$success = true;
						$options = array_merge($this->request->getData('options'), [
							'games' => $edit_games,
							'game_slots' => $game_slots,
							'validate' => 'scheduleEdit',
						]);
						// We intentionally do not use saveMany here; it returns immediately when one save fails,
						// whereas this method will generate error messages for everything applicable.
						foreach ($edit_games as $game) {
							if (!$this->Leagues->Divisions->Games->save($game, $options)) {
								$success = false;
							}
						}
						return $success;
					})) {
						$this->Flash->success(__('Schedule changes saved!'));

						// With the saves being inside a transaction, afterSaveCommit is not called.
						$event = new Event('Model.afterSaveCommit', $this, [null]);
						$this->getEventManager()->dispatch($event);

						return $this->redirect (['action' => 'schedule', 'league' => $id]);
					}

					$this->Flash->warning(__('The games could not be saved. Please correct the errors below and try again.'));

					$event = new Event('Model.afterSaveRollback', $this, [null]);
					$this->getEventManager()->dispatch($event);
				} catch (ScheduleException $ex) {
					$this->Flash->html($ex->getMessages(), ['params' => $ex->getAttributes()]);

					$event = new Event('Model.afterSaveRollback', $this, [null]);
					$this->getEventManager()->dispatch($event);
				}
			}
		}

		$league->games = collection($league->games)->indexBy('id')->toArray();
		$this->set(compact('id', 'league', 'edit_date', 'game_slots', 'is_tournament', 'multi_day'));
		$this->set('_serialize', ['league']);
	}

	public function standings() {
		$id = intval($this->request->getQuery('league') ?: $this->request->getQuery('tournament'));

		// Hopefully, everything we need is already cached
		$league = Cache::remember("league/{$id}/standings", function () use ($id) {
			try {
				$league = $this->Leagues->get($id, [
					'finder' => 'translations',
					'contain' => [
						'Divisions' => [
							'queryBuilder' => function (Query $q) {
								return $q->find('translations');
							},
							'Days' => [
								'queryBuilder' => function (Query $q) {
									return $q->order(['DivisionsDays.day_id']);
								},
							],
							'Teams',
						],
					],
				]);
			} catch (RecordNotFoundException $ex) {
				$this->Flash->info(__('Invalid league.'));
				return null;
			} catch (InvalidPrimaryKeyException $ex) {
				$this->Flash->info(__('Invalid league.'));
				return null;
			}

			$this->Configuration->loadAffiliate($league->affiliate_id);
			$spirit_obj = $league->hasSpirit() ? $this->moduleRegistry->load("Spirit:{$league->sotg_questions}") : null;

			foreach ($league->divisions as $key => $division) {
				// Find all games played by teams that are currently in this division,
				// or tournament games for this division
				$teams = collection($division->teams)->extract('id')->toArray();
				$conditions = [
					'Games.division_id' => $division->id,
					'Games.type !=' => SEASON_GAME,
				];
				if (!empty($teams)) {
					$conditions = [
						'OR' => [
							'Games.home_team_id IN' => $teams,
							'Games.away_team_id IN' => $teams,
							'AND' => $conditions,
						],
					];
				}

				$division->games = $this->Leagues->Divisions->Games->find('played')
					->contain([
						'GameSlots',
						'HomePoolTeam' => [
							'Pools' => [
								'queryBuilder' => function (Query $q) {
									return $q->find('translations');
								},
							],
							'DependencyPool' => [
								'queryBuilder' => function (Query $q) {
									return $q->find('translations');
								},
							],
						],
						'AwayPoolTeam' => [
							'Pools' => [
								'queryBuilder' => function (Query $q) {
									return $q->find('translations');
								},
							],
							'DependencyPool' => [
								'queryBuilder' => function (Query $q) {
									return $q->find('translations');
								},
							],
						],
						'ScoreEntries',
						'SpiritEntries',
					])
					->where($conditions)
					->toArray();

				if (!empty($division->games)) {
					// Sort games by date, time and field
					usort($division->games, [GamesTable::class, 'compareDateAndField']);
					GamesTable::adjustEntryIndices($division->games);
					$division->setDirty('games', false);
				}

				$league_obj = $this->moduleRegistry->load("LeagueType:{$division->schedule_type}");
				$league_obj->sort($division, $league, $division->games, $spirit_obj, false);
				$division->render_element = $league_obj->render_element;

				// If there's anyone without seed information, save the seeds
				if (collection($division->teams)->some(function ($team) { return $team->seed == 0; })) {
					$seed = 0;
					foreach ($division->teams as $tkey => $team) {
						$team->seed = ++$seed;
					}
					$division->setDirty('teams', true);
					$league->setDirty('divisions', true);
				}
			}
			$this->Leagues->save($league);

			return $league;
		}, 'long_term');

		if (!$league) {
			return $this->redirect(['action' => 'index']);
		}

		$this->Configuration->loadAffiliate($league->affiliate_id);
		$spirit_obj = $league->hasSpirit() ? $this->moduleRegistry->load("Spirit:{$league->sotg_questions}") : null;

		$this->set(compact('league', 'spirit_obj'));
		$this->set('_serialize', ['league']);
	}

	public function slots() {
		$id = $this->request->getQuery('league') ?: $this->request->getQuery('tournament');
		try {
			$league = $this->Leagues->get($id, [
				'contain' => [
					'Divisions',
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid league.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid league.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($league, 'edit_schedule');
		$this->Configuration->loadAffiliate($league->affiliate_id);

		// Find all the dates that this league has game slots on
		$divisions = collection($league->divisions)->extract('id')->toArray();
		$dates = $this->Leagues->Divisions->GameSlots->find()
			->enableHydration(false)
			->select(['GameSlots.game_date'])
			->distinct(['GameSlots.game_date'])
			->matching('Divisions', function (Query $q) use ($divisions) {
				return $q->where(['Divisions.id IN' => $divisions]);
			})
			->order(['GameSlots.game_date'])
			->extract('game_date')
			->toArray();

		$date = $this->request->getQuery('date');
		if ($this->request->is(['patch', 'post', 'put']) && array_key_exists('date', $this->request->data)) {
			$date = $this->request->getData('date');
			// TODO: Is there a way to make the Ajax form submitter not send the string literal "null"?
			if (empty($date) || $date == 'null') {
				$this->Flash->info(__('You must select a date.'));
				return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
			}
		}
		if (!empty($date)) {
			$slots = $this->Leagues->Divisions->GameSlots->find()
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
						'Divisions',
						'Pools',
						'HomeTeam' => [
							'Facilities' => ['Fields'],
							'Regions',
						],
						'HomePoolTeam' => ['DependencyPool'],
						'AwayTeam' => [
							'Facilities' => ['Fields'],
							'Regions',
						],
						'AwayPoolTeam' => ['DependencyPool'],
					],
					'Fields' => [
						'Facilities' => ['Regions'],
					],
				])
				->distinct('GameSlots.id')
				->matching('Divisions', function (Query $q) use ($divisions) {
					return $q->where(['Divisions.id IN' => $divisions]);
				})
				->where(['GameSlots.game_date' => $date])
				->order(['GameSlots.game_start', 'Fields.id'])
				->toArray();

			$is_tournament = collection($slots)->extract('games.{*}')->some(function ($game) { return $game->type != SEASON_GAME; });
		} else {
			$slots = [];
			$is_tournament = false;
		}

		$this->set(compact('league', 'dates', 'date', 'slots', 'is_tournament'));
	}

}
