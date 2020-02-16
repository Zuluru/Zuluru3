<?php
namespace App\Model\Table;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use App\Model\Rule\InConfigRule;

/**
 * Incidents Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Games
 * @property \Cake\ORM\Association\BelongsTo $Teams
 */
class IncidentsTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->setTable('incidents');
		$this->setDisplayField('id');
		$this->setPrimaryKey('id');

		$this->addBehavior('Trim');

		$this->belongsTo('Games', [
			'foreignKey' => 'game_id',
			'joinType' => 'INNER',
		]);
		$this->belongsTo('Teams', [
			'foreignKey' => 'team_id',
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

			->requirePresence('type', 'create', __('Select the incident type'))
			->notEmpty('type', __('Select the incident type'))

			->requirePresence('details', 'create', __('Provide all relevant details of the incident'))
			->notEmpty('details', __('Provide all relevant details of the incident'))

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
		$rules->add(new InConfigRule('options.incident_types'), 'validIncidentType', [
			'errorField' => 'type',
			'message' => __('Select the incident type'),
		]);

		return $rules;
	}

	public function division($id) {
		try {
			return $this->Games->division($this->field('game_id', ['Incidents.id' => $id]));
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}

	public function team($id) {
		try {
			return $this->field('team_id', ['Incidents.id' => $id]);
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}

}
