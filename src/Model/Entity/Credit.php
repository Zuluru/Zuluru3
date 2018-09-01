<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Credit Entity.
 *
 * @property int $id
 * @property int $affiliate_id
 * @property int $person_id
 * @property float $amount
 * @property float $amount_used
 * @property string $notes
 * @property \Cake\I18n\FrozenTime $created
 * @property int $created_person_id
 *
 * @property \App\Model\Entity\Affiliate $affiliate
 * @property \App\Model\Entity\Person $person
 *
 * @property float $balance
 */
class Credit extends Entity {

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

	protected function _getBalance() {
		return round($this->amount - $this->amount_used, 2);
	}

}
