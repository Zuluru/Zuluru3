<?php
namespace App\Test\TestCase\Controller;

use App\Test\Factory\GameFactory;
use App\Test\Scenario\DiverseUsersScenario;
use App\Test\Scenario\LeagueScenario;
use App\Test\Scenario\LeagueWithFullScheduleScenario;
use Cake\I18n\FrozenDate;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\LeaguesController Test Case
 */
class LeaguesControllerTest extends ControllerTestCase {

	use ScenarioAwareTrait;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.Days',
		'app.Groups',
		'app.RosterRoles',
		'app.Settings',
	];

	/**
	 * Test index method
	 */
	public function testIndex(): void {
		$this_year = date('Y');
		$last_year = $this_year - 1;

		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueScenario::class, [
			'affiliate' => $affiliates[0], 'coordinator' => [$volunteer], 'divisions' => 2,
		]);

		/** @var \App\Model\Entity\League $affiliate_league */
		$affiliate_league = $this->loadFixtureScenario(LeagueScenario::class, [
			'affiliate' => $affiliates[1],
		]);

		$dates = ['open' => FrozenDate::now()->subYears(1)->subMonths(1), 'close' => FrozenDate::now()->subYears(1)->addMonths(1), 'is_open' => false];
		$this->loadFixtureScenario(LeagueScenario::class, [
			'affiliate' => $affiliates[0],
			'league_details' => $dates,
			'division_details' => $dates,
		]);

		// Admins are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'index'], $admin->id);
		$this->assertResponseContains('/leagues/edit?league=' . $league->id);
		$this->assertResponseContains('/leagues/delete?league=' . $league->id);
		$this->assertResponseContains('/leagues/edit?league=' . $affiliate_league->id);
		$this->assertResponseContains('/leagues/delete?league=' . $affiliate_league->id);
		$this->assertResponseContains('/leagues/index?year=' . $this_year);
		$this->assertResponseContains('/leagues/index?year=' . $last_year);

		// Managers are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'index'], $manager->id);
		$this->assertResponseContains('/leagues/edit?league=' . $league->id);
		$this->assertResponseContains('/leagues/delete?league=' . $league->id);
		$this->assertResponseNotContains('/leagues/edit?league=' . $affiliate_league->id);
		$this->assertResponseNotContains('/leagues/delete?league=' . $affiliate_league->id);
		$this->assertResponseContains('/leagues/index?year=' . $this_year);
		$this->assertResponseContains('/leagues/index?year=' . $last_year);

		// Others are allowed to see the index, but not edit anything
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'index'], $volunteer->id);
		$this->assertResponseNotContains('/leagues/edit?league=' . $league->id);
		$this->assertResponseNotContains('/leagues/delete?league=' . $league->id);
		$this->assertResponseContains('/leagues/index?year=' . $this_year);
		$this->assertResponseContains('/leagues/index?year=' . $last_year);

		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'index'], $player->id);
		$this->assertResponseNotContains('/leagues/edit?league=' . $league->id);
		$this->assertResponseNotContains('/leagues/delete?league=' . $league->id);
		$this->assertResponseContains('/leagues/index?year=' . $this_year);
		$this->assertResponseContains('/leagues/index?year=' . $last_year);

		$this->assertGetAnonymousAccessOk(['controller' => 'Leagues', 'action' => 'index']);
		$this->assertResponseNotContains('/leagues/edit?league=' . $league->id);
		$this->assertResponseNotContains('/leagues/delete?league=' . $league->id);
		$this->assertResponseContains('/leagues/index?year=' . $this_year);
		$this->assertResponseContains('/leagues/index?year=' . $last_year);
	}

	/**
	 * Test summary method
	 */
	public function testSummary(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		/** @var \App\Model\Entity\League $league */
		$this->loadFixtureScenario(LeagueScenario::class, [
			'affiliate' => $admin->affiliates[0], 'coordinator' => $volunteer,
		]);

		// Admins are allowed to view the summary
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'summary'], $admin->id);

		// Managers are allowed to view the summary
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'summary'], $manager->id);

		// Others are not allowed to view the summary
		$this->assertGetAsAccessDenied(['controller' => 'Leagues', 'action' => 'summary'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Leagues', 'action' => 'summary'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Leagues', 'action' => 'summary']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test view method
	 */
	public function testView(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueScenario::class, [
			'affiliate' => $affiliates[0], 'coordinator' => [$volunteer], 'divisions' => 2,
		]);

		/** @var \App\Model\Entity\League $single_league */
		$single_league = $this->loadFixtureScenario(LeagueScenario::class, [
			'affiliate' => $affiliates[0], 'coordinator' => $volunteer,
		]);

		/** @var \App\Model\Entity\League $affiliate_league */
		$affiliate_league = $this->loadFixtureScenario(LeagueScenario::class, [
			'affiliate' => $affiliates[1],
		]);

		// Admins are allowed to view leagues, with full edit and delete permissions
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'view', '?' => ['league' => $league->id]], $admin->id);
		$this->assertResponseContains('/leagues/edit?league=' . $league->id);
		$this->assertResponseContains('/leagues/delete?league=' . $league->id);

		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'view', '?' => ['league' => $affiliate_league->id]], $admin->id);
		$this->assertResponseContains('/leagues/edit?league=' . $affiliate_league->id);
		$this->assertResponseContains('/leagues/delete?league=' . $affiliate_league->id);

		// Managers are allowed to view, edit and delete leagues in their affiliate
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'view', '?' => ['league' => $league->id]], $manager->id);
		$this->assertResponseContains('/leagues/edit?league=' . $league->id);
		$this->assertResponseContains('/leagues/delete?league=' . $league->id);

		// But are not allowed to edit ones in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'view', '?' => ['league' => $affiliate_league->id]], $manager->id);
		$this->assertResponseNotContains('/leagues/edit?league=' . $affiliate_league->id);
		$this->assertResponseNotContains('/leagues/delete?league=' . $affiliate_league->id);

		// Others are allowed to view leagues, but have no edit permissions
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'view', '?' => ['league' => $league->id]], $volunteer->id);
		$this->assertResponseNotContains('/leagues/edit?league=' . $league->id);
		$this->assertResponseNotContains('/leagues/delete?league=' . $league->id);

		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'view', '?' => ['league' => $league->id]], $player->id);
		$this->assertResponseNotContains('/leagues/edit?league=' . $league->id);
		$this->assertResponseNotContains('/leagues/delete?league=' . $league->id);

		$this->assertGetAnonymousAccessOk(['controller' => 'Leagues', 'action' => 'view', '?' => ['league' => $league->id]]);
		$this->assertResponseNotContains('/leagues/edit?league=' . $league->id);
		$this->assertResponseNotContains('/leagues/delete?league=' . $league->id);

		// Except that coordinators can edit (but still not delete) the league if they coordinate all the divisions in that league
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'view', '?' => ['league' => $single_league->id]], $volunteer->id);
		$this->assertResponseContains('/leagues/edit?league=' . $single_league->id);
		$this->assertResponseNotContains('/leagues/delete?league=' . $single_league->id);
	}

	/**
	 * Test tooltip method
	 */
	public function testTooltip(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueScenario::class, [
			'affiliate' => $admin->affiliates[0], 'coordinator' => $volunteer, 'divisions' => 2,
		]);
		$divisions = $league->divisions;

		// Anyone is allowed to view league tooltips
		$this->assertGetAjaxAsAccessOk(['controller' => 'Leagues', 'action' => 'tooltip', '?' => ['league' => $league->id]], $admin->id);
		$this->assertResponseContains('/leagues\\/view?league=' . $league->id);
		$this->assertResponseContains('/divisions\\/view?division=' . $divisions[0]->id);
		$this->assertResponseContains('/divisions\\/schedule?division=' . $divisions[0]->id);
		$this->assertResponseContains('/divisions\\/standings?division=' . $divisions[0]->id);
		$this->assertResponseContains('/divisions\\/view?division=' . $divisions[1]->id);
		$this->assertResponseContains('/divisions\\/schedule?division=' . $divisions[1]->id);
		$this->assertResponseContains('/divisions\\/standings?division=' . $divisions[1]->id);

		$this->assertGetAjaxAsAccessRedirect(['controller' => 'Leagues', 'action' => 'tooltip', '?' => ['league' => 0]],
			$admin->id, ['controller' => 'Leagues', 'action' => 'index'],
			'Invalid league.');

		$this->assertGetAjaxAsAccessOk(['controller' => 'Leagues', 'action' => 'tooltip', '?' => ['league' => $league->id]], $manager->id);
		$this->assertResponseContains('/leagues\\/view?league=' . $league->id);
		$this->assertResponseContains('/divisions\\/view?division=' . $divisions[0]->id);
		$this->assertResponseContains('/divisions\\/schedule?division=' . $divisions[0]->id);
		$this->assertResponseContains('/divisions\\/standings?division=' . $divisions[0]->id);
		$this->assertResponseContains('/divisions\\/view?division=' . $divisions[1]->id);
		$this->assertResponseContains('/divisions\\/schedule?division=' . $divisions[1]->id);
		$this->assertResponseContains('/divisions\\/standings?division=' . $divisions[1]->id);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Leagues', 'action' => 'tooltip', '?' => ['league' => $league->id]], $volunteer->id);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Leagues', 'action' => 'tooltip', '?' => ['league' => $league->id]], $player->id);
		$this->assertResponseContains('/leagues\\/view?league=' . $league->id);
		$this->assertResponseContains('/divisions\\/view?division=' . $divisions[0]->id);
		$this->assertResponseContains('/divisions\\/schedule?division=' . $divisions[0]->id);
		$this->assertResponseContains('/divisions\\/standings?division=' . $divisions[0]->id);
		$this->assertResponseContains('/divisions\\/view?division=' . $divisions[1]->id);
		$this->assertResponseContains('/divisions\\/schedule?division=' . $divisions[1]->id);
		$this->assertResponseContains('/divisions\\/standings?division=' . $divisions[1]->id);

		$this->assertGetAjaxAnonymousAccessOk(['controller' => 'Leagues', 'action' => 'tooltip', '?' => ['league' => $league->id]]);
	}

	/**
	 * Test participation method
	 */
	public function testParticipation(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueScenario::class, [
			'affiliate' => $admin->affiliates[0], 'coordinator' => [$volunteer], 'divisions' => 2,
		]);

		// Admins are allowed to view the participation report
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'participation', '?' => ['league' => $league->id]], $admin->id);

		// Managers are allowed to view the participation report
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'participation', '?' => ['league' => $league->id]], $manager->id);

		// Others are not allowed to view the participation report
		$this->assertGetAsAccessDenied(['controller' => 'Leagues', 'action' => 'participation', '?' => ['league' => $league->id]], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Leagues', 'action' => 'participation', '?' => ['league' => $league->id]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Leagues', 'action' => 'participation', '?' => ['league' => $league->id]]);
	}

	/**
	 * Test add method as an admin
	 */
	public function testAddAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$affiliates = $admin->affiliates;
		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueScenario::class, [
			'affiliate' => $affiliates[0],
		]);

		// Admins are allowed to add new leagues anywhere
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'add'], $admin->id);
		// TODO: Database has default value of "1" for event affiliate_id, which auto-selects the primary affiliate in normal use.
		// Unit tests get some other ID for the affiliates, #1 doesn't exist, so there is no option selected. Either fix the
		// test or fix the default in the template or get rid of the default in the database. All only applies when there are
		// multiple affiliates anyway, otherwise the form makes the affiliate_id a hidden input.
		$this->assertResponseContains('<option value="' . $affiliates[0]->id . '">' . $affiliates[0]->name . '</option>');
		$this->assertResponseContains('<option value="' . $affiliates[1]->id . '">' . $affiliates[1]->name . '</option>');

		// If a league ID is given, we will clone that league
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'add', '?' => ['league' => $league->id]], $admin->id);
		$this->assertResponseRegExp('#<input type="text" name="name"[^>]*value="' . $league->name . '"#ms');
	}

	/**
	 * Test add method as a manager
	 */
	public function testAddAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\League $affiliate_league */
		$affiliate_league = $this->loadFixtureScenario(LeagueScenario::class, [
			'affiliate' => $affiliates[1],
		]);

		// Managers are allowed to add new leagues in their own affiliate, but not others
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'add'], $manager->id);
		$this->assertResponseContains('<input type="hidden" name="affiliate_id" value="' . $affiliates[0]->id . '"/>');
		$this->assertResponseNotContains('<option value="' . $affiliates[1]->id . '">' . $affiliates[1]->name . '</option>');

		$this->assertGetAsAccessDenied(['controller' => 'Leagues', 'action' => 'add', '?' => ['league' => $affiliate_league->id]], $manager->id);
	}

	/**
	 * Test add method as others
	 */
	public function testAddAsOthers(): void {
		[$volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['volunteer', 'player']);

		// Others are not allowed to add
		$this->assertGetAsAccessDenied(['controller' => 'Leagues', 'action' => 'add'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Leagues', 'action' => 'add'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Leagues', 'action' => 'add']);
	}

	/**
	 * Test edit method as an admin
	 */
	public function testEditAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueScenario::class, [
			'affiliate' => $affiliates[0],
		]);

		/** @var \App\Model\Entity\League $affiliate_league */
		$affiliate_league = $this->loadFixtureScenario(LeagueScenario::class, [
			'affiliate' => $affiliates[1],
		]);

		// Admins are allowed to edit leagues anywhere
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'edit', '?' => ['league' => $league->id]], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'edit', '?' => ['league' => $affiliate_league->id]], $admin->id);
	}

	/**
	 * Test edit method as a manager
	 */
	public function testEditAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueScenario::class, [
			'affiliate' => $affiliates[0],
		]);

		/** @var \App\Model\Entity\League $affiliate_league */
		$affiliate_league = $this->loadFixtureScenario(LeagueScenario::class, [
			'affiliate' => $affiliates[1],
		]);

		// Managers are allowed to edit leagues in their own affiliate, but not others
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'edit', '?' => ['league' => $league->id]], $manager->id);
		$this->assertGetAsAccessDenied(['controller' => 'Leagues', 'action' => 'edit', '?' => ['league' => $affiliate_league->id]], $manager->id);
	}

	/**
	 * Test edit method as a coordinator
	 */
	public function testEditAsCoordinator(): void {
		[$admin, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer']);
		$affiliate = $admin->affiliates[0];

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueScenario::class, [
			'affiliate' => $affiliate, 'coordinator' => [$volunteer], 'divisions' => 2,
		]);

		/** @var \App\Model\Entity\League $single_league */
		$single_league = $this->loadFixtureScenario(LeagueScenario::class, [
			'affiliate' => $affiliate, 'coordinator' => $volunteer,
		]);

		// Coordinators are allowed to edit leagues where they coordinate all the divisions
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'edit', '?' => ['league' => $single_league->id]], $volunteer->id);

		// But not leagues where they coordinate only some divisions
		$this->assertGetAsAccessDenied(['controller' => 'Leagues', 'action' => 'edit', '?' => ['league' => $league->id]], $volunteer->id);
	}

	/**
	 * Test edit method as others
	 */
	public function testEditAsOthers(): void {
		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);
		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueScenario::class, [
			'affiliate' => $admin->affiliates[0], 'coordinator' => $volunteer,
		]);

		// Others are not allowed to edit leagues
		$this->assertGetAsAccessDenied(['controller' => 'Leagues', 'action' => 'edit', '?' => ['league' => $league->id]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Leagues', 'action' => 'edit', '?' => ['league' => $league->id]]);
	}

	/**
	 * Test add_division method as an admin
	 */
	public function testAddDivisionAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		// Admins are allowed to add division
		$this->assertGetAjaxAsAccessOk(['controller' => 'Leagues', 'action' => 'add_division', '?' => ['league' => $league->id]], $admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add_division method as a manager
	 */
	public function testAddDivisionAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		// Managers are allowed to add divisions
		$this->assertGetAjaxAsAccessOk(['controller' => 'Leagues', 'action' => 'add_division', '?' => ['league' => $league->id]], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add_division method as others
	 */
	public function testAddDivisionAsOthers(): void {
		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);
		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueScenario::class, [
			'affiliate' => $admin->affiliates[0], 'coordinator' => $volunteer,
		]);

		// Coordinators are not allowed to add divisions
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Leagues', 'action' => 'add_division', '?' => ['league' => $league->id]], $volunteer->id);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Leagues', 'action' => 'add_division', '?' => ['league' => $league->id]], $player->id);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Leagues', 'action' => 'add_division', '?' => ['league' => $league->id]]);
	}

	/**
	 * Test delete method as an admin
	 */
	public function testDeleteAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer']);
		$affiliate = $admin->affiliates[0];

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueScenario::class, [
			'affiliate' => $affiliate, 'coordinator' => $volunteer,
		]);

		/** @var \App\Model\Entity\League $dependent_league */
		$dependent_league = $this->loadFixtureScenario(LeagueScenario::class, [
			'affiliate' => $affiliate,
		]);
		GameFactory::make(['division_id' => $dependent_league->divisions[0]->id])->persist();

		// Admins are allowed to delete leagues
		$this->assertPostAsAccessRedirect(['controller' => 'Leagues', 'action' => 'delete', '?' => ['league' => $league->id]],
			$admin->id, [], ['controller' => 'Leagues', 'action' => 'index'],
			'The league has been deleted.');

		// But not ones with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'Leagues', 'action' => 'delete', '?' => ['league' => $dependent_league->id]],
			$admin->id, [], ['controller' => 'Leagues', 'action' => 'index'],
			'#The following records reference this league, so it cannot be deleted#');
	}

	/**
	 * Test delete method as a manager
	 */
	public function testDeleteAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueScenario::class, [
			'affiliate' => $affiliates[0],
		]);

		/** @var \App\Model\Entity\League $affiliate_league */
		$affiliate_league = $this->loadFixtureScenario(LeagueScenario::class, [
			'affiliate' => $affiliates[1],
		]);

		// Managers are allowed to delete leagues in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Leagues', 'action' => 'delete', '?' => ['league' => $league->id]],
			$manager->id, [], ['controller' => 'Leagues', 'action' => 'index'],
			'The league has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Leagues', 'action' => 'delete', '?' => ['league' => $affiliate_league->id]],
			$manager->id);
	}

	/**
	 * Test delete method as others
	 */
	public function testDeleteAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);
		$affiliate = $admin->affiliates[0];
		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueScenario::class, [
			'affiliate' => $affiliate, 'coordinator' => $volunteer,
		]);

		// Others are not allowed to delete leagues
		$this->assertPostAsAccessDenied(['controller' => 'Leagues', 'action' => 'delete', '?' => ['league' => $league->id]],
			$volunteer->id);
		$this->assertPostAsAccessDenied(['controller' => 'Leagues', 'action' => 'delete', '?' => ['league' => $league->id]],
			$player->id);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Leagues', 'action' => 'delete', '?' => ['league' => $league->id]]);
	}

	/**
	 * Test schedule method
	 */
	public function testSchedule(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliate = $admin->affiliates[0];

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $affiliate, 'coordinator' => $volunteer]);

		// Anyone is allowed to see the schedule
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'schedule', '?' => ['league' => $league->id]], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'schedule', '?' => ['league' => $league->id]], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'schedule', '?' => ['league' => $league->id]], $volunteer->id);
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'schedule', '?' => ['league' => $league->id]], $player->id);
		$this->assertGetAnonymousAccessOk(['controller' => 'Leagues', 'action' => 'schedule', '?' => ['league' => $league->id]]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test standings method
	 */
	public function testStandings(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliate = $admin->affiliates[0];

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $affiliate, 'coordinator' => $volunteer]);

		// Anyone is allowed to see the standings
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'standings', '?' => ['league' => $league->id]], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'standings', '?' => ['league' => $league->id]], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'standings', '?' => ['league' => $league->id]], $volunteer->id);
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'standings', '?' => ['league' => $league->id]], $player->id);
		$this->assertGetAnonymousAccessOk(['controller' => 'Leagues', 'action' => 'standings', '?' => ['league' => $league->id]]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test slots method
	 */
	public function testSlots(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliate = $admin->affiliates[0];

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $affiliate, 'coordinator' => $volunteer]);

		// Admins are allowed to see the slots report
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'slots', '?' => ['league' => $league->id]], $admin->id);

		// Managers are allowed to see the slots report
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'slots', '?' => ['league' => $league->id]], $manager->id);

		// Coordinators are allowed to see the slots report of leagues they coordinate all divisions of
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'slots', '?' => ['league' => $league->id]], $volunteer->id);

		// Others are not allowed to see the slots report
		$this->assertGetAsAccessDenied(['controller' => 'Leagues', 'action' => 'slots', '?' => ['league' => $league->id]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Leagues', 'action' => 'slots', '?' => ['league' => $league->id]]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

}
