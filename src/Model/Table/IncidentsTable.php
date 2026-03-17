<?php
namespace App\Model\Table;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event as CakeEvent;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use App\Model\Rule\InConfigRule;
use InvalidArgumentException;

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
	public function initialize(array $config): void {
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
	public function validationDefault(Validator $validator): \Cake\Validation\Validator {
		$validator
			->numeric('id')
			->allowEmptyString('id', null, 'create')

			->requirePresence('type', 'create', __('Select the incident type'))
			->notEmptyString('type', __('Select the incident type'))

			->requirePresence('details', 'create', __('Provide all relevant details of the incident'))
			->notEmptyString('details', __('Provide all relevant details of the incident'))

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
	public function buildRules(RulesChecker $rules): \Cake\ORM\RulesChecker {
		$rules->add(new InConfigRule('options.incident_types'), 'validIncidentType', [
			'errorField' => 'type',
			'message' => __('Select the incident type'),
		]);

		return $rules;
	}

	/**
	 * Perform additional operations after it is saved.
	 *
	 * @param \Cake\Event\Event $cakeEvent The afterSave event that was fired
	 * @param \Cake\Datasource\EntityInterface $entity The entity that was saved
	 * @param \ArrayObject $options The options passed to the save method
	 * @return void
	 */
	public function afterSave(\Cake\Event\EventInterface $cakeEvent, EntityInterface $entity, ArrayObject $options) {
		if ($entity->isDirty('details') || $entity->isDirty('type')) {
			$game = $options['game'];
			if ($game->has('game_slot')) {
				$gameSlot = $game->game_slot;
			} else {
				$gameSlot = $this->Games->GameSlots->get($game->game_slot_id, ['contain' => ['Fields' => 'Facilities']]);
			}
			$event = new CakeEvent('Model.Game.incidentReport', $this, [$entity, $game, $gameSlot]);
			$this->getEventManager()->dispatch($event);
		}
	}

	public function division($id) {
		try {
			return $this->Games->division($this->field('game_id', ['Incidents.id' => $id]));
		} catch (RecordNotFoundException|InvalidArgumentException $ex) {
			return null;
		}
	}

	public function team($id) {
		try {
			return $this->field('team_id', [$this->aliasField('id') => $id]);
		} catch (RecordNotFoundException|InvalidArgumentException $ex) {
			return null;
		}
	}

}
