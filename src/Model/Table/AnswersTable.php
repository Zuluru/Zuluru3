<?php
namespace App\Model\Table;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * Answers Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Questions
 * @property \Cake\ORM\Association\HasMany $Responses
 */
class AnswersTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('answers');
		$this->setDisplayField('id');
		$this->setPrimaryKey('id');

		$this->addBehavior('Translate', ['fields' => ['answer']]);

		$this->belongsTo('Questions', [
			'foreignKey' => 'question_id',
			'joinType' => 'INNER',
		]);

		$this->hasMany('Responses', [
			'foreignKey' => 'answer_id',
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

			->requirePresence('answer', 'create')
			->notEmptyString('answer')

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
		$rules->add($rules->existsIn(['question_id'], 'Questions', __('You must select a valid question.')));
		return $rules;
	}

	public function affiliate($id) {
		try {
			return $this->Questions->affiliate($this->field('question_id', ['Answers.id' => $id]));
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}

}
