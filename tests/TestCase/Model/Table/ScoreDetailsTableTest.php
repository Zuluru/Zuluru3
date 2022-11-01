<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\ScoreDetailsTable;

/**
 * App\Model\Table\ScoreDetailsTable Test Case
 */
class ScoreDetailsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var ScoreDetailsTable
	 */
	public $ScoreDetailsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('ScoreDetails') ? [] : ['className' => ScoreDetailsTable::class];
		$this->ScoreDetailsTable = TableRegistry::getTableLocator()->get('ScoreDetails', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->ScoreDetailsTable);

		parent::tearDown();
	}

}
