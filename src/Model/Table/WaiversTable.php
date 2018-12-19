<?php
namespace App\Model\Table;

use ArrayObject;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event as CakeEvent;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use App\Model\Rule\InConfigRule;

/**
 * Waivers Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Affiliates
 * @property \Cake\ORM\Association\BelongsToMany $People
 */
class WaiversTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->table('waivers');
		$this->displayField('name');
		$this->primaryKey('id');

		$this->addBehavior('Trim');

		$this->belongsTo('Affiliates', [
			'foreignKey' => 'affiliate_id',
			'joinType' => 'INNER',
		]);

		$this->belongsToMany('People', [
			'foreignKey' => 'waiver_id',
			'targetForeignKey' => 'person_id',
			'joinTable' => 'waivers_people',
			'saveStrategy' => 'append',
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

			->requirePresence('name', 'create', __('The name cannot be blank.'))
			->notEmpty('name', __('The name cannot be blank.'))

			->requirePresence('description', 'create')
			->notEmpty('description')

			->requirePresence('text', 'create', __('Waiver text cannot be blank.'))
			->notEmpty('text', __('Waiver text cannot be blank.'))

			->boolean('active')
			->requirePresence('active', 'create')
			->notEmpty('active')

			->requirePresence('expiry_type', 'create')
			->notEmpty('expiry_type')

			->numeric('start_month')
			->allowEmpty('start_month')

			->numeric('start_day')
			->allowEmpty('start_day')

			->numeric('end_month')
			->allowEmpty('end_month')

			->numeric('end_day')
			->allowEmpty('end_day')

			->numeric('duration')
			->allowEmpty('duration')

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
		$rules->add($rules->existsIn(['affiliate_id'], 'Affiliates', __('You must select a valid affiliate.')));

		$rules->add(new InConfigRule('options.waivers.expiry_type'), 'validExpiryType', [
			'errorField' => 'expiry_type',
			'message' => __('You must select a valid expiry type.'),
		]);

		return $rules;
	}

	/**
	 * Deal with array structure of incoming data
	 *
	 * @param CakeEvent $cakeEvent Unused
	 * @param ArrayObject $data The data record being patched in
	 * @param ArrayObject $options The options passed to the new/patchEntity method
	 */
	public function beforeMarshal(CakeEvent $cakeEvent, ArrayObject $data, ArrayObject $options) {
		foreach (['start_month' => 'month', 'start_day' => 'day', 'end_month' => 'month', 'end_day' => 'day'] as $field => $type) {
			if ($data->offsetExists($field) && is_array($data[$field]) && array_key_exists($type, $data[$field])) {
				$data[$field] = $data[$field][$type];
			}
		}
	}

	public function findActive(Query $query, Array $options) {
		$query->where(['Waivers.active' => true]);

		if (!empty($options['affiliates'])) {
			$query->andWhere(['Waivers.affiliate_id IN' => $options['affiliates']]);
		}

		return $query;
	}

	public static function signed($waivers, $date) {
		return collection($waivers)->some(function ($waiver) use ($date) {
			return $waiver->_joinData->valid_from <= $date && $waiver->_joinData->valid_until >= $date;
		});
	}

	public function affiliate($id) {
		try {
			return $this->field('affiliate_id', ['id' => $id]);
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}
}
