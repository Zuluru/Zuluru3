<?php
namespace App\Model\Table;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use InvalidArgumentException;

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
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('notes');
		$this->setDisplayField('id');
		$this->setPrimaryKey('id');

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
	public function validationDefault(Validator $validator): \Cake\Validation\Validator {
		$validator
			->numeric('id')
			->allowEmptyString('id', null, 'create')

			->numeric('visibility')
			->requirePresence('visibility', 'create')
			->notEmptyString('visibility')

			->allowEmptyString('note')

			;

		return $validator;
	}

	// TODO: Add rules to check the visibility against valid options per the game/team/person and identity

	public function affiliate($id) {
		try {
			$note = $this->get($id);
			if ($note->game_id) {
				return $this->Games->affiliate($note->game_id);
			} else if ($note->team_id) {
				return $this->Teams->affiliate($note->team_id);
			} else if ($note->field_id) {
				return $this->Fields->affiliate($note->field_id);
			}
			throw new InvalidArgumentException('Note does not have a valid record associated.');
		} catch (RecordNotFoundException|InvalidArgumentException $ex) {
			return null;
		}
	}

	public function division($id) {
		try {
			$note = $this->get($id);
			if ($note->game_id) {
				return $this->Games->division($note->game_id);
			} else if ($note->team_id) {
				return $this->Teams->division($note->team_id);
			}
			throw new InvalidArgumentException('Note does not have a valid record associated.');
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}

}
