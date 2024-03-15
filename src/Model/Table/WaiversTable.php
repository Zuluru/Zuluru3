<?php
namespace App\Model\Table;

use App\Model\Entity\WaiversPerson;
use ArrayObject;
use Cake\Chronos\ChronosInterface;
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

		$this->setTable('waivers');
		$this->setDisplayField('name');
		$this->setPrimaryKey('id');

		$this->addBehavior('Trim');
		$this->addBehavior('Translate', ['fields' => ['name', 'description', 'text']]);

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
			->allowEmptyString('id', null, 'create')

			->requirePresence('name', 'create', __('The name cannot be blank.'))
			->notEmptyString('name', __('The name cannot be blank.'))

			->requirePresence('description', 'create')
			->notEmptyString('description')

			->requirePresence('text', 'create', __('Waiver text cannot be blank.'))
			->notEmptyString('text', __('Waiver text cannot be blank.'))

			->boolean('active')
			->requirePresence('active', 'create')
			->notEmptyString('active')

			->requirePresence('expiry_type', 'create')
			->notEmptyString('expiry_type')

			->numeric('start_month')
			->allowEmptyString('start_month')

			->numeric('start_day')
			->allowEmptyString('start_day')

			->numeric('end_month')
			->allowEmptyString('end_month')

			->numeric('end_day')
			->allowEmptyString('end_day')

			->numeric('duration')
			->allowEmptyString('duration')

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

	public function findActive(Query $query, array $options) {
		$query->where(['Waivers.active' => true]);

		if (!empty($options['affiliates'])) {
			$query->andWhere(['Waivers.affiliate_id IN' => $options['affiliates']]);
		}

		return $query;
	}

	public static function signed(array $waivers, ChronosInterface $date) {
		// TODO: Assumption here is that only the relevant waiver is loaded. That's probably not ideal.
		return collection($waivers)->some(function (WaiversPerson $waiver) use ($date) {
			return $waiver->valid_from <= $date && $waiver->valid_until >= $date;
		});
	}

	public function affiliate($id) {
		try {
			return $this->field('affiliate_id', ['Waivers.id' => $id]);
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}
}
