<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Entity\Division;
use App\Model\Entity\League;
use App\Model\Table\DivisionsTable;
use App\Test\Factory\DivisionFactory;
use App\Test\Factory\TeamFactory;
use App\Test\Scenario\LeagueScenario;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\TableRegistry;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Model\Table\AppTable Test Case
 */
class AppTableTest extends TableTestCase {

	use ScenarioAwareTrait;

	/**
	 * Test subject
	 *
	 * @var DivisionsTable
	 */
	public $DivisionsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('Divisions') ? [] : ['className' => DivisionsTable::class];
		$this->DivisionsTable = TableRegistry::getTableLocator()->get('Divisions', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->DivisionsTable);

		parent::tearDown();
	}

	/**
	 * Test field method
	 */
	public function testField(): void {
		$division = DivisionFactory::make(['current_round' => 'playoff', 'schedule_type' => 'ratings_ladder'])->persist();
		$this->assertEquals('ratings_ladder', $this->DivisionsTable->field('schedule_type', ['Divisions.id' => $division->id]));
		$this->assertEquals($division->id, $this->DivisionsTable->field('id', ['Divisions.current_round' => 'playoff']));
		try {
			$this->DivisionsTable->field('schedule_type', ['Divisions.id' => 0]);
			$this->assertFalse(true, 'field function should throw on failure');
		} catch (RecordNotFoundException $ex) {
			$this->assertTrue(true);
		}
	}

	/**
	 * Test dependencies method
	 */
	public function testDependencies(): void {
		$division = DivisionFactory::make()
			->with('Days')
			->with('GameSlots[6]')
			->with('People')
			->with('Events[2]')
			->with('Games[8]')
			->with('Teams[4]')
			->persist();

		$dependencies = $this->DivisionsTable->dependencies($division->id);
		$this->assertEquals('1 days, 6 game slots, 1 people, 2 events, 8 games, 4 teams', $dependencies);
		$dependencies = $this->DivisionsTable->dependencies($division->id, ['Teams']);
		$this->assertEquals('1 days, 6 game slots, 1 people, 2 events, 8 games', $dependencies);
		$dependencies = $this->DivisionsTable->dependencies($division->id, ['Days', 'People', 'Teams']);
		$this->assertEquals('6 game slots, 2 events, 8 games', $dependencies);

		$playoff_division = DivisionFactory::make()
			->with('Days[2]')
			->with('GameSlots[4]')
			->with('People')
			->with('Games')
			->with('Pools[2]')
			->with('Teams')
			->persist();

		$dependencies = $this->DivisionsTable->dependencies($playoff_division->id);
		$this->assertEquals('2 days, 4 game slots, 1 people, 1 games, 2 pools, 1 teams', $dependencies);
	}

	/**
	 * Test cloneWithoutIds method
	 */
	public function testCloneWithoutIds(): void {
		/** @var League $league */
		$league = $this->loadFixtureScenario(LeagueScenario::class, []);
		$division = $league->divisions[0];
		TeamFactory::make(['affiliate_id' => $league->affiliate_id, 'home_field_id' => 123], 2)->with('Divisions', $division)->persist();

		/** @var Division $new */
		$new = $this->DivisionsTable->cloneWithoutIds($division->id, ['contain' => ['Leagues', 'Teams']]);

		// The ID in the new entity is reset
		$this->assertNull($new->id);

		// The league_id remains unchanged, because divisions belongTo leagues
		$this->assertEquals($league->id, $new->league_id);

		// Some IDs in teams are reset
		$this->assertCount(2, $new->teams);
		$this->assertArrayHasKey(0, $new->teams);
		$this->assertNull($new->teams[0]->id);
		$this->assertNull($new->teams[0]->division_id);
		$this->assertEquals($league->affiliate_id, $new->teams[0]->affiliate_id);
		$this->assertEquals(123, $new->teams[0]->home_field_id);
	}

}
