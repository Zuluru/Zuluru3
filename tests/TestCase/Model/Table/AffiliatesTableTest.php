<?php
namespace App\Test\TestCase\Model\Table;

use App\Test\Factory\AffiliateFactory;
use App\Test\Factory\GameFactory;
use App\Test\Factory\PersonFactory;
use Cake\ORM\TableRegistry;
use App\Model\Table\AffiliatesTable;

/**
 * App\Model\Table\AffiliatesTable Test Case
 */
class AffiliatesTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\AffiliatesTable
	 */
	public $AffiliatesTable;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Affiliates') ? [] : ['className' => 'App\Model\Table\AffiliatesTable'];
		$this->AffiliatesTable = TableRegistry::get('Affiliates', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->AffiliatesTable);

		parent::tearDown();
	}

	/**
	 * Test readByPlayerId method
	 *
	 * @return void
	 */
	public function testReadByPlayerId(): void {
        $player = PersonFactory::make()->with('Affiliates')->persist();
		$affiliates = $this->AffiliatesTable->readByPlayerId($player->id);
		$this->assertEquals(1, count($affiliates));
		$this->assertArrayHasKey(0, $affiliates);
		$this->assertTrue($affiliates[0]->has('id'));
		$this->assertArrayHasKey('People', $affiliates[0]->_matchingData);
		$this->assertTrue($affiliates[0]->_matchingData['People']->has('id'));
		$this->assertEquals($player->id, $affiliates[0]->_matchingData['People']->id);
	}

	/**
	 * Test mergeList method
	 *
	 * @return void
	 */
	public function testMergeList(): void {
        $this->markTestSkipped(GameFactory::TODO_FACTORIES);
		$original = $this->AffiliatesTable->People->get(PERSON_ID_MANAGER, ['contain' => ['Affiliates']]);
		$duplicate = $this->AffiliatesTable->People->get(PERSON_ID_DUPLICATE, ['contain' => ['Affiliates']]);
		$affiliates = $this->AffiliatesTable->mergeList($original->affiliates, $duplicate->affiliates);
		$this->assertEquals(2, count($affiliates));

		$this->assertArrayHasKey(0, $affiliates);
		$this->assertEquals(AFFILIATE_ID_CLUB, $affiliates[0]->id);
		$this->assertNotNull($affiliates[0]->_joinData);
		$this->assertEquals('manager', $affiliates[0]->_joinData->position);

		$this->assertArrayHasKey(1, $affiliates);
		$this->assertEquals(AFFILIATE_ID_SUB, $affiliates[1]->id);
		$this->assertNotNull($affiliates[1]->_joinData);
		$this->assertEquals('player', $affiliates[1]->_joinData->position);
	}

}
