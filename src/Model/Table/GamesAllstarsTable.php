<?php
namespace App\Model\Table;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event as CakeEvent;
use Cake\ORM\RulesChecker;
use App\Model\Rule\OnTeamRule;

/**
 * GamesAllstars Model
 *
 * @property \Cake\ORM\Association\BelongsTo $People
 * @property \Cake\ORM\Association\BelongsTo $ScoreEntries
 */
class GamesAllstarsTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('games_allstars');
		$this->setDisplayField('id');
		$this->setPrimaryKey('id');

		$this->belongsTo('People', [
			'foreignKey' => 'person_id',
			'joinType' => 'INNER',
		]);
		$this->belongsTo('ScoreEntries', [
			'foreignKey' => 'score_entry_id',
			'joinType' => 'INNER',
		]);
	}

	/**
	 * Returns a rules checker object that will be used for validating
	 * application integrity.
	 *
	 * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
	 * @return \Cake\ORM\RulesChecker
	 */
	public function buildRules(RulesChecker $rules): \Cake\ORM\RulesChecker {
		$rules->add(new OnTeamRule(), 'teamMember', [
			'errorField' => 'person_id',
			'message' => __('That person is not on that team.'),
		]);

		return $rules;
	}

	/**
	 * Modifies the entity before rules are run.
	 *
	 * @param \Cake\Event\Event $cakeEvent The beforeRules event that was fired
	 * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
	 * @param \ArrayObject $options The options passed to the save method
	 * @param mixed $operation The operation (e.g. create, delete) about to be run
	 * @return void
	 */
	public function beforeRules(\Cake\Event\EventInterface $cakeEvent, EntityInterface $entity, ArrayObject $options, $operation) {
		if ($entity->isNew() && !$entity->has('team_id')) {
			// Set the team ID, based on what we know
			if ($options['game']->division->allstars_from == 'submitter') {
				$entity->team_id = $options['team_id'];
			} else if ($options['game']['home_team_id'] == $options['team_id']) {
				$entity->team_id = $options['game']['away_team_id'];
			} else {
				$entity->team_id = $options['game']['home_team_id'];
			}
		}
	}

}
