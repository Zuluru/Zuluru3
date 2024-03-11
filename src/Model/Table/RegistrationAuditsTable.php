<?php
namespace App\Model\Table;

use App\Http\API;
use App\Model\Entity\RegistrationAudit;
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

		$this->setTable('registration_audits');
		$this->setDisplayField('id');
		$this->setPrimaryKey('id');

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

	public function getAPI(RegistrationAudit $audit): ?API {
		if (!$audit->payment_plugin) {
			return null;
		}

		$plugin = $audit->payment_plugin;
		$class = $plugin . 'Payment\Http\API';
		try {
			// TODO: If we change isTestData to rely in any way on the data being posted, this will need to change.
			// Alternately, skip isTestData entirely, and track in the audit record whether it was a test payment.
			return new $class(API::isTestData(null));
		} catch (\Exception $ex) {
			$this->log($ex->getMessage());
			return null;
		}
	}

}
