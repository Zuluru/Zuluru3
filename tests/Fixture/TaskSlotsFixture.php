<?php
namespace App\Test\Fixture;

use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * TaskSlotsFixture
 *
 */
class TaskSlotsFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'task_slots'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'task_id' => TASK_ID_CAPTAINS_MEETING,
				'task_date' => FrozenDate::now()->addDays(5),
				'task_start' => new FrozenTime('12:00:00'),
				'task_end' => new FrozenTime('16:00:00'),
				'person_id' => PERSON_ID_PLAYER,
				'approved' => true,
				'approved_by_id' => PERSON_ID_ADMIN,
				'modified' => FrozenDate::now(),
			],
			[
				'task_id' => TASK_ID_CAPTAINS_MEETING,
				'task_date' => FrozenDate::now()->addDays(5),
				'task_start' => new FrozenTime('12:00:00'),
				'task_end' => new FrozenTime('16:00:00'),
				'person_id' => PERSON_ID_CAPTAIN,
				'approved' => false,
				'approved_by_id' => null,
				'modified' => FrozenDate::now(),
			],
			[
				'task_id' => TASK_ID_POSTERS_SUB,
				'task_date' => FrozenDate::now()->addDays(5),
				'task_start' => new FrozenTime('12:00:00'),
				'task_end' => new FrozenTime('16:00:00'),
				'person_id' => PERSON_ID_ANDY_SUB,
				'approved' => true,
				'approved_by_id' => PERSON_ID_ADMIN,
				'modified' => FrozenDate::now(),
			],
		];

		if (!defined('TASK_SLOT_ID_CAPTAINS_MEETING')) {
			$i = 0;
			define('TASK_SLOT_ID_CAPTAINS_MEETING', ++$i);
			define('TASK_SLOT_ID_CAPTAINS_MEETING_UNAPPROVED', ++$i);
			define('TASK_SLOT_ID_POSTERS_SUB', ++$i);
		}

		parent::init();
	}

}
