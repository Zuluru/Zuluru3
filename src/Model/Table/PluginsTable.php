<?php
namespace App\Model\Table;

use App\Model\Entity\Plugin;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Plugins Model
 *
 */
class PluginsTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->setTable('plugins');
		$this->setDisplayField('name');
		$this->setPrimaryKey('id');
	}

	/**
	 * Default validation rules.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 * @return \Cake\Validation\Validator
	 */
	public function validationDefault(Validator $validator) {
		$validator
			->numeric('id', 'valid')
			->allowEmptyString('id', null, 'create')

			->requirePresence('name', 'create')
			->notEmptyString('name')

			->requirePresence('load_name', 'create')
			->notEmptyString('load_name')

			->requirePresence('path', 'create')
			->notEmptyString('path')

			->boolean('advertise', 'valid')
			->requirePresence('advertise', 'create')
			->notEmptyString('advertise')

			->boolean('enabled', 'valid')
			->requirePresence('enabled', 'create')
			->notEmptyString('enabled')
			;

		return $validator;
	}

}
