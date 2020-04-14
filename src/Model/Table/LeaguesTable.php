<?php
namespace App\Model\Table;

use ArrayObject;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event as CakeEvent;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use App\Core\UserCache;
use App\Model\Rule\InConfigRule;

/**
 * Leagues Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Affiliates
 * @property \Cake\ORM\Association\HasMany $Divisions
 * @property \Cake\ORM\Association\BelongsToMany $StatTypes
 */
class LeaguesTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->setTable('leagues');
		$this->setDisplayField('name');
		$this->setPrimaryKey('id');

		$this->addBehavior('Trim');
		$this->addBehavior('Translate', ['fields' => ['name']]);

		$this->belongsTo('Affiliates', [
			'foreignKey' => 'affiliate_id',
			'joinType' => 'INNER',
		]);

		$this->hasMany('Divisions', [
			'foreignKey' => 'league_id',
			'dependent' => true,
		]);

		$this->belongsToMany('StatTypes', [
			'foreignKey' => 'league_id',
			'targetForeignKey' => 'stat_type_id',
			'joinTable' => 'leagues_stat_types',
			'saveStrategy' => 'replace',
			'sort' => 'StatTypes.sort',
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

			->requirePresence('name', 'create', __('A valid league name must be entered.'))
			->notEmpty('name', __('The name cannot be blank.'))

			->requirePresence('sport', 'create', __('You must select a valid sport.'))
			->notEmpty('sport', __('You must select a valid sport.'))

			->requirePresence('season', 'create', __('You must select a valid season.'))
			->notEmpty('season', __('You must select a valid season.'))

			->numeric('schedule_attempts', __('Enter a valid number of schedules to try before picking the best.'))
			->notEmpty('schedule_attempts', __('Enter a valid number of schedules to try before picking the best.'))

			->requirePresence('display_sotg', function ($context) { return Configure::read('feature.spirit') && $context['newRecord']; }, __('You must select a valid spirit display method.'))
			->notEmpty('display_sotg', __('You must select a valid spirit display method.'), function ($context) { return Configure::read('feature.spirit') && $context['newRecord']; })

			->requirePresence('sotg_questions', function ($context) { return Configure::read('feature.spirit') && $context['newRecord']; }, __('You must select a valid spirit questionnaire.'))
			->notEmpty('sotg_questions', __('You must select a valid spirit questionnaire.'), 'create')

			->boolean('numeric_sotg', __('You must select whether or not numeric spirit entry is enabled.'))
			->notEmpty('numeric_sotg', __('You must select whether or not numeric spirit entry is enabled.'), function ($context) { return Configure::read('feature.spirit') && $context['newRecord']; })

			->numeric('expected_max_score', __('Enter the highest score that you expect the winning team to reasonably reach.'))
			->requirePresence('expected_max_score', 'create', __('Enter the highest score that you expect the winning team to reasonably reach.'))
			->notEmpty('expected_max_score', __('Enter the highest score that you expect the winning team to reasonably reach.'))

			->requirePresence('stat_tracking', function ($context) {
				return $context['newRecord'] && Configure::read('scoring.stat_tracking');
			}, __('You must select when to do stat tracking.'))
			->notEmpty('stat_tracking', __('You must select when to do stat tracking.'))

			// The field in the edit page is tie_breakers for the select list, not tie_breaker for the string
			->requirePresence('tie_breakers', 'create', __('You must select one or more tie breaker methods.'))
			->notEmpty('tie_breakers', __('You must select one or more tie breaker methods.'))

			->boolean('carbon_flip', __('You must select whether or not the carbon flip is enabled.'))
			->requirePresence('carbon_flip', function ($context) {
				return $context['newRecord'] && Configure::read('scoring.carbon_flip');
			}, __('You must select whether or not the carbon flip is enabled.'))
			->notEmpty('carbon_flip', __('You must select whether or not the carbon flip is enabled.'))

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
		$rules->add($rules->existsIn(['affiliate_id'], 'Affiliates', __('You must select a valid affiliate.')));

		$rules->add(new InConfigRule('options.sport'), 'validSport', [
			'errorField' => 'sport',
			'message' => __('You must select a valid sport.'),
		]);

		$rules->add(new InConfigRule('options.season'), 'validSeason', [
			'errorField' => 'season',
			'message' => __('You must select a valid season.'),
		]);

		$rules->add(new InConfigRule(['key' => 'options.sotg_display', 'optional' => !Configure::read('feature.spirit')]), 'validSOTGDisplay', [
			'errorField' => 'display_sotg',
			'message' => __('You must select a valid spirit display method.'),
		]);

		$rules->add(new InConfigRule(['key' => 'options.spirit_questions', 'optional' => !Configure::read('feature.spirit')]), 'validSpiritQuestions', [
			'errorField' => 'sotg_questions',
			'message' => __('You must select a valid spirit questionnaire.'),
		]);

		$rules->add(function (EntityInterface $entity, Array $options) {
			if (!$entity->has('tie_breaker')) {
				return true;
			}
			$tie_breakers = $entity->tie_breakers;
			$options = Configure::read('options.tie_breaker');
			foreach ($tie_breakers as $tie_breaker) {
				if (!array_key_exists($tie_breaker, $options)) {
					return false;
				}
			}
			return true;
		}, 'validTieBreaker', [
			'errorField' => 'tie_breakers',
			'message' => __('You have selected an invalid tie breaker method.'),
		]);

		return $rules;
	}

	/**
	 * Build the tie_breaker string before updating the entity.
	 *
	 * @param CakeEvent $cakeEvent Unused
	 * @param ArrayObject $data The data record being patched in
	 * @param ArrayObject $options Unused
	 */
	public function beforeMarshal(CakeEvent $cakeEvent, ArrayObject $data, ArrayObject $options) {
		if (array_key_exists('tie_breakers', $data)) {
			$data['tie_breaker'] = implode(',', $data['tie_breakers']);
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
	public function afterSave(CakeEvent $cakeEvent, EntityInterface $entity, ArrayObject $options) {
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
				'Leagues.is_open' => true,
				'Leagues.open >' => FrozenDate::now(),
			],
		]);

		if (!empty($options['affiliates'])) {
			$query->andWhere(['Leagues.affiliate_id IN' => $options['affiliates']]);
		}

		return $query;
	}

	public static function compareLeagueAndDivision($a, $b) {
		if (is_a($a, 'App\Model\Entity\League')) {
			// Might be sorting leagues
			$a_league = $a;
			$a_divisions = $a->divisions;
			$b_league = $b;
			$b_divisions = $b->divisions;
		} else if (is_a($a, 'App\Model\Entity\Division')) {
			// Might be sorting divisions
			$a_league = $a->league;
			$a_divisions = [$a];
			$b_league = $b->league;
			$b_divisions = [$b];
		} else if (is_a($a, 'App\Model\Entity\Team')) {
			// Might be sorting teams
			$a_league = $a->division->league;
			$a_divisions = [$a->division];
			$b_league = $b->division->league;
			$b_divisions = [$b->division];
		}

		if ($a_league->has('affiliate')) {
			$a_affiliate = $a_league->affiliate;
			$b_affiliate = $b_league->affiliate;
		} else if ($a->has('affiliate')) {
			$a_affiliate = $a->affiliate;
			$b_affiliate = $b->affiliate;
		}

		// If they are different affiliates, we use that
		if ($a_affiliate->name > $b_affiliate->name) {
			return 1;
		} else if ($a_affiliate->name < $b_affiliate->name) {
			return -1;
		}

		// If they are different sports, we use that
		if ($a_league->sport > $b_league->sport) {
			return 1;
		} else if ($a_league->sport < $b_league->sport) {
			return -1;
		}

		// If they are in different years, we use that
		if ($a_league->open->year > $b_league->open->year) {
			return 1;
		} else if ($a_league->open->year < $b_league->open->year) {
			return -1;
		}

		// If they are in different seasons, we use that
		$seasons = array_flip(array_keys(Configure::read('options.season')));
		$a_season = $seasons[$a_league->season];
		$b_season = $seasons[$b_league->season];
		if ($a_season > $b_season) {
			return 1;
		} else if ($a_season < $b_season) {
			return -1;
		}

		$a_schedule_type = $b_schedule_type = PHP_INT_MAX;
		foreach ($a_divisions as $division) {
			$a_schedule_type = min($a_schedule_type, Configure::read("schedule_type.{$division->schedule_type}"));
		}
		foreach ($b_divisions as $division) {
			$b_schedule_type = min($b_schedule_type, Configure::read("schedule_type.{$division->schedule_type}"));
		}

		// Compare the schedule type, so "regular" leagues are grouped before tournaments
		if ($a_schedule_type > $b_schedule_type) {
			return 1;
		} else if ($a_schedule_type < $b_schedule_type) {
			return -1;
		}

		// For tournaments, use the league open date
		if ($a_schedule_type == SCHEDULE_TYPE_TOURNAMENT) {
			if ($a_league->open > $b_league->open) {
				return 1;
			} else if ($a_league->open < $b_league->open) {
				return -1;
			}
		}

		if (count($a_divisions) == 1) {
			if (!empty($a_divisions[0]->season_days)) {
				$a_days = $a_divisions[0]->season_days;
			} else if (!empty($a_divisions[0]->days)) {
				$a_days = array_unique(collection($a_divisions)->extract('days.{*}.id')->toList());
			}
		} else {
			$a_days = array_unique(collection($a_divisions)->filter(function ($division) {
				return Configure::read("schedule_type.{$division->schedule_type}") == SCHEDULE_TYPE_LEAGUE;
			})->extract('days.{*}.id')->toList());
		}

		if (count($b_divisions) == 1) {
			if (!empty($b_divisions[0]->season_days)) {
				$b_days = $b_divisions[0]->season_days;
			} else if (!empty($b_divisions[0]->days)) {
				$b_days = array_unique(collection($b_divisions)->extract('days.{*}.id')->toList());
			}
		} else {
			$b_days = array_unique(collection($b_divisions)->filter(function ($division) {
				return Configure::read("schedule_type.{$division->schedule_type}") == SCHEDULE_TYPE_LEAGUE;
			})->extract('days.{*}.id')->toList());
		}

		if (empty($a_days)) {
			$a_min = 0;
		} else {
			$a_min = min($a_days);
		}
		if (empty($b_days)) {
			$b_min = 0;
		} else {
			$b_min = min($b_days);
		}

		if ($a_min > $b_min) {
			return 1;
		} else if ($a_min < $b_min) {
			return -1;
		}

		if ($a_league->name > $b_league->name) {
			return 1;
		} else if ($a_league->name < $b_league->name) {
			return -1;
		}

		if (count($a_divisions) == 1) {
			// Divisions on the same day use the id to sort. Assumption is that
			// higher-level divisions are created first.
			return $a_divisions[0]->id > $b_divisions[0]->id;
		}

		return $a_league->id > $b_league->id;
	}

	public function affiliate($id) {
		try {
			return $this->field('affiliate_id', ['Leagues.id' => $id]);
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}

	public function divisions($league_id) {
		return $this->Divisions->find()
			->enableHydration(false)
			->where(compact('league_id'))
			->combine('id', 'id')
			->toArray();
	}

}
