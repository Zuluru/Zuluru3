<?php
namespace App\Model\Table;

use App\Core\UserCache;
use ArrayObject;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event as CakeEvent;
use Cake\ORM\RulesChecker;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;
use App\Core\ModuleRegistry;
use App\Model\Rule\InConfigRule;
use InvalidArgumentException;

/**
 * TeamsPeople Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Teams
 * @property \Cake\ORM\Association\BelongsTo $People
 */
class TeamsPeopleTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('teams_people');
		$this->setDisplayField('id');
		$this->setPrimaryKey('id');

		$this->addBehavior('Timestamp');

		$this->belongsTo('Teams', [
			'foreignKey' => 'team_id',
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

			->numeric('status')
			->range('status', [ROSTER_APPROVED, ROSTER_REQUESTED], __('You must select a valid status.'))
			->requirePresence('status', 'create')
			->notEmptyString('status')

			->requirePresence('role', 'create')
			->notEmptyString('role')

			->requirePresence('position', 'create')
			->notEmptyString('position')

			->numeric('number')
			->allowEmptyString('number')

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
		$rules->add($rules->existsIn(['team_id'], 'Teams'));
		$rules->add($rules->existsIn(['person_id'], 'People'));

		$rules->add(new InConfigRule('options.roster_role'), 'validRole', [
			'errorField' => 'role',
			'message' => __('You must select a valid role.'),
		]);

		$rules->add(function (EntityInterface $entity, array $options) {
			$division = $this->Teams->field('division_id', ['Teams.id' => $entity->team_id]);
			if (!$division) {
				return true;
			}
			$sport = $this->Teams->sport($entity->team_id);
			$positions = Configure::read("sports.$sport.positions");
			return empty($positions) || array_key_exists($entity->position, $positions);
		}, 'validPosition', [
			'errorField' => 'position',
			'message' => __('That is not a valid position.'),
		]);

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
		if ($options->offsetExists('team')) {
			$team = $options['team'];
		} else {
			$team = $this->Teams->get($entity->team_id);
		}
		if ($team->division_id) {
			$this->Teams->Divisions->clearCache($team->division_id, ['standings']);
		}
		UserCache::getInstance()->_deleteTeamData($entity->person_id);

		if ($options->offsetExists('person')) {
			$person = $options['person'];
		} else {
			$person = $this->People->get($entity->person_id);
		}

		if (Configure::read('feature.badges')) {
			$badge_obj = ModuleRegistry::getInstance()->load('Badge');
			$badge_obj->update('team', $entity, $person);
		}

		// Acceptance of roster invites requires account reactivation
		if ($entity->isDirty('status') && in_array($entity->status, [ROSTER_APPROVED, ROSTER_REQUESTED]) && $person->status == 'inactive') {
			$person->status = 'active';
			$this->People->save($person);
		}
	}

	/**
	 * Perform additional operations before it is deleted.
	 *
	 * @param \Cake\Event\Event $cakeEvent The beforeDelete event that was fired
	 * @param \Cake\Datasource\EntityInterface $entity The entity to be deleted
	 * @param \ArrayObject $options The options passed to the delete method
	 * @return bool
	 */
	public function beforeDelete(\Cake\Event\EventInterface $cakeEvent, EntityInterface $entity, ArrayObject $options) {
		if ($options->offsetExists('team')) {
			$team = $options['team'];
		} else {
			$team = $this->Teams->get($entity->team_id);
		}
		if ($team->division_id) {
			$this->Teams->Divisions->clearCache($team->division_id, ['standings']);
		}

		if (Configure::read('feature.badges')) {
			if ($options->offsetExists('person')) {
				$person = $options['person'];
			} else {
				$person = $this->People->get($entity->person_id);
			}

			// When we are unlinking, it's equivalent to (if not because of)
			// the role is being set to none, and the badge update function
			// needs to know that, but it won't have been done explicitly in
			// the entity we're passed here.
			$entity->role = 'none';

			$badge_obj = ModuleRegistry::getInstance()->load('Badge');
			$badge_obj->update('team', $entity, $person);
		}

		// Delete the roster reminder email records, so that people get the full two weeks again if they're re-invited
		TableRegistry::getTableLocator()->get('ActivityLogs')->deleteAll([
			'type' => ($entity->status == ROSTER_INVITED ? 'roster_invite_reminder' : 'roster_request_reminder'),
			'team_id' => $entity->team_id,
			'person_id' => $entity->person_id,
		]);

		return true;
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
		UserCache::getInstance()->_deleteTeamData($entity->person_id);
	}

	public function affiliate($id) {
		// Teams may be unassigned
		try {
			return $this->Teams->affiliate($this->team($id));
		} catch (RecordNotFoundException|InvalidArgumentException $ex) {
			return null;
		}
	}

	public function division($id) {
		try {
			return $this->Teams->division($this->team($id));
		} catch (RecordNotFoundException|InvalidArgumentException $ex) {
			return null;
		}
	}

	public function team($id) {
		try {
			return $this->field('team_id', ['TeamsPeople.id' => $id]);
		} catch (RecordNotFoundException|InvalidArgumentException $ex) {
			return null;
		}
	}

}
