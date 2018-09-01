<?php
namespace App\Model\Entity;

use Cake\Core\Configure;
use Cake\ORM\Entity;

/**
 * Price Entity.
 *
 * @property int $id
 * @property int $event_id
 * @property string $name
 * @property string $description
 * @property float $cost
 * @property float $tax1
 * @property float $tax2
 * @property \Cake\I18n\FrozenTime $open
 * @property \Cake\I18n\FrozenTime $close
 * @property string $register_rule
 * @property float $minimum_deposit
 * @property bool $allow_late_payment
 * @property int $online_payment_option
 * @property bool $allow_reservations
 * @property int $reservation_duration
 *
 * @property \App\Model\Entity\Event $event
 * @property \App\Model\Entity\Registration[] $registrations
 *
 * @property float $total
 * @property bool $allow_deposit
 * @property float $fixed_deposit
 * @property bool $deposit_only
 */
class Price extends Entity {

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

	/**
	 * Calculate the total cost of this price point, based on which taxes are enabled.
	 *
	 * @return float Total cost
	 */
	protected function _getTotal() {
		$total = $this->cost;
		if (Configure::read('payment.tax1_enable')) {
			$total += $this->tax1;
		}
		if (Configure::read('payment.tax2_enable')) {
			$total += $this->tax2;
		}
		return round($total, 2);
	}

	/**
	 * Extract the old allow_deposit boolean from the new combined options values
	 *
	 * @return bool
	 */
	protected function _getAllowDeposit() {
		return $this->online_payment_option != ONLINE_FULL_PAYMENT;
	}

	/**
	 * Extract the old fixed_deposit boolean from the new combined options values
	 *
	 * @return bool
	 */
	protected function _getFixedDeposit() {
		return in_array($this->online_payment_option, [ONLINE_SPECIFIC_DEPOSIT, ONLINE_DEPOSIT_ONLY, ONLINE_NO_PAYMENT]);
	}

	/**
	 * Extract the old deposit_only boolean from the new combined options values
	 *
	 * @return bool
	 */
	protected function _getDepositOnly() {
		return in_array($this->online_payment_option, [ONLINE_DEPOSIT_ONLY, ONLINE_NO_PAYMENT]);
	}

}
