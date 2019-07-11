<?php
namespace App\Model\Table;

use App\View\Helper\ZuluruTimeHelper;
use ArrayObject;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event as CakeEvent;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;
use App\Model\Rule\InConfigRule;
use App\Model\Rule\InDateConfigRule;

/**
 * GameSlots Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Fields
 * @property \Cake\ORM\Association\HasMany $Games
 * @property \Cake\ORM\Association\HasMany $Divisions
 */
class GameSlotsTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->table('game_slots');
		$this->displayField('id');
		$this->primaryKey('id');

		$this->addBehavior('Trim');

		$this->belongsTo('Fields', [
			'foreignKey' => 'field_id',
			'joinType' => 'INNER',
		]);

		$this->hasMany('Games', [
			'foreignKey' => 'game_slot_id',
			'dependent' => false,
		]);

		$this->belongsToMany('Divisions', [
			'foreignKey' => 'game_slot_id',
			'targetForeignKey' => 'division_id',
			'through' => 'DivisionsGameslots',
			'saveStrategy' => 'replace',
		]);
	}

	/**
	 * Common validation rules.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 * @return \Cake\Validation\Validator
	 */
	public function validationCommon(Validator $validator) {
		$validator
			->numeric('id')
			->allowEmpty('id', 'create')

			->date('game_date', __('You must provide a valid game date.'))
			->notEmpty('game_date', __('You must provide a valid game date.'))

			->time('game_start', __('You must provide a valid game start time.'))
			->notEmpty('game_start', __('You must provide a valid game start time.'))

			->time('game_end', __('You must provide a valid game end time.'))
			->allowEmpty('game_end')

			->boolean('assigned')
			->notEmpty('assigned')

			;

		return $validator;
	}

	/**
	 * Default validation rules.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 * @return \Cake\Validation\Validator
	 */
	public function validationDefault(Validator $validator) {
		$validator = $this->validationCommon($validator);

		$validator
			->requirePresence('divisions', 'create', __('You must select at least one division!'))
			->notEmpty('divisions', __('You must select at least one division!'))

			;

		return $validator;
	}

	/**
	 * Bulk validation rules.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 * @return \Cake\Validation\Validator
	 */
	public function validationBulk(Validator $validator) {
		$validator = $this->validationDefault($validator);

		$validator
			->requirePresence('fields', 'create', __('You must select at least one {0}!', __(Configure::read('UI.field'))))
			->notEmpty('fields', __('You must select at least one {0}!', __(Configure::read('UI.field'))))

			->add('length', 'noLengthWithSunset', [
				'rule' => function ($value, $context) {
					$end = $context['data']['game_end'];
					// If it's an edit or sunset time is not selected, it's okay.
					if (!($context['newRecord'] && empty($end['hour']) && empty($end['minute']) && empty($end['meridian']))) {
						return true;
					}
					// Otherwise, it's okay if the length is zero
					return ($value == 0);
				},
				'message' => __('You cannot specify game lengths in conjunction with sunset end times!'),
			])

			->add('weeks', 'valid', ['rule' => ['inList', array_combine($r = range(1, 26), $r)], 'message' => __('Invalid number of weeks.')]);

			;

		return $validator;
	}

	/**
	 * Returns a rules checker object that will be used for validating
	 * application integrity.
	 *
	 * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
	 * @return \Cake\ORM\RulesChecker
	 */
	public function buildRules(RulesChecker $rules) {
		$rules->add($rules->existsIn(['field_id'], 'Fields', __('You must select a valid field.')));

		$rules->addCreate(new InDateConfigRule('gameslot'), 'rangeGameDate', [
			'errorField' => 'game_date',
			'message' => __('You must provide a valid game date.'),
		]);

		$rules->add(new InConfigRule(['key' => 'options.game_lengths', 'optional' => true]), 'validGameLength', [
			'errorField' => 'length',
			'message' => __('You must select a valid slot length.'),
		]);

		$rules->add(new InConfigRule(['key' => 'options.game_buffers', 'optional' => true]), 'validGameBuffer', [
			'errorField' => 'buffer',
			'message' => __('You must select a valid game buffer.'),
		]);

		$rules->add(function (EntityInterface $entity, Array $options) {
			if (!empty($entity->game_end)) {
				return true;
			}
			if (!empty($entity->field_id)) {
				$fields = [$entity->field_id];
			} else if (!empty($entity->fields)) {
				$fields = array_keys($entity->fields);
			} else {
				// This may happen when we're checking data for bulk adds. The error will
				// be reported by validation, though, so we don't need to deal with it here.
				// But we can't let the check proceed, because we assume later on that there
				// are values in the fields array.
				return true;
			}

			$fields_table = TableRegistry::get('Fields');
			$indoor_fields = $fields_table->find()
				->where([
					'id IN' => $fields,
					'indoor' => true,
				])
				->count();
			if ($indoor_fields > 0) {
				$sport = $this->Fields->field('sport', ['id' => current($fields)]);
				return __('You cannot select indoor {0} in conjunction with sunset end times!', __(Configure::read("sports.{$sport}.fields")));
			}

			return true;
		}, 'allowEmpty', [
			'errorField' => 'game_end',
		]);

		$rules->add(function (EntityInterface $entity, Array $options) {
			if ($entity->end_time->diffInMinutes($entity->start_time) > 12 * 60) {
				return __('Game end time of {0} is more than 12 hours from game start time of {1}!', ZuluruTimeHelper::time($entity->end_time), ZuluruTimeHelper::time($entity->start_time));
			}
			return true;
		}, 'tooLong', [
			'errorField' => 'game_end',
		]);

		$rules->add(function (EntityInterface $entity, Array $options) {
			$sunset = \App\Lib\local_sunset_for_date($entity->game_date);
			if (empty($entity->game_end)) {
				$game_end = $sunset;
			} else {
				$game_end = $entity->game_end;
			}

			$conditions = [
				'field_id' => $entity->field_id,
				'game_date' => $entity->game_date,
				'OR' => [
					[
						'game_start >=' => $entity->game_start,
						'game_start <' => $game_end,
					],
					[
						'game_start <' => $entity->game_start,
						'game_end >' => $entity->game_start,
					],
				],
			];
			if ($sunset > $entity->game_start) {
				$conditions['OR'][] = [
					'game_start <' => $entity->game_start,
					'game_end IS' => null,
				];
			}
			if (!$entity->isNew()) {
				$conditions['id !='] = $entity->id;
			}

			$overlap = $this->find()
				->where($conditions)
				->count();
			if ($overlap) {
				if (!empty($options['single'])) {
					return __('Conflicts with an existing game slot.');
				} else {
					$field = $this->Fields->get($entity->field_id, [
						'contain' => ['Facilities']
					]);
					return __('{0} on {1} at {2} conflicts with an existing game slot. Unable to continue. (There may be more conflicts; this is only the first one detected.)',
						ZuluruTimeHelper::time($entity->game_start), ZuluruTimeHelper::date($entity->game_date), $field->long_name
					);
				}
			}
			return true;
		}, 'noOverlap', [
			'errorField' => 'game_start',
		]);

		$rules->addDelete(function ($entity, $options) {
			if ($entity->assigned) {
				return __('This game slot has a game assigned to it and cannot be deleted.');
			}
			return true;
		}, 'unassigned', ['errorField' => 'delete']);

		return $rules;
	}

	/**
	 * If we require divisions to be present (true during add and edit, but not when updating the "assigned" field),
	 * then make sure there's something there that the validator can complain about being empty.
	 *
	 * @param CakeEvent $cakeEvent Unused
	 * @param ArrayObject $data The data record being patched in
	 * @param ArrayObject $options The options passed to the new/patchEntity method
	 */
	public function beforeMarshal(CakeEvent $cakeEvent, ArrayObject $data, ArrayObject $options) {
		if ($options->offsetExists('divisions') && $options['divisions'] && !$data->offsetExists('divisions')) {
			$data['divisions'] = [];
		}
	}

	// TODOLATER: Replace all the getXxx functions with query-based custom finders like this?
	public function findAvailable(Query $query, Array $options) {
		$query
			->contain([
				'Games',
				'Fields' => [
					'Facilities',
				],
				'Divisions' => [
					'queryBuilder' => function (Query $q) use ($options) {
						return $q->where(['Divisions.id IN' => $options['divisions']]);
					},
				],
			]);

		if ($options['is_tournament']) {
			$query->where([
				'OR' => [
					// Unassigned slots from other days
					'AND' => [
						function ($exp) use ($options, $query) {
							// TODO: Database-independent way of doing the interval?
							$start = $query->func()->date_add([
								"'{$options['date']}', INTERVAL -6 DAY" => 'literal',
							]);
							$end = $query->func()->date_add([
								"'{$options['date']}', INTERVAL 6 DAY" => 'literal',
							]);
							return $exp->between('GameSlots.game_date', $start, $end, 'date');
						},
						'GameSlots.game_date !=' => $options['date'],
						'GameSlots.assigned' => false,
					],
					// Or any slot from today
					'GameSlots.game_date' => $options['date'],
				]
			]);
		} else if ($options['multi_day']) {
			$query->where([
				function ($exp) use ($options, $query) {
					$end = (new FrozenDate($options['date']))->next(Configure::read('organization.first_day'))->subDay();
					return $exp->between('GameSlots.game_date', $options['date'], $end, 'date');
				},
			]);
		} else {
			$query->where(['GameSlots.game_date' => $options['date']]);
		}

		$mapper = function ($slot, $key, $mapReduce) use ($options) {
			$applicable = true;
			if (empty($slot->divisions)) {
				// Remove any game slots that are not available to any division that
				// we are interested in
				$applicable = false;
			} else if (!$options['double_booking']) {
				// Remove any game slots that are exclusively assigned to a division
				// that we are not interested in
				$assigned_to_divisions = array_unique(collection($slot->games)->extract('division_id')->toArray());
				$intersect = array_intersect($assigned_to_divisions, $options['divisions']);
				if (!empty($assigned_to_divisions) && empty($intersect)) {
					$applicable = false;
				}
			}
			if ($applicable) {
				$mapReduce->emitIntermediate($slot, $key);
			}
		};

		// Each key will have only a single slot in the array; here, we eliminate the array entirely.
		$reducer = function ($slots, $key, $mapReduce) {
			$mapReduce->emit(current($slots), $key);
		};

		return $query->mapReduce($mapper, $reducer);
	}

	public function affiliate($id) {
		try {
			return $this->Fields->affiliate($this->field('field_id', ['id' => $id]));
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}

	public function sport($id) {
		try {
			return $this->Fields->sport($this->field('field_id', ['id' => $id]));
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}

	public static function compareTimeAndField($a, $b) {
		if (!is_a($a, 'App\Model\Entity\GameSlot')) {
			trigger_error('TODOTESTING', E_USER_WARNING);
			exit;
		}
		if (!is_a($b, 'App\Model\Entity\GameSlot')) {
			trigger_error('TODOTESTING', E_USER_WARNING);
			exit;
		}

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

		if ($a->field->long_name > $b->field->long_name) {
			return 1;
		} else if ($a->field->long_name < $b->field->long_name) {
			return -1;
		}

		return 0;
	}
}
