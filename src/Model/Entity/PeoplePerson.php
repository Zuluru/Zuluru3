<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * PeoplePerson Entity.
 * person_id is the one that has control, relative_id is the one that can be controlled.
 * TODO: Better names for these that make the distinction clearer. Also rename act_as and control...
 *
 * @property int $id
 * @property int $person_id
 * @property int $relative_id
 * @property bool $approved
 * @property \Cake\I18n\FrozenTime $created
 *
 * @property \App\Model\Entity\Person $person
 * @property \App\Model\Entity\Person $relative
 */
class PeoplePerson extends Entity {

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
