<?php
namespace App\Model\Entity;

use App\Model\Traits\TranslateFieldTrait;
use Cake\ORM\Behavior\Translate\TranslateTrait;
use Cake\ORM\Entity;

/**
 * Holiday Entity.
 *
 * @property int $id
 * @property \Cake\I18n\FrozenTime $date
 * @property string $name
 * @property int $affiliate_id
 *
 * @property \App\Model\Entity\Affiliate $affiliate
 *
 * @property string $date_string
 */
class Holiday extends Entity {

	use TranslateTrait;
	use TranslateFieldTrait;

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

	protected function _getDateString() {
		return $this->date->toDateString();
	}
}
