<?php
namespace App\Model\Table;

use App\Model\Entity\Report;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Reports Model
 *
 * @property \Cake\ORM\Association\BelongsTo $People
 * @property \Cake\ORM\Association\HasMany $PhpbbReports
 */
class ReportsTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->setTable('reports');
		$this->setDisplayField('id');
		$this->setPrimaryKey('id');

		$this->belongsTo('People', [
			'foreignKey' => 'person_id',
			'joinType' => 'INNER'
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
			->numeric('id', 'valid')
			->allowEmptyString('id', null, 'create')

			->requirePresence('report', 'create')
			->notEmptyString('report')

			->requirePresence('params', 'create')
			->notEmptyString('params')

			->numeric('failures', 'valid')
			->notEmptyString('failures')

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
		$rules->add($rules->existsIn(['person_id'], 'People'));
		return $rules;
	}

}
