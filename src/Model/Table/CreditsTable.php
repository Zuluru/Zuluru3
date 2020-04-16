<?php
namespace App\Model\Table;

use Cake\Datasource\EntityInterface;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * Credits Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Affiliates
 * @property \Cake\ORM\Association\BelongsTo $People
 */
class CreditsTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->setTable('credits');
		$this->setDisplayField('id');
		$this->setPrimaryKey('id');

		$this->addBehavior('Timestamp');
		$this->addBehavior('Muffin/Footprint.Footprint', [
			'events' => [
				'Model.beforeSave' => [
					'created_person_id' => 'new',
				],
			],
			'propertiesMap' => [
				'created_person_id' => '_footprint.person.id',
			],
		]);

		$this->belongsTo('Affiliates', [
			'foreignKey' => 'affiliate_id',
			'joinType' => 'INNER',
		]);
		$this->belongsTo('People', [
			'foreignKey' => 'person_id',
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

			->numeric('amount')
			->notEmpty('amount')
			->add('amount', 'valid', ['rule' => ['comparison', '>', 0], 'message' => __('{0} amounts must be positive.', __('Credit'))])

			->numeric('amount_used')
			->allowEmpty('amount_used')

			->allowEmpty('notes')

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
		$rules->add($rules->existsIn(['affiliate_id'], 'Affiliates'));
		$rules->add($rules->existsIn(['person_id'], 'People'));
		$rules->add($rules->existsIn(['created_person_id'], 'People'));

		$rules->add(function (EntityInterface $entity, Array $options) {
			return $entity->amount_used === null || $entity->amount_used <= $entity->amount;
		}, 'validAmountUsed', [
			'errorField' => 'amount_used',
		]);

		return $rules;
	}

}
