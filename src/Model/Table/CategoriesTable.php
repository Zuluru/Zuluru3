<?php
namespace App\Model\Table;

use App\Model\Rule\InConfigRule;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use InvalidArgumentException;

/**
 * Categories Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Affiliates
 * @property \Cake\ORM\Association\BelongsToMany $Leagues
 * @property \Cake\ORM\Association\HasMany $Tasks
 */
class CategoriesTable extends AppTable {

	const SLUG_REGEX = '/^[\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Mc}\p{Mn}\p{Nd}\p{Pd}_-]+$/mu';

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('categories');
		$this->setDisplayField('name');
		$this->setPrimaryKey('id');

		$this->addBehavior('Trim');
		$this->addBehavior('Translate', [
			'strategyClass' => \Cake\ORM\Behavior\Translate\ShadowTableStrategy::class,
			'fields' => ['name', 'description'],
		]);

		$this->belongsTo('Affiliates', [
			'foreignKey' => 'affiliate_id',
		]);

		$this->belongsToMany('Leagues', [
			'foreignKey' => 'category_id',
			'joinTable' => 'leagues_categories',
			'targetForeignKey' => 'league_id',
			'saveStrategy' => 'append',
		]);

		$this->hasMany('Tasks', [
			'foreignKey' => 'category_id',
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

			->requirePresence('affiliate_id', 'create')
			->notEmptyString('affiliate_id', __('You must select a valid affiliate.'))

			->requirePresence('type', 'create')
			->notEmptyString('type')

			->requirePresence('name', 'create', __('The name cannot be blank.'))
			->notEmptyString('name', __('The name cannot be blank.'))

			->allowEmptyString('slug', null, function($context) {
				return (!array_key_exists('type', $context['data']) || $context['data']['type'] !== 'Leagues');
			})
			->add('slug', 'valid', [
				'rule' => ['custom', self::SLUG_REGEX],
				'message' => __('Slugs can only include letters, numbers, hyphens and underscores.'),
			])

			->url('image_url', __('Please enter a valid URL.'))
			->allowEmptyString('image_url', null, function($context) {
				return (!array_key_exists('type', $context['data']) || $context['data']['type'] !== 'Leagues');
			})

			->allowEmptyString('description')

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

		$rules->add(new InConfigRule('options.category_types'), 'validType', [
			'errorField' => 'type',
			'message' => __('You must select a valid type.'),
		]);

		$rules->add($rules->isUnique(['slug'], __('There is already a category using this slug.')));

		return $rules;
	}

	public function affiliate($id) {
		try {
			return $this->field('affiliate_id', ['Categories.id' => $id]);
		} catch (RecordNotFoundException|InvalidArgumentException $ex) {
			return null;
		}
	}
}
