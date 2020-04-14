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
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.Affiliates',
			'app.Franchises',
				'app.FranchisesTeams',
		'app.I18n',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('FranchisesTeams') ? [] : ['className' => 'App\Model\Table\FranchisesTeamsTable'];
		$this->FranchisesTeamsTable = TableRegistry::get('FranchisesTeams', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->FranchisesTeamsTable);

		parent::tearDown();
	}

}
