<?php
namespace App\Test\TestCase\Module;

use App\Core\ModuleRegistry;
use App\Test\Factory\TeamFactory;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;

/**
 * Testing of the "none" league type. For the most part, this actually does
 * testing of LeagueType base class function implementations, which we can't
 * test directly due to the abstract declaration of LeagueType.
 */
class LeagueTypeNoneTest extends ModuleTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Module\LeagueType
	 */
	public $LeagueType;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$this->LeagueType = ModuleRegistry::getInstance()->load('LeagueType:none');
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
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
		return TableRegistry::getTableLocator()->get('Divisions')->get($id, ['contain' => $contain]);
	}

	/**
	 * Test links method
	 */
	public function testLinks(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test schedulingFields method
	 */
	public function testSchedulingFields(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test schedulingFieldsRules method
	 */
	public function testSchedulingFieldsRules(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test newTeam method
	 */
	public function testNewTeam(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test addResults method
	 */
	public function testAddResults(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test sort method
	 */
	public function testSort(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test presort method
	 */
	public function testPresort(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test compareTeams method
	 */
	public function testCompareTeams(): void {
		$teams = TeamFactory::make([
			['name' => 'Red', 'initial_seed' => 3],
			['name' => 'Blue', 'initial_seed' => 2],
			['name' => 'Green', 'initial_seed' => 1],
			['name' => 'Yellow', 'initial_seed' => 4],
		])->getEntities();

		$red = $teams[0];
		$blue = $teams[1];
		$green = $teams[2];
		$yellow = $teams[3];

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
	 */
	public function testSchedulePreview(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test scheduleOptions method
	 */
	public function testScheduleOptions(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test scheduleDescription method
	 */
	public function testScheduleDescription(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test scheduleRequirements method
	 */
	public function testScheduleRequirements(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test canSchedule method
	 */
	public function testCanSchedule(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test startSchedule method
	 */
	public function testStartSchedule(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test finishSchedule method
	 */
	public function testFinishSchedule(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test createEmptyGame method
	 */
	public function testCreateEmptyGame(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test createGamesForTeams method
	 */
	public function testCreateGamesForTeams(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test addTeamsBalanced method
	 */
	public function testAddTeamsBalanced(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test homeAwayRatio method
	 */
	public function testHomeAwayRatio(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test assignFieldsByPreferences method
	 */
	public function testAssignFieldsByPreferences(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test hasHomeField method
	 */
	public function testHasHomeField(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test preferredFieldRatio method
	 */
	public function testPreferredFieldRatio(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test selectRandomGameslot method
	 */
	public function testSelectRandomGameslot(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test selectWeightedGameslot method
	 */
	public function testSelectWeightedGameslot(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test matchingSlots method
	 */
	public function testMatchingSlots(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test removeGameslot method
	 */
	public function testRemoveGameslot(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test countAvailableGameslotDays method
	 */
	public function testCountAvailableGameslotDays(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test nextGameslotDay method
	 */
	public function testNextGameslotDay(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
