<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\FranchisesTable;

/**
 * App\Model\Table\FranchisesTable Test Case
 */
class FranchisesTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\FranchisesTable
	 */
	public $FranchisesTable;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.affiliates',
			'app.users',
				'app.people',
			'app.franchises',
				'app.franchises_people',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Franchises') ? [] : ['className' => 'App\Model\Table\FranchisesTable'];
		$this->FranchisesTable = TableRegistry::get('Franchises', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->FranchisesTable);

		parent::tearDown();
	}

	/**
	 * Test readByPlayerId method
	 *
	 * @return void
	 */
	public function testReadByPlayerId() {
		$franchises = $this->FranchisesTable->readByPlayerId(PERSON_ID_CAPTAIN);
		$this->assertEquals(2, count($franchises));
		$this->assertArrayHasKey(0, $franchises);
		$this->assertEquals(FRANCHISE_ID_RED, $franchises[0]->id);
		$this->assertEquals(FRANCHISE_ID_RED2, $franchises[1]->id);

		$franchises = $this->FranchisesTable->readByPlayerId(PERSON_ID_CAPTAIN, ['Franchises.id IN' => [FRANCHISE_ID_RED]]);
		$this->assertEquals(1, count($franchises));
		$this->assertArrayHasKey(0, $franchises);
		$this->assertEquals(FRANCHISE_ID_RED, $franchises[0]->id);

		$franchises = $this->FranchisesTable->readByPlayerId(PERSON_ID_CAPTAIN, ['Franchises.id IN' => [FRANCHISE_ID_BLUE]]);
		$this->assertEmpty($franchises);
	}

	/**
	 * Test affiliate method
	 *
	 * @return void
	 */
	public function testAffiliate() {
		$this->assertEquals(AFFILIATE_ID_CLUB, $this->FranchisesTable->affiliate(FRANCHISE_ID_RED));
	}

}
