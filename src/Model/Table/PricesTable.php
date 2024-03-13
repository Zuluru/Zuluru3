<?php
namespace App\Model\Table;

use ArrayObject;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event as CakeEvent;
use Cake\ORM\Entity;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use App\Model\Rule\GreaterDateRule;
use App\Model\Rule\InConfigRule;
use App\Model\Rule\InDateConfigRule;
use App\Model\Rule\RuleSyntaxRule;

/**
 * Prices Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Events
 * @property \Cake\ORM\Association\HasMany $Registrations
 */
class PricesTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->setTable('prices');
		$this->setDisplayField('name');
		$this->setPrimaryKey('id');

		$this->addBehavior('Trim');
		$this->addBehavior('Translate', ['fields' => ['name', 'description']]);

		$this->belongsTo('Events', [
			'foreignKey' => 'event_id',
			'joinType' => 'INNER',
		]);

		$this->hasMany('Registrations', [
			'foreignKey' => 'price_id',
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

			// validation will allow empty names; rules will limit this
			->allowEmpty('name')

			->allowEmpty('description')

			->numeric('cost', __('You must enter a valid cost.'))
			->notEmpty('cost', __('You must enter a valid cost.'))

			->numeric('tax1', __('You must enter a valid tax amount.'))
			->allowEmpty('tax1', function () { return !Configure::read('payment.tax1_enable'); })

			->numeric('tax2', __('You must enter a valid tax amount.'))
			->allowEmpty('tax2', function () { return !Configure::read('payment.tax2_enable'); })

			->dateTime('open', __('You must select a valid opening date.'))
			->requirePresence('open', 'create', __('You must select a valid opening date.'))
			->notEmpty('open', __('You must select a valid opening date.'))

			->dateTime('close', __('You must select a valid closing date.'))
			->requirePresence('close', 'create', __('You must select a valid closing date.'))
			->notEmpty('close', __('You must select a valid closing date.'))

			->allowEmpty('register_rule')

			->boolean('allow_late_payment', __('You must select whether or not payment will be accepted after the close date.'))
			->requirePresence('allow_late_payment', 'create')
			->allowEmpty('allow_late_payment')

			->numeric('minimum_deposit', __('You must enter a valid deposit amount.'))
			->requirePresence('minimum_deposit', function ($context) {
				return Configure::read('registration.online_payments') && in_array($context['data']['online_payment_option'], [ONLINE_MINIMUM_DEPOSIT, ONLINE_SPECIFIC_DEPOSIT, ONLINE_DEPOSIT_ONLY]);
			})
			->allowEmpty('minimum_deposit')

			->boolean('allow_reservations', __('You must select whether reservations are allowed.'))
			->requirePresence('allow_reservations', 'create')
			->allowEmpty('allow_reservations')

			->numeric('reservation_duration', __('You must enter a valid reservation duration.'))
			->requirePresence('reservation_duration', function ($context) { return !empty($context['data']['allow_reservations']); })
			->allowEmpty('reservation_duration')

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
		$rules->add($rules->existsIn(['event_id'], 'Events', __('You must select a valid event.')));

		$rules->add(function (EntityInterface $entity, array $options) {
			if (array_key_exists('prices', $options)) {
				$prices = count($options['prices']);
			} else if ($entity->has('event') && $entity->event->has('prices')) {
				$prices = count($entity->event->prices);
			} else {
				$prices = $this->find()->where(['Prices.event_id' => $entity->event_id]);
				if (!$entity->isNew()) {
					$prices->andWhere(['Prices.id !=' => $entity->id]);
				}
				$prices = $prices->count() + 1;
			}

			if ($prices <= 1) {
				return true;
			}
			return !empty($entity->name);
		}, 'validName', [
			'errorField' => 'name',
			'message' => __('Price names can only be blank if there is a single price for the event.'),
		]);

		$rules->addCreate(new InDateConfigRule('event'), 'rangeOpenDate', [
			'errorField' => 'open',
			'message' => __('Price point open date must be between last year and next year.'),
		]);

		$rules->addCreate(new InDateConfigRule('event'), 'rangeCloseDate', [
			'errorField' => 'close',
			'message' => __('Price point close date must be between last year and next year.'),
		]);

		$rules->add(new GreaterDateRule('open'), 'greaterCloseDate', [
			'errorField' => 'close',
			'message' => __('The price point close date cannot be before the open date.'),
		]);

		if (Configure::read('registration.online_payments')) {
			$rules->add(new InConfigRule('options.online_payment'), 'validOnlinePayment', [
				'errorField' => 'online_payment_option',
				'message' => __('You must select a valid online payment option.'),
			]);
		}

		$rules->add(new RuleSyntaxRule(), 'validRule', [
			'errorField' => 'register_rule',
			'message' => __('There is an error in the rule syntax.'),
		]);

		$rules->addDelete(function ($entity, $options) {
			// Don't delete the last price on an event
			if (count($entity->event->prices) < 2) {
				return __('You cannot delete the only price point on an event.');
			}
			return true;
		}, 'last', ['errorField' => 'delete']);

		return $rules;
	}

	/**
	 * Updates data before trying to update the entity.
	 *
	 * @param CakeEvent $cakeEvent Unused
	 * @param ArrayObject $data The data record being patched in
	 * @param ArrayObject $options The options passed to the patchEntity method
	 */
	public function beforeMarshal(CakeEvent $cakeEvent, ArrayObject $data, ArrayObject $options) {
		// If online payment option indicates that the minimum deposit should be zero, make sure it's so
		if ($data->offsetExists('online_payment_option') &&
			in_array($data['online_payment_option'], [ONLINE_FULL_PAYMENT, ONLINE_NO_MINIMUM, ONLINE_NO_PAYMENT])
		) {
			$data['minimum_deposit'] = 0;
		}
	}

	/**
	 * Perform additional operations after it is saved.
	 *
	 * @param \Cake\Event\Event $cakeEvent The afterSave event that was fired
	 * @param \Cake\Datasource\EntityInterface $entity The entity that was saved
	 * @param \ArrayObject $options The options passed to the save method
	 * @return void
	 */
	public function afterSave(CakeEvent $cakeEvent, EntityInterface $entity, ArrayObject $options) {
		// Update this price's event open and close dates, if required
		$event = $this->Events->get($entity->event_id, [
			'contain' => ['Prices']
		]);

		$open = min(collection($event->prices)->extract('open')->toArray());
		if ($open != $event->open) {
			$event->open = $open;
		}
		$close = max(collection($event->prices)->extract('close')->toArray());
		if ($close != $event->close) {
			$event->close = $close;
		}
		$this->Events->save($event);
	}

	public function event($id) {
		try {
			return $this->field('event_id', ['Prices.id' => $id]);
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}

	public function affiliate($id) {
		try {
			return $this->Events->affiliate($this->event($id));
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}

	public static function duration($duration) {
		$ret = [];

		$days = floor($duration / 1440);
		if ($days) {
			$duration -= $days * 1440;
			$ret[] = $days . ' ' . __n('day', 'days', $days);
		}

		$hours = floor($duration / 60);
		if ($hours) {
			$duration -= $hours * 60;
			$ret[] = $hours . ' ' . __n('hour', 'hours', $hours);
		}

		if ($duration || empty($ret)) {
			$ret[] = $duration . ' ' . __n('minute', 'minutes', $duration);
		}

		return implode(', ', $ret);
	}
}
