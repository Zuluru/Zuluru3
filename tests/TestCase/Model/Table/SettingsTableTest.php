<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Entity\Person;
use App\Model\Entity\Setting;
use App\Test\Factory\PersonFactory;
use App\Test\Factory\SettingFactory;
use Cake\ORM\TableRegistry;
use App\Model\Table\SettingsTable;

/**
 * App\Model\Table\SettingsTable Test Case
 */
class SettingsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var SettingsTable
	 */
	public $SettingsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('Settings') ? [] : ['className' => SettingsTable::class];
		$this->SettingsTable = TableRegistry::getTableLocator()->get('Settings', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->SettingsTable);

		parent::tearDown();
	}

	/**
	 * Test mergeList method
	 */
	public function testMergeList(): void {
		/** @var Person $original */
		$original = PersonFactory::make()->with('Settings', SettingFactory::make([
			[
				'category' => 'personal',
				'name' => 'enable_ical',
				'value' => 0,
			],
			[
				'category' => 'personal',
				'name' => 'date_format',
				'value' => 'M j, Y',
			],
		]))->getEntity();
		$this->assertCount(2, $original->settings);

		/** @var Setting[] $new */
		$new = SettingFactory::make([
			[
				'category' => 'personal',
				'name' => 'enable_ical',
				'value' => 1,
			],
			[
				'category' => 'personal',
				'name' => 'attendance_emails',
				'value' => 1,
			],
		])->getEntities();
		$this->assertCount(2, $new);

		$settings = $this->SettingsTable->mergeList($original->settings, $new);
		$this->assertCount(3, $settings);

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
