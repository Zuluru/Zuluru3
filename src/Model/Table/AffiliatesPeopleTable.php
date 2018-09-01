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
	public function initialize(array $config) {
		parent::initialize($config);

		$this->table('affiliates_people');
		$this->displayField('id');
		$this->primaryKey('id');

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
	public function validationDefault(Validator $validator) {
		$validator
			->numeric('id')
			->allowEmpty('id', 'create')

			->requirePresence('position', 'create')
			->notEmpty('position')

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
	public function afterSave(CakeEvent $cakeEvent, EntityInterface $entity, ArrayObject $options) {
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
	public function afterDelete(CakeEvent $cakeEvent, EntityInterface $entity, ArrayObject $options) {
		// Delete the cached data, so it's reloaded next time it's needed
		$cache = UserCache::getInstance();
		$cache->clear('Affiliates', $entity->person_id);
		$cache->clear('AffiliateIDs', $entity->person_id);
		$cache->clear('ManagedAffiliates', $entity->person_id);
		$cache->clear('ManagedAffiliateIDs', $entity->person_id);
	}

}
