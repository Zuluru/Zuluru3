<?php
namespace App\Model\Table;

use Cake\ORM\Query;
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

		$this->setTable('affiliates');
		$this->setDisplayField('name');
		$this->setPrimaryKey('id');

		$this->addBehavior('Trim');
		$this->addBehavior('Translate', ['fields' => ['name']]);

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

		$this->hasMany('AffiliatesPeople', [
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
			->allowEmptyString('id', 'create')

			->requirePresence('name', 'create')
			->notEmptyString('name', __('The name cannot be blank.'))

			->boolean('active')
			->allowEmptyString('active', 'create')

			;

		return $validator;
	}

	public function findActive(Query $query, array $options) {
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

		$affiliates = $this->find('translations')
			->matching('People', function (Query $q) use ($id) {
				return $q->where(['People.id' => $id]);
			})
			->order('Affiliates.name')
			->toArray();

		return $affiliates;
	}

}
