<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * GamesOfficial Entity
 *
 * @property int $id
 * @property int $game_id
 * @property int|null $official_id
 * @property int|null $team_id
 *
 * @property \App\Model\Entity\Game $game
 * @property \App\Model\Entity\Person $official
 * @property \App\Model\Entity\Team $team
 */
class GamesOfficial extends Entity
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
