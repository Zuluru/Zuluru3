<?php
namespace App\Model\Table;

use App\Model\Rule\InConfigRule;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * Categories Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Affiliates
 * @property \Cake\ORM\Association\BelongsToMany $Leagues
 * @property \Cake\ORM\Association\HasMany $Tasks
 */
class CategoriesTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->setTable('categories');
		$this->setDisplayField('name');
		$this->setPrimaryKey('id');

		$this->addBehavior('Trim');
		$this->addBehavior('Translate', ['fields' => ['name']]);

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
	public function validationDefault(Validator $validator) {
		$validator
			->numeric('id')
			->allowEmpty('id', 'create')

			->requirePresence('affiliate_id', 'create')
			->notEmpty('affiliate_id')

			->requirePresence('type', 'create')
			->notEmpty('type')

			->requirePresence('name', 'create', __('The name cannot be blank.'))
			->notEmpty('name', __('The name cannot be blank.'))

			->allowEmpty('slug')

			->url('image_url', __('Please enter a valid URL.'))
			->allowEmpty('image_url')

			->url('description_url', __('Please enter a valid URL.'))
			->allowEmpty('description_url')

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

		$rules->add(new InConfigRule('options.category_types'), 'validType', [
			'errorField' => 'type',
			'message' => __('You must select a valid type.'),
		]);

		return $rules;
	}

	public function affiliate($id) {
		try {
			return $this->field('affiliate_id', ['Categories.id' => $id]);
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}
}
