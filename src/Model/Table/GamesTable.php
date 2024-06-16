<?php
namespace App\Model\Table;

use App\Authorization\ContextResource;
use App\Model\Traits\DateTimeCombinator;
use ArrayObject;
use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Event\Event as CakeEvent;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\FrozenTime;
use Cake\ORM\Query;
use Cake\ORM\Rule\ExistsIn;
use Cake\ORM\RulesChecker;
use Cake\Routing\Router;
use Cake\Validation\Validator;
use App\Core\ModuleRegistry;
use App\Model\Entity\TeamsPerson;
use App\Model\Rule\InConfigRule;
use App\Model\Rule\ValidScoreRule;
use App\Model\Table\TeamsTable;
use InvalidArgumentException;

/**
 * Games Model
 *
 * TODOLATER: Review this list of properties to make sure it matches. Check other models too.
 * @property \Cake\ORM\Association\BelongsTo $Divisions
 * @property \Cake\ORM\Association\BelongsTo $HomeTeam
 * @property \Cake\ORM\Association\BelongsTo $AwayTeam
 * @property \Cake\ORM\Association\BelongsTo $ApprovedBy
 * @property \Cake\ORM\Association\BelongsTo $Pools
 * @property \Cake\ORM\Association\BelongsTo $HomePoolTeam
 * @property \Cake\ORM\Association\BelongsTo $AwayPoolTeam
 * @property \Cake\ORM\Association\BelongsTo $GameSlots
 * @property \Cake\ORM\Association\HasMany $ActivityLogs
 * @property \Cake\ORM\Association\HasMany $Attendances
 * @property \Cake\ORM\Association\HasMany $BadgesPeople
 * @property \Cake\ORM\Association\HasMany $FieldRankingStats
 * @property \Cake\ORM\Association\HasMany $Incidents
 * @property \Cake\ORM\Association\HasMany $Notes
 * @property \Cake\ORM\Association\HasMany $ScoreDetails
 * @property \Cake\ORM\Association\HasMany $ScoreEntries
 * @property \Cake\ORM\Association\HasMany $SpiritEntries
 * @property \Cake\ORM\Association\HasMany $Stats
 */
class GamesTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('games');
		$this->setDisplayField('name');
		$this->setPrimaryKey('id');

		$this->addBehavior('Trim');
		$this->addBehavior('Timestamp');

		$this->belongsTo('Divisions', [
			'foreignKey' => 'division_id',
			'joinType' => 'INNER',
		]);
		$this->belongsTo('GameSlots', [
			'foreignKey' => 'game_slot_id',
		]);
		$this->belongsTo('Pools', [
			'foreignKey' => 'pool_id',
		]);
		$this->belongsTo('HomeTeam', [
			'className' => 'Teams',
			'foreignKey' => 'home_team_id',
		]);
		$this->belongsTo('HomePoolTeam', [
			'className' => 'PoolsTeams',
			'foreignKey' => 'home_pool_team_id',
		]);
		$this->belongsTo('AwayTeam', [
			'className' => 'Teams',
			'foreignKey' => 'away_team_id',
		]);
		$this->belongsTo('AwayPoolTeam', [
			'className' => 'PoolsTeams',
			'foreignKey' => 'away_pool_team_id',
		]);
		$this->belongsTo('ApprovedBy', [
			'className' => 'People',
			'foreignKey' => 'approved_by_id',
		]);

		$this->hasMany('Attendances', [
			'foreignKey' => 'game_id',
			'dependent' => false,
		]);
		$this->hasMany('Incidents', [
			'foreignKey' => 'game_id',
			'dependent' => true,
		]);
		$this->hasMany('ScoreDetails', [
			'foreignKey' => 'game_id',
			'dependent' => true,
			// Required to delete from score_detail_stats
			'cascadeCallbacks' => true,
		]);
		$this->hasMany('ScoreEntries', [
			'foreignKey' => 'game_id',
			'dependent' => true,
			// Required to delete from score_entries
			'cascadeCallbacks' => true,
		]);
		$this->hasMany('SpiritEntries', [
			'foreignKey' => 'game_id',
			'dependent' => true,
		]);
		$this->hasMany('ScoreReminderEmails', [
			'foreignKey' => 'game_id',
			'className' => 'ActivityLogs',
			'dependent' => true,
			'conditions' => ['type IN' => ['email_score_reminder', 'email_approval_notice']],
		]);
		$this->hasMany('ScoreMismatchEmails', [
			'foreignKey' => 'game_id',
			'className' => 'ActivityLogs',
			'dependent' => true,
			'conditions' => ['type' => 'email_score_mismatch'],
		]);
		$this->hasMany('AttendanceReminderEmails', [
			'foreignKey' => 'game_id',
			'className' => 'ActivityLogs',
			'dependent' => true,
			'conditions' => ['type' => 'email_attendance_reminder'],
		]);
		$this->hasMany('AttendanceSummaryEmails', [
			'className' => 'ActivityLogs',
			'dependent' => true,
			'conditions' => ['type' => 'email_attendance_summary'],
		]);
		$this->hasMany('Notes', [
			'foreignKey' => 'game_id',
			'dependent' => true,
			'sort' => 'Notes.created',
		]);
		$this->hasMany('Stats', [
			'foreignKey' => 'game_id',
			'dependent' => true,
		]);
	}

	/**
	 * Default validation rules.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 * @return \Cake\Validation\Validator
	 */
	public function validationDefault(Validator $validator): \Cake\Validation\Validator {
		$validator
			->allowEmptyString('id', null, 'create')
			->numeric('id')

			->notEmptyString('published')
			->boolean('published')

			->requirePresence('division_id', 'create')

			->requirePresence('type', 'create')
			->range('type', [SEASON_GAME, BRACKET_GAME])

			->requirePresence('game_slot_id', function($context) {
				return $context['newRecord'] && !empty($context['data']['home_dependency_type']) && $context['data']['home_dependency_type'] != 'copy';
			})
			->notEmptyString('game_slot_id', __('You must select a game slot.'))

			->notEmptyString('home_team_id', __('You must select a team.'))

			->notEmptyString('away_team_id', __('You must select a team.'))
			->setProvider('zuluru', \App\Validation\Zuluru::class)
			->add('away_team_id', 'valid', ['provider' => 'zuluru', 'rule' => ['comparisonWith', '!=', 'home_team_id'], 'message' => __('Team was scheduled twice in the same game slot.')])

			->requirePresence('round', function ($context) {
				return $context['newRecord'] && array_key_exists('type', $context['data']) && $context['data']['type'] == SEASON_GAME;
			})
			->notEmptyString('round', null, function ($context) {
				return $context['newRecord'] && array_key_exists('type', $context['data']) && $context['data']['type'] == SEASON_GAME;
			})

			->notEmptyString('tournament_pool', null, function ($context) {
				return $context['newRecord'] && array_key_exists('type', $context['data']) && $context['data']['type'] != SEASON_GAME;
			})
			->naturalNumber('tournament_pool')

			->requirePresence('name', function ($context) {
				return $context['newRecord'] && array_key_exists('type', $context['data']) && $context['data']['type'] != SEASON_GAME;
			})
			->notEmptyString('name', null, function ($context) {
				return $context['newRecord'] && array_key_exists('type', $context['data']) && $context['data']['type'] != SEASON_GAME && empty($context['data']['placement']);
			})

			->requirePresence('placement', function ($context) {
				return $context['newRecord'] && array_key_exists('type', $context['data']) && $context['data']['type'] != SEASON_GAME;
			})
			->allowEmptyString('placement')
			->naturalNumber('placement')

			->requirePresence('home_dependency_type', function ($context) {
				return $context['newRecord'] && array_key_exists('type', $context['data']) && $context['data']['type'] != SEASON_GAME;
			})
			->notEmptyString('home_dependency_type', null, function ($context) {
				return array_key_exists('type', $context['data']) && $context['data']['type'] != SEASON_GAME;
			})

			->requirePresence('home_dependency_id', function ($context) {
				return !empty($context['data']['home_dependency_type']) && substr($context['data']['home_dependency_type'], 0, 5) == 'game_';
			})
			->notEmptyString('home_dependency_id', null, function ($context) {
				return !empty($context['data']['home_dependency_type']) && substr($context['data']['home_dependency_type'], 0, 5) == 'game_';
			})

			->requirePresence('away_dependency_type', function ($context) {
				return $context['newRecord'] && array_key_exists('type', $context['data']) && $context['data']['type'] != SEASON_GAME;
			})
			->notEmptyString('away_dependency_type', null, function ($context) {
				return array_key_exists('type', $context['data']) && $context['data']['type'] != SEASON_GAME;
			})

			->requirePresence('away_dependency_id', function ($context) {
				return !empty($context['data']['away_dependency_type']) && substr($context['data']['away_dependency_type'], 0, 5) == 'game_';
			})
			->notEmptyString('away_dependency_id', null, function ($context) {
				return !empty($context['data']['away_dependency_type']) && substr($context['data']['away_dependency_type'], 0, 5) == 'game_';
			})

			->allowEmptyString('home_field_rank')
			->naturalNumber('home_field_rank')

			->allowEmptyString('away_field_rank')
			->naturalNumber('away_field_rank')

			->allowEmptyString('rating_points', null, 'create')
			->integer('rating_points')

			->requirePresence('home_carbon_flip', function ($context) {
				if (!Configure::read('scoring.carbon_flip') ||
					in_array($context['data']['status'], Configure::read('unplayed_status')) ||
					strpos($context['data']['status'], 'default') !== false ||
					!array_key_exists('home_score', $context['data'])
				) {
					return false;
				}

				$game = $this->get($context['data']['id'], [
					'contain' => ['Divisions' => 'Leagues']
				]);

				return $game->division->league->hasCarbonFlip();
			}, __('You must select a valid carbon flip result.'))
			->range('home_carbon_flip', [0, 2], __('You must select a valid carbon flip result.'))

			;

		return $validator;
	}

	/**
	 * Custom validation rules for single game edits.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 * @return \Cake\Validation\Validator
	 */
	public function validationGameEdit(Validator $validator) {
		$validator = $this->validationDefault($validator);

		$validator
			->requirePresence('status', 'create', __('You must select a valid status.'))
			->notEmptyString('status', __('You must select a valid status.'))

		;

		return $validator;
	}

	/**
	 * Custom validation rules for schedule adds.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 * @return \Cake\Validation\Validator
	 */
	public function validationScheduleAdd(Validator $validator) {
		return $this->validationDefault($validator);
	}

	/**
	 * Custom validation rules for schedule edits.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 * @return \Cake\Validation\Validator
	 */
	public function validationScheduleEdit(Validator $validator) {
		return $this->validationDefault($validator);
	}

	/**
	 * Returns a rules checker object that will be used for validating
	 * application integrity.
	 *
	 * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
	 * @return \Cake\ORM\RulesChecker
	 */
	public function buildRules(RulesChecker $rules): \Cake\ORM\RulesChecker {
		$rules->add($rules->existsIn(['division_id'], 'Divisions', __('You must select a valid division.')));
		$rules->add($rules->existsIn(['home_team_id'], 'HomeTeam'));
		$rules->add($rules->existsIn(['away_team_id'], 'AwayTeam'));
		$rules->add($rules->existsIn(['pool_id'], 'Pools'));
		$rules->add($rules->existsIn(['home_pool_team_id'], 'HomePoolTeam'));
		$rules->add($rules->existsIn(['away_pool_team_id'], 'AwayPoolTeam'));
		$rules->add($rules->existsIn(['game_slot_id'], 'GameSlots'));

		$rules->add(function (EntityInterface $entity, array $options) {
			if (in_array($entity->approved_by_id, [APPROVAL_AUTOMATIC, APPROVAL_AUTOMATIC_HOME, APPROVAL_AUTOMATIC_AWAY, APPROVAL_AUTOMATIC_FORFEIT])) {
				return true;
			}
			$rule = new ExistsIn(['approved_by_id'], 'ApprovedBy');
			return $rule($entity, $options);
		}, 'valid', [
			'errorField' => 'approved_by_id',
			'message' => __d('cake', 'This value does not exist'),
		]);

		$rules->add(function (EntityInterface $entity, array $options) {
			if (in_array($entity->home_dependency_type, ['game_winner', 'game_loser'])) {
				$rule = new ExistsIn(['home_dependency_id'], $this);
				return $rule($entity, $options);
			}
			return true;
		}, 'valid', [
			'errorField' => 'home_dependency_id',
			'message' => __d('cake', 'This value does not exist'),
		]);

		$rules->add(function (EntityInterface $entity, array $options) {
			if (in_array($entity->away_dependency_type, ['game_winner', 'game_loser'])) {
				$rule = new ExistsIn(['away_dependency_id'], $this);
				return $rule($entity, $options);
			}
			return true;
		}, 'valid', [
			'errorField' => 'away_dependency_id',
			'message' => __d('cake', 'This value does not exist'),
		]);

		$rules->add(new InConfigRule('options.game_status'), 'validStatus', [
			'errorField' => 'status',
			'message' => __('You must select a valid status.'),
		]);

		// These aren't simple "range" rules that could go in the validation above.
		// TODO: Make the 99 configurable
		$rules->addUpdate(new ValidScoreRule(0, 99), 'validScore', [
			'errorField' => 'home_score',
			'message' => __('Scores must be in the range 0-99.'),
		]);

		$rules->addUpdate(new ValidScoreRule(0, 99), 'validScore', [
			'errorField' => 'away_score',
			'message' => __('Scores must be in the range 0-99.'),
		]);

		// Make sure that dependencies are resolved before saving
		$rules->addCreate(function (EntityInterface $entity, array $options) {
			if (!$entity->has('home_dependency_resolved') || $entity->home_dependency_resolved) {
				return true;
			}
		}, 'home_dependency', [
			'errorField' => 'home_dependency_id',
			'message' => __('A game dependency was not resolved before saving the game. Check the scheduling algorithm.'),
		]);

		$rules->addCreate(function (EntityInterface $entity, array $options) {
			if (!$entity->has('away_dependency_resolved') || $entity->away_dependency_resolved) {
				return true;
			}
		}, 'away_dependency', [
			'errorField' => 'away_dependency_id',
			'message' => __('A game dependency was not resolved before saving the game. Check the scheduling algorithm.'),
		]);

		//
		// The following rules perform a number of checks on the games being saved as a collection.
		//

		// This does a subset of the Update checks, trusting that the game slots will have been correctly set by the
		// creation algorithm. This will need to be revisited if we provide a manual bulk game creation mechanism.
		$rules->addCreate(function (EntityInterface $entity, array $options) {
			// Make sure that some other coordinator hasn't scheduled a game in a
			// different league on one of the unused slots.
			if ($this->find()
				->where([
					'game_slot_id' => $entity->game_slot_id,
				])
				->count()
			) {
				return __('A game slot chosen for this schedule has been allocated elsewhere in the interim. Please try again.');
			}

			return true;
		}, 'valid_game_slot', [
			'errorField' => 'game_slot_id',
		]);

		$rules->addUpdate(function (EntityInterface $entity, array $options) {
			if (!array_key_exists('games', $options)) {
				// If we're only saving a single game, none of this applies.
				return true;
			}

			$options += ['double_header' => false, 'multiple_days' => false, 'double_booking' => false, 'cross_division' => false];

			$other_games = collection($options['games'])->filter(function ($game) use ($entity) {
				return $game->id != $entity->id;
			});

			// Check that game slots are not assigned to multiple games, unless that's allowed
			if (!$options['double_booking'] && $other_games->some(function ($game) use ($entity) {
				return $entity->game_slot_id == $game->game_slot_id;
			})) {
				return __('Game slot selected more than once.');
			}

			// Make sure that the game slot selected is available to one of the teams
			$game_slot = collection($options['game_slots'])->firstMatch(['id' => $entity->game_slot_id]);
			if ($game_slot) {
				// TODO: Use lazy loading to eliminate this?
				if ($entity->isDirty('home_team_id')) {
					if ($entity->home_team_id) {
						$entity->home_team = $this->HomeTeam->get($entity->home_team_id);
					} else {
						$entity->home_team = null;
					}
					$entity->setDirty('home_team', false);
				}
				if ($entity->isDirty('away_team_id')) {
					if ($entity->away_team_id) {
						$entity->away_team = $this->AwayTeam->get($entity->away_team_id);
					} else {
						$entity->away_team = null;
					}
					$entity->setDirty('away_team', false);
				}

				if ($entity->home_team && collection($game_slot->divisions)->firstMatch(['id' => $entity->home_team->division_id])) {
					// Setting the division ID here and below seems like it would fit better in beforeSave, but then we have to redo a bunch of checks...
					// Do the check to prevent dirtying a game unnecessarily, but don't patch because we don't want to re-run validation.
					if ($entity->division_id != $entity->home_team->division_id) {
						$entity->division_id = $entity->home_team->division_id;
					}
				} else if ($entity->home_dependency_pool && collection($game_slot->divisions)->firstMatch(['id' => $entity->home_dependency_pool->division_id])) {
					// Would we even ever need to change the division_id of a playoff/tournament schedule?
					$entity->division_id = $entity->home_dependency_pool->division_id;
					// There's no similar checks for the away_dependency and away_pool_team_id, because the only thing
					// that generates these games is a tournament schedule, and we don't do cross-division tournament
					// scheduling right now. When we do, we'll start getting "not available" error messages, which will
					// lead us here...
				} else {
					if ($entity->pool_id && !$entity->has('pool')) {
						$this->loadInto($entity, ['Pools']);
					}
					if ($entity->pool && collection($game_slot->divisions)->firstMatch(['id' => $entity->pool->division_id])) {
						$entity->division_id = $entity->pool->division_id;
					} else if ($entity->away_team && collection($game_slot->divisions)->firstMatch(['id' => $entity->away_team->division_id])) {
						// Game is happening on a field only available to the away team, so make them the home team instead
						[$entity->home_team_id, $entity->home_team, $entity->away_team_id, $entity->away_team] =
							[$entity->away_team_id, $entity->away_team, $entity->home_team_id, $entity->home_team];
						// No check to prevent dirtying here; it's already going to be dirty from the team swap.
						$entity->division_id = $entity->away_team->division_id;
					} else if ($entity->type == SEASON_GAME && $entity->home_team_id == null && $entity->away_team_id == null) {
						// Regular season game that's got no teams assigned at all, don't need to change the division at all
					} else {
						return __('This game slot is not available to these teams.');
					}
				}
			} else {
				return __('This game slot is not available to these teams.');
			}

			// Make sure that some other coordinator hasn't scheduled a game in a
			// different league on one of the unused slots.
			if ($this->find()
				->where([
					'game_slot_id' => $game_slot->id,
					// Don't include game slots that are previously allocated to one of these games;
					// of course those will be taken, but it's okay!
					'NOT' => ['id IN' => collection($options['games'])->extract('id')->toList()],
				])
				->count()
			) {
				return __('This game slot has been allocated elsewhere in the interim.');
			}

			return true;
		}, 'valid_game_slot', [
			'errorField' => 'game_slot_id',
		]);

		// We don't do any of these checks for new games, trusting that the data will have been correctly set by the
		// creation algorithm. This will need to be revisited if we provide a manual bulk game creation mechanism.
		$rules->addUpdate(function (EntityInterface $entity, array $options) {
			if (!array_key_exists('games', $options)) {
				// If we're only saving a single game, none of this applies.
				return true;
			}

			$options += ['double_header' => false, 'multiple_days' => false, 'double_booking' => false, 'cross_division' => false];

			$double_header_result = $this->checkDoubleHeader($entity, $options, 'home_team_id', 'away_team_id');
			if ($double_header_result !== true) {
				return $double_header_result;
			}

			if ($entity->has('home_team') && $entity->has('away_team') && $entity->home_team->division_id != $entity->away_team->division_id && !$options['cross_division']) {
				return __('Cross-division scheduling was not selected.');
			}

			return true;
		}, 'valid_home_team', [
			'errorField' => 'home_team_id',
		]);

		// We don't do any of these checks for new games, trusting that the data will have been correctly set by the
		// creation algorithm. This will need to be revisited if we provide a manual bulk game creation mechanism.
		$rules->addUpdate(function (EntityInterface $entity, array $options) {
			if (!array_key_exists('games', $options)) {
				// If we're only saving a single game, none of this applies.
				return true;
			}

			$options += ['double_header' => false, 'multiple_days' => false, 'double_booking' => false, 'cross_division' => false];

			$double_header_result = $this->checkDoubleHeader($entity, $options, 'away_team_id', 'home_team_id');
			if ($double_header_result !== true) {
				return $double_header_result;
			}

			return true;
		}, 'valid_away_team', [
			'errorField' => 'away_team_id',
		]);

		// We don't do any of these checks for new games, trusting that the data will have been correctly set by the
		// creation algorithm. This will need to be revisited if we provide a manual bulk game creation mechanism.
		$rules->addUpdate(function (EntityInterface $entity, array $options) {
			if (!array_key_exists('games', $options)) {
				// If we're only saving a single game, none of this applies.
				return true;
			}

			if (!$entity->has('division')) {
				$this->loadInto($entity, ['Divisions']);
			}
			if ($entity->division->schedule_type == 'tournament') {
				// We totally expect there to be double headers in tournaments
				return true;
			}

			$options += ['double_header' => false, 'multiple_days' => false, 'double_booking' => false, 'cross_division' => false];

			$double_header_result = $this->checkDoubleHeader($entity, $options, 'home_pool_team_id', 'away_pool_team_id');
			if ($double_header_result !== true) {
				return $double_header_result;
			}

			return true;
		}, 'valid_home_pool_team', [
			'errorField' => 'home_pool_team_id',
		]);

		// We don't do any of these checks for new games, trusting that the data will have been correctly set by the
		// creation algorithm. This will need to be revisited if we provide a manual bulk game creation mechanism.
		$rules->addUpdate(function (EntityInterface $entity, array $options) {
			if (!array_key_exists('games', $options)) {
				// If we're only saving a single game, none of this applies.
				return true;
			}

			if (!$entity->has('division')) {
				$this->loadInto($entity, ['Divisions']);
			}
			if ($entity->division->schedule_type == 'tournament') {
				// We totally expect there to be double headers in tournaments
				return true;
			}

			$options += ['double_header' => false, 'multiple_days' => false, 'double_booking' => false, 'cross_division' => false];

			$double_header_result = $this->checkDoubleHeader($entity, $options, 'away_pool_team_id', 'home_pool_team_id');
			if ($double_header_result !== true) {
				return $double_header_result;
			}

			return true;
		}, 'valid_away_pool_team', [
			'errorField' => 'away_pool_team_id',
		]);

		// We don't do any of these checks for new games, trusting that the data will have been correctly set by the
		// creation algorithm. This will need to be revisited if we provide a manual bulk game creation mechanism.
		$rules->addUpdate(function (EntityInterface $entity, array $options) {
			if (!array_key_exists('games', $options)) {
				// If we're only saving a single game, none of this applies.
				return true;
			}

			if (collection($options['games'])->some(function ($game) use ($entity) {
				return $entity->id != $game->id && !empty($entity->home_dependency_type) && (
					($entity->home_dependency_type == $game->home_dependency_type && $entity->home_dependency_id && $entity->home_dependency_id == $game->home_dependency_id) ||
					($entity->home_dependency_type == $game->away_dependency_type && $entity->home_dependency_id && $entity->home_dependency_id == $game->away_dependency_id)
				);
			})) {
				return __('Dependency selected more than once.');
			}

			return true;
		}, 'valid_home_dependency', [
			'errorField' => 'home_dependency_id',
		]);

		// We don't do any of these checks for new games, trusting that the data will have been correctly set by the
		// creation algorithm. This will need to be revisited if we provide a manual bulk game creation mechanism.
		$rules->addUpdate(function (EntityInterface $entity, array $options) {
			if (!array_key_exists('games', $options)) {
				// If we're only saving a single game, none of this applies.
				return true;
			}

			if (collection($options['games'])->some(function ($game) use ($entity) {
				return $entity->id != $game->id && !empty($entity->away_dependency_type) && (
					($entity->away_dependency_type == $game->home_dependency_type && $entity->away_dependency_id && $entity->away_dependency_id == $game->home_dependency_id) ||
					($entity->away_dependency_type == $game->away_dependency_type && $entity->away_dependency_id && $entity->away_dependency_id == $game->away_dependency_id)
				);
			})) {
				return __('Dependency selected more than once.');
			}

			return true;
		}, 'valid_away_dependency', [
			'errorField' => 'away_dependency_id',
		]);

		return $rules;
	}

	private function checkDoubleHeader(EntityInterface $entity, array $options, $team, $opp) {
		// Tournaments just inherently have all kinds of double headers
		if ($entity->division) {
			$division = $entity->division;
		} else {
			$division = $this->Divisions->get($entity->division_id);
		}
		if ($division->schedule_type === 'tournament') {
			return true;
		}

		$other_games = collection($options['games'])->filter(function ($game) use ($entity) {
			return $game->id != $entity->id;
		});

		// Check that teams aren't being given double-headers, unless that's allowed.
		if (!empty($entity->$team)) {
			$other_team_games = $other_games->filter(function ($game) use ($entity, $team, $opp) {
				return $game->$team == $entity->$team || $game->$opp == $entity->$team;
			});
			if (!$other_team_games->isEmpty()) {
				if ($options['double_header'] || $options['multiple_days']) {
					// Check that the double-header doesn't cause conflicts; must be at the same facility, but different times
					foreach ($other_team_games as $other_team_game) {
						if (!$options['double_header'] && $entity->game_slot->game_date == $other_team_game->game_slot->game_date) {
							return __('Team scheduled twice on the same day.');
						}
						if (!$options['multiple_days'] && $entity->game_slot->game_date != $other_team_game->game_slot->game_date) {
							return __('Team scheduled on different days.');
						}
						if ($entity->game_slot->overlaps($other_team_game->game_slot)) {
							return __('Team scheduled in overlapping time slots.');
						}
						if ($entity->game_slot->game_date == $other_team_game->game_slot->game_date &&
							$entity->game_slot->facility_id != $other_team_game->game_slot->facility_id
						) {
							return __('Team scheduled on {0} at different facilities.', Configure::read('UI.fields'));
						}
					}
				} else {
					return __('Team was selected more than once.');
				}
			}
		}

		return true;
	}

	/**
	 * Make some adjustments to the data to be saved.
	 *
	 * @param CakeEvent $cakeEvent Unused
	 * @param ArrayObject $data The data record being patched in
	 * @param ArrayObject $options The options passed to the new/patchEntity method
	 */
	public function beforeMarshal(CakeEvent $cakeEvent, ArrayObject $data, ArrayObject $options) {
		if (!$data->offsetExists('status')) {
			$data['status'] = 'normal';
		}

		if (!$data->offsetExists('round') && $options->offsetExists('division')) {
			$data['round'] = $options['division']->current_round;
		}

		if (!$data->offsetExists('division_id') && $options->offsetExists('division')) {
			$data['division_id'] = $options['division']->id;
		}

		if ($data->offsetExists('incidents') && (!$data->offsetExists('has_incident') || !$data['has_incident'])) {
			$data->offsetUnset('incidents');
		}

		// This will be present when we are coming from schedule add/edit pages.
		if ($options->offsetExists('publish')) {
			$data['published'] = $options['publish'];
		}
	}

	/**
	 * Modifies the entity before rules are run. Updates done in here rely on the earlier games in the set already
	 * having been saved so their ID is available, so they can't be done in beforeMarshal.
	 *
	 * @param \Cake\Event\Event $cakeEvent The beforeRules event that was fired
	 * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
	 * @param \ArrayObject $options The options passed to the save method
	 * @param mixed $operation The operation (e.g. create, delete) about to be run
	 * @return void
	 */
	public function beforeRules(\Cake\Event\EventInterface $cakeEvent, EntityInterface $entity, ArrayObject $options, $operation) {
		// If we're only saving a single game, none of this applies.
		if ($options->offsetExists('games')) {
			if ($entity->has('home_dependency_resolved') && !$entity->home_dependency_resolved && $options['games'][$entity->home_dependency_id]->id) {
				$entity->home_dependency_resolved = true;
				$entity->home_dependency_id = $options['games'][$entity->home_dependency_id]->id;
			}
			if ($entity->has('away_dependency_resolved') && !$entity->away_dependency_resolved && $options['games'][$entity->away_dependency_id]->id) {
				$entity->away_dependency_resolved = true;
				$entity->away_dependency_id = $options['games'][$entity->away_dependency_id]->id;
			}

			// TODO: Cake issue? Patching game entities that have game slots in them doesn't reset the game slot property to the new one
			foreach ($options['games'] as $game) {
				if (!$game->game_slot || $game->game_slot_id != $game->game_slot->id) {
					$game->game_slot = $this->GameSlots->get($game->game_slot_id, [
						'contain' => ['Fields' => ['Facilities']]
					]);
					$game->setDirty('game_slot', false);
				}
			}
		}

		// Clear any dependencies that may have been set; they will cause "duplicate id" issues
		foreach (['home', 'away'] as $team_type) {
			$dependency_type = "{$team_type}_dependency_type";
			$dependency_id = "{$team_type}_dependency_id";
			$team_id_field = "{$team_type}_team_id";
			if ($entity->has($dependency_type) && in_array($entity->$dependency_type, ['game_winner', 'game_loser']) && $entity->isDirty($dependency_id)) {
				$entity->$team_id_field = null;
			}
		}

		// TODO: This wouldn't be required if we called adjustScoreAndRatings here. Can we do that without breaking other things?
		if (in_array($entity->status, Configure::read('unplayed_status'))) {
			$entity->home_score = $entity->away_score = null;
		}
	}

	/**
	 * Modifies the entity before it is saved.
	 *
	 * @param \Cake\Event\Event $cakeEvent The beforeSave event that was fired
	 * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
	 * @param \ArrayObject $options The options passed to the save method
	 * @return void
	 */
	public function beforeSave(\Cake\Event\EventInterface $cakeEvent, EntityInterface $entity, ArrayObject $options) {
		// If we're saving a batch of games, and the game slot formerly assigned to this game
		// is now not assigned to any of this batch, we must free it up.
		if ($options->offsetExists('games') && $entity->isDirty('game_slot_id') && !collection($options['games'])->firstMatch(['game_slot_id' => $entity->getOriginal('game_slot_id')])) {
			if (!$this->GameSlots->updateAll(['assigned' => false], ['GameSlots.id' => $entity->getOriginal('game_slot_id')])) {
				return false;
			}
		}

		if (!empty($entity->score_entries)) {
			if (!$entity->isFinalized()) {
				// The only way we should ever get here is after a score submission.
				// If there are two, we can try to finalize it.
				// TODO: Handle this kind of a save when someone is editing a schedule?
				if (reset($entity->score_entries)->team_id == $entity->home_team_id) {
					$opponent_score = $entity->getScoreEntry($entity->away_team_id);
				} else {
					$opponent_score = $entity->getScoreEntry($entity->home_team_id);
				}
				if ($opponent_score->person_id) {
					if (count($entity->score_entries) == 1) {
						$entity->score_entries[] = $opponent_score;
						$entity->finalize();
						array_pop($entity->score_entries);
					} else {
						$entity->finalize();
					}
				}
			}
		}

		if (!empty($entity->spirit_entries)) {
			// If "most spirited" isn't allowed, remove anything that was submitted.
			if (!Configure::read('scoring.most_spirited') || $entity->division->most_spirited == 'never') {
				foreach ($entity->spirit_entries as $entry) {
					$entry->most_spirited_id = null;
				}
			}
		}

		// If incident reports aren't allowed, remove anything that was submitted.
		if (!empty($entity->incidents) && !Configure::read('scoring.incident_reports')) {
			$entity->incidents = [];
			$entity->setDirty('incidents', true);
		}

		// "Copy" dependency games do not have a game slot ID set
		if ($entity->isDirty('game_slot_id') && !empty($entity->game_slot_id)) {
			if (empty($entity->game_slot)) {
				trigger_error('TODOTESTING', E_USER_WARNING);
				exit;
				$entity->game_slot = $this->GameSlots->newEntity([
					'id' => $entity->game_slot_id,
					'assigned' => true,
				]);
				pr($entity->game_slot);
			} else {
				$entity->game_slot = $this->GameSlots->patchEntity($entity->game_slot, ['assigned' => true]);
			}

			// TODOLATER: We should only do this when a change is made that might affect the field rankings:
			// home team, away team, game slot, field id
			if ($entity->game_slot->has('field')) {
				$this->updateFieldRanking($entity);
			}
		}

		if ($entity->isFinalized()) {
			// Possibly adjust the score if the game status changed.
			// We don't do this in finalize(), because that isn't
			// called in the case that an admin edits an approved score.
			$entity->adjustScoreAndRatings();

			// If the game was unplayed, we must delete any spirit entries.
			// TODO: Delete all stars too
			if (in_array($entity->status, Configure::read('unplayed_status'))) {
				$this->getAssociation('SpiritEntries')->setSaveStrategy('replace');
				$entity->spirit_entries = [];
				$entity->setDirty('spirit_entries', true);
			}
		}

		// Check for a dependency that has already been resolved
		foreach (['home', 'away'] as $team_type) {
			$dependency_type = "{$team_type}_dependency_type";
			$dependency_id = "{$team_type}_dependency_id";
			$team_id_field = "{$team_type}_team_id";
			if (!empty($entity->$dependency_type)) {
				switch ($entity->$dependency_type) {
					case 'game_winner':
						$result = $this->get($entity->$dependency_id);
						if ($result->home_score !== null) {
							if ($result->home_score >= $result->away_score) {
								$entity->$team_id_field = $result->home_team_id;
							} else {
								$entity->$team_id_field = $result->away_team_id;
							}
						} else {
							$entity->$team_id_field = null;
						}
						break;

					case 'game_loser':
						$result = $this->get($entity->$dependency_id);
						if ($result->home_score !== null) {
							if ($result->home_score >= $result->away_score) {
								$entity->$team_id_field = $result->away_team_id;
							} else {
								$entity->$team_id_field = $result->home_team_id;
							}
						} else {
							$entity->$team_id_field = null;
						}
						break;
				}
			}
		}
	}

	/**
	 * Perform additional operations after it is saved.
	 *
	 * @param \Cake\Event\Event $cakeEvent The afterSave event that was fired
	 * @param \Cake\Datasource\EntityInterface $entity The entity that was saved
	 * @param \ArrayObject $options The options passed to the save method
	 * @return void
	 */
	public function afterSave(\Cake\Event\EventInterface $cakeEvent, EntityInterface $entity, ArrayObject $options) {
		if ($entity->isFinalized()) {
			$entity->updateDependencies();
		}

		if ($entity->has('division')) {
			$this->Divisions->clearCache($entity->division);
		} else {
			$this->Divisions->clearCache($entity->division_id);
		}

		// TODO: We probably want to change the text of the email slightly if it's an update instead of a new incident.
		// TODO: This would seem to fit better in afterSaveCommit, but at that point incidents is no longer dirty.
		// Might also make sense to do this in the IncidentsTable afterSave, but then we don't have the game info handy.
		if ($entity->isDirty('incidents') && !empty($entity->incidents)) {
			$event = new CakeEvent('Model.Game.incidentReport', $this, [$entity]);
			$this->getEventManager()->dispatch($event);
		}

		if (Configure::read('feature.badges') && $entity->isFinalized() && (!$options->offsetExists('update_badges') || $options['update_badges'])) {
			$badge_obj = ModuleRegistry::getInstance()->load('Badge');
			if (!$badge_obj->update('game', $entity)) {
				return false;
			}
		}
	}

	/**
	 * Perform additional operations before it is deleted.
	 *
	 * @param \Cake\Event\Event $cakeEvent The beforeDelete event that was fired
	 * @param \Cake\Datasource\EntityInterface $entity The entity to be deleted
	 * @param \ArrayObject $options The options passed to the delete method
	 * @return bool
	 */
	public function beforeDelete(\Cake\Event\EventInterface $cakeEvent, EntityInterface $entity, ArrayObject $options) {
		// If the game was finalized, we have to reverse the ratings change.
		$entity->undoRatings();
		if ($entity->isDirty('home_team')) {
			if (!$this->HomeTeam->save($entity->home_team)) {
				return false;
			}
		}
		if ($entity->isDirty('away_team')) {
			if (!$this->AwayTeam->save($entity->away_team)) {
				return false;
			}
		}

		// "Copy" dependency games do not have a game slot ID set
		if (!empty($entity->game_slot_id)) {
			// Free up the game slot for re-use.
			if (!$entity->has('game_slot')) {
				$this->loadInto($entity, ['GameSlots']);
			}
			$entity->game_slot->assigned = false;
			$entity->setDirty('game_slot', true);
			if (!$this->GameSlots->save($entity->game_slot)) {
				return false;
			}
		}
	}

	/**
	 * Perform additional operations after it is deleted.
	 *
	 * @param \Cake\Event\Event $cakeEvent The afterDelete event that was fired
	 * @param \Cake\Datasource\EntityInterface $entity The entity that was deleted
	 * @param \ArrayObject $options The options passed to the delete method
	 * @return void
	 */
	public function afterDelete(\Cake\Event\EventInterface $cakeEvent, EntityInterface $entity, ArrayObject $options) {
		if ($this->Attendances->getConnection()->transactional(function () use ($entity) {
			foreach ($this->Attendances->find()->where(['game_id' => $entity->id]) as $attendance) {
				$attendance->game_id = null;
				if (!$this->Attendances->save($attendance)) {
					return false;
				}
			}

			return true;
		})) {
			// With the saves being inside a transaction, afterDeleteCommit is not called.
			$event = new CakeEvent('Model.afterDeleteCommit', $this, [null]);
			$this->getEventManager()->dispatch($event);
		} else {
			$event = new CakeEvent('Model.afterDeleteRollback', $this, [null]);
			$this->getEventManager()->dispatch($event);
		}

		if ($entity->has('division')) {
			$this->Divisions->clearCache($entity->division);
		} else {
			$this->Divisions->clearCache($entity->division_id);
		}
	}

	public function findPlayed(Query $query, array $options) {
		return $query->where(['NOT' => ['Games.status IN' => Configure::read('unplayed_status')]]);
	}

	public function findSchedule(Query $query, array $options) {
		if (!empty($options['teams'])) {
			$conditions = [
				'Games.home_team_id IN' => $options['teams'],
				'Games.away_team_id IN' => $options['teams'],
			];
		} else {
			$conditions = [];
		}
		if (isset($options['division'])) {
			$conditions['Games.division_id'] = $options['division'];
		}
		if (isset($options['playoff_division'])) {
			$conditions['AND'] = [
				'Games.division_id' => $options['playoff_division'],
				'Games.type !=' => SEASON_GAME,
			];
		}

		return $query
			->contain([
				'GameSlots' => ['Fields' => ['Facilities']],
				'HomeTeam',
				// TODO: Not everything that uses this function needs both DependencyPool and Pools
				'HomePoolTeam' => [
					'DependencyPool',
					'Pools',
				],
				'AwayTeam',
				'AwayPoolTeam' => [
					'DependencyPool',
					'Pools',
				],
				'Pools',
			])
			->where(['OR' => $conditions]);
	}

	public function findWithAttendance(Query $query, array $options) {
		$contain = [
			'Attendances' => [
				'queryBuilder' => function (Query $q) use ($options) {
					if (isset($options['people'])) {
						$q->where(['Attendances.person_id IN' => $options['people']]);
					}
					if (isset($options['teams'])) {
						$q->contain(['People'])->where(['Attendances.team_id IN' => $options['teams']]);
					}
					if (isset($options['status'])) {
						$q->where(['Attendances.status IN' => $options['status']]);
					}
					return $q;
				},
			]
		];
		return $query->contain($contain);
	}

	public static function compareSportDateAndField ($a, $b) {
		if ($a->division->league->sport < $b->division->league->sport) {
			return -1;
		} else if ($a->division->league->sport > $b->division->league->sport) {
			return 1;
		}
		return self::compareDateAndField($a, $b);
	}

	public static function compareDateAndField ($a, $b) {
		// This handles game, team event and task records
		if ($a->start_time > $b->start_time) {
			return 1;
		} else if ($a->start_time < $b->start_time) {
			return -1;
		}

		if ($a->end_time > $b->end_time) {
			return 1;
		} else if ($a->end_time < $b->end_time) {
			return -1;
		}

		// Handle named playoff games (and team events have names too)
		if (!empty($a->name)) {
			if (strpos($a->name, '-') !== false) {
				[, $a_name] = explode('-', $a->name);
			} else {
				$a_name = $a->name;
			}
			if (strpos($b->name, '-') !== false) {
				[, $b_name] = explode('-', $b->name);
			} else {
				$b_name = $b->name;
			}

			if ($a_name < $b_name) {
				return -1;
			} else if ($a_name > $b_name) {
				return 1;
			}
		}

		// Handle games based on field id
		if (!empty($a->game_slot->field_id) && !empty($b->game_slot->field_id)) {
			if ($a->game_slot->field_id < $b->game_slot->field_id) {
				return -1;
			} else if ($a->game_slot->field_id > $b->game_slot->field_id) {
				return 1;
			}

			if ($a->home_team && $b->home_team) {
				// Must both be games. Could be where there's multiple games in the same slot.
				return $a->home_team->name <=> $b->home_team->name;
			}
		}

		// Handle tasks based on task slot end time and then id
		if (!empty($a->task_end) && !empty($b->task_end)) {
			if ($a->task_end < $b->task_end) {
				return -1;
			} else if ($a->task_end > $b->task_end) {
				return 1;
			} else if ($a->id < $b->id) {
				return -1;
			} else {
				return 1;
			}
		}

		// Handle other things just based on their type
		if (is_a($a, \App\Model\Entity\Game::class)) {
			return -1;
		} else if (is_a($b, \App\Model\Entity\Game::class)) {
			return 1;
		} else if (is_a($a, \App\Model\Entity\TeamEvent::class)) {
			return -1;
		} else if (is_a($b, \App\Model\Entity\TeamEvent::class)) {
			return 1;
		}

		// Shouldn't ever reach here, but just in case...
		return -1;
	}

	protected function updateFieldRanking($game) {
		$use_homes = Configure::read('feature.home_field');
		$use_facilities = Configure::read('feature.facility_preference');
		$use_regions = Configure::read('feature.region_preference');
		if (!$use_homes && !$use_facilities && !$use_regions) {
			return;
		}

		$some_home_preference = false;
		foreach (['home_team' => 'home_field_rank', 'away_team' => 'away_field_rank'] as $team_type => $rank_field) {
			$rank = null;
			if ($game->$team_type) {
				$team = $game->$team_type;

				if ($use_homes) {
					$home_field_id = $team->home_field_id;
					if ($home_field_id == $game->game_slot->field->id) {
						$rank = 1;
					} else if ($team_type == 'home_team' && $home_field_id != null) {
						$some_home_preference = true;
					}
				}

				if (!$rank && $use_facilities) {
					$this->HomeTeam->loadInto($team, ['Facilities']);
					if (!empty($team->facilities)) {
						$pref = collection($team->facilities)->firstMatch(['id' => $game->game_slot->field->facility_id]);
						if ($pref) {
							$rank = $pref->_joinData->rank;
						} else if ($team_type == 'home_team' && !empty($team->facilities)) {
							$some_home_preference = true;
						}
					}
				}

				if (!$rank && $use_regions) {
					$home_region_id = $team->region_preference_id;
					if ($home_region_id == $game->game_slot->field->facility->region_id) {
						// A regional match won't count as more preferred than
						// a 2. This will give teams with regional preferences
						// a slight advantage over teams with specific field
						// preferences in terms of how often they're likely
						// to have their preferences met.
						$rank = max(2, count($team->facilities) + 1);
					} else if ($team_type == 'home_team' && $home_region_id != null) {
						$some_home_preference = true;
					}
				}
			}

			if ($team_type == 'home_team' && $rank === null && $some_home_preference) {
				$rank = 0;
			}
			$game->$rank_field = $rank;
		}

		// If this is a field that the away team likes more than the home
		// team, swap the teams, so that the current home team doesn't get
		// penalized in future field selections. But only do it when we're
		// building a schedule, not when we're editing. We also don't do this
		// if the home team hasn't expressed any interests, or else they may
		// *never* get a home game!
		if ($game->isNew() && $game->away_field_rank !== null && $some_home_preference &&
			($game->home_field_rank === null || $game->home_field_rank > $game->away_field_rank)
		)
		{
			[$game->home_team_id, $game->home_field_rank, $game->away_team_id, $game->away_field_rank] =
				[$game->away_team_id, $game->away_field_rank, $game->home_team_id, $game->home_field_rank];
		}
	}

	/**
	 * Adjust the indices of the score_entries and spirit_entries, so that
	 * the arrays are indexed by team_id instead of from zero.
	 *
	 */
	public static function adjustEntryIndices($game) {
		if (is_array($game)) {
			foreach ($game as $g) {
				self::adjustEntryIndices($g);
			}
		} else {
			foreach (['score_entries' => 'team_id', 'spirit_entries' => 'team_id'] as $model => $field) {
				if ($game->has($model)) {
					$game->$model = collection($game->$model)->indexBy($field)->toArray();
				}
			}
		}
	}

	/**
	 * Read the attendance records for a game.
	 * This will also create any missing records, with "unknown" status.
	 *
	 * @param mixed $team The team to read attendance for.
	 * @param mixed $days The days on which the division operates.
	 * @param mixed $game_id The game id, if known.
	 * @param mixed $dates The date of the game, or an array of dates.
	 * @param bool $force If true, teams without attendance tracking will have a "default" attendance array generated; otherwise, they will get an empty array
	 * @return mixed List of attendance records.
	 *
	 */
	public function readAttendance($team, $days, $game_id, $dates = null, $force = false) {
		if (empty($days)) {
			return [];
		}

		// We accept either a pre-read team entity with roster info, or just an id
		if (is_numeric($team)) {
			try {
				$team = $this->HomeTeam->get($team, [
					'contain' => ['People', 'Divisions'],
				]);
			} catch (RecordNotFoundException|InvalidArgumentException|InvalidPrimaryKeyException $ex) {
				return [];
			}
		} else {
			if (!is_a($team, \App\Model\Entity\Team::class) || !$team->has('people')) {
				trigger_error('Team records must include rosters when used with readAttendance', E_USER_ERROR);
			}
		}

		if (!$team->track_attendance) {
			// Teams without attendance tracking may get no data.
			// This shouldn't actually ever happen, and is really only in
			// place to help detect coding errors elsewhere.
			if (!$force) {
				return [];
			}

			// Make up data that looks like what we'd have if tracking was enabled.
			if (is_array($dates)) {
				trigger_error('Forcing attendance records for multiple dates for teams without attendance tracking enabled is not yet supported.', E_USER_ERROR);
			} else if (!$game_id) {
				trigger_error('Forcing attendance records for unscheduled games for teams without attendance tracking enabled is not yet supported.', E_USER_ERROR);
			} else {
				return $this->forcedAttendance($team, $game_id);
			}
		}

		// Make sure that all required records exist
		$conditions = [
			'team_id' => $team->id,
			'team_event_id IS' => null,
		];
		if (is_array($dates)) {
			foreach ($dates as $date) {
				$this->createAttendance($team, $days, null, $date);
			}
			$game_dates = GamesTable::matchDates($dates, $days);
			if (!empty($game_dates)) {
				$conditions['game_date IN'] = $game_dates;
			}
		} else {
			$this->createAttendance($team, $days, $game_id, $dates);
			if ($game_id !== null) {
				$conditions['game_id'] = $game_id;
			} else {
				$game_dates = GamesTable::matchDates($dates, $days);
				if (!empty($game_dates)) {
					$conditions['game_date IN'] = $game_dates;
				}
			}
		}

		// Re-read whatever is current, including join tables that will be useful in the output
		$attendance = $this->Attendances->Teams->get($team->id, [
			'contain' => [
				'People' => [
					'queryBuilder' => function (Query $q) {
						return $q->where(['TeamsPeople.status' => ROSTER_APPROVED]);
					},
					Configure::read('Security.authModel'),
					'Attendances' => [
						'queryBuilder' => function (Query $q) use ($conditions) {
							return $q->where($conditions);
						},
					],
					'Settings' => [
						'queryBuilder' => function (Query $q) {
							return $q->where(['category' => 'personal', 'name' => 'attendance_emails']);
						},
					],
				],
			]
		]);

		// There may be other attendance records from people that are no longer on the roster
		$extra = $this->Attendances->People->find()
			->contain([
				Configure::read('Security.authModel'),
			])
			->matching('Attendances', function (Query $q) use ($conditions, $attendance) {
				if (!empty($attendance->people)) {
					$conditions = array_merge($conditions, [
						'NOT' => ['person_id IN' => collection($attendance->people)->extract('id')->toArray()],
					]);
				}
				return $q->where($conditions);
			})
			->toArray();

		// Mangle these records into the same format as from the read above
		// TODO: Any way to change the find above so that this is unnecessary?
		$new = [];
		foreach ($extra as $person) {
			if (!array_key_exists($person->id, $new)) {
				$person->attendances = [$person->_matchingData['Attendances']];
				$person->unset('_matchingData');
				$person->_joinData = new TeamsPerson(['role' => 'none', 'status' => ROSTER_APPROVED]);
				$new[$person->id] = $person;
			} else {
				$new[$person->id]->attendances[] = $person->_matchingData['Attendances'];
			}
		}
		$attendance->people = array_merge($attendance->people, $new);

		$identity = Router::getRequest() ? Router::getRequest()->getAttribute('identity') : null;
		$include_gender = $identity && $identity->can('display_gender', new ContextResource($team, ['division' => $team->division]));
		\App\lib\context_usort($attendance->people, [TeamsTable::class, 'compareRoster'], ['include_gender' => $include_gender]);
		return $attendance;
	}

	// TODO: If a double-header is scheduled, attendance records will be created for both. If one of those
	// games is then edited to another team, the extra records won't be deleted. Not really a problem, but
	// would be nice to clear those strays out in such cases.
	protected function createAttendance($team, $days, $game_id, $date) {
		$copy_from_game_id = false;

		// Find game details
		if ($game_id !== null) {
			try {
				$game = $this->get($game_id, [
					'contain' => ['GameSlots']
				]);
			} catch (RecordNotFoundException|InvalidArgumentException|InvalidPrimaryKeyException $ex) {
				return;
			}
			if ($game->home_team_id != $team->id && $game->away_team_id != $team->id) {
				return;
			}
			$date = $game->game_slot->game_date;

			// Find all attendance records for this team for this game
			$attendance = $this->Attendances->find()
				->where([
					'team_id' => $team->id,
					'game_id' => $game_id,
				])
				->toArray();

			if (empty($attendance)) {
				$match_dates = GamesTable::matchDates($date, $days);

				// There might be no attendance records because of a schedule change.
				// Check for other attendance records for this team on the same date.
				$attendance = $this->Attendances->find()
					->where([
						'team_id' => $team->id,
						'game_date IN' => $match_dates,
						'team_event_id IS' => null,
					])
					->toArray();
				$attendance_game_ids = array_unique(collection($attendance)->extract('game_id')->toArray());

				// Check for other scheduled games including this team on the same date.
				$scheduled_game_ids = $this->find()
					->select('id')
					->contain(['GameSlots'])
					->where([
						'OR' => [
							'Games.home_team_id' => $team->id,
							'Games.away_team_id' => $team->id,
						],
						'GameSlots.game_date IN' => $match_dates,
						'Games.id !=' => $game_id,
					])
					->order(['GameSlots.game_date', 'GameSlots.game_start'])
					->all()
					->extract('id')
					->toArray();

				if (count($attendance_game_ids) > count($scheduled_game_ids)) {
					// If there are more other games with attendance records than there
					// are other games scheduled, then one of those games might be the
					// date-only placeholder game.
					if (in_array(null, $attendance_game_ids)) {
						// Find all placeholder game attendance records for this team for this date.
						$attendance = $this->Attendances->find()
							->where([
								'team_id' => $team->id,
								'game_date IN' => $match_dates,
								'game_id IS' => null,
								'team_event_id IS' => null,
							])
							->toArray();
					} else {
						// Otherwise, it must be this game, but it was rescheduled. Figure
						// out which one.
						// Note that this guess may not be right when a team has more than
						// one game that gets rescheduled; this will hopefully be a very
						// rare circumstance.
						foreach ($attendance_game_ids as $i) {
							if (!in_array($i, $scheduled_game_ids)) {
								$rescheduled_game_id = $i;
								break;
							}
						}
					}
				} else {
					// Otherwise, this game is a new one. If there are other attendance
					// records, we'll copy them.
					$copy_from_game_id = reset($attendance_game_ids);
				}
			}
		} else if ($date !== null) {
			$match_dates = GamesTable::matchDates($date, $days);

			$games = $this->find()
				->contain(['GameSlots'])
				->where([
					'OR' => [
						'Games.home_team_id' => $team->id,
						'Games.away_team_id' => $team->id,
					],
					'GameSlots.game_date IN' => $match_dates,
					'Games.published' => true,
				])
				->order(['GameSlots.game_start'])
				->toArray();
			if (empty($games)) {
				// Find all game attendance records for this team for this date
				$attendance = $this->Attendances->find()
					->where([
						'team_id' => $team->id,
						'game_date IN' => $match_dates,
						'team_event_id IS' => null,
					])
					->toArray();
			} else {
				foreach ($games as $game) {
					$this->createAttendance($team, $days, $game->id, $date);
				}
				return;
			}
		} else {
			return;
		}

		if ($this->getConnection()->transactional(function () use ($attendance, $team, $date, $game_id, $copy_from_game_id) {
			// Extract list of players on the roster as of this date.
			$roster = collection($team->people)->filter(function ($person) use ($date) {
				return $person->_joinData->created < $date->addDays(1) && $person->_joinData->status == ROSTER_APPROVED;
			})->toArray();

			// Go through the roster and make sure there are records for all players on this date.
			foreach ($roster as $person) {
				if ($copy_from_game_id !== false) {
					$record = collection($attendance)->firstMatch(['person_id' => $person->id, 'game_id' => $copy_from_game_id]);
				} else if (isset($rescheduled_game_id)) {
					// We might need to update an existing record with a rescheduled game id.
					$record = collection($attendance)->firstMatch(['person_id' => $person->id, 'game_id' => $rescheduled_game_id]);
				} else {
					$record = collection($attendance)->firstMatch(['person_id' => $person->id]);
				}

				// Any record we have at this point is either something to copy from,
				// rescheduled or a new game on a date that we already had a placeholder
				// record for, or correct.
				if (!empty($record)) {
					if ($copy_from_game_id !== false) {
						$record = $this->Attendances->cloneWithoutIds($record);
						$record = $this->Attendances->patchEntity($record, [
							'game_id' => $game_id,
						]);
					} else if ($game_id != $record->game_id) {
						$record = $this->Attendances->patchEntity($record, [
							'game_id' => $game_id,
							'game_date' => $date,
						]);
					}
					if ($this->Attendances->hasBehavior('Timestamp')) {
						$this->Attendances->removeBehavior('Timestamp');
					}
				} else {
					// We didn't find any appropriate record, so create a new one
					$record = $this->Attendances->newEntity([
						'team_id' => $team->id,
						'game_date' => $date,
						'game_id' => $game_id,
						'person_id' => $person->id,
						'status' => ATTENDANCE_UNKNOWN,
					]);
					if (!$this->Attendances->hasBehavior('Timestamp')) {
						$this->Attendances->addBehavior('Timestamp');
					}
				}

				// It's possible that there were no patches made, in which case this is a no-op
				$this->Attendances->save($record);
			}

			return true;
		})) {
			// With the saves being inside a transaction, afterSaveCommit is not called.
			$event = new CakeEvent('Model.afterSaveCommit', $this, [null]);
		} else {
			$event = new CakeEvent('Model.afterSaveRollback', $this, [null]);
		}
		$this->getEventManager()->dispatch($event);
	}

	protected function forcedAttendance($team, $game_id) {
		// Find game details
		try {
			$game = $this->get($game_id);
		} catch (RecordNotFoundException|InvalidArgumentException|InvalidPrimaryKeyException $ex) {
			return [];
		}
		if ($game->home_team_id != $team->id && $game->away_team_id != $team->id) {
			return [];
		}

		// Go through the roster and make fake records for all players on this date.
		$player_roles = Configure::read('regular_roster_roles');
		foreach ($team->people as $person) {
			if ($person->_joinData->status == ROSTER_APPROVED) {
				if (in_array($person->_joinData->role, $player_roles)) {
					$status = ATTENDANCE_ATTENDING;
				} else {
					$status = ATTENDANCE_UNKNOWN;
				}
				$person->attendances = [$this->Attendances->newEntity([
					'team_id' => $team->id,
					'game_id' => $game_id,
					'person_id' => $person->id,
					'status' => $status,
					'comment' => null,
				])];
			}
		}

		$identity = Router::getRequest() ? Router::getRequest()->getAttribute('identity') : null;
		$include_gender = $identity && $identity->can('display_gender', new ContextResource($team, ['division' => $team->division]));
		\App\lib\context_usort($team->people, [TeamsTable::class, 'compareRoster'], ['include_gender' => $include_gender]);
		return $team;
	}

	public static function matchDates($dates, $days) {
		if (!is_array($dates)) {
			$dates = [$dates];
		}

		$match_dates = [];
		foreach ($dates as $date) {
			$date_day = $date->format('N');
			foreach ($days as $day) {
				$match_dates[] = $date->addDays($day - $date_day);
			}
		}
		return $match_dates;
	}

	public static function attendanceOptions($role, $status, $past, $is_captain) {
		$is_regular = in_array($role, Configure::read('playing_roster_roles'));
		$options = Configure::read('attendance');

		// Only a captain can mark someone as a no show for a past game
		if (!$is_captain || !$past) {
			unset($options[ATTENDANCE_NO_SHOW]);
		}

		// Invited and available are only for subs
		if ($is_regular) {
			unset($options[ATTENDANCE_INVITED]);
			unset($options[ATTENDANCE_AVAILABLE]);
		} else if (!$is_captain) {
			// What a sub can set themselves to depends on their current status
			switch ($status) {
				case ATTENDANCE_UNKNOWN:
				case ATTENDANCE_ABSENT:
				case ATTENDANCE_AVAILABLE:
					unset($options[ATTENDANCE_ATTENDING]);
					unset($options[ATTENDANCE_INVITED]);
					break;

				case ATTENDANCE_ATTENDING:
					unset($options[ATTENDANCE_INVITED]);
					unset($options[ATTENDANCE_AVAILABLE]);
					break;

				case ATTENDANCE_INVITED:
					unset($options[ATTENDANCE_UNKNOWN]);
					unset($options[ATTENDANCE_AVAILABLE]);
					break;
			}
		}

		return $options;
	}

	public static function twitterScore($team, $team_score, $opponent, $opponent_score) {
		if ($team_score >= $opponent_score) {
			return $team->twitterName() . ' ' . $team_score . ', ' . $opponent->twitterName() . ' ' . $opponent_score;
		} else {
			return $opponent->twitterName() . ' ' . $opponent_score . ', ' . $team->twitterName() . ' ' . $team_score;
		}
	}

	public function affiliate($id) {
		try {
			return $this->Divisions->affiliate($this->division($id));
		} catch (RecordNotFoundException|InvalidArgumentException $ex) {
			return null;
		}
	}

	public function division($id) {
		try {
			return $this->field('division_id', ['Games.id' => $id]);
		} catch (RecordNotFoundException|InvalidArgumentException $ex) {
			return null;
		}
	}

}
