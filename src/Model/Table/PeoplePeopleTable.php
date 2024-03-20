<?php
namespace App\Model\Table;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event as CakeEvent;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use App\Core\UserCache;

/**
 * PeoplePeople Model
 *
 * @property \Cake\ORM\Association\BelongsTo $People
 * @property \Cake\ORM\Association\BelongsTo $Relatives
 */
class PeoplePeopleTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('people_people');
		$this->setDisplayField('id');
		$this->setPrimaryKey('id');

		$this->addBehavior('Timestamp');

		$this->belongsTo('People', [
			'foreignKey' => 'person_id',
			'joinType' => 'INNER',
		]);
		$this->belongsTo('Relatives', [
			'className' => 'People',
			'foreignKey' => 'relative_id',
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

			->boolean('approved')
			->requirePresence('approved', 'create')
			->notEmptyString('approved')

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
		$cache->clear('Relatives', $entity->person_id);
		$cache->clear('RelativeIDs', $entity->person_id);
		if ($entity->isDirty('approved')) {
			$cache->clear('RelativeTeamIDs', $entity->person_id);
			$cache->clear('AllRelativeTeamIDs', $entity->person_id);
		}

		$cache->clear('RelatedTo', $entity->relative_id);
		$cache->clear('RelatedToIDs', $entity->relative_id);
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
		$cache->clear('Relatives', $entity->person_id);
		$cache->clear('RelativeIDs', $entity->person_id);
		if ($entity->approved) {
			$cache->clear('RelativeTeamIDs', $entity->person_id);
			$cache->clear('AllRelativeTeamIDs', $entity->person_id);
		}

		$cache->clear('RelatedTo', $entity->relative_id);
		$cache->clear('RelatedToIDs', $entity->relative_id);
	}

}
