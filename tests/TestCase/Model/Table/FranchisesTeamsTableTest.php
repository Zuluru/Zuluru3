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
	 * @var \App\Model\Table\FranchisesTeamsTable
	 */
	public $FranchisesTeamsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::exists('FranchisesTeams') ? [] : ['className' => 'App\Model\Table\FranchisesTeamsTable'];
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
