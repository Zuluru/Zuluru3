<?php
namespace App\Model\Entity;

use App\Model\Traits\TranslateFieldTrait;
use Cake\ORM\Behavior\Translate\TranslateTrait;
use Cake\ORM\Entity;

/**
 * StatType Entity.
 *
 * @property int $id
 * @property string $sport
 * @property string $positions
 * @property string $name
 * @property string $abbr
 * @property string $internal_name
 * @property int $sort
 * @property string $class
 * @property string $type
 * @property string $base
 * @property string $handler
 * @property string $sum_function
 * @property string $formatter_function
 * @property string $validation
 *
 * @property \App\Model\Entity\ScoreDetailStat[] $score_detail_stats
 * @property \App\Model\Entity\Stat[] $stats
 * @property \App\Model\Entity\League[] $leagues
 */
class StatType extends Entity {

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
