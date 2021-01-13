<?php
namespace App\Test\Fixture_deprecated;

use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * DivisionsGameslotsFixture
 *
 */
class DivisionsGameslotsFixture extends TestFixture {

	use GameSlotsFixtureTrait;

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'divisions_gameslots'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		// Build the game slot availability records, based on trait's definitions
		$records = new \ArrayObject();
		$this->__process(function ($context, $id, $field_id, $division_ids, FrozenDate $date, FrozenTime $start, FrozenTime $end, $name) {
			foreach ($division_ids as $division_id) {
				$context[] = [
					'division_id' => $division_id,
					'game_slot_id' => $id,
					'name' => $name,
				];
			}
		}, $records);
		$this->records = $records->getArrayCopy();

		parent::init();
	}

}
