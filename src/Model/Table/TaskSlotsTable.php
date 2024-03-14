<?php
namespace App\Model\Table;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use App\Model\Rule\InDateConfigRule;

/**
 * TaskSlots Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Tasks
 * @property \Cake\ORM\Association\BelongsTo $People
 * @property \Cake\ORM\Association\BelongsTo $ApprovedBy
 */
class TaskSlotsTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->setTable('task_slots');
		$this->setDisplayField('id');
		$this->setPrimaryKey('id');

		$this->belongsTo('Tasks', [
			'foreignKey' => 'task_id',
			'joinType' => 'INNER',
		]);
		$this->belongsTo('People', [
			'foreignKey' => 'person_id',
		]);
		$this->belongsTo('ApprovedBy', [
			'foreignKey' => 'approved_by_id',
			'className' => 'People',
			'foreignKey' => 'approved_by_id',
		]);
	}

	/**
	 * Default validation rules.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 * @return \Cake\Validation\Validator
	 */
	public function validationDefault(Validator $validator) {
		$validator
			->numeric('id')
			->allowEmptyString('id', 'create')

			->date('task_date', __('You must provide a valid task date.'))
			->allowEmptyDate('task_date')

			->time('task_start', __('You must select a valid start time.'))
			->allowEmptyTime('task_start')

			->time('task_end', __('You must select a valid end time.'))
			->allowEmptyTime('task_end')

			->boolean('approved', __('Indicate whether the task assignment has been approved.'))
			->allowEmptyString('approved')

			->numeric('number_of_slots', __('Number of slots must be a number. Use 1 to create a single slot.'))

			->numeric('days_to_repeat', __('Days to repeat must be a number. Use 1 to create slots on a single day.'))

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
		$rules->add($rules->existsIn(['task_id'], 'Tasks'));
		$rules->add($rules->existsIn(['person_id'], 'People'));
		$rules->add($rules->existsIn(['approved_by_id'], 'People'));

		$rules->addCreate(new InDateConfigRule('gameslot'), 'rangeTaskDate', [
			'errorField' => 'task_date',
			'message' => __('You must provide a valid task date.'),
		]);

		return $rules;
	}

	public function findAssigned(Query $query, array $options) {
		return $query
			->contain([
				'Tasks' => [
					'queryBuilder' => function (Query $q) {
						return $q->find('translations');
					},
					'Categories' => [
						'queryBuilder' => function (Query $q) {
							return $q->find('translations');
						},
					],
					'People',
				],
				'People',
				'ApprovedBy',
			])
			->where([
				'TaskSlots.person_id' => $options['person'],
				'TaskSlots.task_date >=' => FrozenDate::now(),
				'TaskSlots.approved' => true,
			])
			->order(['TaskSlots.task_date', 'TaskSlots.task_start']);
	}

	public function affiliate($id) {
		try {
			return $this->Tasks->affiliate($this->field('task_id', ['TaskSlots.id' => $id]));
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}
}
