<?php
namespace App\Model\Entity;

use App\Model\Traits\DateTimeCombinator;
use Cake\ORM\Entity;

/**
 * GameSlot Entity.
 *
 * @property int $id
 * @property int $field_id
 * @property \Cake\I18n\FrozenDate $game_date
 * @property \Cake\I18n\FrozenTime $game_start
 * @property \Cake\I18n\FrozenTime $game_end
 * @property bool $assigned
 *
 * @property \App\Model\Entity\Field $field
 * @property \App\Model\Entity\Game[] $games
 * @property \App\Model\Entity\Division[] $divisions
 *
 * @property \Cake\I18n\FrozenTime $display_game_end
 */
class GameSlot extends Entity {

	use DateTimeCombinator;

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

	// Make sure the virtual fields are included when we convert to arrays
	protected $_virtual = [
		'display_game_end',
		'start_time',
		'end_time',
	];

	private $_dateTimeCombinatorFields = [
		'date' => 'game_date',
		'start' => 'game_start',
		'end' => 'display_game_end',
	];

	protected function _getDisplayGameEnd() {
		if ($this->game_end === null) {
			return \App\Lib\local_sunset_for_date($this->game_date);
		} else {
			return $this->game_end;
		}
	}

	public function overlaps(GameSlot $other) {
		return $this->start_time < $other->end_time && $other->start_time < $this->end_time;
	}
}
