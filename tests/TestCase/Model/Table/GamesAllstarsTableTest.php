<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\GamesAllstarsTable;

/**
 * App\Model\Table\GamesAllstarsTable Test Case
 */
class GamesAllstarsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var GamesAllstarsTable
	 */
	public $GamesAllstarsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('GamesAllstars') ? [] : ['className' => GamesAllstarsTable::class];
		$this->GamesAllstarsTable = TableRegistry::getTableLocator()->get('GamesAllstars', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->GamesAllstarsTable);

		parent::tearDown();
	}

}
