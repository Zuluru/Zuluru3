<?php
namespace App\Controller;

use App\Exception\ScheduleException;
use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event as CakeEvent;
use Cake\Event\EventManager;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\I18n\Number;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;
use App\Model\Table\GamesTable;

/**
 * Schedules Controller
 *
 * @property \App\Model\Table\DivisionsTable $Divisions
 */
class SchedulesController extends AppController {

	private $numTeams = null;
	private $pool = null;

	/**
	 * @return void
	 * @throws \Exception
	 */
	public function initialize() {
		parent::initialize();
		$this->loadModel('Divisions');
	}

	/**
	 * _noAuthenticationActions method
	 *
	 * @return array of actions that can be taken even by visitors that are not logged in.
	 */
	protected function _noAuthenticationActions() {
		return ['today', 'day'];
	}

	/**
	 * _noAuthenticationJsonActions method
	 *
	 * @return array of JSON actions that can be taken even by visitors that are not logged in.
	 */
	protected function _noAuthenticationJsonActions() {
		return ['today'];
	}

	// TODO: Proper fix for black-holing of schedule deletion
	public function beforeFilter(\Cake\Event\Event $event) {
		parent::beforeFilter($event);
		if (isset($this->Security)) {
			$this->Security->setConfig('unlockedActions', ['delete']);
		}
	}

