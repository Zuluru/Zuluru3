<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Entity\Person;
use App\Model\Entity\Skill;
use App\Test\Factory\PersonFactory;
use App\Test\Factory\SkillFactory;
use Cake\Event\Event;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use App\Model\Table\SkillsTable;

/**
 * App\Model\Table\SkillsTable Test Case
 */
class SkillsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var SkillsTable
	 */
	public $SkillsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('Skills') ? [] : ['className' => SkillsTable::class];
		$this->SkillsTable = TableRegistry::getTableLocator()->get('Skills', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->SkillsTable);

		parent::tearDown();
	}

	/**
	 * Test beforeMarshal method
	 */
	public function testBeforeMarshal(): void {
		$data = new \ArrayObject([
			'year_started' => ['year' => 2000, 'month' => 1, 'day' => 1],
		]);
		$this->SkillsTable->beforeMarshal(new Event('testing'), $data, new \ArrayObject());
		$this->assertEquals(2000, $data['year_started']);
	}

	/**
	 * Test mergeList method
	 */
	public function testMergeList(): void {
		/** @var Person $original */
		$original = PersonFactory::make()->with('Skills', SkillFactory::make([
			'sport' => 'ultimate',
			'enabled' => 1,
			'skill_level' => 6,
			'year_started' => FrozenTime::now()->year - 3,
		]))->getEntity();
		$this->assertCount(1, $original->skills);

		/** @var Skill[] $new */
		$new = SkillFactory::make([
			[
				'sport' => 'ultimate',
				'enabled' => 1,
				'skill_level' => 7,
				'year_started' => FrozenTime::now()->year - 3,
			],
			[
				'sport' => 'baseball',
				'enabled' => 1,
				'skill_level' => 4,
				'year_started' => FrozenTime::now()->year,
			],
		])->getEntities();
		$this->assertCount(2, $new);

		$skills = $this->SkillsTable->mergeList($original->skills, $new);
		$this->assertCount(2, $skills);

		$this->assertArrayHasKey(0, $skills);
		$this->assertEquals('ultimate', $skills[0]->sport);
		$this->assertEquals(7, $skills[0]->skill_level);

		$this->assertArrayHasKey(1, $skills);
		$this->assertEquals('baseball', $skills[1]->sport);
		$this->assertEquals(4, $skills[1]->skill_level);
	}

}
