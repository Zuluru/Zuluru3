<?php
namespace App\Test\TestCase\Model\Table;

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
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.Affiliates',
			'app.Users',
				'app.People',
					'app.AffiliatesPeople',
		'app.I18n',
	];

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
	public function testReadByPlayerId() {
		$affiliates = $this->AffiliatesTable->readByPlayerId(PERSON_ID_PLAYER);
		$this->assertEquals(1, count($affiliates));
		$this->assertArrayHasKey(0, $affiliates);
		$this->assertTrue($affiliates[0]->has('id'));
		$this->assertArrayHasKey('People', $affiliates[0]->_matchingData);
		$this->assertTrue($affiliates[0]->_matchingData['People']->has('id'));
		$this->assertEquals(PERSON_ID_PLAYER, $affiliates[0]->_matchingData['People']->id);
	}

	/**
	 * Test mergeList method
	 *
	 * @return void
	 */
	public function testMergeList() {
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
