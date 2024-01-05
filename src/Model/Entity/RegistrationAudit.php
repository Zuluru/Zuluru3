<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * RegistrationAudit Entity.
 *
 * @property int $id
 * @property int $response_code
 * @property int $iso_code
 * @property string $date
 * @property string $time
 * @property string $transaction_id
 * @property string $approval_code
 * @property string $transaction_name
 * @property float $charge_total
 * @property string $cardholder
 * @property string $expiry
 * @property string $f4l4
 * @property string $card
 * @property string $message
 * @property string $issuer
 * @property string $issuer_invoice
 * @property string $issuer_confirmation
 * @property string $payment_plugin
 *
 * @property \App\Model\Entity\Payment[] $payments
 */
class RegistrationAudit extends Entity {

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

}
