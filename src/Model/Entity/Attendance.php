<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Attendance Entity.
 *
 * @property int $id
 * @property int $team_id
 * @property \Cake\I18n\FrozenTime $game_date
 * @property int $game_id
 * @property int $team_event_id
 * @property int $person_id
 * @property int $status
 * @property string $comment
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Team $team
 * @property \App\Model\Entity\Game $game
 * @property \App\Model\Entity\TeamEvent $team_event
 * @property \App\Model\Entity\Person $person
 */
class Attendance extends Entity {

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
