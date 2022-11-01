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
	 * @var CreditsTable
	 */
	public $CreditsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('Credits') ? [] : ['className' => CreditsTable::class];
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
