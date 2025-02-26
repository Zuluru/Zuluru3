<?php
namespace App\Model\Table;

use ArrayObject;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event as CakeEvent;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use App\Model\Rule\InConfigRule;
use App\Model\Rule\InDateConfigRule;
use App\Model\Rule\OrRule;

/**
 * Skills Model
 *
 * @property \Cake\ORM\Association\BelongsTo $People
 */
class SkillsTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('skills');
		$this->setDisplayField('id');
		$this->setPrimaryKey('id');

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
	public function validationDefault(Validator $validator): \Cake\Validation\Validator {
		$validator
			->numeric('id')
			->allowEmptyString('id', null, 'create')

			->requirePresence('sport', 'create')
			->notEmptyString('sport')

			->numeric('skill_level')
			->allowEmptyString('skill_level')

			->numeric('year_started')
			->allowEmptyString('year_started')

			->boolean('enabled')
			->requirePresence('enabled', 'create')
			->notEmptyString('enabled')

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
		$rules->add($rules->existsIn(['person_id'], 'People'));

		// We are happy if the field is disabled or the record is not enabled or the skill level is valid
		$rules->add(new OrRule([
			function (EntityInterface $entity, array $options) { return !Configure::read('profile.skill_level') || !$entity->enabled; },
			new InConfigRule('options.skill')
		]), 'validSkill', [
			'errorField' => 'skill_level',
			'message' => __('You must select a skill level between 1 and 10.'),
		]);

		// We are happy if the field is disabled or the record is not enabled or the year started is valid
		$rules->add(new OrRule([
			function (EntityInterface $entity, array $options) { return !Configure::read('profile.year_started') || !$entity->enabled; },
			new InDateConfigRule('started')
		]), 'validYearStarted', [
			'errorField' => 'year_started',
			// TODO: Use something based on current year instead of 1986
			'message' => __('Year started must be after {0}. If you started before then, just use {0}!', 1986),
		]);

		return $rules;
	}

	/**
	 * The 'year' input type creates an array with a year index. The field is just a string.
	 *
	 * @param CakeEvent $cakeEvent Unused
	 * @param ArrayObject $data The data record being patched in
	 * @param ArrayObject $options Unused
	 */
	public function beforeMarshal(CakeEvent $cakeEvent, ArrayObject $data, ArrayObject $options) {
		if ($data->offsetExists('year_started') && is_array($data['year_started'])) {
			$data['year_started'] = $data['year_started']['year'];
		}
	}

	public function mergeList(array $old, array $new) {
		// Clear ids from all the new skills
		foreach ($new as $skill) {
			unset($skill->id);
			unset($skill->person_id);
			$skill->setNew(true);
		}

		// Find any non-empty old skills that aren't present in the new list and copy them over
		foreach ($old as $skill) {
			if (!empty($skill->skill_level)) {
				$existing = collection($new)->firstMatch(['sport' => $skill->sport]);
				if ($existing && empty($existing->skill_level)) {
					$existing->skill_level = $skill->skill_level;
					$existing->year_started = $skill->year_started;
				} else if (!$existing) {
					// Here, we have to clear the id, but the person_id can stay
					unset($skill->id);
					$skill->enabled = false;
					$skill->setNew(true);
					$new[] = $skill;
				}
			}
		}

		return $new;
	}

}
