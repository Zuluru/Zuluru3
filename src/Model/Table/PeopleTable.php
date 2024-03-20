<?php
namespace App\Model\Table;

use App\Model\Entity\Group;
use App\Model\Entity\Person;
use ArrayObject;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event as CakeEvent;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Utility\Inflector;
use Cake\Validation\Validation;
use Cake\Validation\Validator;
use App\Core\UserCache;
use App\Model\Rule\InConfigRule;
use App\Model\Rule\InDateConfigRule;

/**
 * People Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\HasMany $GamesAllstars
 * @property \Cake\ORM\Association\HasMany $Attendances
 * @property \Cake\ORM\Association\HasMany $Credits
 * @property \Cake\ORM\Association\HasMany $Notes
 * @property \Cake\ORM\Association\HasMany $Preregistrations
 * @property \Cake\ORM\Association\HasMany $Registrations
 * @property \Cake\ORM\Association\HasMany $Settings
 * @property \Cake\ORM\Association\HasMany $Skills
 * @property \Cake\ORM\Association\HasMany $Stats
 * @property \Cake\ORM\Association\HasMany $Subscriptions
 * @property \Cake\ORM\Association\HasMany $TaskSlots
 * @property \Cake\ORM\Association\HasMany $Tasks
 * @property \Cake\ORM\Association\HasMany $Uploads
 * @property \Cake\ORM\Association\BelongsToMany $Affiliates
 * @property \Cake\ORM\Association\BelongsToMany $Badges
 * @property \Cake\ORM\Association\BelongsToMany $Divisions
 * @property \Cake\ORM\Association\BelongsToMany $Franchises
 * @property \Cake\ORM\Association\BelongsToMany $Groups
 * @property \Cake\ORM\Association\BelongsToMany $Teams
 * @property \Cake\ORM\Association\BelongsToMany $Waivers
 */
class PeopleTable extends AppTable {
	const NAME_REGEX = '/^[ \p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Mc}\p{Mn}\p{Nd}\p{Pd}\.\',]+$/mu';

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('people');
		$this->setDisplayField('id');
		$this->setPrimaryKey('id');

		$this->addBehavior('Trim');
		$this->addBehavior('Timestamp');
		$this->addBehavior('Formatter', [
			'fields' => [
				'first_name' => 'proper_case_format',
				'legal_name' => 'proper_case_format',
				'last_name' => 'proper_case_format',
				'addr_street' => 'proper_case_format',
				'addr_city' => 'proper_case_format',
				'addr_postalcode' => 'postal_format',
				'home_phone' => 'phone_format',
				'work_phone' => 'phone_format',
				'mobile_phone' => 'phone_format',
				'alternate_work_phone' => 'phone_format',
				'alternate_mobile_phone' => 'phone_format',
			],
		]);

		// Which user model to use depends on system configuration
		$user_model = Configure::read('Security.authModel');
		$this->belongsTo(Configure::read('Security.authPlugin') . $user_model, [
			'foreignKey' => 'user_id',
			'strategy' => 'select',
		]);

		$this->hasMany('ActivityLogs', [
			'foreignKey' => 'person_id',
			'dependent' => true,
		]);
		$this->hasMany('GamesAllstars', [
			'foreignKey' => 'person_id',
			'dependent' => false,
		]);
		$this->hasMany('Attendances', [
			'foreignKey' => 'person_id',
			'dependent' => true,
		]);
		$this->hasMany('Credits', [
			'foreignKey' => 'person_id',
			'dependent' => false,
		]);
		$this->hasMany('Notes', [
			'foreignKey' => 'person_id',
			'dependent' => true,
		]);
		$this->hasMany('Preregistrations', [
			'foreignKey' => 'person_id',
			'dependent' => true,
		]);
		$this->hasMany('Registrations', [
			'foreignKey' => 'person_id',
			'dependent' => false,
		]);
		$this->hasMany('Settings', [
			'foreignKey' => 'person_id',
			'dependent' => true,
			'saveStrategy' => 'replace',
		]);
		$this->hasMany('Skills', [
			'foreignKey' => 'person_id',
			'dependent' => true,
			'saveStrategy' => 'replace',
		]);
		$this->hasMany('Stats', [
			'foreignKey' => 'person_id',
			'dependent' => true,
		]);
		$this->hasMany('Subscriptions', [
			'foreignKey' => 'person_id',
			'dependent' => true,
		]);
		$this->hasMany('TaskSlots', [
			'foreignKey' => 'person_id',
			'dependent' => true,
		]);
		$this->hasMany('Tasks', [
			'foreignKey' => 'person_id',
			'dependent' => true,
		]);
		$this->hasMany('Uploads', [
			'foreignKey' => 'person_id',
			'dependent' => false,
		]);

