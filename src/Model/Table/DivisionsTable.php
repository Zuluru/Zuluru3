<?php
namespace App\Model\Table;

use App\Authorization\ContextResource;
use ArrayObject;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event as CakeEvent;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Routing\Router;
use Cake\Validation\Validator;
use App\Core\UserCache;
use App\Core\ModuleRegistry;
use App\Model\Entity\Division;
use App\Model\Rule\GreaterDateRule;
use App\Model\Rule\InConfigRule;
use App\Model\Rule\InDateConfigRule;
use App\Model\Rule\LesserDateRule;
use App\Model\Rule\RuleSyntaxRule;

/**
 * Divisions Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Leagues
 * @property \Cake\ORM\Association\HasMany $Events
 * @property \Cake\ORM\Association\HasMany $Games
 * @property \Cake\ORM\Association\HasMany $Pools
 * @property \Cake\ORM\Association\HasMany $Teams
 * @property \Cake\ORM\Association\BelongsToMany $Days
 * @property \Cake\ORM\Association\BelongsToMany $GameSlots
 * @property \Cake\ORM\Association\BelongsToMany $People
 */
class DivisionsTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->setTable('divisions');
		$this->setDisplayField('name');
		$this->setPrimaryKey('id');

		$this->addBehavior('Trim');
		$this->addBehavior('Translate', ['fields' => ['name', 'header', 'footer']]);

		$this->belongsTo('Leagues', [
			'foreignKey' => 'league_id',
			// TODOLATER: INNER seems like the right thing here for straight Divisions -> Leagues relation,
			// but it screws up other things, like Events -> Divisions -> Leagues, where an Event may not
			// have a Division, then Events LEFT JOIN Divisions INNER JOIN Leagues doesn't return the Event.
			// Other places where this might happen? Investigate a Cake fix for this?
			//'joinType' => 'INNER',
		]);

		$this->hasMany('Events', [
			'foreignKey' => 'division_id',
		]);
		$this->hasMany('Games', [
			'foreignKey' => 'division_id',
		]);
		$this->hasMany('Pools', [
			'foreignKey' => 'division_id',
		]);
		$this->hasMany('Teams', [
			'foreignKey' => 'division_id',
		]);

		$this->belongsToMany('Days', [
			'foreignKey' => 'division_id',
			'targetForeignKey' => 'day_id',
			'joinTable' => 'divisions_days',
			'saveStrategy' => 'replace',
		]);
		$this->belongsToMany('GameSlots', [
			'foreignKey' => 'division_id',
			'targetForeignKey' => 'game_slot_id',
			'joinTable' => 'divisions_gameslots',
			'through' => 'DivisionsGameslots',
			'saveStrategy' => 'append',
		]);
		$this->belongsToMany('People', [
			'foreignKey' => 'division_id',
			'targetForeignKey' => 'person_id',
			'joinTable' => 'divisions_people',
			'saveStrategy' => 'replace',
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

			// validation will allow empty names; rules will limit this
			->allowEmpty('name')

			->date('open', __('You must provide a valid date for the first game.'))
			->requirePresence('open', 'create', __('You must provide a valid date for the first game.'))
			->notEmpty('open', __('You must provide a valid date for the first game.'))

			->date('close', __('You must provide a valid date for the last game.'))
			->requirePresence('close', 'create', __('You must provide a valid date for the last game.'))
			->notEmpty('close', __('You must provide a valid date for the last game.'))

			->requirePresence('ratio_rule', 'create', __('You must select a valid ratio rule.'))
			->notEmpty('ratio_rule', __('You must select a valid ratio rule.'))

			->date('roster_deadline', __('You must provide a valid roster deadline.'))
			->allowEmpty('roster_deadline')

			->allowEmpty('roster_rule')

			->requirePresence('schedule_type', 'create')
			->notEmpty('schedule_type', __('You must select a valid schedule type.'))

			->boolean('exclude_teams')
			->notEmpty('exclude_teams')

			->allowEmpty('coord_list')

			->allowEmpty('capt_list')

			->numeric('email_after')
			->requirePresence('email_after', 'create')
			->notEmpty('email_after')

			->numeric('finalize_after')
			->requirePresence('finalize_after', 'create')
			->notEmpty('finalize_after')

			->requirePresence('roster_method', 'create')
			->notEmpty('roster_method')

			->requirePresence('rating_calculator', function ($context) { return array_key_exists('schedule_type', $context['data']) && $context['data']['schedule_type'] == 'ratings_ladder' && $context['newRecord']; })
			->notEmpty('rating_calculator', null, function ($context) { return array_key_exists('schedule_type', $context['data']) && $context['data']['schedule_type'] == 'ratings_ladder'; })

			->boolean('flag_membership')
			->notEmpty('flag_membership', null, function () { return Configure::read('feature.registration'); })

			->boolean('flag_roster_conflict')
			->notEmpty('flag_roster_conflict')

			->boolean('flag_schedule_conflict')
			->notEmpty('flag_schedule_conflict')

			->requirePresence('allstars', function ($context) { return Configure::read('scoring.allstars') && $context['newRecord']; })
			->notEmpty('allstars', null, function () { return Configure::read('scoring.allstars'); })

			->requirePresence('allstars_from', function ($context) { return Configure::read('scoring.allstars') && $context['newRecord']; })
			->notEmpty('allstars_from', null, function () { return Configure::read('scoring.allstars'); })

			->boolean('double_booking')
			->notEmpty('double_booking')

			->requirePresence('most_spirited', function ($context) { return Configure::read('scoring.most_spirited') && $context['newRecord']; })
			->notEmpty('most_spirited', null, function () { return Configure::read('scoring.most_spirited'); })

			->allowEmpty('header')

			->allowEmpty('footer')

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
	public function buildRules(RulesChecker $rules) {
		$rules->add($rules->existsIn(['league_id'], 'Leagues', __('You must select a valid league.')));

		$rules->add(function (EntityInterface $entity, Array $options) {
			if (array_key_exists('divisions', $options)) {
				$divisions = count($options['divisions']);
			} else if ($entity->has('league') && $entity->league->has('divisions')) {
				$divisions = count($entity->league->divisions);
			} else {
				$divisions = $this->find()->where(['Divisions.league_id' => $entity->league_id]);
				if (!$entity->isNew()) {
					$divisions->andWhere(['Divisions.id !=' => $entity->id]);
				}
				$divisions = $divisions->count() + 1;
			}

			if ($divisions <= 1) {
				return true;
			}
			return !empty($entity->name);
		}, 'validName', [
			'errorField' => 'name',
			'message' => __('Division names can only be blank if there is a single division in the league.'),
		]);

		$rules->addCreate(new InDateConfigRule('event'), 'rangeOpenDate', [
			'errorField' => 'open',
			'message' => __('First game date must be between last year and next year.'),
		]);

		$rules->addCreate(new InDateConfigRule('event'), 'rangeCloseDate', [
			'errorField' => 'close',
			'message' => __('Last game date must be between last year and next year.'),
		]);

		$rules->add(new GreaterDateRule('open'), 'greaterCloseDate', [
			'errorField' => 'close',
			'message' => __('The last game cannot be before the first game.'),
		]);

		$rules->addCreate(new InDateConfigRule('event'), 'rangeRosterDeadlineDate', [
			'errorField' => 'roster_deadline',
			'message' => __('Roster deadline date must be between last year and next year.'),
		]);

		$rules->add(new LesserDateRule('close'), 'lesserRosterDeadlineDate', [
			'errorField' => 'roster_deadline',
			'message' => __('The roster deadline date cannot be after the close date.'),
		]);

		$rules->add(new RuleSyntaxRule(), 'validRule', [
			'errorField' => 'roster_rule',
			'message' => __('There is an error in the rule syntax.'),
		]);

		$rules->add(function (EntityInterface $entity, Array $options) {
			if ($entity->has('league')) {
				$sport = $entity->league->sport;
			} else {
				$sport = $this->Leagues->field('sport', ['Leagues.id' => $entity->league_id]);
			}
			$rule = new InConfigRule("sports.{$sport}.ratio_rule");
			return $rule($entity, $options);
		}, 'validRatio', [
			'errorField' => 'ratio_rule',
			'message' => __('You must select a valid ratio rule.'),
		]);

		$rules->add(new InConfigRule('options.roster_methods'), 'validRosterMethod', [
			'errorField' => 'roster_method',
			'message' => __('You must select a valid roster method.'),
		]);

		$rules->add(new InConfigRule('options.schedule_type'), 'validScheduleType', [
			'errorField' => 'schedule_type',
			'message' => __('You must select a valid schedule type.'),
		]);

		$rules->add(new InConfigRule('options.rating_calculator'), 'validRatingCalculator', [
			'errorField' => 'rating_calculator',
			'message' => __('You must select a valid rating calculator.'),
		]);

		$rules->add(new InConfigRule(['key' => 'options.allstar', 'optional' => !Configure::read('scoring.allstars')]), 'validAllstars', [
			'errorField' => 'allstars',
			'message' => __('You must select a valid allstar entry option.'),
		]);

		$rules->add(new InConfigRule(['key' => 'options.allstar_from', 'optional' => !Configure::read('scoring.allstars')]), 'validAllstarsFrom', [
			'errorField' => 'allstars_from',
			'message' => __('You must select a valid allstar entry option.'),
		]);

		$rules->add(new InConfigRule(['key' => 'options.most_spirited', 'optional' => !Configure::read('scoring.most_spirited')]), 'validMostSpirited', [
			'errorField' => 'most_spirited',
			'message' => __('You must select a valid "most spirited player" entry option.'),
		]);

		$rules->add(new InConfigRule('options.enable'), 'validExcludeTeams', [
			'errorField' => 'exclude_teams',
			'message' => __('You must select whether or not teams can be excluded from scheduling.'),
		]);

		$rules->add(new InConfigRule(['key' => 'options.enable', 'optional' => !Configure::read('feature.registration')]), 'validFlagMembership', [
			'errorField' => 'flag_membership',
			'message' => __('You must select whether or not to flag players without current memberships on team rosters.'),
		]);

		$rules->add(new InConfigRule('options.enable'), 'validFlagRosterConflict', [
			'errorField' => 'flag_roster_conflict',
			'message' => __('You must select whether or not to flag players on multiple teams in the same league.'),
		]);

		$rules->add(new InConfigRule('options.enable'), 'validFlagScheduleConflict', [
			'errorField' => 'flag_schedule_conflict',
			'message' => __('You must select whether or not to flag players that potentially have scheduling conflicts.'),
		]);

		$rules->add(function (EntityInterface $entity, Array $options) {
			if ($entity->has('schedule_type') && !empty($entity->schedule_type)) {
				try {
					$league_obj = ModuleRegistry::getInstance()->load("LeagueType:{$entity->schedule_type}");
					return $league_obj->schedulingFieldsRules($entity);
				} catch (Exception $ex) {
				}
				return true;
			}
		}, 'validScheduleFields');

		$rules->add(function (EntityInterface $entity, Array $options) {
			if ($entity->has('schedule_type') && $entity->schedule_type != 'none' && $entity->has('days') && empty($entity->days)) {
				// If a schedule type was chosen, require at least one "day of play"
				return false;
			}
			return true;
		}, 'validDays', [
			'errorField' => 'days',
			'message' => __('You must select at least one day!'),
		]);

		$rules->addDelete(function ($entity, $options) {
			// Don't delete the last division in a league
			if (count($entity->league->divisions) < 2) {
				return __('You cannot delete the only division in a league.');
			}
			return true;
		}, 'last', ['errorField' => 'delete']);

		return $rules;
	}

	/**
	 * Handle custom field serialization before trying to write anything out.
	 *
	 * @param CakeEvent $cakeEvent Unused
	 * @param ArrayObject $data The data record being patched in
	 * @param ArrayObject $options Unused
	 */
	public function beforeMarshal(CakeEvent $cakeEvent, ArrayObject $data, ArrayObject $options) {
		if ($options->offsetExists('validateDays') && $options['validateDays'] && !$data->offsetExists('days')) {
			$data['days'] = [];
		}
	}

	/**
	 * Modifies the entity before it is saved.
	 *
	 * @param \Cake\Event\Event $cakeEvent The beforeSave event that was fired
	 * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
	 * @param \ArrayObject $options The options passed to the save method
	 * @return void
	 */
	public function beforeSave(CakeEvent $cakeEvent, EntityInterface $entity, ArrayObject $options) {
		// Does the division need to be opened immediately?
		$entity->is_open = ($entity->open < FrozenDate::now()->addWeeks(3) &&
			$entity->close > FrozenDate::now()->subWeek());
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
		// Update this division's league open and close dates, if required
		$league = $this->Leagues->get($entity->league_id, [
			'contain' => ['Divisions']
		]);

		$open = min(collection($league->divisions)->extract('open')->toArray());
		if ($open != $league->open) {
			$league->open = $open;
		}
		$close = max(collection($league->divisions)->extract('close')->toArray());
		if ($close != $league->close) {
			$league->close = $close;
		}
		$is_open = in_array(true, collection($league->divisions)->extract('is_open')->toArray());
		if ($is_open != $league->is_open) {
			$league->is_open = $is_open;
		}
		$this->Leagues->save($league);

		// Update the badges in existing divisions that are being opened or closed
		if (Configure::read('feature.badges') && !$entity->isNew() && $entity->is_open != $entity->getOriginal('is_open')) {
			$badges = $this->People->Badges->find()
				->where([
					'Badges.category' => 'team',
					'Badges.active' => true,
				]);

			if (!$badges->isEmpty()) {
				$badge_obj = ModuleRegistry::getInstance()->load('Badge');

				$this->loadInto($entity, ['Teams']);
				foreach ($entity->teams as $team) {
					$this->Teams->loadInto($team, ['People']);
					foreach ($team->people as $person) {
						// TODO: Consider passing the $team along with $person in the $extra variable?
						$badge_obj->update('team', $person->_joinData, $person);
						UserCache::getInstance()->_deleteTeamData($person->id);
					}
				}
			}
		}

		if (!$entity->isNew()) {
			// Clear the Divisions cache for all coordinators
			if (!$entity->has('people')) {
				$this->loadInto($entity, ['People']);
			}
			$cache = UserCache::getInstance();
			foreach ($entity->people as $person) {
				$cache->clear('Divisions', $person->id);
			}

			$this->clearCache($entity);
		}

		Cache::delete('tournaments', 'today');
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
		Cache::delete('tournaments', 'today');
	}

	public function findOpen(Query $query, Array $options) {
		$query->where([
			'OR' => [
				'Divisions.is_open' => true,
				'Divisions.open >' => FrozenDate::now(),
			],
		]);

		return $query;
	}

	public function findDay(Query $query, Array $options) {
		$day = $options['date']->format('N');
		$query->matching('Days', function (Query $q) use ($day) {
			return $q->where(['Days.id' => $day]);
		});

		return $query;
	}

	public function findByLeague(Query $query, Array $options) {
		$query->where(['Divisions.league_id' => $options['league']]);
		return $query;
	}

	public function readByPlayerId($id, $open = true, $teams = false) {
		// Check for invalid users
		if ($id === null) {
			return [];
		}

		$conditions = [
			'DivisionsPeople.person_id' => $id,
		];
		if ($open) {
			$conditions['OR'] = [
				'Divisions.is_open' => $open,
				'Divisions.open >' => FrozenDate::now(),
			];
		}

		$contain = [
			'Leagues' => [
				'queryBuilder' => function (Query $q) {
					return $q->find('translations');
				},
			]
		];
		if ($teams) {
			$contain[] = 'Teams';
		}

		$divisions = $this->find('translations')
			->contain($contain)
			->where($conditions)
			->matching('People', function (Query $q) use ($id) {
				return $q->where(['People.id' => $id]);
			})
			->order(['Divisions.open', 'Divisions.id'])
			->toArray();

		return $divisions;
	}

	public function affiliate($id) {
		return $this->Leagues->affiliate($this->league($id));
	}

	public function league($id) {
		try {
			return $this->field('league_id', ['Divisions.id' => $id]);
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}

	public function clearCache($division, $keys = ['schedule', 'standings', 'stats']) {
		if ($division instanceof \App\Model\Entity\Division) {
			$division_id = $division->id;
			$league_id = $division->league_id;
		} else if (is_int($division) || (is_string($division) && !empty($division))) {
			// TODO: Any way to get incoming form data to be converted to int instead of string, so this can be simplified?
			$division_id = $division;
			$league_id = $this->league($division_id);
		}
		foreach ($keys as $key) {
			Cache::delete("division/{$division_id}/{$key}", 'long_term');
			Cache::delete("league/{$league_id}/{$key}", 'long_term');
		}
	}

	public static function clearLocationsCache(array $divisions) {
		foreach ($divisions as $division) {
			if (is_int($division)) {
				Cache::delete("division/{$division}/locations", 'long_term');
			} else {
				Cache::delete("division/{$division->id}/locations", 'long_term');
			}
		}
	}

	public function prepForView(Division $division) {
		$this->loadInto($division, [
			'People',
			'Days' => [
				'queryBuilder' => function (Query $q) {
					return $q->order(['DivisionsDays.day_id']);
				},
			],
			'Leagues',
			'Events' => ['EventTypes', 'Prices'],
		]);

		$league_obj = ModuleRegistry::getInstance()->load("LeagueType:{$division->schedule_type}");
		$spirit_obj = $division->league->hasSpirit() ? ModuleRegistry::getInstance()->load("Spirit:{$division->league->sotg_questions}") : null;

		$league_obj->addResults($division, $spirit_obj);

		if ($division->is_playoff) {
			foreach ($division->teams as $team) {
				$affiliated_team = $team->_getAffiliatedTeam($division);
				if ($affiliated_team) {
					// Should maybe rename "affiliate" here, as it's the affiliated team, not the Zuluru Affiliate concept
					$affiliate_division = $this->get($affiliated_team->division_id, [
						'contain' => ['Leagues'],
					]);
					$team->affiliate_division = $affiliate_division->league_name;
				}
			}
		}

		// Eliminate any events that cannot be registered for
		$identity = Router::getRequest() ? Router::getRequest()->getAttribute('identity') : null;
		if ($identity && $identity->isLoggedIn()) {
			foreach ($division->events as $key => $event) {
				try {
					if (!$identity->can('register', new ContextResource($event, ['strict' => false]), 'register')) {
						unset($division->events[$key]);
					}
				} catch (\Authorization\Exception\Exception $ex) {
					unset($division->events[$key]);
				}
			}
		}
	}
}
