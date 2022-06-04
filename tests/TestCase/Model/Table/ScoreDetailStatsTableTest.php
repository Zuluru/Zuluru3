<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\ScoreDetailStatsTable;

/**
 * App\Model\Table\ScoreDetailStatsTable Test Case
 */
class ScoreDetailStatsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var ScoreDetailStatsTable
	 */
	public $ScoreDetailStatsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('ScoreDetailStats') ? [] : ['className' => ScoreDetailStatsTable::class];
		$this->ScoreDetailStatsTable = TableRegistry::getTableLocator()->get('ScoreDetailStats', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->ScoreDetailStatsTable);

		parent::tearDown();
	}

}
