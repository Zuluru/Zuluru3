<?php
namespace App\Model\Table;

use Cake\Validation\Validator;

/**
 * EventTypes Model
 *
 * @property \Cake\ORM\Association\HasMany $Events
 */
class EventTypesTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->table('event_types');
		$this->displayField('name');
		$this->primaryKey('id');

		$this->addBehavior('Trim');

		$this->hasMany('Events', [
			'foreignKey' => 'event_type_id',
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
			->allowEmpty('id', 'create')

			->requirePresence('name', 'create', __('The name cannot be blank.'))
			->notEmpty('name', __('The name cannot be blank.'))

			->requirePresence('type', 'create')
			->notEmpty('type')

			;

		return $validator;
	}

}
