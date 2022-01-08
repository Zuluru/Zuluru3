<?php
namespace App\Test\TestCase\Model\Table;

use App\Test\Factory\GameFactory;
use Cake\ORM\TableRegistry;
use App\Model\Table\GroupsTable;

/**
 * App\Model\Table\GroupsTable Test Case
 */
class GroupsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\GroupsTable
	 */
	public $GroupsTable;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Groups') ? [] : ['className' => 'App\Model\Table\GroupsTable'];
		$this->GroupsTable = TableRegistry::get('Groups', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->GroupsTable);

		parent::tearDown();
	}

	/**
	 * Test findOptions method
	 *
	 * @return void
	 */
	public function testFindOptions() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test mergeList method
	 *
	 * @return void
	 */
	public function testMergeList() {
        $this->markTestSkipped(GameFactory::TODO_FACTORIES);
		$original = $this->GroupsTable->People->get(PERSON_ID_MANAGER, ['contain' => ['Groups']]);
		$duplicate = $this->GroupsTable->People->get(PERSON_ID_DUPLICATE, ['contain' => ['Groups']]);
		$groups = $this->GroupsTable->mergeList($original->groups, $duplicate->groups);
		$this->assertEquals(2, count($groups));

		$this->assertArrayHasKey(0, $groups);
		$this->assertEquals(GROUP_PLAYER, $groups[0]->id);

		$this->assertArrayHasKey(1, $groups);
		$this->assertEquals(GROUP_MANAGER, $groups[1]->id);
	}

}
