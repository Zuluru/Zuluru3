<?php
namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
/**
 * TeamsFacilities Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Teams
 * @property \Cake\ORM\Association\BelongsTo $Facilities
 */
class TeamsFacilitiesTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->table('teams_facilities');
		$this->displayField('id');
		$this->primaryKey('id');

		$this->belongsTo('Teams', [
			'foreignKey' => 'team_id',
			'joinType' => 'INNER',
		]);
		$this->belongsTo('Facilities', [
			'foreignKey' => 'facility_id',
			'joinType' => 'INNER',
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

			->numeric('rank')
			->requirePresence('rank', 'create')
			->notEmpty('rank')

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
		$rules->add($rules->existsIn(['team_id'], 'Teams'));
		$rules->add($rules->existsIn(['facility_id'], 'Facilities'));
		return $rules;
	}

}
