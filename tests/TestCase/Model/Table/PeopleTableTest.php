<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Entity\Affiliate;
use App\Model\Entity\Person;
use App\Test\Factory\AffiliateFactory;
use App\Test\Factory\PersonFactory;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use App\Model\Table\PeopleTable;

/**
 * App\Model\Table\PeopleTable Test Case
 */
class PeopleTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var PeopleTable
	 */
	public $PeopleTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('People') ? [] : ['className' => PeopleTable::class];
		$this->PeopleTable = TableRegistry::getTableLocator()->get('People', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->PeopleTable);

		parent::tearDown();
	}

	/**
	 * Test validationCreate method
	 */
	public function testValidationCreate(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationPlayer method
	 */
	public function testValidationPlayer(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationContact method
	 */
	public function testValidationContact(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationCoach method
	 */
	public function testValidationCoach(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test beforeRules method
	 */
	public function testBeforeRules(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test afterSave method
	 */
	public function testAfterSave(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test CreatePersonRecord method
	 */
	public function testCreatePersonRecord(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test findDuplicates method
	 */
	public function testFindDuplicates(): void {
		// TODO: Use this, or extract into some central function, instead of the settings fixture
		Configure::write([
			'Security' => ['authModel' => 'Users'],
			'profile' => [
				'home_phone' => true,
				'work_phone' => true,
				'mobile_phone' => true,
				'addr_street' => true,
			]
		]);

		/** @var Person[] $people */
		$people = PersonFactory::make([
			['first_name' => 'Aaron', 'last_name' => 'Allen'],
			['first_name' => 'Aaron', 'last_name' => 'Allen'],
			['first_name' => 'Carla', 'last_name' => 'Booth'],
			['first_name' => 'Brenda', 'last_name' => 'Booth'],
			['addr_street' => '123 Main St'],
			['addr_street' => '123 Main St'],
			['home_phone' => '(416)555-5555'],
			['home_phone' => '(416)555-5555'],
		])
			->with('Affiliates', AffiliateFactory::make()->persist())
			->persist();

		$duplicates = $this->PeopleTable->find('duplicates', ['person' => $people[0]])->toArray();
		$this->assertCount(1, $duplicates);
		$this->assertArrayHasKey(0, $duplicates);
		$this->assertEquals($people[1]->id, $duplicates[0]->id);

		$duplicates = $this->PeopleTable->find('duplicates', ['person' => $people[2]])->toArray();
		$this->assertEmpty($duplicates);

		$duplicates = $this->PeopleTable->find('duplicates', ['person' => $people[4]])->toArray();
		$this->assertCount(1, $duplicates);
		$this->assertArrayHasKey(0, $duplicates);
		$this->assertEquals($people[5]->id, $duplicates[0]->id);

		$duplicates = $this->PeopleTable->find('duplicates', ['person' => $people[6]])->toArray();
		$this->assertCount(1, $duplicates);
		$this->assertArrayHasKey(0, $duplicates);
		$this->assertEquals($people[7]->id, $duplicates[0]->id);

		$this->markTestIncomplete('Test duplicate match on email addresses.');
	}

	/**
	 * Test delete method
	 */
	public function testDelete(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test mergeList method
	 */
	public function testMergeList(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test comparePerson method
	 */
	public function testComparePerson(): void {
		/** @var Person[] $people */
		$people = PersonFactory::make([
			['first_name' => 'Aaron', 'last_name' => 'Allen'],
			['first_name' => 'Amy', 'last_name' => 'Adamson'],
			['first_name' => 'Carla', 'last_name' => 'Booth'],
			['first_name' => 'Brenda', 'last_name' => 'Booth'],
		])
			->getEntities();

		$this->assertCount(4, $people);
		usort($people, [PeopleTable::class, 'comparePerson']);
		$this->assertArrayHasKey(0, $people);
		$this->assertArrayHasKey(3, $people);

		$this->assertEquals('Amy', $people[0]->first_name);
		$this->assertEquals('Aaron', $people[1]->first_name);
		$this->assertEquals('Brenda', $people[2]->first_name);
		$this->assertEquals('Carla', $people[3]->first_name);
	}

}
