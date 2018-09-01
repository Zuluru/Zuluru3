<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use App\Model\Entity\Affiliate;

/**
 * Affiliates Model
 *
 * @property \Cake\ORM\Association\HasMany $Badges
 * @property \Cake\ORM\Association\HasMany $Categories
 * @property \Cake\ORM\Association\HasMany $Contacts
 * @property \Cake\ORM\Association\HasMany $Credits
 * @property \Cake\ORM\Association\HasMany $Events
 * @property \Cake\ORM\Association\HasMany $Franchises
 * @property \Cake\ORM\Association\HasMany $Holidays
 * @property \Cake\ORM\Association\HasMany $Leagues
 * @property \Cake\ORM\Association\HasMany $Locks
 * @property \Cake\ORM\Association\HasMany $MailingLists
 * @property \Cake\ORM\Association\HasMany $Questionnaires
 * @property \Cake\ORM\Association\HasMany $Questions
 * @property \Cake\ORM\Association\HasMany $Regions
 * @property \Cake\ORM\Association\HasMany $Settings
 * @property \Cake\ORM\Association\HasMany $Teams
 * @property \Cake\ORM\Association\HasMany $UploadTypes
 * @property \Cake\ORM\Association\HasMany $Waivers
 * @property \Cake\ORM\Association\BelongsToMany $People
 */
class AffiliatesTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->table('affiliates');
		$this->displayField('name');
		$this->primaryKey('id');

		$this->addBehavior('Trim');

		$this->hasMany('Badges', [
			'foreignKey' => 'affiliate_id',
		]);
		$this->hasMany('Categories', [
			'foreignKey' => 'affiliate_id',
		]);
		$this->hasMany('Contacts', [
			'foreignKey' => 'affiliate_id',
		]);
		$this->hasMany('Credits', [
			'foreignKey' => 'affiliate_id',
		]);
		$this->hasMany('Events', [
			'foreignKey' => 'affiliate_id',
		]);
		$this->hasMany('Franchises', [
			'foreignKey' => 'affiliate_id',
		]);
		$this->hasMany('Holidays', [
			'foreignKey' => 'affiliate_id',
		]);
		$this->hasMany('Leagues', [
			'foreignKey' => 'affiliate_id',
		]);
		$this->hasMany('MailingLists', [
			'foreignKey' => 'affiliate_id',
		]);
		$this->hasMany('Questionnaires', [
			'foreignKey' => 'affiliate_id',
		]);
		$this->hasMany('Questions', [
			'foreignKey' => 'affiliate_id',
		]);
		$this->hasMany('Regions', [
			'foreignKey' => 'affiliate_id',
		]);
		$this->hasMany('Settings', [
			'foreignKey' => 'affiliate_id',
		]);
		$this->hasMany('Teams', [
			'foreignKey' => 'affiliate_id',
		]);
		$this->hasMany('UploadTypes', [
			'foreignKey' => 'affiliate_id',
		]);
		$this->hasMany('Waivers', [
			'foreignKey' => 'affiliate_id',
		]);

		$this->belongsToMany('People', [
			'foreignKey' => 'affiliate_id',
			'targetForeignKey' => 'person_id',
			'joinTable' => 'affiliates_people',
			'saveStrategy' => 'append',
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

			->requirePresence('name', 'create')
			->notEmpty('name', __('The name cannot be blank.'))

			->boolean('active')
			->allowEmpty('active', 'create')

			;

		return $validator;
	}

	public function findActive(Query $query, Array $options) {
		$query->where(['Affiliates.active' => true]);
		return $query;
	}

	/**
	 * @param $id
	 * @return Affiliate[]
	 */
	public function readByPlayerId($id) {
		// Check for invalid users
		if ($id === null) {
			return [];
		}

		$affiliates = $this->find()
			->matching('People', function (Query $q) use ($id) {
				return $q->where(['People.id' => $id]);
			})
			->order('Affiliates.name')
			->toArray();

		return $affiliates;
	}

	public function mergeList(Array $old, Array $new) {
		// Clear ids from the join data in all the new affiliates
		foreach ($new as $affiliate) {
			unset($affiliate->_joinData->id);
			unset($affiliate->_joinData->person_id);
			$affiliate->_joinData->isNew(true);
		}

		// Find any old affiliates that aren't present in the new list, and copy them over
		foreach ($old as $affiliate) {
			$existing = collection($new)->firstMatch(['id' => $affiliate->id]);
			// TODO: If we ever add more than just "player" and "manager" positions, this will need to change.
			if ($affiliate->_joinData->position != 'player') {
				$existing->_joinData->position = $affiliate->_joinData->position;
			} else if (!$existing) {
				// Here, we have to clear the id, but the person_id can stay
				unset($affiliate->_joinData->id);
				$affiliate->_joinData->isNew(true);
				$new[] = $affiliate;
			}
		}

		return $new;
	}

}
