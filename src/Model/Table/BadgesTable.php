<?php
namespace App\Model\Table;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use App\Model\Rule\InConfigRule;

/**
 * Badges Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Affiliates
 * @property \Cake\ORM\Association\BelongsToMany $People
 */
class BadgesTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->setTable('badges');
		$this->setDisplayField('name');
		$this->setPrimaryKey('id');

		$this->addBehavior('Translate', ['fields' => ['name', 'description']]);

		$this->belongsTo('Affiliates', [
			'foreignKey' => 'affiliate_id',
			'joinType' => 'INNER',
		]);

		$this->belongsToMany('People', [
			'foreignKey' => 'badge_id',
			'targetForeignKey' => 'person_id',
			'joinTable' => 'badges_people',
			'through' => 'BadgesPeople',
			'saveStrategy' => 'append',
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
			->allowEmptyString('id', 'create')

			->requirePresence('affiliate_id', 'create')
			->notEmptyString('affiliate_id', __('You must select a valid affiliate.'))

			->requirePresence('name', 'create')
			->notEmptyString('name', __('The name cannot be blank.'))

			->requirePresence('description', 'create')
			->notEmptyString('description', __('The description cannot be blank.'))

			->requirePresence('category', 'create')
			->notEmptyString('category', __('You must select a valid category.'))

			->allowEmptyString('handler')

			->boolean('active', __('Select whether or not this badge will be active in your system.'))

			->numeric('visibility')
			->requirePresence('visibility', 'create')
			->notEmptyString('visibility')

			->requirePresence('icon', 'create')
			->notEmptyString('icon', __('You must provide the file name of the badge icon, relative to the icons folder.'))

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

		$rules->add(new InConfigRule('options.category'), 'validCategory', [
			'errorField' => 'category',
			'message' => __('You must select a valid category.'),
		]);

		$rules->add(new InConfigRule('options.visibility'), 'validVisibility', [
			'errorField' => 'visibility',
			'message' => false,
		]);

		return $rules;
	}

	public function affiliate($id) {
		try {
			return $this->field('affiliate_id', ['Badges.id' => $id]);
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}

}
