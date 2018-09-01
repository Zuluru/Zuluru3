<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * LeaguesStatType Entity.
 *
 * @property int $league_id
 * @property int $stat_type_id
 * @property int $id
 *
 * @property \App\Model\Entity\League $league
 * @property \App\Model\Entity\StatType $stat_type
 */
class LeaguesStatType extends Entity {

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
