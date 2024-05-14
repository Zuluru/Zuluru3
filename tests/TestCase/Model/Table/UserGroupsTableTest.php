<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Entity\UserGroup;
use App\Model\Entity\Person;
use App\Test\Factory\UserGroupFactory;
use App\Test\Factory\PersonFactory;
use Cake\ORM\TableRegistry;
use App\Model\Table\UserGroupsTable;

/**
 * App\Model\Table\UserGroupsTable Test Case
 */
class UserGroupsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var UserGroupsTable
	 */
	public $UserGroupsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('UserGroups') ? [] : ['className' => UserGroupsTable::class];
		$this->UserGroupsTable = TableRegistry::getTableLocator()->get('UserGroups', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->UserGroupsTable);

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
		$original = PersonFactory::make()->with('UserGroups', ['id' => GROUP_MANAGER])->getEntity();
		$this->assertCount(1, $original->user_groups);

		/** @var UserGroup[] $new */
		$new = UserGroupFactory::make(['id' => GROUP_PLAYER])->getEntities();
		$this->assertCount(1, $new);

		$groups = $this->UserGroupsTable->mergeList($original->user_groups, $new);
		$this->assertCount(2, $groups);

		$this->assertArrayHasKey(0, $groups);
		$this->assertEquals(GROUP_PLAYER, $groups[0]->id);

		$this->assertArrayHasKey(1, $groups);
		$this->assertEquals(GROUP_MANAGER, $groups[1]->id);
	}

}
