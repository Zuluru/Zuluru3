<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\PeopleTable;

/**
 * App\Model\Table\PeopleTable Test Case
 */
class PeopleTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\PeopleTable
	 */
	public $PeopleTable;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.affiliates',
			'app.users',
				'app.people',
					'app.affiliates_people',
					'app.people_people',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('People') ? [] : ['className' => 'App\Model\Table\PeopleTable'];
		$this->PeopleTable = TableRegistry::get('People', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->PeopleTable);

		parent::tearDown();
	}

	/**
	 * Test validationCreate method
	 *
	 * @return void
	 */
	public function testValidationCreate() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationPlayer method
	 *
	 * @return void
	 */
	public function testValidationPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationContact method
	 *
	 * @return void
	 */
	public function testValidationContact() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationCoach method
	 *
	 * @return void
	 */
	public function testValidationCoach() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test beforeRules method
	 *
	 * @return void
	 */
	public function testBeforeRules() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test afterSave method
	 *
	 * @return void
	 */
	public function testAfterSave() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test CreatePersonRecord method
	 *
	 * @return void
	 */
	public function testCreatePersonRecord() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test findDuplicates method
	 *
	 * @return void
	 */
	public function testFindDuplicates() {
		$person = $this->PeopleTable->get(PERSON_ID_MANAGER, ['contain' => ['Affiliates']]);
		$duplicates = $this->PeopleTable->find('duplicates', compact('person'))->toArray();
		$this->assertEquals(1, count($duplicates));
		$this->assertArrayHasKey(0, $duplicates);
		$this->assertEquals(PERSON_ID_DUPLICATE, $duplicates[0]->id);

		$person = $this->PeopleTable->get(PERSON_ID_PLAYER, ['contain' => ['Affiliates']]);
		$duplicates = $this->PeopleTable->find('duplicates', compact('person'))->toArray();
		$this->assertEmpty($duplicates);
	}

	/**
	 * Test delete method
	 *
	 * @return void
	 */
	public function testDelete() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test mergeList method
	 *
	 * @return void
	 */
	public function testMergeList() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test comparePerson method
	 *
	 * @return void
	 */
	public function testComparePerson() {
		// TODO: Add more person records, to more completely test the sort options
		$people = $this->PeopleTable->find()->toArray();
		$this->assertEquals(12, count($people));
		usort($people, ['App\Model\Table\PeopleTable', 'comparePerson']);

		// Amy Administrator will be first
		$this->assertArrayHasKey(0, $people);
		$this->assertEquals(PERSON_ID_ADMIN, $people[0]->id);

		// Then Andy Affiliate
		$this->assertArrayHasKey(1, $people);
		$this->assertEquals(PERSON_ID_ANDY_SUB, $people[1]->id);

		// Then Carl Captain
		$this->assertArrayHasKey(2, $people);
		$this->assertEquals(PERSON_ID_CAPTAIN4, $people[2]->id);

		// Then Carolyn Captain
		$this->assertArrayHasKey(3, $people);
		$this->assertEquals(PERSON_ID_CAPTAIN3, $people[3]->id);

		// Then Chuck Captain
		$this->assertArrayHasKey(4, $people);
		$this->assertEquals(PERSON_ID_CAPTAIN2, $people[4]->id);

		// Then Crystal Captain
		$this->assertArrayHasKey(5, $people);
		$this->assertEquals(PERSON_ID_CAPTAIN, $people[5]->id);

		// Then Carla Child
		$this->assertArrayHasKey(6, $people);
		$this->assertEquals(PERSON_ID_CHILD, $people[6]->id);

		// Then Cindy Coordinator
		$this->assertArrayHasKey(7, $people);
		$this->assertEquals(PERSON_ID_COORDINATOR, $people[7]->id);

		// Then Mary Duplicate
		$this->assertArrayHasKey(8, $people);
		$this->assertEquals(PERSON_ID_DUPLICATE, $people[8]->id);

		// Then Mary Manager
		$this->assertArrayHasKey(9, $people);
		$this->assertEquals(PERSON_ID_MANAGER, $people[9]->id);

		// Then Pam Player
		$this->assertArrayHasKey(10, $people);
		$this->assertEquals(PERSON_ID_PLAYER, $people[10]->id);

		// Finally Veronica Visitor
		$this->assertArrayHasKey(11, $people);
		$this->assertEquals(PERSON_ID_VISITOR, $people[11]->id);
	}

}
