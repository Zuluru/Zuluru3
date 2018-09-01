<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ScoreDetailStat Entity.
 *
 * @property int $id
 * @property int $score_detail_id
 * @property int $person_id
 * @property int $stat_type_id
 *
 * @property \App\Model\Entity\ScoreDetail $score_detail
 * @property \App\Model\Entity\Person $person
 * @property \App\Model\Entity\StatType $stat_type
 */
class ScoreDetailStat extends Entity {

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
