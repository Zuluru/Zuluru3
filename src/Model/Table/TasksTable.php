<?php
namespace App\Model\Table;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * Tasks Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Categories
 * @property \Cake\ORM\Association\BelongsTo $People
 * @property \Cake\ORM\Association\HasMany $TaskSlots
 */
class TasksTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->setTable('tasks');
		$this->setDisplayField('name');
		$this->setPrimaryKey('id');

		$this->addBehavior('Trim');
		$this->addBehavior('Translate', ['fields' => ['name', 'description', 'notes']]);

		$this->belongsTo('Categories', [
			'foreignKey' => 'category_id',
			'joinType' => 'INNER',
		]);
		$this->belongsTo('People', [
			'foreignKey' => 'person_id',
			'joinType' => 'INNER',
		]);

		$this->hasMany('TaskSlots', [
			'foreignKey' => 'task_id',
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

			->requirePresence('name', 'create')
			->notEmptyString('name', __('The name cannot be blank.'))

			->notEmptyString('description', __('The description cannot be blank.'))

			->allowEmptyString('notes')

			->requirePresence('category_id', 'create')
			->notEmptyString('category_id', __('You must select a valid category.'))

			->requirePresence('person_id', 'create')
			->notEmptyString('person_id', __('You must select a valid person.'))

			->boolean('auto_approve')
			->requirePresence('auto_approve', 'create')
			->allowEmptyString('auto_approve')

			->boolean('allow_signup')
			->requirePresence('allow_signup', 'create')
			->allowEmptyString('allow_signup')

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
		$rules->add($rules->existsIn(['category_id'], 'Categories'), [
			'message' => __('You must select a valid category.'),
		]);

		$rules->add($rules->existsIn(['person_id'], 'People'), [
			'message' => __('You must select a valid person.'),
		]);

		return $rules;
	}

	public function affiliate($id) {
		try {
			return $this->Categories->affiliate($this->field('category_id', ['Tasks.id' => $id]));
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}

}
