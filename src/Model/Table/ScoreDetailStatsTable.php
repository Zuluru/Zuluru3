<?php
namespace App\Model\Table;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * ScoreDetailStats Model
 *
 * @property \Cake\ORM\Association\BelongsTo $ScoreDetails
 * @property \Cake\ORM\Association\BelongsTo $People
 * @property \Cake\ORM\Association\BelongsTo $StatTypes
 */
class ScoreDetailStatsTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->table('score_detail_stats');
		$this->displayField('id');
		$this->primaryKey('id');

		$this->belongsTo('ScoreDetails', [
			'foreignKey' => 'score_detail_id',
			'joinType' => 'INNER',
		]);
		$this->belongsTo('People', [
			'foreignKey' => 'person_id',
			'joinType' => 'INNER',
		]);
		$this->belongsTo('StatTypes', [
			'foreignKey' => 'stat_type_id',
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
		$rules->add($rules->existsIn(['score_detail_id'], 'ScoreDetails'));
		$rules->add($rules->existsIn(['person_id'], 'People'));
		$rules->add($rules->existsIn(['stat_type_id'], 'StatTypes'));
		return $rules;
	}

	public function division($id) {
		try {
			return $this->ScoreDetails->division($this->field('score_detail_id', ['id' => $id]));
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}

	public function team($id) {
		try {
			return $this->ScoreDetails->team($this->field('score_detail_id', ['id' => $id]));
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}

}
