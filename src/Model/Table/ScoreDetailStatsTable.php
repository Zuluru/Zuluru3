<?php
namespace App\Model\Table;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use InvalidArgumentException;

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
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('score_detail_stats');
		$this->setDisplayField('id');
		$this->setPrimaryKey('id');

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
	public function validationDefault(Validator $validator): \Cake\Validation\Validator {
		$validator
			->numeric('id')
			->allowEmptyString('id', null, 'create')

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
		$rules->add($rules->existsIn(['score_detail_id'], 'ScoreDetails'));
		$rules->add($rules->existsIn(['person_id'], 'People'));
		$rules->add($rules->existsIn(['stat_type_id'], 'StatTypes'));
		return $rules;
	}

	public function division($id) {
		try {
			return $this->ScoreDetails->division($this->field('score_detail_id', ['ScoreDetailStats.id' => $id]));
		} catch (RecordNotFoundException|InvalidArgumentException $ex) {
			return null;
		}
	}

	public function team($id) {
		try {
			return $this->ScoreDetails->team($this->field('score_detail_id', ['ScoreDetailStats.id' => $id]));
		} catch (RecordNotFoundException|InvalidArgumentException $ex) {
			return null;
		}
	}

}
