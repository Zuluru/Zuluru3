<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ScoreDetail Entity.
 *
 * @property int $id
 * @property int $game_id
 * @property int $team_id
 * @property int $created_team_id
 * @property int $score_from
 * @property string $play
 * @property int $points
 * @property \Cake\I18n\FrozenTime $created
 *
 * @property \App\Model\Entity\Game $game
 * @property \App\Model\Entity\Team $team
 * @property \App\Model\Entity\ScoreDetailStat[] $score_detail_stats
 */
class ScoreDetail extends Entity {

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
