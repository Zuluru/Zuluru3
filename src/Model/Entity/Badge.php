<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Badge Entity.
 *
 * @property int $id
 * @property int $affiliate_id
 * @property string $name
 * @property string $description
 * @property string $category
 * @property string $handler
 * @property bool $active
 * @property int $visibility
 * @property string $icon
 * @property int $refresh_from
 *
 * @property \App\Model\Entity\Affiliate $affiliate
 * @property \App\Model\Entity\Person[] $people
 */
class Badge extends Entity {

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
