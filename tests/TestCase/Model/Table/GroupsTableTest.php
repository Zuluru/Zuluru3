<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Entity\Group;
use App\Model\Entity\Person;
use App\Test\Factory\GroupFactory;
use App\Test\Factory\PersonFactory;
use Cake\ORM\TableRegistry;
use App\Model\Table\GroupsTable;

/**
 * App\Model\Table\GroupsTable Test Case
 */
class GroupsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var GroupsTable
	 */
	public $GroupsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('Groups') ? [] : ['className' => GroupsTable::class];
		$this->GroupsTable = TableRegistry::getTableLocator()->get('Groups', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->GroupsTable);

		parent::tearDown();
	}

	/**
	 * Test findOptions method
	 */
	public function testFindOptions(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test mergeList method
	 */
	public function testMergeList(): void {
		/** @var Person $original */
		$original = PersonFactory::make()->with('Groups', ['id' => GROUP_MANAGER])->getEntity();
		$this->assertCount(1, $original->groups);

		/** @var Group[] $new */
		$new = GroupFactory::make(['id' => GROUP_PLAYER])->getEntities();
		$this->assertCount(1, $new);

		$groups = $this->GroupsTable->mergeList($original->groups, $new);
		$this->assertCount(2, $groups);

		$this->assertArrayHasKey(0, $groups);
		$this->assertEquals(GROUP_PLAYER, $groups[0]->id);

		$this->assertArrayHasKey(1, $groups);
		$this->assertEquals(GROUP_MANAGER, $groups[1]->id);
	}

}
