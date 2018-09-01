<?php
namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * QuestionnairesQuestions Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Questionnaires
 * @property \Cake\ORM\Association\BelongsTo $Questions
 */
class QuestionnairesQuestionsTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->table('questionnaires_questions');
		$this->displayField('id');
		$this->primaryKey('id');

		$this->belongsTo('Questionnaires', [
			'foreignKey' => 'questionnaire_id',
			'joinType' => 'INNER',
		]);
		$this->belongsTo('Questions', [
			'foreignKey' => 'question_id',
			'joinType' => 'INNER',
		]);
	}

}
