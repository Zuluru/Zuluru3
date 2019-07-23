<?php
namespace App\Model\Table;

use App\Model\Rule\OrRule;
use ArrayObject;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event as CakeEvent;
use Cake\Event\EventManager;
use Cake\ORM\RulesChecker;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;
use App\Model\Rule\InConfigRule;
use App\Model\Rule\ValidScoreRule;

/**
 * ScoreEntries Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Teams
 * @property \Cake\ORM\Association\BelongsTo $Games
 * @property \Cake\ORM\Association\BelongsTo $People
 * @property \Cake\ORM\Association\BelongsToMany $Allstars
 */
class ScoreEntriesTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->table('score_entries');
		$this->displayField('id');
		$this->primaryKey('id');

		$this->addBehavior('Timestamp');
		$this->addBehavior('Muffin/Footprint.Footprint', [
			'events' => [
				'Model.beforeSave' => [
					'person_id' => 'new',
				],
			],
			'propertiesMap' => [
				'person_id' => '_footprint.person.id',
			],
		]);

		$this->belongsTo('Teams', [
			'foreignKey' => 'team_id',
		]);
		$this->belongsTo('Games', [
			'foreignKey' => 'game_id',
			'joinType' => 'INNER',
		]);
		$this->belongsTo('People', [
			'foreignKey' => 'person_id',
			// Old score entries do not have a person_id associated with them.
			'joinType' => 'LEFT',
		]);

		$this->belongsToMany('Allstars', [
			'className' => 'People',
			'joinTable' => 'games_allstars',
			'through' => 'GamesAllstars',
			'foreignKey' => 'score_entry_id',
			'targetForeignKey' => 'person_id',
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

			->nonNegativeInteger('score_for', __('Scores must be in the range 0-99.'))
			->allowEmpty('score_for', function($context) { return !empty($context['data']['status']) && $context['data']['status'] != 'normal'; })

			->nonNegativeInteger('score_against', __('Scores must be in the range 0-99.'))
			->allowEmpty('score_against', function($context) { return !empty($context['data']['status']) && $context['data']['status'] != 'normal'; })

			->requirePresence('status', function($context) { return $context['newRecord'] && !empty($context['data']['person_id']); }, __('You must select a valid status.'))
			->notEmpty('status', __('You must select a valid status.'))

			->range('home_carbon_flip', [0, 2], __('You must select a valid carbon flip result.'))
			->requirePresence('home_carbon_flip', function ($context) {
				return Configure::read('scoring.carbon_flip') && array_key_exists('score_for', $context['data']);
			}, __('You must select a valid carbon flip result.'))

			->range('women_present', [0, 25], __('You must enter the number of women designated players.'))
			->requirePresence('women_present', function ($context) {
				if (!Configure::read('scoring.women_present') ||
					empty($context['data']['game_id']) ||
					// Score entries for games being finalized may not include a status, and don't need the number of women
					!array_key_exists('status', $context['data']) ||
					// Games that were cancelled or defaulted don't need the number of women
					in_array($context['data']['status'], Configure::read('unplayed_status')) ||
					strpos($context['data']['status'], 'default') !== false ||
					// Lots of old games without this data, don't require it if we edit those.
					!$context['newRecord']
				) {
					return false;
				}

				$game = $this->Games->get($context['data']['game_id'], [
					'contain' => ['Divisions' => ['Leagues']]
				]);
				return $game->division->women_present;
			}, __('You must enter the number of women designated players.'))
			->allowEmpty('women_present')

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
		$rules->add($rules->existsIn(['team_id'], 'Teams'));
		$rules->add($rules->existsIn(['game_id'], 'Games'));
		$rules->add($rules->existsIn(['person_id'], 'People'));

		$rules->add(new OrRule([
			// If there's no person_id on the entity, it's just a container for allstars,
			// in which case we don't need a status.
			function (EntityInterface $entity, Array $options) { return !$entity->person_id; },
			new InConfigRule('options.game_status'),
		]), 'validStatus', [
			'errorField' => 'status',
			'message' => __('You must select a valid status.'),
		]);

		// TODO: Make the 99 configurable
		$rules->addUpdate(new ValidScoreRule(0, 99), 'validScore', [
			'errorField' => 'score_for',
			'message' => __('Scores must be in the range 0-99.'),
		]);

		$rules->addUpdate(new ValidScoreRule(0, 99), 'validScore', [
			'errorField' => 'score_against',
			'message' => __('Scores must be in the range 0-99.'),
		]);

		if (Configure::read('scoring.allstars')) {
			$rules->add(function (EntityInterface $entity, Array $options) {
				return empty($entity->allstars) || count($entity->allstars) <= 2;
			}, 'validAllstars', [
				'errorField' => 'allstars',
				'message' => __('You cannot select more than two all-stars.'),
			]);
		}

		if (Configure::read('scoring.women_present')) {
			$rules->add(function (EntityInterface $entity, Array $options) {
				if (!$options['game']->division->women_present ||
					// If the game has been finalized, it's an admin editing a score that was not submitted
					// by the team. These have no women_present value, and we can't require that they do,
					// because how would the admin know what it should be?
					$options['game']->isFinalized()
				) {
					return true;
				}

				return ($entity->women_present !== null);
			}, 'validWomenPresent', [
				'errorField' => 'women_present',
				'message' => __('You must enter the number of women designated players.'),
			]);
		}

		return $rules;
	}

	/**
	 * Set default scores in the case of a default reported. Can't trust the JavaScript to ensure this.
	 *
	 * @param CakeEvent $cakeEvent Unused
	 * @param ArrayObject $data The data record being patched in
	 * TODO: Use the options array to pass in home and away team ids?
	 * @param ArrayObject $options Unused
	 */
	public function beforeMarshal(CakeEvent $cakeEvent, ArrayObject $data, ArrayObject $options) {
		// When editing a game, the score entries won't have their statuses changed.
		if (!array_key_exists('status', $data)) {
			return;
		}

		if ($data['status'] == 'home_default') {
			$home_team = TableRegistry::get('Games')->field('home_team_id', ['id' => $data['game_id']]);
			if ($home_team == $data['team_id']) {
				$data['score_for'] = Configure::read('scoring.default_losing_score');
				$data['score_against'] = Configure::read('scoring.default_winning_score');
			} else {
				$data['score_for'] = Configure::read('scoring.default_winning_score');
				$data['score_against'] = Configure::read('scoring.default_losing_score');
			}
		} else if ($data['status'] == 'away_default') {
			$home_team = TableRegistry::get('Games')->field('home_team_id', ['id' => $data['game_id']]);
			if ($home_team == $data['team_id']) {
				$data['score_for'] = Configure::read('scoring.default_winning_score');
				$data['score_against'] = Configure::read('scoring.default_losing_score');
			} else {
				$data['score_for'] = Configure::read('scoring.default_losing_score');
				$data['score_against'] = Configure::read('scoring.default_winning_score');
			}
		} else {
			if (in_array($data['status'], Configure::read('unplayed_status'))) {
				$data['score_for'] = $data['score_against'] = null;
			}
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
		// If allstars aren't allowed, remove anything that was submitted.
		if (!Configure::read('scoring.allstars') && !empty($entity->allstars)) {
			$entity->allstars = [];
			$entity->dirty('allstars', true);
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
		if (!$options->offsetExists('game')) {
			trigger_error('TODOTESTING', E_USER_WARNING);
			exit;
		}

		if (count($options['game']->score_entries) == 1) {
			$event = new CakeEvent('Model.Game.scoreSubmission', $this, [$options['game']]);
			EventManager::instance()->dispatch($event);
		}
	}

	public function division($id) {
		try {
			return $this->Games->division($this->field('game_id', ['id' => $id]));
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}

	public function team($id) {
		try {
			return $this->field('team_id', ['id' => $id]);
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}

}
