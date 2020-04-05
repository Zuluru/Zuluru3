<?php
namespace App\Model\Entity;

use App\Model\Traits\TranslateFieldTrait;
use Cake\ORM\Behavior\Translate\TranslateTrait;
use Cake\ORM\Entity;

/**
 * Pool Entity.
 *
 * @property int $id
 * @property int $division_id
 * @property int $stage
 * @property string $name
 * @property string $type
 *
 * @property \App\Model\Entity\Division $division
 * @property \App\Model\Entity\PoolsTeam[] $pools_teams
 * @property \App\Model\Entity\Game[] $games
 * @property \App\Model\Entity\Team[] $teams
 */
class Pool extends Entity {

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
