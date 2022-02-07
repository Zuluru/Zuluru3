<?php
namespace App\Test\TestCase\Model\Table;

use App\Test\Factory\GameFactory;
use Cake\ORM\TableRegistry;
use App\Model\Table\SettingsTable;

/**
 * App\Model\Table\SettingsTable Test Case
 */
class SettingsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\SettingsTable
	 */
	public $SettingsTable;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Settings') ? [] : ['className' => 'App\Model\Table\SettingsTable'];
		$this->SettingsTable = TableRegistry::get('Settings', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->SettingsTable);

		parent::tearDown();
	}

	/**
	 * Test mergeList method
	 *
	 * @return void
	 */
	public function testMergeList(): void {
        $this->markTestSkipped(GameFactory::TODO_FACTORIES);
		$original = $this->SettingsTable->People->get(PERSON_ID_MANAGER, ['contain' => ['Settings']]);
		$this->assertEquals(2, count($original->settings));
		$duplicate = $this->SettingsTable->People->get(PERSON_ID_DUPLICATE, ['contain' => ['Settings']]);
		$this->assertEquals(2, count($duplicate->settings));
		$settings = $this->SettingsTable->mergeList($original->settings, $duplicate->settings);
		$this->assertEquals(3, count($settings));

		$this->assertArrayHasKey(0, $settings);
		$this->assertEquals('enable_ical', $settings[0]->name);
		$this->assertEquals(1, $settings[0]->value);

		$this->assertArrayHasKey(1, $settings);
		$this->assertEquals('attendance_emails', $settings[1]->name);
		$this->assertEquals(1, $settings[1]->value);

		$this->assertArrayHasKey(2, $settings);
		$this->assertEquals('date_format', $settings[2]->name);
		$this->assertEquals('M j, Y', $settings[2]->value);
	}

}
