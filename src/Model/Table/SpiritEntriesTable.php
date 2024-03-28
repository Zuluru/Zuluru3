<?php
namespace App\Model\Table;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use App\Model\Entity\League;

/**
 * SpiritEntries Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Teams
 * @property \Cake\ORM\Association\BelongsTo $Games
 * @property \Cake\ORM\Association\BelongsTo $People
 */
class SpiritEntriesTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('spirit_entries');
		$this->setDisplayField('id');
		$this->setPrimaryKey('id');

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
			'joinType' => 'INNER',
		]);
		$this->belongsTo('Games', [
			'foreignKey' => 'game_id',
			'joinType' => 'INNER',
		]);
		$this->belongsTo('People', [
			'foreignKey' => 'person_id',
			'joinType' => 'INNER',
		]);
		$this->belongsTo('MostSpirited', [
			'className' => 'People',
			'foreignKey' => 'most_spirited_id',
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

			->numeric('score_entry_penalty')
			->notEmptyString('score_entry_penalty')

			->allowEmptyString('comments')

			->allowEmptyString('highlights')

			// TODOLATER: Require based on league setting? Do in SpiritModule? Any other similar fields?
			->numeric('most_spirited_id')
			->allowEmptyString('most_spirited_id')

			;

		return $validator;
	}

	public function addValidation($spirit, League $league) {
		$validator = $this->validationDefault(new Validator());
		return $spirit->addValidation($validator, $league);
	}

	/**
	 * Returns a rules checker object that will be used for validating
	 * application integrity.
	 *
	 * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
	 * @return \Cake\ORM\RulesChecker
	 */
	public function buildRules(RulesChecker $rules): \Cake\ORM\RulesChecker {
		$rules->add($rules->existsIn(['created_team_id'], 'Teams'));
		$rules->add($rules->existsIn(['team_id'], 'Teams'));
		$rules->add($rules->existsIn(['game_id'], 'Games'));
		$rules->add($rules->existsIn(['person_id'], 'People'));
		return $rules;
	}

	public function division($id) {
		try {
			return $this->Games->division($this->field('game_id', ['SpiritEntries.id' => $id]));
		} catch (RecordNotFoundException|InvalidArgumentException $ex) {
			return null;
		}
	}

	public function team($id) {
		try {
			return $this->field('created_team_id', ['SpiritEntries.id' => $id]);
		} catch (RecordNotFoundException|InvalidArgumentException $ex) {
			return null;
		}
	}

	public static function compareSpirit($a,$b) {
		if (array_key_exists('entered_sotg', $a['summary'])) {
			if ($a['summary']['entered_sotg'] > $b['summary']['entered_sotg']) {
				return -1;
			} else if ($a['summary']['entered_sotg'] < $b['summary']['entered_sotg']) {
				return 1;
			}
		}
		if (array_key_exists('assigned_sotg', $a['summary'])) {
			if ($a['summary']['assigned_sotg'] > $b['summary']['assigned_sotg']) {
				return -1;
			} else if ($a['summary']['assigned_sotg'] < $b['summary']['assigned_sotg']) {
				return 1;
			}
		}
		if ($a['details']['name'] < $b['details']['name']) {
			return -1;
		} else if ($a['details']['name'] > $b['details']['name']) {
			return 1;
		}
		return 0;
	}

}
