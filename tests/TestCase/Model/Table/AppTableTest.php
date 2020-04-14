<?php
namespace App\Test\TestCase\Model\Table;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\TableRegistry;

/**
 * App\Model\Table\AppTable Test Case
 */
class AppTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\DivisionsTable
	 */
	public $DivisionsTable;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.EventTypes',
		'app.Affiliates',
			'app.Users',
				'app.People',
			'app.Regions',
				'app.Facilities',
					'app.Fields',
			'app.Leagues',
				'app.Divisions',
					'app.Teams',
					'app.DivisionsDays',
					'app.DivisionsGameslots',
					'app.DivisionsPeople',
					'app.Pools',
						'app.PoolsTeams',
					'app.Games',
			'app.Events',
		'app.I18n',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Divisions') ? [] : ['className' => 'App\Model\Table\DivisionsTable'];
		$this->DivisionsTable = TableRegistry::get('Divisions', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->DivisionsTable);

		parent::tearDown();
	}

	/**
	 * Test field method
	 *
	 * @return void
	 */
	public function testField() {
		$this->assertEquals('ratings_ladder', $this->DivisionsTable->field('schedule_type', ['Divisions.id' => DIVISION_ID_MONDAY_LADDER]));
		$this->assertEquals(DIVISION_ID_MONDAY_PLAYOFF, $this->DivisionsTable->field('id', ['Divisions.current_round' => 'playoff']));
		try {
			$this->DivisionsTable->field('schedule_type', ['Divisions.id' => 0]);
			$this->assertFalse(true, 'field function should throw on failure');
		} catch (RecordNotFoundException $ex) {
			$this->assertTrue(true);
		}
	}

	/**
	 * Test dependencies method
	 *
	 * @return void
	 */
	public function testDependencies() {
		$dependencies = $this->DivisionsTable->dependencies(DIVISION_ID_MONDAY_LADDER);
		$this->assertEquals('1 days, 48 game slots, 1 people, 2 events, 14 games, 8 teams', $dependencies);
		$dependencies = $this->DivisionsTable->dependencies(DIVISION_ID_MONDAY_LADDER, ['Teams']);
		$this->assertEquals('1 days, 48 game slots, 1 people, 2 events, 14 games', $dependencies);
		$dependencies = $this->DivisionsTable->dependencies(DIVISION_ID_MONDAY_LADDER, ['Days', 'People', 'Teams']);
		$this->assertEquals('48 game slots, 2 events, 14 games', $dependencies);
		$dependencies = $this->DivisionsTable->dependencies(DIVISION_ID_MONDAY_PLAYOFF);
		$this->assertEquals('2 days, 15 game slots, 1 people, 1 games, 2 pools, 1 teams', $dependencies);
	}

	/**
	 * Test cloneWithoutIds method
	 *
	 * @return void
	 */
	public function testCloneWithoutIds() {
		$new = $this->DivisionsTable->cloneWithoutIds(DIVISION_ID_MONDAY_LADDER, ['contain' => ['Leagues', 'Teams']]);

		// The ID in the new entity is reset
		$this->assertNull($new->id);

		// The league_id remains unchanged, because divisions belongTo leagues
		$this->assertEquals(DIVISION_ID_MONDAY_LADDER, $new->league_id);

		// Some IDs in teams are reset
		$this->assertEquals(8, count($new->teams));
		$this->assertArrayHasKey(0, $new->teams);
		$this->assertNull($new->teams[0]->id);
		$this->assertNull($new->teams[0]->division_id);
		$this->assertEquals(AFFILIATE_ID_CLUB, $new->teams[0]->affiliate_id);
		$this->assertEquals(1, $new->teams[0]->home_field_id);
	}

}
