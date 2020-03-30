<?php
namespace App\Model\Entity;

use App\Model\Traits\TranslateFieldTrait;
use Cake\ORM\Behavior\Translate\TranslateTrait;
use Cake\ORM\Entity;

/**
 * Notice Entity.
 *
 * @property int $id
 * @property string $display_to
 * @property string $repeat_on
 * @property string $notice
 * @property bool $active
 * @property \Cake\I18n\FrozenTime $effective_date
 * @property \Cake\I18n\FrozenTime $created
 *
 * @property \App\Model\Entity\Person[] $people
 */
class Notice extends Entity {

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

}
