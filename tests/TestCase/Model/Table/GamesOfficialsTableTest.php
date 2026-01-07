<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\GamesOfficialsTable;

/**
 * App\Model\Table\GamesOfficialsTable Test Case
 */
class GamesOfficialsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var GamesOfficialsTable
	 */
	public $GamesOfficialsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('GamesOfficials') ? [] : ['className' => GamesOfficialsTable::class];
		$this->GamesOfficialsTable = TableRegistry::getTableLocator()->get('GamesOfficials', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->GamesOfficialsTable);

		parent::tearDown();
	}
}