		// TODO: The "TeamsPeople" association is used in the Team::_getRoster function.
		// Typically, this is set automatically by Cake, maybe when a "Teams" contain is done?
		// But sometimes, it's not... If that gets "fixed", we can remove this.
		$this->hasMany('TeamsPeople', [
			'foreignKey' => 'person_id',
			'dependent' => true,
		]);
		$this->hasMany('AffiliatesPeople', [
			'foreignKey' => 'person_id',
			'dependent' => true,
			'saveStrategy' => 'replace',
		]);
		$this->hasMany('WaiversPeople', [
			'foreignKey' => 'person_id',
			'dependent' => true,
		]);

		// Some associations here mainly to automate the profile merge process
		$this->hasMany('CreatedCredits', [
			'className' => 'Credits',
			'foreignKey' => 'created_person_id',
			'dependent' => true,
		]);
		$this->hasMany('CreatedNotes', [
			'className' => 'Notes',
			'foreignKey' => 'created_person_id',
			'dependent' => true,
		]);
		$this->hasMany('CreatedPayments', [
			'className' => 'Payments',
			'foreignKey' => 'created_person_id',
			'dependent' => true,
		]);
		$this->hasMany('UpdatedPayments', [
			'className' => 'Payments',
			'foreignKey' => 'updated_person_id',
			'dependent' => true,
		]);
		$this->hasMany('ScoreDetailStats', [
			'foreignKey' => 'person_id',
			'dependent' => true,
		]);
		$this->hasMany('ScoreEntries', [
			'foreignKey' => 'person_id',
			'dependent' => true,
		]);
		$this->hasMany('ScoreEntries', [
			'foreignKey' => 'person_id',
			'dependent' => true,
		]);

