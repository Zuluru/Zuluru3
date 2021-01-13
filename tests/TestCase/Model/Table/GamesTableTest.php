<?php
namespace App\Test\TestCase\Model\Table;

use App\Test\Factory\GameFactory;
use Cake\ORM\TableRegistry;
use App\Model\Table\GamesTable;

/**
 * App\Model\Table\GamesTable Test Case
 */
class GamesTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\GamesTable
	 */
	public $GamesTable;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Games') ? [] : ['className' => 'App\Model\Table\GamesTable'];
		$this->GamesTable = TableRegistry::get('Games', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->GamesTable);

		parent::tearDown();
	}

	/**
	 * Test validationGameEdit method
	 *
	 * @return void
	 */
	public function testValidationGameEdit() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationScheduleAdd method
	 *
	 * @return void
	 */
	public function testValidationScheduleAdd() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationScheduleEdit method
	 *
	 * @return void
	 */
	public function testValidationScheduleEdit() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test beforeMarshal method
	 *
	 * @return void
	 */
	public function testBeforeMarshal() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test beforeRules method
	 *
	 * @return void
	 */
	public function testBeforeRules() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test beforeSave method
	 *
	 * @return void
	 */
	public function testBeforeSave() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test afterSave method
	 *
	 * @return void
	 */
	public function testAfterSave() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test beforeDelete method
	 *
	 * @return void
	 */
	public function testBeforeDelete() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test afterDelete method
	 *
	 * @return void
	 */
	public function testAfterDelete() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test compareSportDateAndField method
	 *
	 * @return void
	 */
	public function testCompareSportDateAndField() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test compareDateAndField method
	 *
	 * @return void
	 */
	public function testCompareDateAndField() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test adjustEntryIndices method
	 *
	 * @return void
	 */
	public function testAdjustEntryIndices() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test readAttendance method
	 *
	 * @return void
	 */
	public function testReadAttendance() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test matchDates method
	 *
	 * @return void
	 */
	public function testMatchDates() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test attendanceOptions method
	 *
	 * @return void
	 */
	public function testAttendanceOptions() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test twitterScore method
	 *
	 * @return void
	 */
	public function testTwitterScore() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test affiliate method
	 *
	 * @return void
	 */
	public function testAffiliate() {
        $affiliateId = rand();
        $game = GameFactory::make()
            ->with('Divisions.Leagues', [
                'affiliate_id' => $affiliateId,
            ])
            ->persist();

		$this->assertEquals($affiliateId, $this->GamesTable->affiliate($game->id));
	}

}
