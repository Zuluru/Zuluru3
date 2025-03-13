<?php
namespace App\Model\Table;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use InvalidArgumentException;

/**
 * Contacts Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Affiliates
 */
class ContactsTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('contacts');
		$this->setDisplayField('name');
		$this->setPrimaryKey('id');

		$this->addBehavior('Trim');
		$this->addBehavior('Translate', [
			'strategyClass' => \Cake\ORM\Behavior\Translate\ShadowTableStrategy::class,
			'fields' => ['name'],
		]);

		$this->belongsTo('Affiliates', [
			'foreignKey' => 'affiliate_id',
			'joinType' => 'INNER',
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

			->requirePresence('name', 'create', __('Contact name must not be blank.'))
			->notEmptyString('name', __('The name cannot be blank.'))

			->requirePresence('email', 'create', __('You must provide a valid email address.'))
			->notEmptyString('email', __('You must provide a valid email address.'))
			->email('email', false, __('You must provide a valid email address.'))

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
	public function buildRules(RulesChecker $rules): \Cake\ORM\RulesChecker {
		$rules->add($rules->isUnique(['name'], __('There is already a contact by that name.')));
		$rules->add($rules->existsIn(['affiliate_id'], 'Affiliates', __('You must select a valid affiliate.')));
		return $rules;
	}

	public function affiliate($id) {
		try {
			return $this->field('affiliate_id', [$this->aliasField('id') => $id]);
		} catch (RecordNotFoundException|InvalidArgumentException $ex) {
			return null;
		}
	}

}
