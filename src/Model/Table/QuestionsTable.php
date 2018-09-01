<?php
namespace App\Model\Table;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event as CakeEvent;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use App\Model\Rule\InConfigRule;

/**
 * Questions Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Affiliates
 * @property \Cake\ORM\Association\HasMany $Answers
 * @property \Cake\ORM\Association\BelongsToMany $Questionnaires
 */
class QuestionsTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->table('questions');
		$this->displayField('name');
		$this->primaryKey('id');

		$this->addBehavior('Trim');

		$this->belongsTo('Affiliates', [
			'foreignKey' => 'affiliate_id',
			'joinType' => 'INNER',
		]);

		$this->hasMany('Answers', [
			'foreignKey' => 'question_id',
			'dependent' => false,
			'sort' => 'Answers.sort',
		]);

		$this->belongsToMany('Questionnaires', [
			'foreignKey' => 'question_id',
			'targetForeignKey' => 'questionnaire_id',
			'joinTable' => 'questionnaires_questions',
			'saveStrategy' => 'replace',
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

			->requirePresence('question', 'create')
			->notEmpty('question')

			->requirePresence('type', 'create')
			->notEmpty('type')

			->boolean('active')
			->notEmpty('active')

			->boolean('anonymous', __('Indicate whether responses to this question will be anonymous.'))
			->requirePresence('anonymous', 'create', __('Indicate whether responses to this question will be anonymous.'))
			->notEmpty('anonymous', __('Indicate whether responses to this question will be anonymous.'))

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

		$rules->add(new InConfigRule('options.question_types'), 'validType', [
			'errorField' => 'type',
			'message' => __('You must select a valid question type.'),
		]);

		return $rules;
	}

	/**
	 * Modifies the entity before rules are run.
	 *
	 * @param \Cake\Event\Event $cakeEvent The beforeRules event that was fired
	 * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
	 * @param \ArrayObject $options The options passed to the save method
	 * @param mixed $operation The operation (e.g. create, delete) about to be run
	 * @return void
	 */
	public function beforeRules(CakeEvent $cakeEvent, EntityInterface $entity, ArrayObject $options, $operation) {
		if ($entity->isNew()) {
			$entity->active = true;
		}
	}

	public function affiliate($id) {
		try {
			return $this->field('affiliate_id', ['id' => $id]);
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}
}
