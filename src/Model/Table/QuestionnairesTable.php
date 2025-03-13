<?php
namespace App\Model\Table;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event as CakeEvent;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use InvalidArgumentException;

/**
 * Questionnaires Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Affiliates
 * @property \Cake\ORM\Association\HasMany $Events
 * @property \Cake\ORM\Association\BelongsToMany $Questions
 */
class QuestionnairesTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('questionnaires');
		$this->setDisplayField('name');
		$this->setPrimaryKey('id');

		$this->addBehavior('Trim');
		$this->addBehavior('Translate', [
			'strategyClass' => \Cake\ORM\Behavior\Translate\ShadowTableStrategy::class,
			'fields' => ['name'],
		]);

		$this->belongsTo('Affiliates', [
			'foreignKey' => 'affiliate_id',
			'joinType' => 'INNER',
		]);

		$this->hasMany('Events', [
			'foreignKey' => 'questionnaire_id',
			'dependent' => false,
		]);

		$this->belongsToMany('Questions', [
			'foreignKey' => 'questionnaire_id',
			'targetForeignKey' => 'question_id',
			'joinTable' => 'questionnaires_questions',
			'saveStrategy' => 'replace',
			'sort' => 'QuestionnairesQuestions.sort',
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

			->requirePresence('name', 'create')
			->notEmptyString('name', __('The name cannot be blank.'))

			->boolean('active')
			->notEmptyString('active')

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
		$rules->add($rules->existsIn(['affiliate_id'], 'Affiliates', __('You must select a valid affiliate.')));
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
	public function beforeRules(\Cake\Event\EventInterface $cakeEvent, EntityInterface $entity, ArrayObject $options, $operation) {
		if ($entity->isNew()) {
			$entity->active = true;
		}
	}

	public function affiliate($id) {
		try {
			return $this->field('affiliate_id', [$this->aliasField('id') => $id]);
		} catch (RecordNotFoundException|InvalidArgumentException $ex) {
			return null;
		}
	}

}
