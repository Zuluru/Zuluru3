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
			->add('id', 'valid', ['rule' => 'numeric'])
			->allowEmpty('id', 'create')

			->requirePresence('name', 'create')
			->notEmpty('name')

			->requirePresence('load_name', 'create')
			->notEmpty('load_name')

			->requirePresence('path', 'create')
			->notEmpty('path')

			->add('advertise', 'valid', ['rule' => 'boolean'])
			->requirePresence('advertise', 'create')
			->notEmpty('advertise')

			->add('enabled', 'valid', ['rule' => 'boolean'])
			->requirePresence('enabled', 'create')
			->notEmpty('enabled')
			;

		return $validator;
	}

}
