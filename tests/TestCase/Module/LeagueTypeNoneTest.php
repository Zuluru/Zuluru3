<?php
namespace App\Test\TestCase\Module;

use App\Core\ModuleRegistry;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;

/**
 * Testing of the "none" league type. For the most part, this actually does
 * testing of LeagueType base class function implementations, which we can't
 * test directly due to the abstract declaration of LeagueType.
 */
class LeagueTypeNoneTest extends ModuleTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.affiliates',
			'app.users',
				'app.people',
			'app.regions',
				'app.facilities',
					'app.fields',
			'app.leagues',
				'app.divisions',
					'app.teams',
					'app.divisions_days',
					'app.game_slots',
						'app.divisions_gameslots',
					'app.pools',
						'app.pools_teams',
					'app.games',
						'app.spirit_entries',
		'app.settings',
	];

	/**
	 * Test subject
	 *
	 * @var \App\Module\LeagueType
	 */
	public $LeagueType;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->LeagueType = ModuleRegistry::getInstance()->load('LeagueType:none');
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->LeagueType);

		parent::tearDown();
	}

	/**
	 * loadDivision method
	 *
	 * We usually don't need to do any containment, because startSchedule does that for us.
	 */
	public function loadDivision($id, $contain = []) {
		if ($contain === true) {
			$contain = [
				'Teams' => [
					'queryBuilder' => function (Query $q) {
						return $q->order(['initial_seed']);
					},
				],
				'Games',
			];
		}
		$contain[] = 'Leagues';
		return TableRegistry::get('Divisions')->get($id, ['contain' => $contain]);
	}

	/**
	 * Test links method
	 *
	 * @return void
	 */
	public function testLinks() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test schedulingFields method
	 *
	 * @return void
	 */
	public function testSchedulingFields() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test schedulingFieldsRules method
	 *
	 * @return void
	 */
	public function testSchedulingFieldsRules() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test newTeam method
	 *
	 * @return void
	 */
	public function testNewTeam() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test addResults method
	 *
	 * @return void
	 */
	public function testAddResults() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test sort method
	 *
	 * @return void
	 */
	public function testSort() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test presort method
	 *
	 * @return void
	 */
	public function testPresort() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test compareTeams method
	 *
	 * @return void
	 */
	public function testCompareTeams() {
		$division = $this->loadDivision(DIVISION_ID_MONDAY_LADDER, true);

		$this->assertEquals(8, count($division->teams));

		$green = $division->teams[0];
		$this->assertEquals(TEAM_ID_GREEN, $green->id);
		$blue = $division->teams[1];
		$this->assertEquals(TEAM_ID_BLUE, $blue->id);
		$red = $division->teams[2];
		$this->assertEquals(TEAM_ID_RED, $red->id);
		$yellow = $division->teams[3];
		$this->assertEquals(TEAM_ID_YELLOW, $yellow->id);
		$orange = $division->teams[4];
		$this->assertEquals(TEAM_ID_ORANGE, $orange->id);
		$purple = $division->teams[5];
		$this->assertEquals(TEAM_ID_PURPLE, $purple->id);
		$black = $division->teams[6];
		$this->assertEquals(TEAM_ID_BLACK, $black->id);
		$white = $division->teams[7];
		$this->assertEquals(TEAM_ID_WHITE, $white->id);

		$sort_context = [];

		// Initial seeding is Green, Blue, Red, Yellow
		$this->assertEquals(-1, $this->LeagueType->compareTeams($green, $blue, $sort_context));
		$this->assertEquals(-1, $this->LeagueType->compareTeams($red, $yellow, $sort_context));
		$this->assertEquals(1, $this->LeagueType->compareTeams($red, $blue, $sort_context));
		$this->assertEquals(-1, $this->LeagueType->compareTeams($green, $yellow, $sort_context));

		// If Blue had the same initial seeding as Green, they'd be ahead based on name comparison
		$blue->initial_seed = 1;
		$this->assertEquals(1, $this->LeagueType->compareTeams($green, $blue, $sort_context));
	}

	/**
	 * Test schedulePreview method
	 *
	 * @return void
	 */
	public function testSchedulePreview() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test scheduleOptions method
	 *
	 * @return void
	 */
	public function testScheduleOptions() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test scheduleDescription method
	 *
	 * @return void
	 */
	public function testScheduleDescription() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test scheduleRequirements method
	 *
	 * @return void
	 */
	public function testScheduleRequirements() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test canSchedule method
	 *
	 * @return void
	 */
	public function testCanSchedule() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test startSchedule method
	 *
	 * @return void
	 */
	public function testStartSchedule() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test finishSchedule method
	 *
	 * @return void
	 */
	public function testFinishSchedule() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test createEmptyGame method
	 *
	 * @return void
	 */
	public function testCreateEmptyGame() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test createGamesForTeams method
	 *
	 * @return void
	 */
	public function testCreateGamesForTeams() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test addTeamsBalanced method
	 *
	 * @return void
	 */
	public function testAddTeamsBalanced() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test homeAwayRatio method
	 *
	 * @return void
	 */
	public function testHomeAwayRatio() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test assignFieldsByPreferences method
	 *
	 * @return void
	 */
	public function testAssignFieldsByPreferences() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test hasHomeField method
	 *
	 * @return void
	 */
	public function testHasHomeField() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test preferredFieldRatio method
	 *
	 * @return void
	 */
	public function testPreferredFieldRatio() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test selectRandomGameslot method
	 *
	 * @return void
	 */
	public function testSelectRandomGameslot() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test selectWeightedGameslot method
	 *
	 * @return void
	 */
	public function testSelectWeightedGameslot() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test matchingSlots method
	 *
	 * @return void
	 */
	public function testMatchingSlots() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test removeGameslot method
	 *
	 * @return void
	 */
	public function testRemoveGameslot() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test countAvailableGameslotDays method
	 *
	 * @return void
	 */
	public function testCountAvailableGameslotDays() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test nextGameslotDay method
	 *
	 * @return void
	 */
	public function testNextGameslotDay() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