		$this->belongsToMany('Affiliates', [
			'joinTable' => 'affiliates_people',
			'through' => 'AffiliatesPeople',
			'foreignKey' => 'person_id',
			'targetForeignKey' => 'affiliate_id',
			'saveStrategy' => 'replace',
		]);
		$this->belongsToMany('Badges', [
			'joinTable' => 'badges_people',
			'through' => 'BadgesPeople',
			'foreignKey' => 'person_id',
			'targetForeignKey' => 'badge_id',
			'saveStrategy' => 'append',
			'sort' => 'Badges.id',
		]);
		$this->belongsToMany('Divisions', [
			'joinTable' => 'divisions_people',
			'foreignKey' => 'person_id',
			'targetForeignKey' => 'division_id',
			'saveStrategy' => 'replace',
		]);
		$this->belongsToMany('Franchises', [
			'joinTable' => 'franchises_people',
			'through' => 'FranchisesPeople',
			'foreignKey' => 'person_id',
			'targetForeignKey' => 'franchise_id',
			'saveStrategy' => 'replace',
		]);
		$this->belongsToMany('Groups', [
			'joinTable' => 'groups_people',
			'foreignKey' => 'person_id',
			'targetForeignKey' => 'group_id',
			'saveStrategy' => 'replace',
		]);
		// Profiles that this person controls
		$this->belongsToMany('Relatives', [
			'className' => 'People',
			'joinTable' => 'people_people',
			'through' => 'PeoplePeople',
			'foreignKey' => 'person_id',
			'targetForeignKey' => 'relative_id',
			'saveStrategy' => 'append',
		]);
		// Profiles that control this person
		$this->belongsToMany('Related', [
			'className' => 'People',
			'joinTable' => 'people_people',
			'through' => 'PeoplePeople',
			'foreignKey' => 'relative_id',
			'targetForeignKey' => 'person_id',
			'saveStrategy' => 'append',
		]);
		$this->belongsToMany('Teams', [
			'joinTable' => 'teams_people',
			'through' => 'TeamsPeople',
			'foreignKey' => 'person_id',
			'targetForeignKey' => 'team_id',
			'saveStrategy' => 'append',
		]);
		$this->belongsToMany('Waivers', [
			'joinTable' => 'waivers_people',
			'foreignKey' => 'person_id',
			'targetForeignKey' => 'waiver_id',
			'saveStrategy' => 'append',
		]);
	}

	/**
	 * Default validation rules.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 * @return \Cake\Validation\Validator
	 */
	public function validationDefault(Validator $validator): \Cake\Validation\Validator {
		$validator->setProvider('intl', \App\Validation\Intl::class);

		$validator
			->numeric('id')
			->allowEmptyString('id', null, 'create')

			->add('first_name', 'valid', [
				'rule' => ['custom', self::NAME_REGEX],
				'message' => __('Names can only include letters, numbers, spaces, commas, periods, apostrophes and hyphens.'),
			])
			->requirePresence('first_name', 'create', Configure::read('profile.legal_name') ? __('Preferred name must not be blank.') : __('First name must not be blank.'))
			->notEmptyString('first_name', Configure::read('profile.legal_name') ? __('Preferred name must not be blank.') : __('First name must not be blank.'))

			->add('last_name', 'valid', [
				'rule' => ['custom', self::NAME_REGEX],
				'message' => __('Names can only include letters, numbers, spaces, commas, periods, apostrophes and hyphens.'),
			])

			->add('legal_name', 'valid', [
				'rule' => ['custom', self::NAME_REGEX],
				'message' => __('Names can only include letters, numbers, spaces, commas, periods, apostrophes and hyphens.'),
			])
			->allowEmptyString('legal_name')

			->requirePresence('last_name', 'create', __('Last name must not be blank.'))
			->notEmptyString('last_name', __('Last name must not be blank.'))

			->add('alternate_first_name', 'valid', [
				'rule' => ['custom', self::NAME_REGEX],
				'message' => __('Names can only include letters, numbers, spaces, commas, periods, apostrophes and hyphens.'),
			])
			->allowEmptyString('alternate_first_name')

			->add('alternate_last_name', 'valid', [
				'rule' => ['custom', self::NAME_REGEX],
				'message' => __('Names can only include letters, numbers, spaces, commas, periods, apostrophes and hyphens.'),
			])
			->allowEmptyString('alternate_last_name')

			->requirePresence('status', 'create', __('You must select a valid status.'))
			->notEmptyString('status', __('You must select a valid status.'))

			->boolean('show_gravatar')
			->allowEmptyString('show_gravatar')

			->boolean('publish_email')
			->allowEmptyString('publish_email')

			->email('alternate_email', false, __('You must supply a valid email address.'))
			->allowEmptyString('alternate_email')

			->boolean('publish_alternate_email')
			->allowEmptyString('publish_alternate_email')

			->boolean('has_dog')
			->allowEmptyString('has_dog')

			->boolean('contact_for_feedback')
			->allowEmptyString('contact_for_feedback')

			->add('home_phone', 'valid', ['provider' => 'intl', 'rule' => 'phone', 'message' => __('Please supply area code and number.')])
			->allowEmptyString('home_phone')

			->boolean('publish_home_phone')
			->allowEmptyString('publish_home_phone')

			->add('work_phone', 'valid', ['provider' => 'intl', 'rule' => 'phone', 'message' => __('Please supply area code and number.')])
			->allowEmptyString('work_phone')

			->naturalNumber('work_ext', __('Please supply extension, if any.'))
			->allowEmptyString('work_ext')

			->boolean('publish_work_phone')
			->allowEmptyString('publish_work_phone')

			->add('alternate_work_phone', 'valid', ['provider' => 'intl', 'rule' => 'phone', 'message' => __('Please supply area code and number.')])
			->allowEmptyString('alternate_work_phone')

			->naturalNumber('alternate_work_ext', __('Please supply extension, if any.'))
			->allowEmptyString('alternate_work_ext')

			->boolean('publish_alternate_work_phone')
			->allowEmptyString('publish_alternate_work_phone')

			->add('mobile_phone', 'valid', ['provider' => 'intl', 'rule' => 'phone', 'message' => __('Please supply area code and number.')])
			->allowEmptyString('mobile_phone')

			->boolean('publish_mobile_phone')
			->allowEmptyString('publish_mobile_phone')

			->add('alternate_mobile_phone', 'valid', ['provider' => 'intl', 'rule' => 'phone', 'message' => __('Please supply area code and number.')])
			->allowEmptyString('alternate_mobile_phone')

			->boolean('publish_alternate_mobile_phone')
			->allowEmptyString('publish_alternate_mobile_phone')

			// TODO: validate this by province
			->add('addr_postalcode', 'valid', ['provider' => 'intl', 'rule' => 'postal', 'message' => __('You must enter a valid postal/zip code')])
			->allowEmptyString('addr_postalcode')

			;

		return $validator;
	}

	/**
	 * Account creation validation rules.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 * @return \Cake\Validation\Validator
	 */
	public function validationCreate(Validator $validator) {
		$validator = $this->validationDefault($validator);

		if (Configure::read('feature.antispam')) {
			$validator
				->allowEmptyArray('relatives')
				->add('relatives', 'antispam', [
					'rule' => function ($value, $context) {
						if (!is_array($value) || !array_key_exists(0, $value)) {
							return false;
						}

						// Anyone that hasn't selected the parent group, but still sends
						// child information, is also a spambot.
						if (!is_array($context['data']['groups']['_ids']) || !in_array(GROUP_PARENT, $context['data']['groups']['_ids'])) {
							return false;
						}

						// Anyone who says their child is older than they are is a spambot.
						if ($value[0]['birthdate']['year'] &&
							array_key_exists('birthdate', $context['data']) && $context['data']['birthdate']['year'] &&
							$value[0]['birthdate']['year'] < $context['data']['birthdate']['year'] + 12
						) {
							return false;
						}

						return true;
					},
					'message' => false,
				]);
		}

		return $validator;
	}

	public function validationPlayer(Validator $validator) {
		$validator
			->requirePresence('gender', 'create', __('You must select a gender identification.'))
			->notEmptyString('gender', __('You must select a gender identification.'));

		$validator
			->boolean('publish_gender')
			->allowEmptyString('publish_gender');

		$validator
			->requirePresence('roster_designation', function ($context) {
				return Configure::read('gender.column') == 'roster_designation';
			}, __('You must select a roster designation.'))
			->notEmptyString('roster_designation', __('You must select a roster designation.'), function ($context) {
				return Configure::read('gender.column') == 'roster_designation';
			});

		$validator
			->allowEmptyString('pronouns');

		$validator
			->boolean('publish_pronouns')
			->allowEmptyString('publish_pronouns');

		if (Configure::read('profile.height')) {
			$validator
				->requirePresence('height', 'create', __('You must enter a valid height.'))
				->notEmptyString('height', __('You must enter a valid height.'));
		}

		if (Configure::read('profile.shirt_size')) {
			$validator
				->requirePresence('shirt_size', 'create', __('You must select a valid shirt size.'))
				->notEmptyString('shirt_size', __('You must select a valid shirt size.'));
		}

		if (Configure::read('profile.birthdate')) {
			$date_format = (Configure::read('feature.birth_year_only') ? 'y' : 'ymd');
			$validator
				->requirePresence('birthdate', 'create', __('You must provide a valid birthdate.'))
				->notEmptyString('birthdate', __('You must provide a valid birthdate.'))
				->add('birthdate', 'valid', ['rule' => ['date', $date_format], 'message' => __('You must provide a valid birthdate.')]);
		}

		return $validator;
	}

	public function validationContact(Validator $validator) {
		$validator->boolean('publish_email');

		if (Configure::read('profile.home_phone')) {
			$validator
				->requirePresence('home_phone', 'create', __('You must provide at least one phone number.'))
				->notEmptyString('home_phone', __('You must provide at least one phone number.'), function ($context) {
					return empty($context['data']['work_phone']) && empty($context['data']['mobile_phone']);
				});
		}

		if (Configure::read('profile.work_phone')) {
			$validator
				->requirePresence('work_phone', 'create', __('You must provide at least one phone number.'))
				->notEmptyString('work_phone', __('You must provide at least one phone number.'), function ($context) {
					return empty($context['data']['home_phone']) && empty($context['data']['mobile_phone']);
				})
				->boolean('publish_work_phone')
				->boolean('publish_alternate_work_phone')
				->allowEmptyString('publish_alternate_work_phone');
		}

		if (Configure::read('profile.mobile_phone')) {
			$validator
				->requirePresence('mobile_phone', 'create', __('You must provide at least one phone number.'))
				->notEmptyString('mobile_phone', __('You must provide at least one phone number.'), function ($context) {
					return empty($context['data']['home_phone']) && empty($context['data']['work_phone']);
				})
				->boolean('publish_mobile_phone')
				->boolean('publish_alternate_mobile_phone')
				->allowEmptyString('publish_alternate_mobile_phone');
		}

		if (Configure::read('profile.addr_street')) {
			$validator
				->requirePresence('addr_street', 'create', __('You must supply a valid street address.'))
				->notEmptyString('addr_street', __('You must supply a valid street address.'));
		}

		if (Configure::read('profile.addr_city')) {
			$validator
				->requirePresence('addr_city', 'create', __('You must supply a city.'))
				->notEmptyString('addr_city', __('You must supply a city.'));
		}

		if (Configure::read('profile.addr_prov')) {
			$validator
				->requirePresence('addr_prov', 'create', __('Select a province/state from the list.'))
				->notEmptyString('addr_prov', __('Select a province/state from the list.'));
		}

		if (Configure::read('profile.addr_country')) {
			$validator
				->requirePresence('addr_country', 'create', __('You must select a country.'))
				->notEmptyString('addr_country', __('You must select a country.'));
		}

		if (Configure::read('profile.addr_postalcode')) {
			$validator
				->requirePresence('addr_postalcode', 'create', __('You must enter a valid postal/zip code'))
				->notEmptyString('addr_postalcode', __('You must enter a valid postal/zip code'));
		}

		return $validator;
	}

	public function validationCoach(Validator $validator) {
		if (Configure::read('profile.shirt_size')) {
			$validator
				->requirePresence('shirt_size', 'create', __('You must select a valid shirt size.'))
				->notEmptyString('shirt_size', __('You must select a valid shirt size.'));
		}

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
		$rules->add(new InConfigRule(['key' => 'provinces', 'optional' => true]), 'validProvince', [
			'errorField' => 'addr_prov',
			'message' => __('Select a province/state from the list.'),
		]);

		$rules->add(new InConfigRule(['key' => 'countries', 'optional' => true]), 'validCountry', [
			'errorField' => 'addr_country',
			'message' => __('You must select a country.'),
		]);

		$rules->add(new InConfigRule(['key' => 'options.gender', 'optional' => true]), 'validGender', [
			'errorField' => 'gender',
			'message' => __('You must select a gender identification.'),
		]);

		$rules->add(new InConfigRule(['key' => 'options.roster_designation', 'optional' => true]), 'validRosterDesignation', [
			'errorField' => 'roster_designation',
			'message' => __('You must select a roster designation.'),
		]);

		$rules->add(new InDateConfigRule('born'), 'validBirthdate', [
			'errorField' => 'birthdate',
			'message' => __('You must provide a valid birthdate.'),
		]);

		// The more obvious method, where we check Configure::read('feature.units') and then add a range
		// validation based on that doesn't work here. When we load the configuration from the database,
		// the Configuration table object is created, the Footprint event is triggered, which reads the
		// current user, and the UsersTable is associated with the PeopleTable, meaning that this function
		// right here is called before the settings are actually loaded into the global config. :-(
		$rules->add(function (EntityInterface $entity, array $options) {
			if (!Configure::read('profile.height') || empty($entity->groups) || !collection($entity->groups)->some(function (Group $group) {
				return $group->id == GROUP_PLAYER;
			})) {
				return true;
			}

			if (Configure::read('feature.units') == 'Metric') {
				return Validation::range($entity->height, 75, 215);
			} else {
				return Validation::range($entity->height, 30, 84);
			}
		}, 'validHeight', [
			'errorField' => 'height',
			'message' => __('You must enter a valid height.'),
		]);

		$rules->add(new InConfigRule(['key' => 'options.shirt_size', 'optional' => true]), 'validShirtSize', [
			'errorField' => 'shirt_size',
			'message' => __('You must select a valid shirt size.'),
		]);

		$rules->add(new InConfigRule('options.record_status'), 'validStatus', [
			'errorField' => 'status',
			'message' => __('You must select a valid status.'),
		]);

		$rules->add(function (EntityInterface $entity, array $options) {
			if (empty($entity->groups)) {
				return true;
			}

			// Start with the list of valid group options for the user making the edit
			$valid_groups = $this->Groups->find('options', ['Groups.require_player' => true])
				// then limit by the groups that were requested.
				->where([
					'Groups.id IN' => collection($entity->groups)->extract('id')->toList(),
					'Groups.active' => true,
				]);
			// The resulting set should have the same number of rows as there were groups selected
			return $valid_groups->count() == count(collection($entity->groups)->match(['active' => true])->toArray());
		}, 'validGroup', [
			'errorField' => 'groups',
			'message' => __('You have selected an invalid group.'),
		]);

		if (Configure::read('feature.affiliates')) {
			if (Configure::read('feature.multiple_affiliates')) {
				$rules->add(function (EntityInterface $entity, array $options) {
					return empty($options['manage_affiliates']) || !empty($entity->affiliates);
				}, 'validAffiliates', [
					'errorField' => 'affiliates',
					'message' => __('You must select at least one affiliate that you are interested in.'),
				]);
			} else {
				$rules->add(function (EntityInterface $entity, array $options) {
					return empty($options['manage_affiliates']) || count($entity->affiliates) == 1;
				}, 'validAffiliates', [
					'errorField' => 'affiliates',
					'message' => __('You must select an affiliate that you are interested in.'),
				]);
			}
		}

		// Seems the simplest way to handle these optional fields is via custom validators accessed as a rule...
		$rules->add(function(EntityInterface $entity, array $options) {
			if (empty($entity->groups)) {
				return true;
			}

			$data = $entity->extract($this->getSchema()->columns());

			foreach (collection($entity->groups)->extract('id') as $group) {
				switch ($group) {
					case GROUP_PLAYER:
						$validator = $this->getValidator('player');
						$errors = $validator->validate($data, $entity->isNew());
						$entity->setErrors($errors);
						break;

					case GROUP_COACH:
						$validator = $this->getValidator('coach');
						$errors = $validator->validate($data, $entity->isNew());
						$entity->setErrors($errors);
						break;
				}
			}

			// We don't want to validate contact info for children. We detect these as:
			// - new entities that are flagged as children
			// - existing entities that don't have a user_id
			// TODO: We should probably handle is_child as a real field, not
			// this temporary field during creation plus pseudo-accessor
			$is_child = $entity->is_child || (!$entity->isNew() && !$entity->user_id);
			if (!$is_child) {
				$validator = $this->getValidator('contact');
				$errors = $validator->validate($data, $entity->isNew());
				$entity->setErrors($errors);
			}

			return empty($entity->getErrors());
		});

		// Don't delete the only admin
		$rules->addDelete(function ($entity, $options) {
			if (in_array(GROUP_ADMIN, UserCache::getInstance()->read('GroupIDs', $entity->id))) {
				$admins = $this->People->GroupsPeople->find()->where(['group_id' => GROUP_ADMIN])->count();
				if ($admins == 1) {
					return false;
				}
			}

			return true;
		}, 'delete_admin', [
			'message' => __('You cannot delete the only administrator.'),
		]);

		// Don't delete someone who is the only parent of someone that cannot be deleted.
		$rules->addDelete(function ($entity, $options) {
			$this->loadInto($entity, ['Relatives']);
			$cache = UserCache::getInstance();
			foreach ($entity->relatives as $relative) {
				if (empty($relative->user_id) && count($cache->read('RelatedToIDs', $relative->id)) == 1) {
					$dependencies = $this->dependencies($relative->id, ['Affiliates', 'Groups', 'Relatives', 'Related', 'Skills', 'Settings']);
					if ($dependencies !== false) {
						return false;
					}
				}
			}

			return true;
		}, 'delete_relative', [
			'errorField' => 'disposition',
			'message' => __('You cannot delete the only parent of a child with history in the system.'),
		]);

		return $rules;
	}

	/**
	 * Adjust some data before patching the entity
	 *
	 * @param CakeEvent $cakeEvent Unused
	 * @param ArrayObject $data The data record being patched in
	 * @param ArrayObject $options Unused
	 */
	public function beforeMarshal(CakeEvent $cakeEvent, ArrayObject $data, ArrayObject $options) {
		if ($options['validate'] === 'create') {
			// Maybe adjust the primary status
			if (Configure::read('feature.auto_approve')) {
				if (!empty($data['groups']['_ids'])) {
					// Check the requested groups and do not auto-approve above a certain level
					$invalid_groups = $this->Groups->find()
						->where([
							'Groups.id IN' => $data['groups']['_ids'],
							'level >' => 1,
						]);
					if ($invalid_groups->count() == 0) {
						$data['status'] = 'active';
					}
				} else {
					$data['status'] = 'active';
				}

				$relative_status = 'active';
			} else {
				$identity = Router::getRequest() ? Router::getRequest()->getAttribute('identity') : null;
				if ($data->offsetExists('status') && $identity && $identity->isManager()) {
					$relative_status = $data['status'];
				} else {
					$relative_status = $data['status'] = 'new';
				}
			}

			// Add dummy affiliate record, if the feature isn't enabled
			if (!Configure::read('feature.affiliates')) {
				$data['affiliates'] = ['_ids' => [AFFILIATE_DUMMY]];
			}

			// Set up a few things on the relative records
			if (!empty($data['relatives'])) {
				foreach (array_keys($data['relatives']) as $key) {
					$data['relatives'][$key]['status'] = $relative_status;
					$data['relatives'][$key]['is_child'] = true;
					$data['relatives'][$key]['groups'] = ['_ids' => [GROUP_PLAYER]];
					if ($data->offsetExists('affiliates')) {
						$data['relatives'][$key]['affiliates'] = $data['affiliates'];
					}
					$data['relatives'][$key]['_joinData'] = ['approved' => true];
				}
			}
		}
	}

	/**
	 * Modifies the entity before rules are run. There are some affiliates and groups
	 * that we don't want to display on the edit page, but might need to add before
	 * we do final validation.
	 *
	 * @param \Cake\Event\Event $cakeEvent The beforeRules event that was fired
	 * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
	 * @param \ArrayObject $options The options passed to the save method
	 * @param mixed $operation The operation (e.g. create, delete) about to be run
	 * @return void
	 */
	public function beforeRules(\Cake\Event\EventInterface $cakeEvent, EntityInterface $entity, ArrayObject $options, $operation) {
		$user_cache = UserCache::getInstance();

		if (!empty($options['manage_affiliates'])) {
			if (!Configure::read('feature.affiliates')) {
				// Everyone must be in an affiliate. If the feature is disabled, force affiliate 1.
				$entity->affiliates = [$this->Affiliates->get(1)];
			} else if (!$entity->isNew()) {
				// Manually add all affiliates the user is a manager of. The edit page does not provide
				// these as options to be selected, so we need not worry about duplication.
				if (!$entity->has('affiliates')) {
					$entity->affiliates = [];
				}
				foreach ($user_cache->read('ManagedAffiliates', $entity->id) as $affiliate) {
					$affiliate->_joinData = $affiliate->_matchingData['AffiliatesPeople'];
					unset($affiliate->_matchingData);
					$entity->affiliates[] = $affiliate;
					$entity->setDirty('affiliates', true);
				}
			}
		}

		if (!empty($options['manage_groups'])) {
			if (!$entity->isNew()) {
				// Preserve any higher-level groups that a relative editing the profile won't have access to
				if (!$entity->has('groups')) {
					$entity->groups = [];
				}
				$groups = $this->Groups->find('options')->toArray();
				foreach ($user_cache->read('Groups', $entity->id) as $group) {
					if (!array_key_exists($group->id, $groups)) {
						$entity->groups[] = $group;
						$entity->setDirty('groups', true);
					}
				}
			}
		}
	}

	/**
	 * Modifies the entity after rules are run.
	 *
	 * @param \Cake\Event\Event $cakeEvent The beforeRules event that was fired
	 * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
	 * @param \ArrayObject $options The options passed to the save method
	 * @param boolean $result Indication of whether the rules passed
	 * @param mixed $operation The operation (e.g. create, delete) about to be run
	 * @return void
	 */
	public function afterRules(\Cake\Event\EventInterface $cakeEvent, EntityInterface $entity, ArrayObject $options, $result, $operation) {
		if ($result && !$entity->complete) {
			$entity->complete = true;
		}

		// Should this go in beforeMarshal instead?
		if (Configure::read('feature.auto_approve') && $entity->status == 'new' && $entity->complete) {
			$entity->status = 'active';
		}
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
		if (!$entity->isNew()) {
			$cache->clear('Person', $entity->id);
			$cache->clear('User', $entity->id);
			if ($entity->has('skills')) {
				$cache->clear('Skills', $entity->id);
			}
			if ($entity->has('groups')) {
				$cache->clear('Groups', $entity->id);
				$cache->clear('GroupIDs', $entity->id);
			}
		}

		// Send an event to any callback listeners
		$event = new CakeEvent('Model.Person.afterSave', $this, [$entity]);
		$this->getEventManager()->dispatch($event);
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
		UserCache::delete($entity->id);

		// Send an event to any callback listeners
		$event = new CakeEvent('Model.Person.afterDelete', $this, [$entity]);
		$this->getEventManager()->dispatch($event);
	}

	public function findDuplicates(Query $query, array $options) {
		// $options parameter must be an array. So we'll pass the entity in the array...
		/** @var Person $person */
		$person = $options['person'];
		// TODO: This is ugly. Fix it.
		if (!empty($person->affiliates)) {
			$affiliates = collection($person->affiliates)->extract('id')->toArray();
		} else if (!empty($person->affiliates_people)) {
			$affiliates = collection($person->affiliates_people)->extract('affiliate_id')->toArray();
		} else {
			$affiliates = [-1];
		}

		$user_model = Configure::read('Security.authModel');
		$email_field = $this->$user_model->emailField;
		$conditions = [
			'People.id !=' => $person->id,
			'OR' => [
				[
					'People.first_name' => $person->first_name,
					'People.last_name' => $person->last_name,
				],
			],
		];

		if (!empty($person->email)) {
			// This has to be a separate query, to handle situations where the user table is in a separate database
			$duplicate_users = $this->$user_model->find()
				->where([$email_field => $person->email])
				->extract($this->$user_model->getPrimaryKey())
				->toArray();
			if (!empty($duplicate_users)) {
				$conditions['OR']['People.user_id IN'] = $duplicate_users;
			}
			$conditions['OR']['People.alternate_email'] = $person->email;
		}

		if (Configure::read('profile.home_phone') && !empty($person->home_phone)) {
			$conditions['OR']['People.home_phone'] = $person->home_phone;
		}
		if (Configure::read('profile.work_phone') && !empty($person->work_phone)) {
			$conditions['OR']['People.work_phone'] = $person->work_phone;
		}
		if (Configure::read('profile.mobile_phone') && !empty($person->mobile_phone)) {
			$conditions['OR']['People.mobile_phone'] = $person->mobile_phone;
		}
		if (Configure::read('profile.addr_street') && !empty($person->addr_street)) {
			$conditions['OR']['People.addr_street'] = $person->addr_street;
		}

		$duplicates = $query
			->contain([$user_model])
			->where($conditions)
			->matching('Affiliates', function (Query $q) use ($affiliates) {
				return $q->where(['Affiliates.id IN' => $affiliates]);
			});

		return $duplicates;
	}

	public function delete(EntityInterface $entity, $options = []): bool {
		$cache = UserCache::getInstance();

		$user_model = Configure::read('Security.authModel');
		$authenticate = TableRegistry::getTableLocator()->get(Configure::read('Security.authPlugin') . $user_model);
		$user_model = Inflector::singularize(Inflector::underscore($user_model));

		// Delete the person, and their user record if any
		if (!parent::delete($entity)) {
			return false;
		}
		if (!empty($entity->user_id)) {
			if (!$entity->has($user_model)) {
				trigger_error('TODOTESTING', E_USER_WARNING);
				exit;
			}
			if (!$authenticate->delete($entity->$user_model)) {
				return false;
			}
		}

		// Delete any relatives that have no user_id of their own, and for which we are the only relation
		foreach ($entity->relatives as $relative) {
			if (empty($relative->user_id) && count($cache->read('RelatedToIDs', $relative->id)) == 1) {
				if (!self::delete($relative)) {
					return false;
				}
			}
		}

		return true;
	}

	public function mergeList(array $old, array $new) {
		// Clear ids from the join data in all the new people
		foreach ($new as $person) {
			unset($person->_joinData->id);
			unset($person->_joinData->relative_id);
			$person->_joinData->setNew(true);
		}

		// Since relationship associations have 'saveStrategy' => 'append', we don't need to merge in old people
		return $new;
	}

	public static function comparePerson($a, $b) {
		return (strtolower($a->last_name) <=> strtolower($b->last_name)) ?:
			((strtolower($a->full_name) <=> strtolower($b->full_name)) ?: $a->id <=> $b->id);
	}

}
