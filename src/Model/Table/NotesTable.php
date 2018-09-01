<?php
namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * Notes Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Teams
 * @property \Cake\ORM\Association\BelongsTo $People
 * @property \Cake\ORM\Association\BelongsTo $Games
 * @property \Cake\ORM\Association\BelongsTo $Fields
 * @property \Cake\ORM\Association\BelongsTo $CreatedTeam
 * @property \Cake\ORM\Association\BelongsTo $CreatedPerson
 */
class NotesTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->table('notes');
		$this->displayField('id');
		$this->primaryKey('id');

		$this->addBehavior('Trim');
		$this->addBehavior('Timestamp');
		$this->addBehavior('Muffin/Footprint.Footprint', [
			'events' => [
				'Model.beforeSave' => [
					'created_person_id' => 'new',
				],
			],
			'propertiesMap' => [
				'created_person_id' => '_footprint.person.id',
			],
		]);

		$this->belongsTo('Teams', [
			'foreignKey' => 'team_id',
		]);
		$this->belongsTo('People', [
			'foreignKey' => 'person_id',
		]);
		$this->belongsTo('Games', [
			'foreignKey' => 'game_id',
		]);
		$this->belongsTo('Fields', [
			'foreignKey' => 'field_id',
		]);
		$this->belongsTo('CreatedTeam', [
			'className' => 'Teams',
			'foreignKey' => 'created_team_id',
		]);
		$this->belongsTo('CreatedPerson', [
			'className' => 'People',
			'foreignKey' => 'created_person_id',
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

			->numeric('visibility')
			->requirePresence('visibility', 'create')
			->notEmpty('visibility')

			->allowEmpty('note')

			;

		return $validator;
	}

}
