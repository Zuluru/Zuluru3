<?php
namespace App\Test\TestCase\Model\Table;

use App\Test\Factory\FranchiseFactory;
use App\Test\Factory\GameFactory;
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
        $this->markTestSkipped(GameFactory::TODO_FACTORIES);
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
	    $affiliateId = rand();
	    $franchise = FranchiseFactory::make(['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->FranchisesTable->affiliate($franchise->id));
	}

}
