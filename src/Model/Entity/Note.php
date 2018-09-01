<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Note Entity.
 *
 * @property int $id
 * @property int $team_id
 * @property int $person_id
 * @property int $game_id
 * @property int $field_id
 * @property int $visibility
 * @property int $created_team_id
 * @property int $created_person_id
 * @property string $note
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Team $team
 * @property \App\Model\Entity\Person $person
 * @property \App\Model\Entity\Game $game
 * @property \App\Model\Entity\Field $field
 * @property \App\Model\Entity\Team $created_team
 * @property \App\Model\Entity\Person $created_person
 */
class Note extends Entity {

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
