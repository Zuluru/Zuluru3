<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * SpiritEntry Entity.
 *
 * @property int $id
 * @property int $created_team_id
 * @property int $team_id
 * @property int $game_id
 * @property int $person_id
 * @property int $entered_sotg
 * @property int $score_entry_penalty
 * @property int $q1
 * @property int $q2
 * @property int $q3
 * @property int $q4
 * @property int $q5
 * @property int $q6
 * @property int $q7
 * @property int $q8
 * @property int $q9
 * @property int $q10
 * @property string $comments
 * @property string $highlights
 * @property int $most_spirited_id
 *
 * @property \App\Model\Entity\Team $team
 * @property \App\Model\Entity\Game $game
 * @property \App\Model\Entity\Person $person
 * @property \App\Model\Entity\Person $most_spirited
 */
class SpiritEntry extends Entity {

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
