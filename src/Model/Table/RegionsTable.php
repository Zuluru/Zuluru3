<?php
namespace App\Model\Table;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * Regions Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Affiliates
 * @property \Cake\ORM\Association\HasMany $Facilities
 */
class RegionsTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->table('regions');
		$this->displayField('name');
		$this->primaryKey('id');

		$this->addBehavior('Trim');

		$this->belongsTo('Affiliates', [
			'foreignKey' => 'affiliate_id',
			'joinType' => 'INNER',
		]);

		$this->hasMany('Facilities', [
			'foreignKey' => 'region_id',
			'dependent' => false,
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

			->requirePresence('name', 'create')
			->notEmpty('name', __('The name cannot be blank.'))

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
		$rules->add($rules->existsIn(['affiliate_id'], 'Affiliates', __('You must select a valid affiliate.')));
		return $rules;
	}

	public function affiliate($id) {
		try {
			return $this->field('affiliate_id', ['id' => $id]);
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}
}
