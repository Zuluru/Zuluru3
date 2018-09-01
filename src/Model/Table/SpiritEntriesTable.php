<?php
namespace App\Model\Table;

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
	public function initialize(array $config) {
		parent::initialize($config);

		$this->table('spirit_entries');
		$this->displayField('id');
		$this->primaryKey('id');

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
	public function validationDefault(Validator $validator) {
		$validator
			->numeric('id')
			->allowEmpty('id', 'create')

			->numeric('score_entry_penalty')
			->notEmpty('score_entry_penalty')

			->allowEmpty('comments')

			->allowEmpty('highlights')

			// TODOLATER: Require based on league setting? Do in SpiritModule? Any other similar fields?
			->numeric('most_spirited_id')
			->allowEmpty('most_spirited_id')

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
	public function buildRules(RulesChecker $rules) {
		$rules->add($rules->existsIn(['created_team_id'], 'Teams'));
		$rules->add($rules->existsIn(['team_id'], 'Teams'));
		$rules->add($rules->existsIn(['game_id'], 'Games'));
		$rules->add($rules->existsIn(['person_id'], 'People'));
		return $rules;
	}

}