	public function add() {
		$id = intval($this->getRequest()->getQuery('division'));
		try {
			$division = $this->Divisions->get($id, [
				'contain' => [
					'Teams' => [
						'queryBuilder' => function (Query $q) {
							return $q->order(['Teams.name']);
						},
					],
					'Leagues',
					'Pools' => [
						'queryBuilder' => function (Query $q) {
							return $q->order(['Pools.id']);
						},
						'PoolsTeams' => [
							'queryBuilder' => function (Query $q) {
								return $q->order(['PoolsTeams.id']);
							},
						],
					],
				]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->warning(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->warning(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		}

		$this->Authorization->authorize($division, 'edit_schedule');

		if ($division->schedule_type == 'none') {
			$this->Flash->warning(__('This division\'s "schedule type" is set to "none", so no games can be added.'));
			return $this->redirect(['controller' => 'Divisions', 'action' => 'view', 'division' => $id]);
		}
		$this->Configuration->loadAffiliate($division->league->affiliate_id);

		if ($this->getRequest()->getQuery('playoff') ||
			($division->schedule_type != 'tournament' && $this->_unscheduledPools($division)))
		{
			$this->league_obj = $this->moduleRegistry->load('LeagueType:tournament');
			$this->set('playoff', true);
		} else {
			$this->league_obj = $this->moduleRegistry->load("LeagueType:{$division->schedule_type}");
		}

		$division->games = [];

		// Import posted data into the _options property
		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			$division->_options = new Entity($this->getRequest()->getData('_options'));
			// TODO: Make a non-table-backed entity type with a custom schema that can do this as part of the patch?
			if ($division->_options->has('start_date')) {
				if (is_array($division->_options->start_date)) {
					$division->_options->start_date = array_map(function ($date) { return new FrozenTime($date); }, $division->_options->start_date);
				} else {
					$division->_options->start_date = new FrozenDate($division->_options->start_date);
				}
			}
		} else {
			$division->_options = new Entity();

			// What's the default first step?
			if (isset($this->pool)) {
				$division->_options->step = 'type';
			} else if ($this->getRequest()->getQuery('playoff') || $division->schedule_type === 'tournament') {
				$division->_options->step = 'pools';
			} else if ($division->exclude_teams) {
				$division->_options->step = 'exclude';
			} else {
				$division->_options->step = 'type';
			}
		}

		if ($this->_numTeams($division) < 2) {
			$this->Flash->warning(__('Cannot schedule games in a division with less than two teams.'));
			return $this->redirect(['controller' => 'Divisions', 'action' => 'view', 'division' => $id]);
		}

		// Non-tournament divisions must currently have even # of teams for scheduling unless the exclude_teams flag is set
		if ($this->_numTeams($division) % 2 && !$division->exclude_teams &&
			$division->schedule_type !== 'tournament' && $division->schedule_type !== 'competition' &&
			!$this->getRequest()->getQuery('playoff') && !$this->pool)
		{
			$this->Flash->html(__('Must currently have an even number of teams in your division. If you need a bye, please create a team named Bye and add it to your division. Otherwise, {0} and set the "exclude teams" flag.'), [
				'params' => [
					'class' => 'warning',
					'replacements' => [
						[
							'type' => 'link',
							'link' => __('edit your division'),
							'target' => ['controller' => 'Divisions', 'action' => 'edit', 'division' => $id],
						],
					],
				],
			]);
			return $this->redirect(['controller' => 'Divisions', 'action' => 'view', 'division' => $id]);
		}

		$stages = collection($division->pools)->extract('stage')->toList();
		if (!empty($stages)) {
			$stage = max($stages);
		} else {
			$stage = 0;
		}

		$this->set(compact(['id', 'division']));

		$func = "_{$division->_options->step}";
		return $this->$func($division, $stage);
	}

	protected function _exclude($division, $stage) {
		// Validate any exclusion selection
		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			if ($this->_numTeams($division) % 2) {
				$this->Flash->warning(__('You marked {0} teams to exclude, that leaves {1}. Cannot schedule games for an un-even number of teams!',
					!empty($division->_options->exclude) ? count($division->_options->exclude) : __('no'),
					$this->_numTeams($division)));
			} else {
				return $this->_type($division, $stage);
			}
		}
		return $this->render('exclude');
	}

	// TODO: A way to allow teams to be excluded from playoffs. Tricky because the teams to skip don't really come
	// into play until the dependencies get initialized.
	protected function _pools($division, $stage) {
		if ($this->_unscheduledPools($division)) {
			return $this->_type($division, $stage);
		}

		// We are looking to create pools for the next stage, not the latest one
		$next_stage = $stage + 1;
		$types = $this->league_obj->poolOptions($this->_numTeams($division), $next_stage);

		// Validate any data posted to us
		if ($this->getRequest()->is(['patch', 'post', 'put']) && $division->_options->step == 'pools') {
			if (!array_key_exists($division->_options->pool_type, $types)) {
				$this->Flash->warning(__('Select the number of pools to add.'));
			} else if ($division->_options->pool_type == 'crossover') {
				return $this->_crosscount($division, $stage);
			} else {
				return $this->_details($division, $stage);
			}
		}

		$this->set(compact('types', 'stage'));
		return $this->render('pools');
	}

	protected function _crosscount($division, $stage) {
		// Validate any data posted to us
		if ($this->getRequest()->is(['patch', 'post', 'put']) && $division->_options->step == 'crosscount') {
			[$type, $pools] = explode('_', $division->_options->pool_type);
			$pool_data = [
				'division_id' => $division->id,
				'type' => $type,
				// Creating pools for the next stage
				'stage' => $stage + 1,
				'pools_teams' => [],
			];
			$division->_options->pools = [];
			for ($i = 1, $name = 'A'; $i <= $pools; ++ $i, ++ $name) {
				$division->_options->pools[$i] = $this->Divisions->Pools->newEntity(
					array_merge([
						'name' => "X$name",
						'count' => 2,
					], $pool_data),
					['accessibleFields' => ['count' => true]]
				);
			}
			return $this->_reseed($division, $stage);
		}

		$this->set('teams', $this->_numTeams($division));
		return $this->render('crossover');
	}

	protected function _details($division, $stage) {
		[$type, $pools] = explode('_', $division->_options->pool_type);

		$pool_data = [
			'division_id' => $division->id,
			'type' => $type,
			// Creating pools for the next stage
			'stage' => $stage + 1,
			'pools_teams' => [],
		];

		if ($pools == 1) {
			$division->_options->pools = [
				1 => $this->Divisions->Pools->newEntity(array_merge([
					'name' => 'A',
					'count' => $this->_numTeams($division),
				], $pool_data))
			];
			$func = "_$type";
			return $this->$func($division, $stage);
		}

		// Validate any data posted to us
		if ($this->getRequest()->is(['patch', 'post', 'put']) && $division->_options->step == 'details') {
			for ($i = 1; $i <= $pools; ++ $i) {
				$division->_options->pools[$i] = $this->Divisions->Pools->newEntity(
					array_merge($division->_options->pools[$i], $pool_data),
					['accessibleFields' => ['count' => true]]
				);
			}

			if ($type != 'snake' && array_sum(collection($division->_options->pools)->extract('count')->toList()) != $this->_numTeams($division)) {
				$division->_options->pools[1]->setErrors('count', __('Number of teams must add up to {0}.', $this->_numTeams($division)));
			}

			if (!$division->getErrors()) {
				// Call the specific type function to populate the pools_teams dependency records
				$func = "_$type";
				$ret = $this->$func($division, $stage);
				if ($ret) {
					return $ret;
				}
			}
		}

		$size = floor($this->_numTeams($division) / $pools);
		$sizes = array_fill(1, $pools, $size);
		$r = $this->_numTeams($division) % $pools;
		for ($i = 1; $i <= $r; ++ $i) {
			++ $sizes[$i];
		}

		$existing_names = collection($division->pools)->filter(function ($pool) {
			return $pool->type != 'crossover';
		})->extract('name')->toList();
		if (!empty($existing_names)) {
			$name = max($existing_names);
			++ $name;
		} else {
			$name = 'A';
		}

		$this->set(compact('type', 'pools', 'sizes', 'name'));
		return $this->render('details');
	}

	protected function _seeded($division, $stage) {
		$seed = 1;

		foreach ($division->_options->pools as $pool) {
			for ($i = 1; $i <= $pool->count; ++ $i) {
				$pool->pools_teams[] = $this->Divisions->Pools->PoolsTeams->newEntity([
					'alias' => "{$pool->name}$i",
					'dependency_type' => 'seed',
					'dependency_id' => $seed++,
				]);
			}
		}

		$ret = $this->_savePools($division);
		if ($ret) {
			return $ret;
		}
	}

	protected function _snake($division, $stage) {
		$num_teams = $this->_numTeams($division);
		$pools = count($division->_options->pools);
		$seed = 1;
		for ($tier = 1; $seed <= $num_teams; ++ $tier) {
			for ($pool_index = 1; $pool_index <= $pools && $seed <= $num_teams; ++ $pool_index) {
				$pool = $division->_options->pools[$pool_index];
				$pool->pools_teams[] = $this->Divisions->Pools->PoolsTeams->newEntity([
					'alias' => "{$pool->name}$tier",
					'dependency_type' => 'seed',
					'dependency_id' => $seed++,
				]);
			}
			++ $tier;
			for ($pool_index = $pools; $pool_index > 0 && $seed <= $num_teams; -- $pool_index) {
				$pool = $division->_options->pools[$pool_index];
				$pool->pools_teams[] = $this->Divisions->Pools->PoolsTeams->newEntity([
					'alias' => "{$pool->name}$tier",
					'dependency_type' => 'seed',
					'dependency_id' => $seed++,
				]);
			}
		}

		$ret = $this->_savePools($division);
		if ($ret) {
			return $ret;
		}
	}

	protected function _reseed($division, $stage) {
		$options = $valid_options = $pool_sizes = $ordinal_counts = $save = [];
		[$type, $pools] = explode('_', $division->_options->pool_type);

		$this_stage = $stage + 1;

		// Check if the previous stage was crossovers
		$crossovers = collection($division->pools)->match(['type' => 'crossover', 'stage' => $stage]);
		if (!$crossovers->isEmpty()) {
			-- $stage;
		}
		$crossover_teams = $crossovers->extract('pools_teams.{*}');

		// List of finishing options for each pool
		foreach ($division->pools as $pool) {
			if ($pool->stage == $stage) {
				$group = "Pool {$pool->name}";
				$pool_sizes[] = count($pool->pools_teams);
				for ($i = 1; $i <= count($pool->pools_teams); ++ $i) {
					// Do not add this as an option if it's already been used as a dependency for a crossover pool
					if (!$crossover_teams->some(function ($team) use ($pool, $i) {
						return $team->dependency_pool_id == $pool->id && $team->dependency_id == $i;
					})) {
						$key = "{$pool->name}-$i";
						$options[$group][$key] = Number::ordinal($i) . " ($key)";
						if (!array_key_exists($i, $ordinal_counts)) {
							$ordinal_counts[$i] = 1;
						} else {
							++ $ordinal_counts[$i];
						}
					}
				}
				if (!empty($options[$group])) {
					$valid_options = array_merge($valid_options, $options[$group]);
				}
			}
		}

		// List of finishing options between pools
		for ($ordinal = 1; $ordinal <= max($pool_sizes); ++ $ordinal) {
			if (array_key_exists($ordinal, $ordinal_counts)) {
				$group = Number::ordinal($ordinal) . ' ' . __('place teams');

				// List of finishing options for each pool
				for ($i = 1; $i <= $ordinal_counts[$ordinal]; ++ $i) {
					if (!$crossover_teams->some(function ($team) use ($ordinal, $i) {
						return $team->dependency_ordinal == $ordinal && $team->dependency_id == $i;
					})) {
						$key = "$ordinal-$i";
						$options[$group][$key] = Number::ordinal($i) . " ($key)";
					}
				}

				$valid_options = array_merge($valid_options, $options[$group]);
			}
		}

		// Add any crossovers
		foreach ($crossovers as $crossover) {
			$options['Crossovers']["{$crossover->name}-1"] = "Winner of {$crossover->name}";
			$options['Crossovers']["{$crossover->name}-2"] = "Loser of {$crossover->name}";
			$valid_options = array_merge($valid_options, $options['Crossovers']);
		}

		// Validate any data posted to us, building the data to save as we go
		if ($this->getRequest()->is(['patch', 'post', 'put']) && $division->_options->step == 'reseed') {
			$proceed = true;

			$validator = $this->Divisions->Pools->PoolsTeams->validationQualifiers(new Validator(), array_keys($valid_options));

			for ($i = 1; $i <= $pools; ++ $i) {
				// Save the array-formatted data
				$data = $division->_options->pools[$i];

				// Replace it with an entity
				$pool = $division->_options->pools[$i] = $this->Divisions->Pools->newEntity(
					array_merge(
						$data,
						[
							'division_id' => $division->id,
							'type' => ($type == 'crossover' ? 'crossover' : 'power'),
							'stage' => $this_stage,
							'pools_teams' => [],
						]
					),
					['accessibleFields' => ['count' => true]]
				);

				$pool->pools_teams = [];
				foreach ($data['pools_teams'] as $j => $team) {
					// TODOLATER: Move this differentiation into beforeMarshal?
					if (strpos($team['qualifier'], '-') !== false) {
						[$qpool, $qpos] = explode('-', $team['qualifier']);
					} else {
						$qpool = 1; // Don't want to run the firstMatch check in this case
						$qpos = null;
					}
					if (is_numeric($qpool)) {
						$pool->pools_teams[$j] = $this->Divisions->Pools->PoolsTeams->newEntity([
							'qualifier' => $team['qualifier'],
							'alias' => "{$pool->name}$j",
							'dependency_type' => 'ordinal',
							'dependency_ordinal' => $qpool,
							'dependency_id' => $qpos,
						], [
							// TODOLATER: Do this validation without passing the qualifier, using pools, etc. directly?
							'accessibleFields' => ['qualifier' => true],
							'validate' => $validator,
						]);
					} else {
						$pool_id = collection($division->pools)->firstMatch(['name' => $qpool])->id;
						$pool->pools_teams[$j] = $this->Divisions->Pools->PoolsTeams->newEntity([
							'qualifier' => $team['qualifier'],
							'alias' => "{$pool->name}$j",
							'dependency_type' => 'pool',
							'dependency_pool_id' => $pool_id,
							'dependency_id' => $qpos,
						], [
							'accessibleFields' => ['qualifier' => true],
							'validate' => $validator,
						]);
					}
				}
			}

			$ret = $this->_savePools($division);
			if ($ret) {
				return $ret;
			}
		}

		$this->set(compact('type', 'options'));
		return $this->render('reseed');
	}

	protected function _savePools($division) {
		$this->loadComponent('Lock');
		if (!$this->Lock->lock('scheduling', $division->league->affiliate_id, 'schedule creation or edit')) {
			return $this->redirect(['controller' => 'Divisions', 'action' => 'view', 'division' => $division->id]);
		}

		if ($this->Divisions->Pools->getConnection()->transactional(function () use ($division) {
			foreach ($division->_options->pools as $pool) {
				if (!$this->Divisions->Pools->save($pool, ['division' => $division, 'pools' => $division->_options->pools])) {
					return false;
				}
			}

			return true;
		})) {
			$this->Flash->success(__('The pools have been saved.'));
			return $this->redirect(['controller' => 'Schedules', 'action' => 'add', 'division' => $division->id]);
		} else {
			$this->Flash->warning(__('The pools could not be saved. Please correct the errors below and try again.'));
		}
	}

	protected function _type($division, $stage) {
		if ($this->pool && $this->pool->type == 'crossover') {
			$types = ['crossover' => 'crossover game'];
		} else {
			$types = $this->league_obj->scheduleOptions($this->_numTeams($division), $stage, $division->league->sport);
		}

		// Validate any data posted to us
		if ($this->getRequest()->is(['patch', 'post', 'put']) && $division->_options->step == 'type') {
			if (!array_key_exists($division->_options->type, $types)) {
				$this->Flash->warning(__('Select the type of game or games to add.'));
			} else {
				return $this->_date($division, $stage);
			}
		}

		$this->set(compact('types', 'stage'));
		return $this->render('type');
	}

	protected function _date($division, $stage) {
		// Validate any data posted to us
		if ($this->getRequest()->is(['patch', 'post', 'put']) && $division->_options->step == 'date') {
			if ($this->_canSchedule($division, $stage)) {
				return $this->_confirm($division, $stage);
			}
		}

		try {
			$preview = $this->league_obj->schedulePreview($division, $this->pool, $division->_options->type, $this->_numTeams($division));
		} catch (ScheduleException $ex) {
			$this->Flash->html($ex->getMessages(), ['params' => $ex->getAttributes()]);
			$preview = null;
		}

		// Find the list of available dates for scheduling this division
		$this->Divisions->loadInto($division, [
			'GameSlots' => [
				'queryBuilder' => function (Query $q) use ($division, $preview) {
					if (empty($preview)) {
						$q->select(['date' => 'GameSlots.game_date']);
						$date_type = 'date';
					} else {
						// TODO: Use a query object here
						$q->select(['date' => 'CONCAT(GameSlots.game_date, " ", GameSlots.game_start)']);
						$date_type = 'datetime';
					}
					$q->distinct('date')
						->selectTypeMap()
						->addDefaults([
							'date' => $date_type
						]);

					if (empty($division->_options->past)) {
						// TODO: Use a query object here
						$q->where(['GameSlots.game_date >=' => FrozenDate::now()]);
					}

					if (empty($division->_options->double_booking)) {
						$q->where(['GameSlots.assigned' => false]);
					}

					return $q->order(['GameSlots.game_date', 'GameSlots.game_start']);
				},
			],
		]);

		if (count($division->game_slots) == 0 && !Configure::read('feature.allow_past_games')) {
			$this->Flash->warning(__('Sorry, there are no {0} available for your division. Check that {0} have been allocated before attempting to proceed.', __(Configure::read("sports.{$division->league->sport}.fields"))));
			return $this->redirect(['controller' => 'Divisions', 'action' => 'view', 'division' => $division->id]);
		}

		$dates = collection($division->game_slots)->extract('date')->toList();
		$required_field_counts = $this->league_obj->scheduleRequirements($division->_options->type, $this->_numTeams($division));
		$desc = $this->league_obj->scheduleDescription($division->_options->type, $this->_numTeams($division), $stage, $division->league->sport);

		$this->set(compact('dates', 'required_field_counts', 'desc', 'preview'));
		return $this->render('date');
	}

	protected function _confirm($division, $stage) {
		if (!$this->_canSchedule($division, $stage)) {
			return $this->redirect(['controller' => 'Divisions', 'action' => 'view', 'division' => $division->id]);
		}

		$this->set([
			'desc' => $this->league_obj->scheduleDescription($division->_options->type, $this->_numTeams($division), $stage, $division->league->sport),
			'start_date' => $division->_options->start_date,
		]);
		return $this->render('confirm');
	}

	protected function _finalize($division, $stage) {
		$this->loadComponent('Lock');
		if (!$this->Lock->lock('scheduling', $division->league->affiliate_id, 'schedule creation or edit')) {
			return $this->redirect(['controller' => 'Divisions', 'action' => 'view', 'division' => $division->id]);
		}

		try {
			if (!$this->_canSchedule($division, $stage)) {
				return $this->redirect(['controller' => 'Divisions', 'action' => 'view', 'division' => $division->id]);
			}

			EventManager::instance()->on('Controller.Schedules.ratings_ladder_scheduled', [$this, '_ratings_ladder_scheduled']);
			$this->league_obj->createSchedule($division, $this->pool);

			if ($this->_unscheduledPools($division)) {
				return $this->_type($division, $stage);
			}

			return $this->redirect(['controller' => 'Divisions', 'action' => 'schedule', 'division' => $division->id]);
		} catch (ScheduleException $ex) {
			$this->Flash->html($ex->getMessages(), ['params' => $ex->getAttributes()]);
		}

		$this->set([
			'desc' => $this->league_obj->scheduleDescription($division->_options->type, $this->_numTeams($division), $stage, $division->league->sport),
			'start_date' => $division->_options->start_date,
		]);
		return $this->render('confirm');
	}

	public function _ratings_ladder_scheduled(CakeEvent $cakeEvent, $seed_closeness, $gbr_diff, $versus_teams) {
		$this->Flash->ratings_ladder_scheduled(null, ['params' => compact('seed_closeness', 'gbr_diff', 'versus_teams')]);
	}

	protected function _canSchedule($division, $stage) {
		if (is_array($division->_options->start_date)) {
			$start_date = new FrozenDate(min($division->_options->start_date));
		} else {
			$start_date = $division->_options->start_date;
		}

		$this->Divisions->loadInto($division, [
			'GameSlots' => [
				'queryBuilder' => function (Query $q) use ($division, $start_date) {
					// TODO: Use a query object here
					$q->select(['count' => 'count(GameSlots.id)'])
						// TODOLATER: This should be a "between" condition? At least, we need to better align this with the slot assignment algorithm.
						->where(['GameSlots.game_date >=' => $start_date]);

					if (empty($division->_options->double_booking)) {
						$q->where(['GameSlots.assigned' => false]);
					}

					return $q
						->group(['GameSlots.game_date', 'GameSlots.game_start'])
						->order(['GameSlots.game_date', 'GameSlots.game_start']);
				},
			],
		]);
		$available_field_counts = collection($division->game_slots)->extract('count')->toList();

		if ($division->_options->double_booking) {
			// If double-booking is allowed, we only need a single field available
			return (!empty($division->game_slots));
		}

		$required_field_counts = $this->league_obj->scheduleRequirements($division->_options->type, $this->_numTeams($division));
		if (!$this->league_obj->canSchedule($required_field_counts, $available_field_counts)) {
			$this->Flash->warning(__('There are insufficient {0} available to support the requested schedule.', __(Configure::read("sports.{$division->league->sport}.fields"))));
			return false;
		}
		return true;
	}

	protected function _numTeams($division) {
		if ($this->numTeams === null) {
			if ($division->_options->has('pool_id')) {
				try {
					$this->pool = $this->Divisions->Pools->get($division->_options->pool_id, [
						'contain' => ['PoolsTeams']
					]);
				} catch (RecordNotFoundException $ex) {
					$this->Flash->error(__('The requested pool was not found!'));
					return 0;
				} catch (InvalidPrimaryKeyException $ex) {
					$this->Flash->error(__('The requested pool was not found!'));
					return 0;
				}
			}
			if (isset($this->pool)) {
				$this->numTeams = count($this->pool->pools_teams);
			} else {
				$this->numTeams = count($division->teams);
				if ($division->_options->has('exclude')) {
					$this->numTeams -= count($division->_options->exclude);
				}
			}
		}

		return $this->numTeams;
	}

	protected function _unscheduledPools($division) {
		// Check if we have any pools defined without games
		foreach ($division->pools as $pool) {
			if (!$this->pool || $pool->id > $this->pool->id) {
				$pool_team_ids = collection($pool->pools_teams)->extract('id')->toList();
				if (empty($pool_team_ids)) {
					pr($pool);
					trigger_error('TODOTESTING', E_USER_WARNING);
					exit;
				}
				$pool_games = $this->Divisions->Games->find()
					->where([
						'Games.division_id' => $division->id,
						'OR' => [
							[
								'Games.home_dependency_type' => 'pool',
								'Games.home_pool_team_id IN' => $pool_team_ids,
							],
							[
								'Games.away_dependency_type' => 'pool',
								'Games.away_pool_team_id IN' => $pool_team_ids,
							],
						],
					])
					->count();
				if (!$pool_games) {
					$this->set(compact('pool'));
					$this->pool = $pool;
					// TODOLATER: This confuses the regular season scheduler, if there are excluded teams
					$this->numTeams = count($pool->pools_teams);
					return true;
				}
			}
		}

		return false;
	}

	public function delete() {
		$division_id = $this->getRequest()->getQuery('division');
		if (!$division_id) {
			$league_id = $this->getRequest()->getQuery('league');
			if (!$league_id) {
				$this->Flash->warning(__('Invalid division.'));
				return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
			}

			try {
				$league = $this->Divisions->Leagues->get($league_id, [
					'contain' => [
						'Divisions' => [
							'Days' => [
								'queryBuilder' => function (Query $q) {
									return $q->order(['DivisionsDays.day_id']);
								},
							],
						],
					]
				]);
				$division = null;
			} catch (RecordNotFoundException $ex) {
				$this->Flash->warning(__('Invalid league.'));
				return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
			} catch (InvalidPrimaryKeyException $ex) {
				$this->Flash->warning(__('Invalid league.'));
				return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
			}

			$this->Authorization->authorize($league, 'edit_schedule');

			if (empty($league->divisions)) {
				$this->Flash->warning(__('This league has no divisions yet.'));
				return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
			}
			$divisions = collection($league->divisions)->extract('id')->toArray();

			$multi_day = (count(array_unique(collection($league->divisions)->filter(function ($division) {
				return $division->schedule_type != 'tournament';
			})->extract('days.{*}.id')->toArray())) > 1);

			$this->Configuration->loadAffiliate($league->affiliate_id);
		} else {
			try {
				$division = $this->Divisions->get($division_id, [
					'contain' => [
						'Days' => [
							'queryBuilder' => function (Query $q) {
								return $q->order(['DivisionsDays.day_id']);
							},
						],
						'Teams' => [
							'queryBuilder' => function (Query $q) {
								return $q->order(['Teams.name']);
							},
						],
						'Leagues',
						'Pools' => [
							'queryBuilder' => function (Query $q) {
								return $q->order(['Pools.id']);
							},
							'PoolsTeams' => [
								'queryBuilder' => function (Query $q) {
									return $q->order(['PoolsTeams.id']);
								},
							],
						],
					]
				]);
				$league = null;
			} catch (RecordNotFoundException $ex) {
				$this->Flash->warning(__('Invalid division.'));
				return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
			} catch (InvalidPrimaryKeyException $ex) {
				$this->Flash->warning(__('Invalid division.'));
				return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
			}
			$divisions = [$division->id];
			$league_id = $division->league_id;

			$this->Authorization->authorize($division, 'edit_schedule');

			$multi_day = ($division->schedule_type != 'tournament' && count($division->days) > 1);

			$this->Configuration->loadAffiliate($division->league->affiliate_id);
		}
		$this->set(compact('division_id', 'division', 'league_id', 'league'));

		$date = $this->getRequest()->getQuery('date');
		$pool_id = $this->getRequest()->getQuery('pool');
		if (!$date && !$pool_id) {
			$this->Flash->warning(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		}

		$query = $this->Divisions->Games->find()
			->where(['Games.division_id IN' => $divisions]);

		// Set defaults
		$reset_pools = $same_pool = $dependent = [];
		$date = new FrozenDate($date);

		if ($date) {
			$query->contain(['GameSlots']);
			if ($multi_day) {
				$query->andWhere([
					function ($exp) use ($query, $date) {
						$end = $date->next(Configure::read('organization.first_day'))->subDay();
						return $exp->between('GameSlots.game_date', $date, $end, 'date');
					},
				]);
			} else {
				$query->andWhere(['GameSlots.game_date' => $date]);
			}
		}

		if ($pool_id) {
			$query->andWhere(['Games.pool_id' => $pool_id]);
			try {
				$pool = $this->Divisions->Pools->get($pool_id);
			} catch (RecordNotFoundException $ex) {
				$this->Flash->warning(__('Invalid pool.'));
				return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
			} catch (InvalidPrimaryKeyException $ex) {
				$this->Flash->warning(__('Invalid pool.'));
				return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
			}
		} else {
			$pool = null;
		}

		if ($query->isEmpty()) {
			$this->Flash->warning(__('There are no games to delete on that date.'));
			if ($division_id) {
				return $this->redirect(['controller' => 'Divisions', 'action' => 'schedule', 'division' => $division_id]);
			} else {
				return $this->redirect(['controller' => 'Leagues', 'action' => 'schedule', 'league' => $league_id]);
			}
		}
		$games = $query->toArray();

		$pools = array_unique(collection($games)->filter(function ($game) {
			return $game->pool_id !== null;
		})->extract('pool_id')->toList());
		if (!empty($pools)) {
			$reset_pools = $pools;

			if ($date) {
				$same_pool = $this->Divisions->Games->find()
					->contain(['GameSlots'])
					->where([
						'Games.pool_id IN' => $pools,
						'OR' => [
							'GameSlots.game_date !=' => $date,
							'GameSlots.game_date IS' => null,
						],
					])
					->toArray();
			}

			$stages = $this->Divisions->Pools->find('list', [
				'conditions' => [
					'Pools.id IN' => $pools,
				],
				'keyField' => 'id',
				'valueField' => 'stage',
			])->toList();

			if (!empty($stages)) {
				$later_pools = $this->Divisions->Pools->find('list', [
					'conditions' => [
						'Pools.division_id IN' => $divisions,
						'Pools.stage >' => max($stages),
					],
					'keyField' => 'id',
					'valueField' => 'id',
				])->toArray();

				if (!empty($later_pools)) {
					$reset_pools = array_merge($reset_pools, $later_pools);

					$dependent = $this->Divisions->Games->find()
						->where(['Games.pool_id IN' => $later_pools])
						->toArray();
				}
			}
		}

		if ($this->getRequest()->getQuery('confirm')) {
			if ($this->Divisions->getConnection()->transactional(function () use ($games, $reset_pools, $same_pool, $dependent) {
				// Reset dependencies for affected pools
				if (!empty($reset_pools)) {
					// There might be no updates here, if pools haven't been initialized yet
					// TODO: Is this something that could be done in GamesTable::beforeDelete?
					$this->Divisions->Pools->PoolsTeams->updateAll(['team_id' => null], ['pool_id IN' => $reset_pools]);
				}

				foreach (array_merge($games, $same_pool, $dependent) as $game) {
					if (!$this->Divisions->Games->delete($game)) {
						return false;
					}
				}

				return true;
			})) {
				if ($date) {
					$this->Flash->success(__('Deleted games on the requested date.'));
				} else {
					$this->Flash->success(__('Deleted games from the requested pool.'));
				}

				// With the saves being inside a transaction, afterDeleteCommit is not called.
				$event = new CakeEvent('Model.afterDeleteCommit', $this, [null]);
				$this->getEventManager()->dispatch($event);

				if (isset($league)) {
					foreach ($league->divisions as $division) {
						$this->Divisions->clearCache($division, ['schedule', 'standings']);
					}
					return $this->redirect(['controller' => 'Leagues', 'action' => 'schedule', 'league' => $league_id]);
				} else {
					$this->Divisions->clearCache($division, ['schedule', 'standings']);
					return $this->redirect(['controller' => 'Divisions', 'action' => 'schedule', 'division' => $division_id]);
				}
			} else {
				$this->Flash->warning(__('Failed to delete games on the requested date.'));

				$event = new CakeEvent('Model.afterDeleteRollback', $this, [null]);
				$this->getEventManager()->dispatch($event);
			}
		}

		$this->set(compact('date', 'pool_id', 'pool', 'games', 'same_pool', 'dependent'));
	}

	public function reschedule() {
		$id = $this->getRequest()->getQuery('division');
		$date = $this->getRequest()->getQuery('date');

		try {
			$division = $this->Divisions->get($id,
				['contain' => [
					'Leagues',
					'Games' => [
						'queryBuilder' => function (Query $q) use ($date) {
							return $q
								->where([
									'NOT' => ['status IN' => ['cancelled', 'rescheduled']],
								])
								->matching('GameSlots', function (Query $q) use ($date) {
									return $q->where(['game_date' => $date]);
								});
						},
						'HomeTeam',
						'AwayTeam',
					],
				]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->warning(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->warning(__('Invalid division.'));
			return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
		}

		$this->Authorization->authorize($division, 'edit_schedule');
		$this->Configuration->loadAffiliate($division->league->affiliate_id);

		$league_obj = $this->moduleRegistry->load("LeagueType:{$division->schedule_type}");
		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			$this->loadComponent('Lock');
			if (!$this->Lock->lock('scheduling', $division->league->affiliate_id, 'schedule creation or edit')) {
				return $this->redirect(['controller' => 'Divisions', 'action' => 'schedule', 'division' => $id]);
			}
			try {
				// Save the list of games to be rescheduled; we'll overwrite it in startSchedule
				$games = $division->games;
				$new_date = new FrozenDate($this->getRequest()->getData('new_date'));
				$division->_options = new Entity(['ignore_games' => $games]);
				$league_obj->startSchedule($division, $new_date);
				$league_obj->assignFieldsByPreferences($division, $new_date, $games);
				$league_obj->finishSchedule($division, $games);

				$ids = collection($games)->extract('id')->toArray();
				TableRegistry::getTableLocator()->get('ActivityLogs')
					->deleteAll(['game_id IN' => $ids]);

				$this->Flash->success(__('Games rescheduled.'));
				return $this->redirect(['controller' => 'Divisions', 'action' => 'schedule', 'division' => $id]);
			} catch (ScheduleException $ex) {
				$this->Flash->html($ex->getMessages(), ['params' => $ex->getAttributes()]);
			}
		}

		// Find the list of available dates for scheduling this division
		// TODO: This is similar, but not identical, to GameSlots->find('available'). Refactor for code re-use?
		$availability_table = TableRegistry::getTableLocator()->get('DivisionsGameslots');
		$dates = $availability_table->find()
			->contain(['GameSlots'])
			// TODO: Use a query object here
			->distinct(['CONCAT(GameSlots.game_date, " ", GameSlots.game_start)'])
			->where([
				'GameSlots.game_date >' => $date,
				'GameSlots.assigned' => false,
				'DivisionsGameslots.division_id' => $id,
			])
			->order(['GameSlots.game_date'])
			->extract('game_slot.game_date')
			->toArray();
		if (empty($dates)) {
			$this->Flash->warning(__('Sorry, there are no {0} available for your division. Check that {0} have been allocated before attempting to proceed.', __(Configure::read("sports.{$division->league->sport}.fields"))));
			return $this->redirect(['controller' => 'Divisions', 'action' => 'schedule', 'division' => $id]);
		}

		$this->set(compact('id', 'division', 'date', 'dates'));
	}

	public function publish() {
		return $this->_publish(1, __('publish'), __('Published'));
	}

	public function unpublish() {
		return $this->_publish(0, __('unpublish'), __('Unpublished'));
	}

	protected function _publish($true, $publish, $published) {
		$division_id = $this->getRequest()->getQuery('division');
		if (!$division_id) {
			$league_id = $this->getRequest()->getQuery('league');
			if (!$league_id) {
				$this->Flash->warning(__('Invalid division.'));
				return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
			}

			try {
				$league = $this->Divisions->Leagues->get($league_id, [
					'contain' => [
						'Divisions' => [
							'Days' => [
								'queryBuilder' => function (Query $q) {
									return $q->order(['DivisionsDays.day_id']);
								},
							],
						],
					]
				]);
			} catch (RecordNotFoundException $ex) {
				$this->Flash->warning(__('Invalid league.'));
				return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
			} catch (InvalidPrimaryKeyException $ex) {
				$this->Flash->warning(__('Invalid league.'));
				return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
			}

			$this->Authorization->authorize($league, 'edit_schedule');

			if (empty($league->divisions)) {
				$this->Flash->warning(__('This league has no divisions yet.'));
				return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
			}
			$divisions = collection($league->divisions)->extract('id')->toList();

			$multi_day = (count(array_unique(collection($league->divisions)->filter(function ($division) {
				return $division->schedule_type != 'tournament';
			})->extract('days.{*}.id')->toArray())) > 1);
		} else {
			try {
				$division = $this->Divisions->get($division_id, [
					'contain' => [
						'Days' => [
							'queryBuilder' => function (Query $q) {
								return $q->order(['DivisionsDays.day_id']);
							},
						],
					]
				]);
			} catch (RecordNotFoundException $ex) {
				$this->Flash->warning(__('Invalid division.'));
				return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
			} catch (InvalidPrimaryKeyException $ex) {
				$this->Flash->warning(__('Invalid division.'));
				return $this->redirect(['controller' => 'Leagues', 'action' => 'index']);
			}

			$this->Authorization->authorize($division, 'edit_schedule');

			$divisions = [$division_id];
			$league_id = $division['league_id'];

			$multi_day = ($division->schedule_type != 'tournament' && count($division->days) > 1);
		}
		$date = $this->getRequest()->getQuery('date');
		$pool_id = $this->getRequest()->getQuery('pool');

		$query = $this->Divisions->Games->find()
			->where(['Games.division_id IN' => $divisions]);

		$query->contain(['GameSlots']);
		if ($multi_day) {
			$query->andWhere([
				function ($exp) use ($query, $date) {
					$end = (new FrozenDate($date))->next(Configure::read('organization.first_day'))->subDay();
					return $exp->between('GameSlots.game_date', $date, $end, 'date');
				},
			]);
		} else {
			$query->andWhere(['GameSlots.game_date' => $date]);
		}

		$games = $query->toArray();

		$pools = array_unique(collection($games)->filter(function ($game) {
			return $game->pool_id !== null;
		})->extract('pool_id')->toList());
		if (!empty($pools) && $date) {
			// We need to include any "Copy" dependency games from the same pools
			$same_pool = $this->Divisions->Games->find()
				->where([
					'Games.pool_id IN' => $pools,
					'OR' => [
						'Games.home_dependency_type' => 'copy',
						'Games.away_dependency_type' => 'copy',
					],
				])
				->toArray();
		} else {
			$same_pool = [];
		}

		$games = array_merge($games, $same_pool);
		if ($this->Divisions->Games->getConnection()->transactional(function () use ($games, $true) {
			foreach ($games as $game) {
				$game->published = $true;
				if (!$this->Divisions->Games->save($game, ['update_badges' => false])) {
					return false;
				}
			}

			return true;
		})) {
			$this->Flash->success(__('{0} games on the requested date.', $published));

			if (isset($league)) {
				foreach ($league->divisions as $division) {
					$this->Divisions->clearCache($division, ['schedule', 'standings']);
				}
			} else {
				$this->Divisions->clearCache($division, ['schedule', 'standings']);
			}

			// With the saves being inside a transaction, afterSaveCommit is not called.
			$event = new CakeEvent('Model.afterSaveCommit', $this, [null]);
			$this->getEventManager()->dispatch($event);
		} else {
			$this->Flash->warning(__('Failed to {0} games on the requested date.', $publish));

			if ($true) {
				$event = new CakeEvent('Model.afterSaveRollback', $this, [null]);
			} else {
				$event = new CakeEvent('Model.afterDeleteRollback', $this, [null]);
			}
			$this->getEventManager()->dispatch($event);
		}

		if (isset($league)) {
			return $this->redirect(['controller' => 'Leagues', 'action' => 'schedule', 'league' => $league_id]);
		} else {
			return $this->redirect(['controller' => 'Divisions', 'action' => 'schedule', 'division' => $division_id]);
		}
	}

	public function today() {
		if (!$this->getRequest()->is('json')) {
			$this->viewBuilder()->setLayout('iframe');
		}

		$games = $this->Divisions->Games->find()
			->contain(['GameSlots'])
			->where([
				'GameSlots.game_date' => FrozenDate::now(),
				'Games.published' => true,
			])
			->count();

		$this->set(compact('games'));
	}

	public function day() {
		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			$date = $this->getRequest()->getData('date.year') . '-' . $this->getRequest()->getData('date.month') . '-' . $this->getRequest()->getData('date.day');
		} else {
			$date = $this->getRequest()->getQuery('date');
		}
		if (empty($date)) {
			$date = FrozenDate::now();
		} else {
			$date = new FrozenDate($date);
		}

		// Hopefully, everything we need is already cached
		// TODOSECOND: See below
		/*
		$cache_key = "schedule/$date";
		$cached = Cache::read($cache_key, 'long_term');
		if ($cached) {
			$games = $cached;
		}
		if (empty($games)) {
		*/
			$affiliates = $this->Authentication->applicableAffiliateIDs(true);

			// Find divisions that match the affiliates, and specified date
			$divisions = $this->Divisions->find()
				->contain(['Leagues'])
				->where([
					'Leagues.affiliate_id IN' => $affiliates,
					'Divisions.open <=' => $date,
					'Divisions.close >=' => $date,
				])
				->extract('id')
				->toList();

			if (empty($divisions)) {
				$games = [];
			} else {
				$query = $this->Divisions->Games->find()
					->contain([
						'GameSlots' => ['Fields' => ['Facilities']],
						'Divisions' => ['Leagues' => ['Affiliates']],
						'ScoreEntries',
						'HomeTeam',
						'HomePoolTeam' => ['DependencyPool'],
						'AwayTeam',
						'AwayPoolTeam' => ['DependencyPool'],
					])
					->where([
						'Divisions.id IN' => $divisions,
						'GameSlots.game_date' => $date,
						'OR' => [
							'Games.home_dependency_type !=' => 'copy',
							'Games.home_dependency_type IS' => null,
						],
					]);

				$identity = $this->Authentication->getIdentity();
				if (!$identity || !$this->Authentication->getIdentity()->isManager()) {
					$query->andWhere(['Games.published' => true]);
				}

				$games = $query->toArray();

				// Sort games by sport, time and field
				usort($games, [GamesTable::class, 'compareSportDateAndField']);
			}

			// TODOSECOND: If we cache this, we need to also clear that cache when games are updated.
			// Build something like the DivisionsTable::clearCache for games?
			/*
			Cache::write($cache_key, $games, 'long_term');
		}
			*/

		$this->set(compact('date', 'games'));
	}

	/**
	 * Override the redirect function; if it's a view and there's only one division, view the league instead
	 */
	public function redirect($url = null, $status = 302) {
		if (isset($url['action']) && $url['action'] == 'view' && isset($url['controller']) && $url['controller'] == 'Divisions') {
			$league = $this->Divisions->league($url['division']);
			if ($this->Divisions->find('byLeague', compact('league'))->count() == 1) {
				parent::redirect(['controller' => 'Leagues', 'action' => $url['action'], 'league' => $league], $status);
			}
		}
		return parent::redirect($url, $status);
	}

}
