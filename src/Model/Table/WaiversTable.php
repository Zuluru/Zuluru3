<?php
namespace App\Model\Table;

use App\Model\Entity\WaiversPerson;
use ArrayObject;
use Cake\Chronos\ChronosInterface;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event as CakeEvent;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use App\Model\Rule\InConfigRule;
use InvalidArgumentException;

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
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('waivers');
		$this->setDisplayField('name');
		$this->setPrimaryKey('id');

		$this->addBehavior('Trim');
		$this->addBehavior('Translate', [
			'strategyClass' => \Cake\ORM\Behavior\Translate\ShadowTableStrategy::class,
			'fields' => ['name', 'description', 'text'],
		]);

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
	public function validationDefault(Validator $validator): \Cake\Validation\Validator {
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
	public function buildRules(RulesChecker $rules): \Cake\ORM\RulesChecker {
		$rules->add($rules->existsIn(['affiliate_id'], 'Affiliates', __('You must select a valid affiliate.')));

		$rules->add(new InConfigRule('options.waivers.expiry_type'), 'validExpiryType', [
			'errorField' => 'expiry_type',
			'message' => __('You must select a valid expiry type.'),
		]);

		$rules->add(function (EntityInterface $entity, array $options) {
			if (!$entity->start_month) {
				return __('Invalid start day.');
			}
			$date = FrozenDate::create(2001, $entity->start_month);
			if ($entity->start_day < 1 || $entity->start_day > $date->lastOfMonth()->day) {
				return __('Invalid start day.');
			}

			return true;
		}, 'valid', [
			'errorField' => 'start_day',
		]);

		$rules->add(function (EntityInterface $entity, array $options) {
			if (!$entity->end_month) {
				return __('Invalid end day.');
			}
			$date = FrozenDate::create(2001, $entity->end_month);
			if ($entity->end_day < 1 || $entity->end_day > $date->lastOfMonth()->day) {
				return __('Invalid end day.');
			}

			return true;
		}, 'valid', [
			'errorField' => 'end_day',
		]);

		return $rules;
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
		} catch (RecordNotFoundException|InvalidArgumentException $ex) {
			return null;
		}
	}
}
