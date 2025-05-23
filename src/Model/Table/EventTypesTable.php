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
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('event_types');
		$this->setDisplayField('name');
		$this->setPrimaryKey('id');

		$this->addBehavior('Trim');
		$this->addBehavior('Translate', [
			'strategyClass' => \Cake\ORM\Behavior\Translate\ShadowTableStrategy::class,
			'fields' => ['name'],
		]);

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
	public function validationDefault(Validator $validator): \Cake\Validation\Validator {
		$validator
			->numeric('id')
			->allowEmptyString('id', null, 'create')

			->requirePresence('name', 'create', __('The name cannot be blank.'))
			->notEmptyString('name', __('The name cannot be blank.'))

			->requirePresence('type', 'create')
			->notEmptyString('type')

			;

		return $validator;
	}

}
