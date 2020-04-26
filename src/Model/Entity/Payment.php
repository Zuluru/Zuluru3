<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Payment Entity.
 *
 * @property int $id
 * @property int $registration_id
 * @property int $registration_audit_id
 * @property string $payment_type
 * @property float $payment_amount
 * @property float $refunded_amount
 * @property string $notes
 * @property \Cake\I18n\FrozenTime $created
 * @property int $created_person_id
 * @property int $updated_person_id
 * @property string $payment_method
 *
 * @property \App\Model\Entity\Registration $registration
 * @property \App\Model\Entity\RegistrationAudit $registration_audit
 * @property \App\Model\Entity\Payment $payment
 * @property \App\Model\Entity\Credit[] $credits
 *
 * @property float $paid
 */
class Payment extends Entity {

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

	protected function _getPaid() {
		return round($this->payment_amount - $this->refunded_amount, 2);
	}

}
