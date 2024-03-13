<?php
namespace App\Model\Table;

use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use Cake\Core\Configure;
use App\Model\Rule\InConfigRule;

/**
 * Fields Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Facilities
 * @property \Cake\ORM\Association\HasMany $GameSlots
 * @property \Cake\ORM\Association\HasMany $Notes
 */
class FieldsTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->setTable('fields');
		$this->setDisplayField('id');
		$this->setPrimaryKey('id');

		$this->addBehavior('Trim');
		$this->addBehavior('Translate', ['fields' => ['num']]);

		$this->belongsTo('Facilities', [
			'foreignKey' => 'facility_id',
		]);

		$this->hasMany('GameSlots', [
			'foreignKey' => 'field_id',
		]);
		$this->hasMany('Notes', [
			'foreignKey' => 'field_id',
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

			// validation will allow empty numbers; rules will limit this
			->allowEmpty('num')

			->boolean('is_open')
			->notEmpty('is_open')

			->boolean('indoor')
			->notEmpty('indoor')

			->notEmpty('rating', __('Select a rating from the list.'))

			->latitude('latitude')
			->allowEmpty('latitude')

			->longitude('longitude')
			->allowEmpty('longitude')

			->numeric('angle')
			->allowEmpty('angle')

			->numeric('length')
			->allowEmpty('length')

			->numeric('width')
			->allowEmpty('width')

			->numeric('zoom')
			->allowEmpty('zoom')

			->url('layout_url', __('Must be a valid URL, if specified'))
			->requirePresence('layout_url', false)
			->allowEmpty('layout_url')

			->requirePresence('surface', 'create')
			->notEmpty('surface', __('Select a playing surface from the list.'))

			->requirePresence('sport', 'create')
			->notEmpty('sport', __('Select a sport from the list.'))

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
		$rules->add($rules->existsIn(['facility_id'], 'Facilities', __('You must select a valid facility.')));

		$rules->add(function (EntityInterface $entity, array $options) {
			if (array_key_exists('fields', $options)) {
				$fields = count($options['fields']);
			} else if ($entity->has('facility') && $entity->facility->has('fields')) {
				$fields = count($entity->facility->fields);
			} else {
				$fields = $this->find()->where(['Fields.facility_id' => $entity->facility_id]);
				if (!$entity->isNew()) {
					$fields->andWhere(['Fields.id !=' => $entity->id]);
				}
				$fields = $fields->count() + 1;
			}

			if ($fields <= 1) {
				return true;
			}
			return !empty($entity->num);
		}, 'validNumber', [
			'errorField' => 'num',
			'message' => __('{0} numbers can only be blank if there is a single {1} at the facility.', Configure::read('UI.field_cap'), Configure::read('UI.field')),
		]);

		$rules->add(new InConfigRule('options.field_rating'), 'validRating', [
			'errorField' => 'rating',
			'message' => __('Select a rating from the list.'),
		]);

		$rules->add(new InConfigRule('options.surface'), 'validSurface', [
			'errorField' => 'surface',
			'message' => __('Select a playing surface from the list.'),
		]);

		$rules->add(new InConfigRule('options.sport'), 'validSport', [
			'errorField' => 'sport',
			'message' => __('Select a sport from the list.'),
		]);

		$rules->addDelete(function ($entity, $options) {
			// Don't delete the last field at a facility
			if (count($entity->facility->fields) < 2) {
				return __('You cannot delete the only {0} at a facility.', Configure::read('UI.field'));
			}
			return true;
		}, 'last', ['errorField' => 'delete']);

		return $rules;
	}

	public function affiliate($id) {
		try {
			return $this->Facilities->affiliate($this->field('facility_id', ['Fields.id' => $id]));
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}

	public function sport($id) {
		try {
			return $this->field('sport', ['Fields.id' => $id]);
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}
}
