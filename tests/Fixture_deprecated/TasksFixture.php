<?php
namespace App\Test\Fixture_deprecated;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TasksFixture
 *
 */
class TasksFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'tasks'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'name' => 'Captain\'s meeting',
				'category_id' => CATEGORY_ID_EVENTS,
				'description' => 'Assist staff with various tasks related to the captain\'s meeting.',
				'notes' => 'Includes set-up and tear-down; some heavy lifting may be involved.',
				'auto_approve' => true,
				'person_id' => PERSON_ID_ADMIN,
				'allow_signup' => false,
			],
			[
				'name' => 'Playoffs setup',
				'category_id' => CATEGORY_ID_EVENTS,
				'description' => 'Assist with setting up fields for playoffs.',
				'notes' => 'Ability to drive a golf cart is helpful.',
				'auto_approve' => false,
				'person_id' => PERSON_ID_MANAGER,
				'allow_signup' => true,
			],
			[
				'name' => 'Playoffs cleanup',
				'category_id' => CATEGORY_ID_EVENTS,
				'description' => 'Assist with cleaning up after playoffs.',
				'notes' => 'Ability to drive a golf cart is helpful.',
				'auto_approve' => false,
				'person_id' => PERSON_ID_MANAGER,
				'allow_signup' => false,
			],
			[
				'name' => 'Poster posting',
				'category_id' => CATEGORY_ID_MARKETING_SUB,
				'description' => 'Put up advertising posters.',
				'notes' => 'Valid driver\'s license required.',
				'auto_approve' => false,
				'person_id' => PERSON_ID_ADMIN,
				'allow_signup' => false,
			],
		];

		if (!defined('TASK_ID_CAPTAINS_MEETING')) {
			$i = 0;
			define('TASK_ID_CAPTAINS_MEETING', ++$i);
			define('TASK_ID_PLAYOFFS_SETUP', ++$i);
			define('TASK_ID_PLAYOFFS_CLEANUP', ++$i);
			define('TASK_ID_POSTERS_SUB', ++$i);
		}

		parent::init();
	}

}
