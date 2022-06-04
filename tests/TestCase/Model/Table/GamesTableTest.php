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
	 * @var GamesTable
	 */
	public $GamesTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('Games') ? [] : ['className' => GamesTable::class];
		$this->GamesTable = TableRegistry::getTableLocator()->get('Games', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->GamesTable);

		parent::tearDown();
	}

	/**
	 * Test validationGameEdit method
	 */
	public function testValidationGameEdit(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationScheduleAdd method
	 */
	public function testValidationScheduleAdd(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationScheduleEdit method
	 */
	public function testValidationScheduleEdit(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test beforeMarshal method
	 */
	public function testBeforeMarshal(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test beforeRules method
	 */
	public function testBeforeRules(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test beforeSave method
	 */
	public function testBeforeSave(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test afterSave method
	 */
	public function testAfterSave(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test beforeDelete method
	 */
	public function testBeforeDelete(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test afterDelete method
	 */
	public function testAfterDelete(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test compareSportDateAndField method
	 */
	public function testCompareSportDateAndField(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test compareDateAndField method
	 */
	public function testCompareDateAndField(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test adjustEntryIndices method
	 */
	public function testAdjustEntryIndices(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test readAttendance method
	 */
	public function testReadAttendance(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test matchDates method
	 */
	public function testMatchDates(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test attendanceOptions method
	 */
	public function testAttendanceOptions(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test twitterScore method
	 */
	public function testTwitterScore(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test affiliate method
	 */
	public function testAffiliate(): void {
        $affiliateId = mt_rand();
        $game = GameFactory::make()
            ->with('Divisions.Leagues', [
                'affiliate_id' => $affiliateId,
            ])
            ->persist();

		$this->assertEquals($affiliateId, $this->GamesTable->affiliate($game->id));
	}

}
