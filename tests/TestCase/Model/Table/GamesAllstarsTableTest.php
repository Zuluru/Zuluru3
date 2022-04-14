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
	 * @var \App\Model\Table\GamesAllstarsTable
	 */
	public $GamesAllstarsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::exists('GamesAllstars') ? [] : ['className' => 'App\Model\Table\GamesAllstarsTable'];
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
