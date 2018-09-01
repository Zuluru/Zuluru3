<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ScoreEntry Entity.
 *
 * @property int $id
 * @property int $team_id
 * @property int $game_id
 * @property int $person_id
 * @property int $score_for
 * @property int $score_against
 * @property \Cake\I18n\FrozenTime $created
 * @property string $status
 * @property \Cake\I18n\FrozenTime $modified
 * @property int $home_carbon_flip
 * @property string $gender_ratio
 *
 * @property \App\Model\Entity\Team $team
 * @property \App\Model\Entity\Game $game
 * @property \App\Model\Entity\Person $person
 * @property \App\Model\Entity\Person[] $allstars
 */
class ScoreEntry extends Entity {

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
