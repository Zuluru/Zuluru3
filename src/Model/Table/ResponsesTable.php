<?php
namespace App\Model\Table;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event as CakeEvent;
use Cake\ORM\Rule\ExistsIn;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * Responses Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Events
 * @property \Cake\ORM\Association\BelongsTo $Registrations
 * @property \Cake\ORM\Association\BelongsTo $Questions
 * @property \Cake\ORM\Association\BelongsTo $Answers
 */
class ResponsesTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->table('responses');
		$this->displayField('id');
		$this->primaryKey('id');

		$this->addBehavior('Trim');

		$this->belongsTo('Events', [
			'foreignKey' => 'event_id',
			'joinType' => 'INNER',
		]);
		$this->belongsTo('Registrations', [
			'foreignKey' => 'registration_id',
		]);
		$this->belongsTo('Questions', [
			'foreignKey' => 'question_id',
			'joinType' => 'INNER',
		]);
		$this->belongsTo('Answers', [
			'foreignKey' => 'answer_id',
		]);
	}

	/**
	 * Returns a rules checker object that will be used for validating
	 * application integrity.
	 *
	 * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
	 * @return \Cake\ORM\RulesChecker
	 */
	public function buildRules(RulesChecker $rules) {
		$rules->add($rules->existsIn(['event_id'], 'Events'));
		$rules->add($rules->existsIn(['registration_id'], 'Registrations'));

		$rules->add(function (EntityInterface $entity, Array $options) {
			// Negative question IDs are for automatic questions, like team name
			if ($entity->question_id < 1) {
				return true;
			}
			$rule = new ExistsIn(['question_id'], 'Questions');
			return $rule($entity, $options);
		}, 'valid', [
			'errorField' => 'question_id',
			'message' => __d('cake', 'This value does not exist'),
		]);

		$rules->add(function (EntityInterface $entity, Array $options) {
			// Negative question IDs are for automatic questions, like team name; we validate them elsewhere
			if ($entity->question_id < 1) {
				return true;
			}
			$question = collection($options['event']->questionnaire->questions)->firstMatch(['id' => $entity->question_id]);
			if (in_array($question->type, ['checkbox', 'text', 'textbox'])) {
				return true;
			}
			$rule = new ExistsIn(['answer_id'], 'Answers');
			return $rule($entity, $options);
		}, 'valid', [
			'errorField' => 'answer_id',
			'message' => __d('cake', 'This value does not exist'),
		]);

		return $rules;
	}

	/**
	 * Modifies the entity before it is saved.
	 *
	 * @param \Cake\Event\Event $cakeEvent The beforeSave event that was fired
	 * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
	 * @param \ArrayObject $options The options passed to the save method
	 * @return void
	 */
	public function beforeSave(CakeEvent $cakeEvent, EntityInterface $entity, ArrayObject $options) {
		if (!empty($options['event']->questionnaire->questions)) {
			$question = collection($options['event']->questionnaire->questions)->firstMatch(['id' => $entity->question_id]);
			if ($question && $question->anonymous) {
				$entity->registration_id = null;
			}
		}
		return true;
	}

}
