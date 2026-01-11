<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * SubRequest Entity
 *
 * @property int $id
 * @property int $captain_id
 * @property int $team_id
 * @property \Cake\I18n\FrozenDate|null $game_date
 * @property int $person_id
 *
 * @property \App\Model\Entity\Person $captain
 * @property \App\Model\Entity\Team $team
 * @property \App\Model\Entity\Person $person
 */
class SubRequest extends Entity
{
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
