<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Entity\Person;
use App\Test\Factory\FranchiseFactory;
use App\Test\Factory\PersonFactory;
use Cake\ORM\TableRegistry;
use App\Model\Table\FranchisesTable;

/**
 * App\Model\Table\FranchisesTable Test Case
 */
class FranchisesTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var FranchisesTable
	 */
	public $FranchisesTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('Franchises') ? [] : ['className' => FranchisesTable::class];
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
		/** @var Person $captain */
		$captain = PersonFactory::make()->with('Franchises[2]')->persist();

		$franchises = $this->FranchisesTable->readByPlayerId($captain->id);
		$this->assertCount(2, $franchises);
		$this->assertArrayHasKey(0, $franchises);
		$this->assertEquals($captain->franchises[0]->id, $franchises[0]->id);
		$this->assertEquals($captain->franchises[1]->id, $franchises[1]->id);

		$franchises = $this->FranchisesTable->readByPlayerId($captain->id, ['Franchises.id IN' => [$captain->franchises[0]->id]]);
		$this->assertCount(1, $franchises);
		$this->assertArrayHasKey(0, $franchises);
		$this->assertEquals($captain->franchises[0]->id, $franchises[0]->id);

		$franchises = $this->FranchisesTable->readByPlayerId($captain->id, ['Franchises.id IN' => [1]]);
		$this->assertEmpty($franchises);
	}

	/**
	 * Test affiliate method
	 */
	public function testAffiliate(): void {
	    $affiliateId = mt_rand();
	    $franchise = FranchiseFactory::make(['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->FranchisesTable->affiliate($franchise->id));
	}

}
