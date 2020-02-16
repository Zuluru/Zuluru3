<?php
namespace App\Model\Table;

use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\RulesChecker;
use Cake\ORM\Rule\ExistsIn;
use Cake\Validation\Validator;

/**
 * Stats Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Games
 * @property \Cake\ORM\Association\BelongsTo $Teams
 * @property \Cake\ORM\Association\BelongsTo $People
 * @property \Cake\ORM\Association\BelongsTo $StatTypes
 */
class StatsTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->setTable('stats');
		$this->setDisplayField('id');
		$this->setPrimaryKey('id');

		$this->belongsTo('Games', [
			'foreignKey' => 'game_id',
			'joinType' => 'INNER',
		]);
		$this->belongsTo('Teams', [
			'foreignKey' => 'team_id',
			'joinType' => 'INNER',
		]);
		$this->belongsTo('People', [
			'foreignKey' => 'person_id',
			'joinType' => 'INNER',
		]);
		$this->belongsTo('StatTypes', [
			'foreignKey' => 'stat_type_id',
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

			->numeric('value', __('Stats must be numeric.'))
			->requirePresence('value', 'create', __('Stats must be numeric.'))
			->notEmpty('value', __('Stats must be numeric.'))

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
		$rules->add($rules->existsIn(['game_id'], 'Games'));
		$rules->add($rules->existsIn(['team_id'], 'Teams'));
		$rules->add($rules->existsIn(['stat_type_id'], 'StatTypes'));

		$rules->add(function (EntityInterface $entity, Array $options) {
			// Person_id can be zero for unlisted subs
			if ($entity->person_id == 0) {
				return true;
			}
			$rule = new ExistsIn(['person_id'], 'People');
			return $rule($entity, $options);
		}, 'valid', [
			'errorField' => 'person_id',
			'message' => __d('cake', 'This value does not exist'),
		]);

		return $rules;
	}

	public static function applicable($stat_type, $position) {
		// If there's nothing specified, it's for everyone
		if (empty($stat_type->positions)) {
			return true;
		}

		$positions = explode(',', $stat_type->positions);
		$good = $bad = [];
		foreach ($positions as $p) {
			if ($p[0] == '!') {
				$bad[] = $p;
			} else {
				$good[] = $p;
			}
		}

		// If the player is one of the specified positions, it's for them
		if (in_array($position, $good)) {
			return true;
		}

		// If exclusions are specified and the player is NOT one of them,
		// it's for them
		if (!empty($bad) && !in_array("!$position", $bad)) {
			return true;
		}

		// It's not for them
		return false;
	}

	public function division($id) {
		try {
			return $this->Games->division($this->field('game_id', ['Stats.id' => $id]));
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}

	public function team($id) {
		try {
			return $this->field('team_id', ['Stats.id' => $id]);
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}

}
