<?php
namespace App\Test\Fixture;

use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * GameSlotsFixture
 *
 */
class GameSlotsFixture extends TestFixture {

	use GameSlotsFixtureTrait;

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'game_slots'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		// Build the game slot records, based on trait's definitions
		$records = new \ArrayObject();
		$this->__process(function ($context, $id, $field_id, $division_ids, FrozenDate $date, FrozenTime $start, FrozenTime $end, $name) {
			$context[$id] = [
				'field_id' => $field_id,
				'game_date' => $date,
				'game_start' => $start,
				'game_end' => $end,
				'assigned' => false,
				'name' => $name,
			];
		}, $records);
		$this->records = $records->getArrayCopy();

		// Set up which slots are already assigned
		// TODO: Do this programmatically based on games fixtures
		$this->records[GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1]['assigned'] = true;
		$this->records[GAME_SLOT_ID_MONDAY_SUNNYBROOK_2_WEEK_1]['assigned'] = true;
		$this->records[GAME_SLOT_ID_MONDAY_SUNNYBROOK_3_WEEK_1]['assigned'] = true;
		$this->records[GAME_SLOT_ID_MONDAY_BROADACRES_WEEK_1]['assigned'] = true;
		$this->records[GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_2]['assigned'] = true;
		$this->records[GAME_SLOT_ID_MONDAY_SUNNYBROOK_2_WEEK_2]['assigned'] = true;
		$this->records[GAME_SLOT_ID_MONDAY_SUNNYBROOK_3_WEEK_2]['assigned'] = true;
		$this->records[GAME_SLOT_ID_MONDAY_BROADACRES_WEEK_2]['assigned'] = true;
		$this->records[GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_3]['assigned'] = true;
		$this->records[GAME_SLOT_ID_MONDAY_SUNNYBROOK_2_WEEK_3]['assigned'] = true;
		$this->records[GAME_SLOT_ID_MONDAY_SUNNYBROOK_3_WEEK_3]['assigned'] = true;
		$this->records[GAME_SLOT_ID_MONDAY_BROADACRES_WEEK_3]['assigned'] = true;
		$this->records[GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_4]['assigned'] = true;
		$this->records[GAME_SLOT_ID_MONDAY_BROADACRES_WEEK_4]['assigned'] = true;
		$this->records[GAME_SLOT_ID_TUESDAY_SUNNYBROOK_1_WEEK_1]['assigned'] = true;
		$this->records[GAME_SLOT_ID_TUESDAY_SUNNYBROOK_1_WEEK_2]['assigned'] = true;
		$this->records[GAME_SLOT_ID_THURSDAY_SUNNYBROOK_1_WEEK_1]['assigned'] = true;
		$this->records[GAME_SLOT_ID_SUNDAY_CENTRAL_TECH_WEEK_1]['assigned'] = true;
		$this->records[GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_PLAYOFFS_9AM]['assigned'] = true;

		parent::init();
	}

}
