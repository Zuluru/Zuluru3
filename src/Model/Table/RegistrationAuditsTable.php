<?php
namespace App\Model\Table;

use Cake\Validation\Validator;

/**
 * RegistrationAudits Model
 *
 * @property \Cake\ORM\Association\HasMany $Payments
 */
class RegistrationAuditsTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->table('registration_audits');
		$this->displayField('id');
		$this->primaryKey('id');

		$this->hasMany('Payments', [
			'foreignKey' => 'registration_audit_id',
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

			->numeric('response_code')
			->requirePresence('response_code', 'create')
			->notEmpty('response_code')

			->numeric('iso_code')
			->allowEmpty('iso_code')

			->requirePresence('date', 'create')
			->notEmpty('date')

			->requirePresence('time', 'create')
			->notEmpty('time')

			->allowEmpty('approval_code')

			->allowEmpty('transaction_name')

			->decimal('charge_total')
			->requirePresence('charge_total', 'create')
			->notEmpty('charge_total')

			->allowEmpty('cardholder')

			->allowEmpty('expiry')

			->allowEmpty('f4l4')

			->allowEmpty('card')

			->allowEmpty('message')

			->allowEmpty('issuer')

			->allowEmpty('issuer_invoice')

			->allowEmpty('issuer_confirmation')

			;

		return $validator;
	}

}
