<?php
namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * Settings Model
 *
 * @property \Cake\ORM\Association\BelongsTo $People
 * @property \Cake\ORM\Association\BelongsTo $Affiliates
 */
class SettingsTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('settings');
		$this->setDisplayField('name');
		$this->setPrimaryKey('id');

		$this->belongsTo('People', [
			'foreignKey' => 'person_id',
		]);
		$this->belongsTo('Affiliates', [
			'foreignKey' => 'affiliate_id',
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

			->requirePresence('category', 'create')
			->notEmptyString('category')

			->requirePresence('name', 'create')
			->notEmptyString('name', __('The name cannot be blank.'))

			->requirePresence('value', 'create')
			->allowEmptyString('value')

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

	public function mergeList(array $old, array $new) {
		// Clear ids from all the new settings
		foreach ($new as $setting) {
			unset($setting->id);
			unset($setting->person_id);
			$setting->setNew(true);
		}

		// Find any non-empty old settings that aren't present in the new list and copy them over
		foreach ($old as $setting) {
			if ($setting->value !== '') {
				if (!collection($new)->firstMatch(['category' => $setting->category, 'name' => $setting->name])) {
					// Here, we have to clear the id, but the person_id can stay
					unset($setting->id);
					$setting->setNew(true);
					$new[] = $setting;
				}
			}
		}

		return $new;
	}

}
