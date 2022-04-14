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
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::exists('Franchises') ? [] : ['className' => 'App\Model\Table\FranchisesTable'];
		$this->FranchisesTable = TableRegistry::getTableLocator()->get('Franchises', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->FranchisesTable);

		parent::tearDown();
	}

	/**
	 * Test readByPlayerId method
	 */
	public function testReadByPlayerId(): void {
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
	 */
	public function testAffiliate(): void {
	    $affiliateId = rand();
	    $franchise = FranchiseFactory::make(['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->FranchisesTable->affiliate($franchise->id));
	}

}
