<?php
namespace App\Model\Table;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use App\Model\Rule\ValidPlayRule;
use InvalidArgumentException;

/**
 * ScoreDetails Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Games
 * @property \Cake\ORM\Association\BelongsTo $Teams
 * @property \Cake\ORM\Association\HasMany $ScoreDetailStats
 */
class ScoreDetailsTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('score_details');
		$this->setDisplayField('id');
		$this->setPrimaryKey('id');

		$this->addBehavior('Timestamp');

		$this->belongsTo('Games', [
			'foreignKey' => 'game_id',
			'joinType' => 'INNER',
		]);
		$this->belongsTo('Teams', [
			'foreignKey' => 'team_id',
			'joinType' => 'INNER',
		]);

		$this->hasMany('ScoreDetailStats', [
			'foreignKey' => 'score_detail_id',
			'dependent' => true,
			'saveStrategy' => 'replace',
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

			->numeric('score_from')
			->allowEmptyString('score_from')

			->requirePresence('play', 'create')
			->notEmptyString('play')

			->numeric('points')
			->allowEmptyString('points')

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
		$rules->add($rules->existsIn(['game_id'], 'Games'));
		$rules->add($rules->existsIn(['team_id'], 'Teams'));
		$rules->add($rules->existsIn(['created_team_id'], 'Teams'));

		$rules->add(new ValidPlayRule(), 'validPlay', [
			'errorField' => 'play',
			'message' => __('You must select a valid play.'),
		]);

		return $rules;
	}

	public function division($id) {
		try {
			return $this->Games->division($this->field('game_id', ['ScoreDetails.id' => $id]));
		} catch (RecordNotFoundException|InvalidArgumentException $ex) {
			return null;
		}
	}

	public function team($id) {
		try {
			return $this->field('created_team_id', ['ScoreDetails.id' => $id]);
		} catch (RecordNotFoundException|InvalidArgumentException $ex) {
			return null;
		}
	}

}
