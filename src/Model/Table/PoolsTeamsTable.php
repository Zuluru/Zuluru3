<?php
namespace App\Model\Table;

use Cake\Datasource\EntityInterface;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * PoolsTeams Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Pools
 * @property \Cake\ORM\Association\BelongsTo $DependencyPool
 * @property \Cake\ORM\Association\BelongsTo $Teams
 */
class PoolsTeamsTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->table('pools_teams');
		$this->displayField('alias');
		$this->primaryKey('id');

		$this->belongsTo('Pools', [
			'foreignKey' => 'pool_id',
		]);
		$this->belongsTo('DependencyPool', [
			'className' => 'Pools',
			'foreignKey' => 'dependency_pool_id',
		]);
		$this->belongsTo('Teams', [
			'foreignKey' => 'team_id',
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

			->requirePresence('alias', 'create')
			->notEmpty('alias')

			->requirePresence('dependency_type', 'create')
			->notEmpty('dependency_type')

			->numeric('dependency_ordinal')
			->allowEmpty('dependency_ordinal')

			;

		return $validator;
	}

	/**
	 * Custom validation rules for generic-type events.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 * @param Array $valid_options List of valid options for the qualifier
	 * @return \Cake\Validation\Validator
	 */
	public function validationQualifiers(Validator $validator, $valid_options) {
		$validator = $this->validationDefault($validator);

		$validator
			->requirePresence('qualifier', 'create')
			->notEmpty('qualifier')
			->add('qualifier', 'valid', ['rule' => ['inList', $valid_options], 'message' => __('Invalid qualifier.')]);

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
		$rules->add($rules->existsIn(['dependency_pool_id'], 'Pools'));

		//
		// The following rules perform a number of checks on the teams being saved as a collection.
		//

		$rules->add(function (EntityInterface $entity, Array $options) {
			if ($entity->has('qualifier')) {
				// Make sure that we haven't got both pool and ordinal types selected
				// for any particular "tier" in the pools. For example, A-1 can be used
				// with B-1, but not with 1-1. Crossovers can be used with either.
				list ($qpool,) = explode('-', $entity->qualifier);
				$qnumeric = is_numeric($qpool);
				$pool = collection($options['division']->pools)->firstMatch(['name' => $qpool]);
				if (!$pool || $pool->type != 'crossover') {
					if (collection($options['pools'])->some(function ($pool) use ($entity, $qnumeric) {
						return collection($pool->pools_teams)->some(function ($team) use ($entity, $qnumeric) {
							list ($tpool,) = explode('-', $team->qualifier);
							$tnumeric = is_numeric($tpool);
							if ($tnumeric != $qnumeric && $team->alias != $entity->alias) {
								$entity->conflict = $team->qualifier;
								return true;
							}

							return false;
						});
					})
					) {
						return __('You have selected {0} and {1}, but you cannot mix "pool"-type options with "ordinal"-type options; both could end up being the same team.', $entity->qualifier, $entity->conflict);
					}
				}

				if (collection($options['pools'])->some(function ($pool) use ($entity) {
					return collection($pool->pools_teams)->some(function ($team) use ($entity) {
						return $team->qualifier == $entity->qualifier && $team->alias != $entity->alias;
					});
				})
				) {
					return __('This qualifier is selected twice.');
				}
			}

			return true;
		}, 'valid_qualifiers', [
			'errorField' => 'qualifier',
		]);

		return $rules;
	}

}
