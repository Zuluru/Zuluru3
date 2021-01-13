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
	 * @var \App\Model\Table\ScoreDetailsTable
	 */
	public $ScoreDetailsTable;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('ScoreDetails') ? [] : ['className' => 'App\Model\Table\ScoreDetailsTable'];
		$this->ScoreDetailsTable = TableRegistry::get('ScoreDetails', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->ScoreDetailsTable);

		parent::tearDown();
	}

}
