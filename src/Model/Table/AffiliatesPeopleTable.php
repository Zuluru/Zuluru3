<?php
namespace App\Model\Table;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event as CakeEvent;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use App\Core\UserCache;

/**
 * AffiliatesPeople Model
 *
 * @property \Cake\ORM\Association\BelongsTo $People
 * @property \Cake\ORM\Association\BelongsTo $Affiliates
 */
class AffiliatesPeopleTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('affiliates_people');
		$this->setDisplayField('id');
		$this->setPrimaryKey('id');

		$this->belongsTo('Affiliates', [
			'foreignKey' => 'affiliate_id',
			'joinType' => 'INNER',
		]);
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

			->requirePresence('position', 'create')
			->notEmptyString('position')

			;

		return $validator;
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
		$cache->clear('Affiliates', $entity->person_id);
		$cache->clear('AffiliateIDs', $entity->person_id);
		$cache->clear('ManagedAffiliates', $entity->person_id);
		$cache->clear('ManagedAffiliateIDs', $entity->person_id);
	}

	/**
	 * Perform additional operations after it is deleted.
	 *
	 * @param \Cake\Event\Event $cakeEvent The afterDelete event that was fired
	 * @param \Cake\Datasource\EntityInterface $entity The entity that was deleted
	 * @param \ArrayObject $options The options passed to the delete method
	 * @return void
	 */
	public function afterDelete(\Cake\Event\EventInterface $cakeEvent, EntityInterface $entity, ArrayObject $options) {
		// Delete the cached data, so it's reloaded next time it's needed
		$cache = UserCache::getInstance();
		$cache->clear('Affiliates', $entity->person_id);
		$cache->clear('AffiliateIDs', $entity->person_id);
		$cache->clear('ManagedAffiliates', $entity->person_id);
		$cache->clear('ManagedAffiliateIDs', $entity->person_id);
	}

	public function mergeList(array $old, array $new) {
		// Clear ids from the join data in all the new affiliates
		foreach ($new as $affiliate_person) {
			unset($affiliate_person->id);
			unset($affiliate_person->person_id);
			$affiliate_person->setNew(true);
		}

		// Find any old affiliates that aren't present in the new list, and copy them over
		foreach ($old as $affiliate_person) {
			$existing = collection($new)->firstMatch(['affiliate_id' => $affiliate_person->affiliate_id]);
			if (!$existing) {
				// Here, we have to clear the id, but the person_id can stay
				unset($affiliate_person->id);
				$affiliate_person->setNew(true);
				$new[] = $affiliate_person;
			} else if ($affiliate_person->position !== 'player') {
				// TODO: If we ever add more than just "player" and "manager" positions, this will need to change.
				$existing->position = $affiliate_person->position;
			}
		}

		return $new;
	}

}
