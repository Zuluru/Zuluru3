<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\FranchisesTeamsTable;

/**
 * App\Model\Table\FranchisesTeamsTable Test Case
 */
class FranchisesTeamsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var FranchisesTeamsTable
	 */
	public $FranchisesTeamsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('FranchisesTeams') ? [] : ['className' => FranchisesTeamsTable::class];
		$this->FranchisesTeamsTable = TableRegistry::getTableLocator()->get('FranchisesTeams', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->FranchisesTeamsTable);

		parent::tearDown();
	}

}
