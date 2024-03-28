<?php
namespace App\Model\Table;

use App\Core\UserCache;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event as CakeEvent;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * Franchises Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Affiliates
 * @property \Cake\ORM\Association\BelongsToMany $People
 * @property \Cake\ORM\Association\BelongsToMany $Teams
 */
class FranchisesTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('franchises');
		$this->setDisplayField('name');
		$this->setPrimaryKey('id');

		$this->addBehavior('Trim');

		$this->belongsTo('Affiliates', [
			'foreignKey' => 'affiliate_id',
			'joinType' => 'INNER',
		]);

		$this->belongsToMany('People', [
			'foreignKey' => 'franchise_id',
			'targetForeignKey' => 'person_id',
			'joinTable' => 'franchises_people',
			'saveStrategy' => 'replace',
			// Required for the FranchisesPeopleTable::afterDelete function to be called
			'cascadeCallbacks' => true,
		]);
		$this->belongsToMany('Teams', [
			'foreignKey' => 'franchise_id',
			'targetForeignKey' => 'team_id',
			'joinTable' => 'franchises_teams',
			'through' => 'FranchisesTeams',
			'saveStrategy' => 'replace',
			'sort' => 'Teams.id DESC',
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

			->notEmptyString('name', __('The name cannot be blank.'))

			->url('url', __('Enter a valid URL, or leave blank.'))
			->allowEmptyString('website')

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
		$rules->add($rules->isUnique(['name'], __('There is already a franchise by that name.')));
		$rules->add($rules->existsIn(['affiliate_id'], 'Affiliates', __('You must select a valid affiliate.')));
		return $rules;
	}

	/**
	 * Perform additional operations after it is saved.
	 *
	 * @param \Cake\Event\Event $cakeEvent The afterSave event that was fired
	 * @param \Cake\Datasource\EntityInterface $entity The entity that was saved
	 * @param \ArrayObject $options The options passed to the save method
	 * @return void
	 */
	public function afterSave(\Cake\Event\EventInterface $cakeEvent, EntityInterface $entity, ArrayObject $options) {
		// Delete the cached data, so it's reloaded next time it's needed
		$cache = UserCache::getInstance();
		foreach ($entity->people as $person) {
			$cache->_deleteFranchiseData($person->id);
		}
	}

	// TODOLATER: Change readBy functions to return a query object? Use custom finders?
	public function readByPlayerId($id, $conditions = []) {
		// Check for invalid users
		if ($id === null) {
			return [];
		}

		$person = $this->People->get($id, [
			'contain' => [
				'Franchises' => function (Query $q) use ($conditions) {
					if (!empty($conditions)) {
						return $q->where($conditions);
					}
					return $q;
				},
			]
		]);

		return $person->franchises;
	}

	public function affiliate($id) {
		try {
			return $this->field('affiliate_id', ['Franchises.id' => $id]);
		} catch (RecordNotFoundException|InvalidArgumentException $ex) {
			return null;
		}
	}
}
