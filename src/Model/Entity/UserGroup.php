<?php
namespace App\Model\Entity;

use App\Model\Traits\TranslateFieldTrait;
use Cake\ORM\Behavior\Translate\TranslateTrait;
use Cake\ORM\Entity;

/**
 * Group Entity.
 *
 * @property int $id
 * @property string $name
 * @property bool $active
 * @property int $level
 * @property string $description
 *
 * @property \App\Model\Entity\Person[] $people
 *
 * @property string $long_name
 */
class UserGroup extends Entity {

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

	protected function _getLongName() {
		if (!empty($this->description)) {
			return "{$this->name}: {$this->description}";
		} else {
			return $this->name;
		}
	}

}
