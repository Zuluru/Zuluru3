<?php
namespace App\Model\Table;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use InvalidArgumentException;

/**
 * Pools Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Divisions
 * @property \Cake\ORM\Association\HasMany $Games
 * @property \Cake\ORM\Association\HasMany $PoolsTeams
 * @property \Cake\ORM\Association\BelongsToMany $Teams
 */
class PoolsTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('pools');
		$this->setDisplayField('name');
		$this->setPrimaryKey('id');

		$this->addBehavior('Trim');
		$this->addBehavior('Translate', [
			'strategyClass' => \Cake\ORM\Behavior\Translate\ShadowTableStrategy::class,
			'fields' => ['name'],
		]);

		$this->belongsTo('Divisions', [
			'foreignKey' => 'division_id',
		]);

		$this->hasMany('PoolsTeams', [
			'dependent' => true,
		]);
		$this->hasMany('Games', [
			'foreignKey' => 'pool_id',
			'dependent' => true,
			// Required to free up assigned game slots
			'cascadeCallbacks' => true,
		]);

		$this->belongsToMany('Teams', [
			'foreignKey' => 'pool_id',
			'targetForeignKey' => 'team_id',
			'joinTable' => 'pools_teams',
			'saveStrategy' => 'replace',
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

			->requirePresence('stage', 'create')
			->numeric('stage')

			->requirePresence('type', 'create')
			->notEmptyString('type')

			->requirePresence('name', 'create')
			->notEmptyString('name', __('The name cannot be blank.'))
			->add('name', 'alpha', ['rule' => ['custom', '/^[A-Z]+$/i'], 'message' => __('Pool names can only include letters.')])
			->add('name', 'length', ['rule' => ['maxLength', 2], 'message' => __('Pool names can be no longer than two letters.')])

			->numeric('count')
			->add('count', 'minmax', [
				'rule' => function ($value, $context) {
					if ($context['data']['type'] != 'snake') {
						if ($value < 1) {
							return __('Pools cannot have no teams.');
						} else if ($value > 12) {
							return __('Pools cannot have more than 12 teams.');
						}
					}
					return true;
				},
			])

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
		$rules->add($rules->existsIn(['division_id'], 'Divisions', __('You must select a valid division.')));
		return $rules;
	}

	public function division($id) {
		try {
			return $this->field('division_id', [$this->aliasField('id') => $id]);
		} catch (RecordNotFoundException|InvalidArgumentException $ex) {
			return null;
		}
	}

}
