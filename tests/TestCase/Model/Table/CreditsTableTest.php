<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\CreditsTable;

/**
 * App\Model\Table\CreditsTable Test Case
 */
class CreditsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\CreditsTable
	 */
	public $CreditsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::exists('Credits') ? [] : ['className' => 'App\Model\Table\CreditsTable'];
		$this->CreditsTable = TableRegistry::getTableLocator()->get('Credits', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->CreditsTable);

		parent::tearDown();
	}

}
