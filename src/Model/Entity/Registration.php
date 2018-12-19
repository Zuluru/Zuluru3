<?php
namespace App\Model\Entity;

use Cake\Core\Configure;
use Cake\ORM\Entity;

/**
 * Registration Entity.
 *
 * @property int $id
 * @property int $person_id
 * @property int $event_id
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property string $payment
 * @property string $notes
 * @property float $total_amount
 * @property int $price_id
 * @property float $deposit_amount
 * @property \Cake\I18n\FrozenTime $reservation_expires
 * @property bool $delete_on_expiry
 *
 * @property \App\Model\Entity\Person $person
 * @property \App\Model\Entity\Event $event
 * @property \App\Model\Entity\Price $price
 * @property \App\Model\Entity\Payment[] $payments
 * @property \App\Model\Entity\Response[] $responses
 *
 * @property string $long_description
 * @property float $total_payment
 * @property float $balance
 */
class Registration extends Entity {

	/**
	 * Fields that can be mass assigned using newEntity() or patchEntity().
	 *
	 * Note that when '*' is set to true, this allows all unspecified fields to
	 * be mass assigned. For security purposes, it is advised to set '*' to false
	 * (or remove it), and explicitly make individual fields accessible as needed.
	 *
	 * @var array
	 */
	protected $_accessible = [
		'*' => true,
		'id' => false,
	];

	public function paymentAmounts() {
		$current_total = $this->price->total;
		if ($current_total == 0 || $this->price->online_payment_option == ONLINE_NO_PAYMENT) {
			return [0,0,0];
		}

		// NOTE: These are not the tax *rates*, but the percentage of the *with tax total* that each tax represents
		$tax1_percent = $this->price->tax1 / $current_total;
		$tax2_percent = $this->price->tax2 / $current_total;

		$total = $this->total_amount;

		if ($this->deposit_amount > 0 && in_array($this->payment, Configure::read('registration_none_paid'))) {
			// Payment amount is the deposit to be paid
			$payment = $this->deposit_amount;
		} else {
			// Break apart the outstanding amount
			$payment = $total - $this->total_payment;
		}

		$tax1 = round($payment * $tax1_percent, 2);
		$tax2 = round($payment * $tax2_percent, 2);
		$cost = round($payment - $tax1 - $tax2, 2);

		return [$cost, $tax1, $tax2];
	}

	protected function _getLongDescription() {
		$name = $this->event->name;
		$extras = [];
		if (!empty($this->price->name)) {
			$extras[] = $this->price->name;
		}

		if ($this->deposit_amount > 0) {
			if (in_array($this->payment, Configure::read('registration_none_paid'))) {
				$extras[] = __('Deposit');
			} else {
				$extras[] = __('Remaining balance');
			}
		}

		if (!empty($extras)) {
			$name .= ' (' . implode(', ', $extras) . ')';
		}
		return $name;
	}

	protected function _getTotalPayment() {
		return round(array_sum(collection($this->payments)->extract('payment_amount')->toArray()), 2);
	}

	protected function _getBalance() {
		return round($this->total_amount - $this->total_payment, 2);
	}

}
