<?php
namespace App\Test\TestCase\Model\Table;

use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use App\Model\Table\SkillsTable;

/**
 * App\Model\Table\SkillsTable Test Case
 */
class SkillsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\SkillsTable
	 */
	public $SkillsTable;

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
					'app.skills',
		'app.i18n',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Skills') ? [] : ['className' => 'App\Model\Table\SkillsTable'];
		$this->SkillsTable = TableRegistry::get('Skills', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->SkillsTable);

		parent::tearDown();
	}

	/**
	 * Test beforeMarshal method
	 *
	 * @return void
	 */
	public function testBeforeMarshal() {
		$data = new \ArrayObject([
			'year_started' => ['year' => 2000, 'month' => 1, 'day' => 1],
		]);
		$this->SkillsTable->beforeMarshal(new Event('testing'), $data, new \ArrayObject());
		$this->assertEquals(2000, $data['year_started']);
	}

	/**
	 * Test mergeList method
	 *
	 * @return void
	 */
	public function testMergeList() {
		$original = $this->SkillsTable->People->get(PERSON_ID_MANAGER, ['contain' => ['Skills']]);
		$this->assertEquals(1, count($original->skills));
		$duplicate = $this->SkillsTable->People->get(PERSON_ID_DUPLICATE, ['contain' => ['Skills']]);
		$this->assertEquals(2, count($duplicate->skills));
		$skills = $this->SkillsTable->mergeList($original->skills, $duplicate->skills);
		$this->assertEquals(2, count($skills));

		$this->assertArrayHasKey(0, $skills);
		$this->assertEquals('ultimate', $skills[0]->sport);
		$this->assertEquals(7, $skills[0]->skill_level);

		$this->assertArrayHasKey(1, $skills);
		$this->assertEquals('baseball', $skills[1]->sport);
		$this->assertEquals(4, $skills[1]->skill_level);
	}

}
