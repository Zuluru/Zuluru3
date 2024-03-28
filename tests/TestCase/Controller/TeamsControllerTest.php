<?php
namespace App\Test\TestCase\Controller;

use App\Model\Entity\Event;
use App\Model\Entity\League;
use App\Model\Entity\Person;
use App\Model\Entity\Team;
use App\Model\Entity\TeamsPerson;
use App\PasswordHasher\HasherTrait;
use App\Test\Factory\DivisionFactory;
use App\Test\Factory\EventFactory;
use App\Test\Factory\LeagueFactory;
use App\Test\Factory\LeaguesStatTypeFactory;
use App\Test\Factory\NoteFactory;
use App\Test\Factory\PeoplePersonFactory;
use App\Test\Factory\PersonFactory;
use App\Test\Factory\RegistrationFactory;
use App\Test\Factory\TeamEventFactory;
use App\Test\Factory\TeamFactory;
use App\Test\Factory\TeamsPersonFactory;
use App\Test\Scenario\DiverseUsersScenario;
use App\Test\Scenario\LeagueScenario;
use App\Test\Scenario\LeagueWithMinimalScheduleScenario;
use App\Test\Scenario\TeamScenario;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\EmailTrait;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

use function App\Lib\base64_url_encode;

/**
 * App\Controller\TeamsController Test Case
 */
class TeamsControllerTest extends ControllerTestCase {

	use EmailTrait;
	use HasherTrait;
	use ScenarioAwareTrait;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.EventTypes',
		'app.Groups',
		'app.RosterRoles',
		'app.Settings',
		'app.StatTypes',
	];

	public function tearDown(): void {
		// Cleanup any emails that were sent
		$this->cleanupEmailTrait();

		parent::tearDown();
	}

	private function createRegistrations(Event $event, array $details): void {
		foreach ($details as $detail) {
			RegistrationFactory::make([
				'payment' => $detail[1],
			])
				->with('People', $detail[0])
				->with('Events', $event)
				->with('Prices', $event->prices[0])
				->persist();
		}
	}
	/**
	 * Test index method
	 */
	public function testIndex(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		LeagueFactory::make(['affiliate_id' => $admin->affiliates[0]->id])
			->with('Divisions.Teams[4]')
			->persist();

		// Anyone is allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'index'], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'index'], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'index'], $volunteer->id);
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'index'], $player->id);
		$this->assertGetAnonymousAccessOk(['controller' => 'Teams', 'action' => 'index']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test letter method
	 */
	public function testLetter(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var League $league */
		$league = LeagueFactory::make(['affiliate_id' => $admin->affiliates[0]->id])
			->with('Divisions.Teams[2]')
			->persist();
		$letter = $league->divisions[0]->teams[0]->name[0];

		// Anyone is allowed to see the list by letter
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'letter', '?' => ['letter' => $letter]], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'letter', '?' => ['letter' => $letter]], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'letter', '?' => ['letter' => $letter]], $volunteer->id);
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'letter', '?' => ['letter' => $letter]], $player->id);
		$this->assertGetAnonymousAccessOk(['controller' => 'Teams', 'action' => 'letter', '?' => ['letter' => $letter]]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test join method
	 */
	public function testJoin(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var League $league */
		$league = LeagueFactory::make(['affiliate_id' => $admin->affiliates[0]->id, 'is_open' => true])
			->with('Divisions', DivisionFactory::make(['is_open' => true])
				->with('Teams', [
					['open_roster' => true],
					['open_roster' => false],
				])
			)
			->persist();
		[$open_team, $closed_team] = $league->divisions[0]->teams;

		// Anyone logged in is allowed to try to find teams to join
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'join'], $admin->id);
		$this->assertResponseContains('/teams/roster_request?team=' . $open_team->id);
		$this->assertResponseNotContains('/teams/roster_request?team=' . $closed_team->id);

		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'join'], $manager->id);
		$this->assertResponseContains('/teams/roster_request?team=' . $open_team->id);
		$this->assertResponseNotContains('/teams/roster_request?team=' . $closed_team->id);

		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'join'], $volunteer->id);
		$this->assertResponseContains('/teams/roster_request?team=' . $open_team->id);
		$this->assertResponseNotContains('/teams/roster_request?team=' . $closed_team->id);

		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'join'], $player->id);
		$this->assertResponseContains('/teams/roster_request?team=' . $open_team->id);
		$this->assertResponseNotContains('/teams/roster_request?team=' . $closed_team->id);

		// Others are not allowed to join any teams
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'join']);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'join']);
	}

	/**
	 * Test unassigned method
	 */
	public function testUnassigned(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var Team $assigned_team */
		$assigned_team = $this->loadFixtureScenario(TeamScenario::class, ['affiliate' => $admin->affiliates[0]]);
		/** @var Team $unassigned_team */
		$unassigned_team = $this->loadFixtureScenario(TeamScenario::class, ['affiliate' => $admin->affiliates[0], 'division' => null]);

		// Admins are allowed to see the unassigned teams list
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'unassigned'], $admin->id);
		$this->assertResponseContains('/teams/view?team=' . $unassigned_team->id);
		$this->assertResponseNotContains('/teams/view?team=' . $assigned_team->id);

		// Managers are allowed to see the unassigned teams list
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'unassigned'], $manager->id);

		// Others are not allowed to see the unassigned teams list
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'unassigned'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'unassigned'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'unassigned']);
	}

	/**
	 * Test statistics method
	 */
	public function testStatistics(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		LeagueFactory::make(['affiliate_id' => $admin->affiliates[0]->id])
			->with('Divisions.Teams[2]')
			->persist();

		// Admins are allowed to view statistics
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'statistics'], $admin->id);

		// Managers are allowed to view statistics
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'statistics'], $manager->id);

		// Others are not allowed to view statistics
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'statistics'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'statistics'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'statistics']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test compareAffiliateAndCount method
	 */
	public function testCompareAffiliateAndCount(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test view method
	 */
	public function testView(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'captain' => true,
				'player' => $player,
			],
		]);

		/** @var Team $affiliate_team */
		$affiliate_team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[1],
		]);

		// Admins are allowed to view teams, with full edit permissions
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], $admin->id);
		// The strings for edit are all longer here than other places, because there can be simple edit links in help text.
		$this->assertResponseContains('<div><a href="' . Configure::read('App.base') . '/teams/edit?team=' . $team->id);
		$this->assertResponseContains('/teams/delete?team=' . $team->id);

		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $affiliate_team->id]], $admin->id);
		$this->assertResponseContains('<div><a href="' . Configure::read('App.base') . '/teams/edit?team=' . $affiliate_team->id);
		$this->assertResponseContains('/teams/delete?team=' . $affiliate_team->id);

		// Managers are allowed to view teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], $manager->id);
		$this->assertResponseContains('<div><a href="' . Configure::read('App.base') . '/teams/edit?team=' . $team->id);
		$this->assertResponseContains('/teams/delete?team=' . $team->id);

		// But are not allowed to edit ones in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $affiliate_team->id]], $manager->id);
		$this->assertResponseNotContains('<div><a href="' . Configure::read('App.base') . '/teams/edit?team=' . $affiliate_team->id);
		$this->assertResponseNotContains('/teams/delete?team=' . $affiliate_team->id);

		// Coordinators are allowed to view teams but cannot edit
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], $volunteer->id);
		$this->assertResponseNotContains('<div><a href="' . Configure::read('App.base') . '/teams/edit?team=' . $team->id);
		$this->assertResponseNotContains('/teams/delete?team=' . $team->id);

		// Captains are allowed to view and edit their teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], $team->people[0]->id);
		$this->assertResponseContains('<div><a href="' . Configure::read('App.base') . '/teams/edit?team=' . $team->id);
		// TODO: Test that captains can delete their own teams when the registration module is turned off
		$this->assertResponseNotContains('/teams/delete?team=' . $team->id);

		// Others are allowed to view teams, but have no edit permissions
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], $player->id);
		$this->assertResponseNotContains('<div><a href="' . Configure::read('App.base') . '/teams/edit?team=' . $team->id);
		$this->assertResponseNotContains('/teams/delete?team=' . $team->id);

		$this->assertGetAnonymousAccessOk(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]]);
		$this->assertResponseNotContains('<div><a href="' . Configure::read('App.base') . '/teams/edit?team=' . $team->id);
		$this->assertResponseNotContains('/teams/delete?team=' . $team->id);
	}

	/**
	 * Test numbers method as an admin
	 */
	public function testNumbersAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		// Admins are allowed to set numbers
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'numbers', '?' => ['team' => $team->id]], $admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test numbers method as a manager
	 */
	public function testNumbersAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		/** @var Team $affiliate_team */
		$affiliate_team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[1],
		]);

		// Managers are allowed to set numbers
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'numbers', '?' => ['team' => $team->id]], $manager->id);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'numbers', '?' => ['team' => $affiliate_team->id]], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test numbers method as a captain
	 */
	public function testNumbersAsCaptain(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'captain' => true,
			],
			'division_details' => ['is_open' => true],
		]);
		$captain = $team->people[0];

		// Captains are allowed to set numbers before the roster deadline
		FrozenDate::setTestNow($team->division->rosterDeadline());
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'numbers', '?' => ['team' => $team->id]], $captain->id);

		// But not after
		FrozenDate::setTestNow($team->division->rosterDeadline()->addDays(1));
		$this->assertGetAsAccessRedirect(['controller' => 'Teams', 'action' => 'numbers', '?' => ['team' => $team->id]],
			$captain->id, ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'The roster deadline for this division has already passed.');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test numbers method as a coordinator
	 */
	public function testNumbersAsCoordinator(): void {
		[$admin, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'coordinator' => $volunteer,
		]);

		// Coordinators are allowed to set numbers
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'numbers', '?' => ['team' => $team->id]], $volunteer->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test numbers method as a player
	 */
	public function testNumbersAsPlayer(): void {
		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'captain' => true,
				'player' => $player,
			],
			'division_details' => ['is_open' => true],
		]);
		$captain = $team->people[0];

		// Players are allowed to set only their own number
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'numbers', '?' => ['team' => $team->id, 'person' => $player->id]], $player->id);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'numbers', '?' => ['team' => $team->id]], $player->id);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'numbers', '?' => ['team' => $team->id, 'person' => $captain->id]], $player->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test numbers method as others
	 */
	public function testNumbersAsOthers(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		// Others are not allowed to set numbers
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'numbers', '?' => ['team' => $team->id]]);
	}

	/**
	 * Test stats method
	 */
	public function testStats(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'coordinator' => $volunteer,
			'roles' => [
				'captain' => true,
				'player' => $player,
			],
			'division_details' => ['is_open' => true],
			'league_details' => ['stat_tracking' => 'always'],
		]);
		$captain = $team->people[0];

		// Anyone logged in is allowed to see stats
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'stats', '?' => ['team' => $team->id]], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'stats', '?' => ['team' => $team->id]], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'stats', '?' => ['team' => $team->id]], $volunteer->id);
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'stats', '?' => ['team' => $team->id]], $captain->id);
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'stats', '?' => ['team' => $team->id]], $player->id);

		// Others are not allowed to see stats
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'stats', '?' => ['team' => $team->id]]);
	}

	/**
	 * Test stat_sheet method
	 */
	public function testStatSheet(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'coordinator' => $volunteer,
			'roles' => [
				'captain' => true,
				'player' => $player,
			],
			'division_details' => ['is_open' => true],
			'league_details' => ['stat_tracking' => 'always'],
		]);
		$captain = $team->people[0];

		LeaguesStatTypeFactory::make(['league_id' => $team->division->league_id, 'stat_type_id' => STAT_TYPE_ID_ULTIMATE_GOALS])->persist();

		// Admins are allowed to see the stat sheet
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'stat_sheet', '?' => ['team' => $team->id]], $admin->id);

		// Managers are allowed to see the stat sheet
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'stat_sheet', '?' => ['team' => $team->id]], $manager->id);

		// Coordinators are allowed to see the stat sheet
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'stat_sheet', '?' => ['team' => $team->id]], $volunteer->id);

		// Captains are allowed to see the stat sheet for their teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'stat_sheet', '?' => ['team' => $team->id]], $captain->id);

		// Others are not allowed to see the stat sheet
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'stat_sheet', '?' => ['team' => $team->id]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'stat_sheet', '?' => ['team' => $team->id]]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test tooltip method
	 */
	public function testTooltip(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'coordinator' => $volunteer,
		]);

		// Anyone is allowed to view team tooltips
		$this->assertGetAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'tooltip', '?' => ['team' => $team->id]], $admin->id);
		$this->assertResponseContains('/teams\\/view?team=' . $team->id);
		$this->assertResponseContains('/teams\\/schedule?team=' . $team->id);
		$this->assertResponseContains('/divisions\\/standings?division=' . $team->division_id . '&amp;team=' . $team->id);
		$this->assertResponseContains('/divisions\\/view?division=' . $team->division_id);
		$this->assertResponseContains('/divisions\\/schedule?division=' . $team->division_id);

		$this->assertGetAjaxAsAccessRedirect(['controller' => 'Teams', 'action' => 'tooltip', '?' => ['team' => 0]],
			$admin->id, ['controller' => 'Teams', 'action' => 'index'],
			'Invalid team.');

		$this->assertGetAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'tooltip', '?' => ['team' => $team->id]], $manager->id);
		$this->assertResponseContains('/teams\\/view?team=' . $team->id);
		$this->assertResponseContains('/teams\\/schedule?team=' . $team->id);
		$this->assertResponseContains('/divisions\\/standings?division=' . $team->division_id . '&amp;team=' . $team->id);
		$this->assertResponseContains('/divisions\\/view?division=' . $team->division_id);
		$this->assertResponseContains('/divisions\\/schedule?division=' . $team->division_id);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'tooltip', '?' => ['team' => $team->id]], $volunteer->id);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'tooltip', '?' => ['team' => $team->id]], $player->id);
		$this->assertResponseContains('/teams\\/view?team=' . $team->id);
		$this->assertResponseContains('/teams\\/schedule?team=' . $team->id);
		$this->assertResponseContains('/divisions\\/standings?division=' . $team->division_id . '&amp;team=' . $team->id);
		$this->assertResponseContains('/divisions\\/view?division=' . $team->division_id);
		$this->assertResponseContains('/divisions\\/schedule?division=' . $team->division_id);

		$this->assertGetAjaxAnonymousAccessOk(['controller' => 'Teams', 'action' => 'tooltip', '?' => ['team' => $team->id]]);
	}

	/**
	 * Test add method as an admin
	 */
	public function testAddAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		// Admins are allowed to add teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'add'], $admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add method as a manager
	 */
	public function testAddAsManager(): void {
		[$manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['manager']);

		// Managers are allowed to add teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'add'], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add method as others
	 */
	public function testAddAsOthers(): void {
		[$volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['volunteer', 'player']);

		// Others are not allowed to add teams
		$this->assertGetAsAccessRedirect(['controller' => 'Teams', 'action' => 'add'],
			$volunteer->id, '/',
			'This system creates teams through the registration process. Team creation through Zuluru is disabled. If you need a team created for some other reason (e.g. a touring team), please email admin@zuluru.org with the details, or call the office.');
		$this->assertGetAsAccessRedirect(['controller' => 'Teams', 'action' => 'add'],
			$player->id, '/',
			'This system creates teams through the registration process. Team creation through Zuluru is disabled. If you need a team created for some other reason (e.g. a touring team), please email admin@zuluru.org with the details, or call the office.');
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'add']);
	}

	/**
	 * Test edit method as an admin
	 */
	public function testEditAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		// Admins are allowed to  teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'edit', '?' => ['team' => $team->id]], $admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test edit method as a manager
	 */
	public function testEditAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		/** @var Team $affiliate_team */
		$affiliate_team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[1],
		]);

		// Managers are allowed to edit teams in their affiliate
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'edit', '?' => ['team' => $team->id]], $manager->id);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'edit', '?' => ['team' => $affiliate_team->id]], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test edit method as a coordinator
	 */
	public function testEditAsCoordinator(): void {
		[$admin, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'coordinator' => $volunteer,
		]);

		// Coordinators are not allowed to edit teams
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'edit', '?' => ['team' => $team->id]], $volunteer->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test edit method as a captain
	 */
	public function testEditAsCaptain(): void {
		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'captain' => $player,
			],
		]);

		/** @var Team $other_team */
		$other_team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		// Captains are allowed to edit their own teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'edit', '?' => ['team' => $team->id]], $player->id);

		// But not others
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'edit', '?' => ['team' => $other_team->id]], $player->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test edit method as others
	 */
	public function testEditAsOthers(): void {
		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'player' => $player,
			],
		]);

		// Others are not allowed to edit teams
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'edit', '?' => ['team' => $team->id]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'edit', '?' => ['team' => $team->id]]);
	}

	/**
	 * Test note method as an admin
	 */
	public function testNoteAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		// Admins are allowed to add notes
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'note', '?' => ['team' => $team->id]], $admin->id);

		// Empty notes don't get added
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'note', '?' => ['team' => $team->id]],
			$admin->id, [
				'team_id' => $team->id,
				'visibility' => VISIBILITY_PRIVATE,
				'note' => '',
			], ['action' => 'view', '?' => ['team' => $team->id]], 'You entered no text, so no note was added.');

		// Add a private note
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'note', '?' => ['team' => $team->id]],
			$admin->id, [
				'team_id' => $team->id,
				'visibility' => VISIBILITY_PRIVATE,
				'note' => 'This is a private note.',
			], ['action' => 'view', '?' => ['team' => $team->id]], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertNoMailSent();

		// Add a note for all admins to see
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'note', '?' => ['team' => $team->id]],
			$admin->id, [
				'team_id' => $team->id,
				'visibility' => VISIBILITY_ADMIN,
				'note' => 'This is an admin note.',
			], ['action' => 'view', '?' => ['team' => $team->id]], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertNoMailSent();

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], $admin->id);
		$this->assertResponseContains('This is a private note.');
		$this->assertResponseContains('This is an admin note.');

		// Check the manager can also see the admin one
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], $manager->id);
		$this->assertResponseNotContains('This is a private note.');
		$this->assertResponseContains('This is an admin note.');

		// Empty notes don't get added
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'note', '?' => ['team' => $team->id]],
			$admin->id, [
				'team_id' => $team->id,
				'visibility' => VISIBILITY_PRIVATE,
				'note' => '',
			], ['action' => 'view', '?' => ['team' => $team->id]], 'You entered no text, so no note was added.');

		// Add a private note
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'note', '?' => ['team' => $team->id]],
			$admin->id, [
				'team_id' => $team->id,
				'visibility' => VISIBILITY_PRIVATE,
				'note' => 'This is a private note.',
			], ['action' => 'view', '?' => ['team' => $team->id]], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertNoMailSent();

		// Add a note for all admins to see
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'note', '?' => ['team' => $team->id]],
			$admin->id, [
				'team_id' => $team->id,
				'visibility' => VISIBILITY_ADMIN,
				'note' => 'This is an admin note.',
			], ['action' => 'view', '?' => ['team' => $team->id]], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertNoMailSent();

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], $admin->id);
		$this->assertResponseContains('This is a private note.');
		$this->assertResponseContains('This is an admin note.');

		// Check the manager can also see the admin one
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], $manager->id);
		$this->assertResponseNotContains('This is a private note.');
		$this->assertResponseContains('This is an admin note.');
	}

	/**
	 * Test note method as a manager
	 */
	public function testNoteAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		// Managers are allowed to add notes
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'note', '?' => ['team' => $team->id]], $manager->id);

		// Add a private note
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'note', '?' => ['team' => $team->id]],
			$manager->id, [
				'team_id' => $team->id,
				'visibility' => VISIBILITY_PRIVATE,
				'note' => 'This is a private note.',
			], ['action' => 'view', '?' => ['team' => $team->id]], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertNoMailSent();

		// Add a note for all admins to see
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'note', '?' => ['team' => $team->id]],
			$manager->id, [
				'team_id' => $team->id,
				'visibility' => VISIBILITY_ADMIN,
				'note' => 'This is an admin note.',
			], ['action' => 'view', '?' => ['team' => $team->id]], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertNoMailSent();

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], $manager->id);
		$this->assertResponseContains('This is a private note.');
		$this->assertResponseContains('This is an admin note.');

		// Check the admin can also see the admin one
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], $admin->id);
		$this->assertResponseNotContains('This is a private note.');
		$this->assertResponseContains('This is an admin note.');
	}

	/**
	 * Test note method as a coordinator
	 */
	public function testNoteAsCoordinator(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'coordinator' => $volunteer,
		]);

		// Coordinators are allowed to add notes
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'note', '?' => ['team' => $team->id]], $volunteer->id);

		// Add a private note
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'note', '?' => ['team' => $team->id]],
			$volunteer->id, [
				'team_id' => $team->id,
				'visibility' => VISIBILITY_PRIVATE,
				'note' => 'This is a private note.',
			], ['action' => 'view', '?' => ['team' => $team->id]], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertNoMailSent();

		// Add a note for all coordinators to see
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'note', '?' => ['team' => $team->id]],
			$volunteer->id, [
				'team_id' => $team->id,
				'visibility' => VISIBILITY_COORDINATOR,
				'note' => 'This is a coordinator note.',
			], ['action' => 'view', '?' => ['team' => $team->id]], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertNoMailSent();

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], $volunteer->id);
		$this->assertResponseContains('This is a private note.');
		$this->assertResponseContains('This is a coordinator note.');
	}

	/**
	 * Test note method as a captain
	 */
	public function testNoteAsCaptain(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'captain' => 2,
				'player' => $player,
			],
		]);
		[$captain1, $captain2] = $team->people;

		// Captains are allowed to add notes
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'note', '?' => ['team' => $team->id]], $captain1->id);

		// Add a note for all captains to see
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'note', '?' => ['team' => $team->id]],
			$captain2->id, [
				'team_id' => $team->id,
				'visibility' => VISIBILITY_CAPTAINS,
				'note' => 'This is a captain note.',
			], ['action' => 'view', '?' => ['team' => $team->id]], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertNoMailSent();

		// Make sure it was added successfully
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], $captain2->id);
		$this->assertResponseContains('This is a captain note.');

		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], $player->id);
		$this->assertResponseNotContains('This is a captain note.');

		// Add a note for the team to see
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'note', '?' => ['team' => $team->id]],
			$captain1->id, [
				'team_id' => $team->id,
				'visibility' => VISIBILITY_TEAM,
				'note' => 'This is a team note.',
			], ['action' => 'view', '?' => ['team' => $team->id]], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertNoMailSent();

		// Make sure it was added successfully
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], $captain2->id);
		$this->assertResponseContains('This is a team note.');

		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], [$player->id, $player->id]);
		$this->assertResponseContains('This is a team note.');
	}

	/**
	 * Test note method as a player
	 */
	public function testNoteAsPlayer(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'captain' => true,
				'player' => $player,
			],
		]);
		$captain = $team->people[0];

		// Players are allowed to add notes
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'note', '?' => ['team' => $team->id]], $player->id);

		// Add a note for all captains to see
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'note', '?' => ['team' => $team->id]],
			$player->id, [
				'team_id' => $team->id,
				'visibility' => VISIBILITY_CAPTAINS,
				'note' => 'This is a captain note.',
			], ['action' => 'view', '?' => ['team' => $team->id]], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertNoMailSent();

		// Make sure it was added successfully
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], $player->id);
		$this->assertResponseContains('This is a captain note.');

		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], $captain->id);
		$this->assertResponseContains('This is a captain note.');

		// Add a note for the team to see
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'note', '?' => ['team' => $team->id]],
			$player->id, [
				'team_id' => $team->id,
				'visibility' => VISIBILITY_TEAM,
				'note' => 'This is a team note.',
			], ['action' => 'view', '?' => ['team' => $team->id]], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertNoMailSent();

		// Make sure it was added successfully
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], $player->id);
		$this->assertResponseContains('This is a team note.');

		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], $captain->id);
		$this->assertResponseContains('This is a team note.');
	}

	/**
	 * Test note method as someone else
	 */
	public function testNoteAsVisitor(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		// People not on the team are allowed to add notes
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'note', '?' => ['team' => $team->id]], $player->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test note method without being logged in
	 */
	public function testNoteAsAnonymous(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		// Others are not allowed to add notes
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'note', '?' => ['team' => $team->id]]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test delete_note method as an admin
	 */
	public function testDeleteNoteAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		$notes = NoteFactory::make([
			['team_id' => $team->id, 'created_person_id' => $manager->id, 'visibility' => VISIBILITY_ADMIN],
			['team_id' => $team->id, 'created_person_id' => $volunteer->id, 'visibility' => VISIBILITY_COORDINATOR],
			['team_id' => $team->id, 'created_person_id' => $player->id, 'visibility' => VISIBILITY_TEAM],
			['team_id' => $team->id, 'created_person_id' => $player->id, 'visibility' => VISIBILITY_PRIVATE],
		])->persist();

		// Admins are allowed to delete admin notes
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'delete_note', '?' => ['note' => $notes[0]->id]],
			$admin->id, [], ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'The note has been deleted.');

		// And coordinator notes
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'delete_note', '?' => ['note' => $notes[1]->id]],
			$admin->id, [], ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'The note has been deleted.');

		// But not other notes
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', '?' => ['note' => $notes[2]->id]],
			$admin->id);
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', '?' => ['note' => $notes[3]->id]],
			$admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test delete_note method as a manager
	 */
	public function testDeleteNoteAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		$notes = NoteFactory::make([
			['team_id' => $team->id, 'created_person_id' => $manager->id, 'visibility' => VISIBILITY_ADMIN],
			['team_id' => $team->id, 'created_person_id' => $volunteer->id, 'visibility' => VISIBILITY_COORDINATOR],
			['team_id' => $team->id, 'created_person_id' => $player->id, 'visibility' => VISIBILITY_TEAM],
			['team_id' => $team->id, 'created_person_id' => $player->id, 'visibility' => VISIBILITY_PRIVATE],
		])->persist();

		// Managers are allowed to delete admin notes
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'delete_note', '?' => ['note' => $notes[0]->id]],
			$manager->id, [], ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'The note has been deleted.');

		// And coordinator notes
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'delete_note', '?' => ['note' => $notes[1]->id]],
			$manager->id, [], ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'The note has been deleted.');

		// But not other notes
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', '?' => ['note' => $notes[2]->id]],
			$manager->id);
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', '?' => ['note' => $notes[3]->id]],
			$manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test delete_note method as a coordinator
	 */
	public function testDeleteNoteAsCoordinator(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'coordinator' => $volunteer,
		]);

		$notes = NoteFactory::make([
			['team_id' => $team->id, 'created_person_id' => $manager->id, 'visibility' => VISIBILITY_ADMIN],
			['team_id' => $team->id, 'created_person_id' => $admin->id, 'visibility' => VISIBILITY_COORDINATOR],
			['team_id' => $team->id, 'created_person_id' => $player->id, 'visibility' => VISIBILITY_TEAM],
			['team_id' => $team->id, 'created_person_id' => $player->id, 'visibility' => VISIBILITY_PRIVATE],
		])->persist();

		// Coordinators are allowed to delete coordinator notes
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'delete_note', '?' => ['note' => $notes[1]->id]],
			$volunteer->id, [], ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'The note has been deleted.');

		// But not other notes
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', '?' => ['note' => $notes[0]->id]],
			$volunteer->id);
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', '?' => ['note' => $notes[2]->id]],
			$volunteer->id);
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', '?' => ['note' => $notes[3]->id]],
			$volunteer->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test delete_note method as a captain
	 */
	public function testDeleteNoteAsCaptain(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'captain' => $player,
			],
		]);

		$notes = NoteFactory::make([
			['team_id' => $team->id, 'created_person_id' => $manager->id, 'visibility' => VISIBILITY_ADMIN],
			['team_id' => $team->id, 'created_person_id' => $volunteer->id, 'visibility' => VISIBILITY_COORDINATOR],
			['team_id' => $team->id, 'created_person_id' => $player->id, 'visibility' => VISIBILITY_TEAM],
			['team_id' => $team->id, 'created_person_id' => $volunteer->id, 'visibility' => VISIBILITY_PRIVATE],
		])->persist();

		// Captains are only allowed to delete notes they created
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'delete_note', '?' => ['note' => $notes[2]->id]],
			$player->id, [], ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'The note has been deleted.');

		// But not other notes
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', '?' => ['note' => $notes[0]->id]],
			$player->id);
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', '?' => ['note' => $notes[1]->id]],
			$player->id);
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', '?' => ['note' => $notes[3]->id]],
			$player->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test delete_note method as a player
	 */
	public function testDeleteNoteAsPlayer(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		$notes = NoteFactory::make([
			['team_id' => $team->id, 'created_person_id' => $manager->id, 'visibility' => VISIBILITY_ADMIN],
			['team_id' => $team->id, 'created_person_id' => $volunteer->id, 'visibility' => VISIBILITY_COORDINATOR],
			['team_id' => $team->id, 'created_person_id' => $player->id, 'visibility' => VISIBILITY_TEAM],
			['team_id' => $team->id, 'created_person_id' => $volunteer->id, 'visibility' => VISIBILITY_PRIVATE],
		])->persist();

		// Players are only allowed to delete notes they created
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'delete_note', '?' => ['note' => $notes[2]->id]],
			$player->id, [], ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'The note has been deleted.');

		// But not other notes
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', '?' => ['note' => $notes[0]->id]],
			$player->id);
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', '?' => ['note' => $notes[1]->id]],
			$player->id);
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', '?' => ['note' => $notes[3]->id]],
			$player->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test delete_note method as someone else
	 */
	public function testDeleteNoteAsVisitor(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		/** @var Person $other */
		$other = PersonFactory::make()->player()->with('Affiliates', $admin->affiliates[0])->persist();

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		$notes = NoteFactory::make([
			['team_id' => $team->id, 'created_person_id' => $manager->id, 'visibility' => VISIBILITY_ADMIN],
			['team_id' => $team->id, 'created_person_id' => $volunteer->id, 'visibility' => VISIBILITY_COORDINATOR],
			['team_id' => $team->id, 'created_person_id' => $player->id, 'visibility' => VISIBILITY_TEAM],
			['team_id' => $team->id, 'created_person_id' => $other->id, 'visibility' => VISIBILITY_PRIVATE],
		])->persist();

		// Visitors are only allowed to delete notes they created
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'delete_note', '?' => ['note' => $notes[3]->id]],
			$other->id, [], ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'The note has been deleted.');

		// But not other notes
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', '?' => ['note' => $notes[0]->id]],
			$other->id);
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', '?' => ['note' => $notes[1]->id]],
			$other->id);
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', '?' => ['note' => $notes[2]->id]],
			$other->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test delete_note method as others
	 */
	public function testDeleteNoteAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		$notes = NoteFactory::make([
			['team_id' => $team->id, 'created_person_id' => $manager->id, 'visibility' => VISIBILITY_ADMIN],
			['team_id' => $team->id, 'created_person_id' => $volunteer->id, 'visibility' => VISIBILITY_COORDINATOR],
			['team_id' => $team->id, 'created_person_id' => $player->id, 'visibility' => VISIBILITY_TEAM],
			['team_id' => $team->id, 'created_person_id' => $volunteer->id, 'visibility' => VISIBILITY_PRIVATE],
		])->persist();

		// Others are not allowed to delete notes
		$this->assertPostAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', '?' => ['note' => $notes[0]->id]]);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', '?' => ['note' => $notes[1]->id]]);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', '?' => ['note' => $notes[2]->id]]);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'delete_note', '?' => ['note' => $notes[3]->id]]);
	}

	/**
	 * Test delete method as an admin
	 */
	public function testDeleteAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		/** @var Team $dependent_team */
		$dependent_team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'player' => $player,
			]
		]);

		// Admins are allowed to delete teams
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'delete', '?' => ['team' => $team->id]],
			$admin->id, [], ['controller' => 'Teams', 'action' => 'index'],
			'The team has been deleted.');

		// But not ones with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'delete', '?' => ['team' => $dependent_team->id]],
			$admin->id, [], ['controller' => 'Teams', 'action' => 'index'],
			'#The following records reference this team, so it cannot be deleted#');
	}

	/**
	 * Test delete method as a manager
	 */
	public function testDeleteAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		/** @var Team $affiliate_team */
		$affiliate_team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[1],
		]);

		// Managers are allowed to delete teams in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'delete', '?' => ['team' => $team->id]],
			$manager->id, [], ['controller' => 'Teams', 'action' => 'index'],
			'The team has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete', '?' => ['team' => $affiliate_team->id]],
			$manager->id);
	}

	/**
	 * Test delete method as a coordinator
	 */
	public function testDeleteAsCoordinator(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'coordinator' => $volunteer,
		]);

		// Coordinators are not allowed to delete teams
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete', '?' => ['team' => $team->id]],
			$volunteer->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test delete method as a captain
	 */
	public function testDeleteAsCaptain(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'captain' => $player,
			],
		]);

		// Team owners are allowed to delete their own teams
		/* TODO: Not at this time
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'delete', '?' => ['team' => $team->id]],
			$player->id, [], ['controller' => 'Teams', 'action' => 'index'],
			'The team has been deleted.');
		*/

		// But not others
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'delete', '?' => ['team' => $team->id]],
			$player->id);
	}

	/**
	 * Test delete method as others
	 */
	public function testDeleteAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'player' => $player,
			],
		]);

		// Others are not allowed to delete teams
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Teams', 'action' => 'delete', '?' => ['team' => $team->id]], $player->id);
		$this->assertPostAjaxAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'delete', '?' => ['team' => $team->id]]);
	}

	/**
	 * Test move method as an admin
	 */
	public function testMoveAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueScenario::class, [
			'affiliate' => $admin->affiliates[0], 'divisions' => 2,
		]);
		$team = TeamFactory::make()->with('Divisions', $league->divisions[0])->persist();

		// Admins are allowed to move teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'move', '?' => ['team' => $team->id]], $admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test move method as a manager
	 */
	public function testMoveAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueScenario::class, [
			'affiliate' => $admin->affiliates[0], 'divisions' => 2,
		]);
		$team = TeamFactory::make()->with('Divisions', $league->divisions[0])->persist();

		// Managers are allowed to move teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'move', '?' => ['team' => $team->id]], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test move method as a coordinator
	 */
	public function testMoveAsCoordinator(): void {
		[$admin, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer']);

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueScenario::class, [
			'affiliate' => $admin->affiliates[0], 'divisions' => 2, 'coordinator' => $volunteer,
		]);
		$team = TeamFactory::make()->with('Divisions', $league->divisions[0])->persist();

		// Coordinators are not allowed to move teams
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'move', '?' => ['team' => $team->id]], $volunteer->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test move method as others
	 */
	public function testMoveAsOthers(): void {
		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueScenario::class, [
			'affiliate' => $admin->affiliates[0], 'divisions' => 2,
		]);
		$team = TeamFactory::make()->with('Divisions', $league->divisions[0])->persist();

		// Others are not allowed to move teams
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'move', '?' => ['team' => $team->id]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'move', '?' => ['team' => $team->id]]);
	}

	/**
	 * Test move method without destination
	 */
	public function testMoveWithoutDestination(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		// Can't move teams if there's nowhere to move them to
		$this->assertGetAsAccessRedirect(['controller' => 'Teams', 'action' => 'move', '?' => ['team' => $team->id]],
			$admin->id, ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'No similar division found to move this team to!');
	}

	/**
	 * Test schedule method
	 */
	public function testSchedule(): void {
		[$admin, $manager, $volunteer, $captain] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithMinimalScheduleScenario::class, ['affiliate' => $admin->affiliates[0], 'coordinator' => $volunteer]);
		$division = $league->divisions[0];
		$game = $division->games[0];
		$bears = $division->teams[0];

		TeamsPersonFactory::make(['person_id' => $captain->id, 'team_id' => $bears->id, 'role' => 'captain'])
			->persist();

		$event = TeamEventFactory::make(['team_id' => $bears->id])->persist();

		// Anyone is allowed to see the schedule
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'schedule', '?' => ['team' => $bears->id]], $admin->id);
		$this->assertResponseContains('/games/edit?game=' . $game->id);
		$this->assertResponseContains('/games/view?game=' . $game->id);
		$this->assertResponseNotContains('/team_events/view?event=' . $event->id);

		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'schedule', '?' => ['team' => $bears->id]], $manager->id);
		$this->assertResponseContains('/games/edit?game=' . $game->id);
		$this->assertResponseContains('/games/view?game=' . $game->id);
		$this->assertResponseNotContains('/team_events/view?event=' . $event->id);

		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'schedule', '?' => ['team' => $bears->id]], $volunteer->id);
		$this->assertResponseContains('/games/edit?game=' . $game->id);
		$this->assertResponseContains('/games/view?game=' . $game->id);
		$this->assertResponseNotContains('/team_events/view?event=' . $event->id);

		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'schedule', '?' => ['team' => $bears->id]], $captain->id);
		$this->assertResponseNotContains('/games/edit?game=' . $game->id);
		$this->assertResponseContains('/games/view?game=' . $game->id);
		$this->assertResponseContains('/team_events/view?event=' . $event->id);

		$this->assertGetAnonymousAccessOk(['controller' => 'Teams', 'action' => 'schedule', '?' => ['team' => $bears->id]]);
		$this->assertResponseNotContains('/games/edit?game=' . $game->id);
		$this->assertResponseContains('/games/view?game=' . $game->id);
		$this->assertResponseNotContains('/team_events/view?event=' . $event->id);
	}

	/**
	 * Test ical method
	 */
	public function testIcal(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		// Can get the ical feed for any team in an active or upcoming league
		FrozenDate::setTestNow($team->division->close->subWeeks(1));
		$this->assertGetAnonymousAccessOk(['controller' => 'Teams', 'action' => 'ical', $team->id]);

		// But not in the past
		FrozenDate::setTestNow($team->division->close->addWeeks(3));
		$this->get(['controller' => 'Teams', 'action' => 'ical', $team->id]);
		$this->assertResponseCode(410);
	}

	/**
	 * Test spirit method
	 */
	public function testSpirit(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithMinimalScheduleScenario::class, ['affiliate' => $admin->affiliates[0], 'coordinator' => $volunteer]);
		$bears = $league->divisions[0]->teams[0];

		/** @var \App\Model\Entity\League $affiliate_league */
		$affiliate_league = $this->loadFixtureScenario(LeagueWithMinimalScheduleScenario::class, ['affiliate' => $admin->affiliates[1]]);
		$affiliate_bears = $affiliate_league->divisions[0]->teams[0];

		/** @var \App\Model\Entity\League $other_league */
		$other_league = $this->loadFixtureScenario(LeagueWithMinimalScheduleScenario::class, ['affiliate' => $admin->affiliates[0]]);
		$other_bears = $other_league->divisions[0]->teams[0];

		// Admins are allowed to see the spirit report
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'spirit', '?' => ['team' => $bears->id]], $admin->id);

		// Managers are allowed to see the spirit report for teams in their affiliate
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'spirit', '?' => ['team' => $bears->id]], $manager->id);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'spirit', '?' => ['team' => $affiliate_bears->id]], $manager->id);

		// Coordinators are allowed to see the spirit report for teams in their divisions
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'spirit', '?' => ['team' => $bears->id]], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'spirit', '?' => ['team' => $other_bears->id]], $volunteer->id);

		// Others are not allowed to see the spirit report
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'spirit', '?' => ['team' => $bears->id]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'spirit', '?' => ['team' => $bears->id]]);
	}

	/**
	 * Test attendance method
	 */
	public function testAttendance(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithMinimalScheduleScenario::class, ['affiliate' => $admin->affiliates[0], 'coordinator' => $volunteer]);
		[$bears, $lions] = $league->divisions[0]->teams;

		/** @var Person $captain */
		$captain = PersonFactory::make()->player()
			->with('TeamsPeople', TeamsPersonFactory::make(['team_id' => $bears->id, 'role' => 'captain']))
			->persist();

		TeamsPersonFactory::make(['person_id' => $player->id, 'team_id' => $bears->id, 'role' => 'player'])
			->persist();

		/** @var \App\Model\Entity\League $affiliate_league */
		$affiliate_league = $this->loadFixtureScenario(LeagueWithMinimalScheduleScenario::class, ['affiliate' => $admin->affiliates[1]]);
		$affiliate_bears = $affiliate_league->divisions[0]->teams[0];

		// Also add a relative to the player on the affiliate Bears
		/** @var Person $relative */
		$relative = PersonFactory::make()
			->withGroup(GROUP_PLAYER)
			->with('Affiliates', $admin->affiliates[1])
			->persist();
		PeoplePersonFactory::make(['person_id' => $player->id, 'relative_id' => $relative->id])->persist();

		TeamsPersonFactory::make(['person_id' => $relative->id, 'team_id' => $affiliate_bears->id, 'role' => 'player'])
			->persist();

		// Admins are allowed to see attendance
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'attendance', '?' => ['team' => $bears->id]], $admin->id);

		// Managers are allowed to see attendance for teams in their affiliate
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'attendance', '?' => ['team' => $bears->id]], $manager->id);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'attendance', '?' => ['team' => $affiliate_bears->id]], $manager->id);

		// Coordinators are not allowed to see attendance, even for teams in their divisions
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'attendance', '?' => ['team' => $bears->id]], $volunteer->id);

		// Captains are allowed to see attendance for their teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'attendance', '?' => ['team' => $bears->id]], $captain->id);
		// But not other teams
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'attendance', '?' => ['team' => $lions->id]], $captain->id);

		// Players are allowed to see attendance for their teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'attendance', '?' => ['team' => $bears->id]], $player->id);
		// And attendance for teams of people they're related to
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'attendance', '?' => ['team' => $affiliate_bears->id]], $player->id);
		// But not other teams
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'attendance', '?' => ['team' => $lions->id]], $player->id);

		// Others are not allowed to see attendance
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'attendance', '?' => ['team' => $bears->id]]);
	}

	/**
	 * Test emails method
	 */
	public function testEmails(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'coordinator' => $volunteer,
			'roles' => [
				'captain' => true,
				'player' => $player,
			],
		]);

		$captain = $team->people[0];

		/** @var Team $affiliate_team */
		$affiliate_team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[1],
		]);

		// Admins are allowed to see emails
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'emails', '?' => ['team' => $team->id]], $admin->id);

		// Managers are allowed to see emails for teams in their affiliate
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'emails', '?' => ['team' => $team->id]], $manager->id);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'emails', '?' => ['team' => $affiliate_team->id]], $manager->id);

		// Captains are allowed to see emails for their teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'emails', '?' => ['team' => $team->id]], $captain->id);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'emails', '?' => ['team' => $affiliate_team->id]], $captain->id);

		// Others are not allowed to see emails
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'emails', '?' => ['team' => $team->id]], $player->id);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'emails', '?' => ['team' => $team->id]], $volunteer->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'emails', '?' => ['team' => $team->id]]);
	}

	/**
	 * Test add_player method as an admin
	 */
	public function testAddPlayerAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		// Admins are allowed to add players to teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'add_player', '?' => ['team' => $team->id]], $admin->id);

		// Try the search page
		$this->assertPostAsAccessOk(['controller' => 'Teams', 'action' => 'add_player', '?' => ['team' => $team->id]],
			$admin->id, [
				'affiliate_id' => $admin->affiliates[0]->id,
				'first_name' => '',
				'last_name' => $player->last_name,
				'sort' => 'last_name',
				'direction' => 'asc',
			]
		);
		$return = urlencode(base64_url_encode(Configure::read('App.base') . '/teams/add_player?team=' . $team->id));
		$this->assertResponseContains('/teams/roster_add?person=' . $player->id . '&amp;return=' . $return . '&amp;team=' . $team->id);
	}

	/**
	 * Test add_player method as a manager
	 */
	public function testAddPlayerAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		/** @var Team $affiliate_team */
		$affiliate_team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[1],
		]);

		// Managers are allowed to add players to teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'add_player', '?' => ['team' => $team->id]], $manager->id);

		// But not teams in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'add_player', '?' => ['team' => $affiliate_team->id]], $manager->id);
	}

	/**
	 * Test add_player method as a coordinator
	 */
	public function testAddPlayerAsCoordinator(): void {
		[$admin, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'coordinator' => $volunteer,
		]);

		/** @var Team $other_team */
		$other_team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		// Coordinators are allowed to add players to teams in their divisions
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'add_player', '?' => ['team' => $team->id]], $volunteer->id);

		// But not other divisions
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'add_player', '?' => ['team' => $other_team->id]], $volunteer->id);
	}

	/**
	 * Test add_player method as a captain
	 */
	public function testAddPlayerAsCaptain(): void {
		[$admin, $captain] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'captain' => $captain,
			],
		]);

		/** @var Team $other_team */
		$other_team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		// Make sure that we're before the roster deadline for captains to add players
		FrozenDate::setTestNow($team->division->rosterDeadline()->subDays(1));

		// Captains are allowed to add players to their own teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'add_player', '?' => ['team' => $team->id]], $captain->id);

		// But not other teams
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'add_player', '?' => ['team' => $other_team->id]], $captain->id);
	}

	/**
	 * Test add_player method as others
	 */
	public function testAddPlayerAsOthers(): void {
		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'player' => $player,
			],
		]);

		// Others are not allowed to add players to teams
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'add_player', '?' => ['team' => $team->id]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'add_player', '?' => ['team' => $team->id]]);
	}

	/**
	 * Test add_from_team method as an admin
	 */
	public function testAddFromTeamAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		/** @var Team $other_team */
		$other_team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		// Admins are allowed to add from team
		$this->assertPostAsAccessOk(['controller' => 'Teams', 'action' => 'add_from_team', '?' => ['team' => $team->id]],
			$admin->id, ['team' => $other_team->id]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add_from_team method as a manager
	 */
	public function testAddFromTeamAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		/** @var Team $other_team */
		$other_team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		/** @var Team $affiliate_team */
		$affiliate_team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[1],
		]);

		// Managers are allowed to add from team to teams in their affiliate
		$this->assertPostAsAccessOk(['controller' => 'Teams', 'action' => 'add_from_team', '?' => ['team' => $team->id]],
			$manager->id, ['team' => $other_team->id]);
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'add_from_team', '?' => ['team' => $affiliate_team->id]],
			$manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add_from_team method as a coordinator
	 */
	public function testAddFromTeamAsCoordinator(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'coordinator' => $volunteer,
		]);

		/** @var Team $other_team */
		$other_team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		// Coordinators are allowed to add from team to teams in their divisions
		$this->assertPostAsAccessOk(['controller' => 'Teams', 'action' => 'add_from_team', '?' => ['team' => $team->id]],
			$volunteer->id, ['team' => $other_team->id]);
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'add_from_team', '?' => ['team' => $other_team->id]],
			$volunteer->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add_from_team method as a captain
	 */
	public function testAddFromTeamAsCaptain(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager, $captain] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'captain' => $captain,
			],
		]);

		/** @var Team $other_team */
		$other_team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'captain' => $captain,
				'assistant' => true,
				'player' => $manager,
			],
		]);
		$invitee = $other_team->people[1];

		// Make sure that we're before the roster deadline for captains to add players
		FrozenDate::setTestNow($team->division->rosterDeadline()->subDays(1));

		// Captains are allowed to add players from their past teams
		$this->assertPostAsAccessOk(['controller' => 'Teams', 'action' => 'add_from_team', '?' => ['team' => $team->id]],
			$captain->id, ['team' => $other_team->id]);
		$this->assertResponseContains('<span id="people_person_' .  $invitee->id . '" class="trigger">' . $invitee->full_name . '</span>');
		$this->assertResponseRegExp('#<input type="radio" name="player\[' .  $invitee->id . '\]\[role\]" value="captain" id="player-' .  $invitee->id . '-role-captain" class="form-check-input">.*Captain#ms');
		$this->assertResponseRegExp('#<input type="radio" name="player\[' .  $invitee->id . '\]\[position\]" value="unspecified" id="player-' .  $invitee->id . '-position-unspecified" checked="checked" class="form-check-input">.*Unspecified#ms');
		$this->assertResponseContains('<span id="people_person_' .  $manager->id . '" class="trigger">' . $manager->full_name . '</span>');
		// The manager is not a player, so doesn't get player options, just coach and none
		$this->assertResponseRegExp('#<input type="radio" name="player\[' .  $manager->id . '\]\[role\]" value="coach" id="player-' .  $manager->id . '-role-coach" class="form-check-input">.*Non-playing coach#ms');
		$this->assertResponseRegExp('#<input type="radio" name="player\[' .  $manager->id . '\]\[position\]" value="unspecified" id="player-' .  $manager->id . '-position-unspecified" checked="checked" class="form-check-input">.*Unspecified#ms');

		// Submit the add form
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'add_from_team', '?' => ['team' => $team->id]],
			$captain->id, [
				'team' => $other_team->id,
				'player' => [
					$invitee->id => [
						'role' => 'player',
						'position' => 'unspecified',
					],
					$manager->id => [
						'role' => 'none',
						'position' => 'unspecified',
					],
				],
			], ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'Invitation has been sent to ' . $invitee->full_name . '.');

		// Confirm the roster email
		$this->assertMailCount(1);
		$this->assertMailSentFrom('admin@zuluru.org');
		$this->assertMailSentWith([$captain->user->email => $captain->full_name], 'ReplyTo');
		$this->assertMailSentTo($invitee->user->email);
		$this->assertMailSentWith([], 'CC');
		$this->assertMailSentWith('Invitation to join ' . $team->name, 'Subject');
		$this->assertMailContains($captain->full_name . ' has invited you to join the roster of the Test Zuluru Affiliate team ' . $team->name . ' as a Regular player.');
		$this->assertMailContains($team->name . ' plays in the ' . $team->division->name . ' division of the ' . $team->division->league->name . ' league');
		$this->assertMailContains('More details about ' . $team->name . ' may be found at');
		$this->assertMailContains(Configure::read('App.fullBaseUrl') . Configure::read('App.base') . '/teams/view?team=' . $team->id);

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], $captain->id);
		$this->assertResponseContains('Regular player [invited:');
		// There is no accept link, because the membership is not yet paid for
		$this->assertResponseNotContains('/teams/roster_accept?team=' . $team->id . '&amp;person=' . $invitee->id);
		$this->assertResponseContains('/teams/roster_decline?team=' . $team->id . '&amp;person=' . $invitee->id);
		$this->assertResponseNotContains('/teams/roster_accept?team=' . $team->id . '&amp;person=' . $manager->id);
		$this->assertResponseNotContains('/teams/roster_decline?team=' . $team->id . '&amp;person=' . $manager->id);
	}

	/**
	 * Test add_from_team method as others
	 */
	public function testAddFromTeamAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'player' => $player,
			],
		]);

		// Others are not allowed to add from team
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'add_from_team', '?' => ['team' => $team->id]],
			$player->id);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'add_from_team', '?' => ['team' => $team->id]]);
	}

	/**
	 * Test add_from_event method as an admin
	 */
	public function testAddFromEventAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		/** @var Person $other */
		$other = PersonFactory::make()->player()->with('Affiliates', $admin->affiliates[0])->persist();

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		// Create an individual event and registrations
		/** @var Event $event */
		$event = EventFactory::make(['event_type_id' => EVENT_TYPE_ID_INDIVIDUALS_FOR_LEAGUES, 'affiliate_id' => $admin->affiliates[0]->id, 'division_id' => $team->division_id])
			->with('Prices')
			->persist();

		$this->createRegistrations($event, [
			[$manager, 'Paid'],
			[$volunteer, 'Unpaid'],
			[$player, 'Paid'],
			[$other, 'Paid'],
		]);

		// Admins are allowed to add players from events
		$this->assertPostAsAccessOk(['controller' => 'Teams', 'action' => 'add_from_event', '?' => ['team' => $team->id]],
			$admin->id, ['event' => $event->id]);
		$this->assertResponseContains('<span id="people_person_' .  $player->id . '" class="trigger">' . $player->full_name . '</span>');
		$this->assertResponseRegExp('#<input type="radio" name="player\[' .  $player->id . '\]\[role\]" value="captain" id="player-' .  $player->id . '-role-captain" class="form-check-input">.*Captain#ms');

		// Submit the add form
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'add_from_event', '?' => ['team' => $team->id]],
			$admin->id, [
				'event' => $event->id,
				'player' => [
					$player->id => [
						'role' => 'player',
						'position' => 'unspecified',
					],
					// Coordinator will not be added; the registration is not paid
					$volunteer->id => [
						'role' => 'player',
						'position' => 'unspecified',
					],
					$other->id => [
						'role' => 'player',
						'position' => 'unspecified',
					],
					// Manager will not be added; the role is "none"
					$manager->id => [
						'role' => 'none',
						'position' => 'unspecified',
					],
				],
			], ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			$player->full_name . ' and ' . $other->full_name . ' have been added to the roster.');

		// Confirm the roster email
		$this->assertMailCount(2);
		$this->assertMailSentFromAt(0, 'admin@zuluru.org');
		$this->assertMailSentWithAt(0, [$admin->user->email => $admin->full_name], 'ReplyTo');
		$this->assertMailSentToAt(0, $player->user->email);
		$this->assertMailSentWithAt(0, [], 'CC');
		$this->assertMailSentWithAt(0, 'You have been added to ' . $team->name, 'Subject');
		$this->assertMailContainsAt(0, 'You have been added to the roster of the Test Zuluru Affiliate team ' . $team->name . ' as a Regular player.');
		$this->assertMailContainsAt(0, $team->name . ' plays in the ' . $team->division->name . ' division of the ' . $team->division->league->name . ' league');
		$this->assertMailContainsAt(0, 'More details about ' . $team->name . ' may be found at');
		$this->assertMailContainsAt(0, Configure::read('App.fullBaseUrl') . Configure::read('App.base') . '/teams/view?team=' . $team->id);

		$this->assertMailSentFromAt(1, 'admin@zuluru.org');
		$this->assertMailSentWithAt(1, [$admin->user->email => $admin->full_name], 'ReplyTo');
		$this->assertMailSentToAt(1, $other->user->email);
		$this->assertMailSentWithAt(1, [], 'CC');
		$this->assertMailSentWithAt(1, 'You have been added to ' . $team->name, 'Subject');
		$this->assertMailContainsAt(1, 'You have been added to the roster of the Test Zuluru Affiliate team ' . $team->name . ' as a Regular player.');
		$this->assertMailContainsAt(1, $team->name . ' plays in the ' . $team->division->name . ' division of the ' . $team->division->league->name . ' league');
		$this->assertMailContainsAt(1, 'More details about ' . $team->name . ' may be found at');
		$this->assertMailContainsAt(1, Configure::read('App.fullBaseUrl') . Configure::read('App.base') . '/teams/view?team=' . $team->id);

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], $admin->id);
		$this->assertResponseContains('Regular player');
		$this->assertResponseContains('/teams/roster_role?team=' . $team->id . '&amp;person=' . $player->id);
		$this->assertResponseContains('/teams/roster_role?team=' . $team->id . '&amp;person=' . $other->id);
		$this->assertResponseNotContains('/teams/roster_role?team=' . $team->id . '&amp;person=' . $volunteer->id);
		$this->assertResponseNotContains('/teams/roster_role?team=' . $team->id . '&amp;person=' . $manager->id);
	}

	/**
	 * Test add_from_event method as a manager
	 */
	public function testAddFromEventAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		// Create an individual event and registrations
		/** @var Event $event */
		$event = EventFactory::make(['event_type_id' => EVENT_TYPE_ID_INDIVIDUALS_FOR_LEAGUES, 'affiliate_id' => $admin->affiliates[0]->id, 'division_id' => $team->division_id])
			->with('Prices')
			->persist();

		$this->createRegistrations($event, [
			[$player, 'Paid'],
		]);

		/** @var Team $affiliate_team */
		$affiliate_team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[1],
		]);

		/** @var Event $affiliate_event */
		$affiliate_event = EventFactory::make(['event_type_id' => EVENT_TYPE_ID_INDIVIDUALS_FOR_LEAGUES, 'affiliate_id' => $admin->affiliates[1]->id, 'division_id' => $affiliate_team->division_id])
			->with('Prices')
			->persist();

		// Managers are allowed to add players from events
		$this->assertPostAsAccessOk(['controller' => 'Teams', 'action' => 'add_from_event', '?' => ['team' => $team->id]],
			$manager->id, ['event' => $event->id]);
		$this->assertResponseContains('<span id="people_person_' .  $player->id . '" class="trigger">' . $player->full_name . '</span>');
		$this->assertResponseRegExp('#<input type="radio" name="player\[' .  $player->id . '\]\[role\]" value="captain" id="player-' .  $player->id . '-role-captain" class="form-check-input">.*Captain#ms');

		// But not to teams in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'add_from_event', '?' => ['team' => $affiliate_team->id]],
			$manager->id, [
				'event' => $affiliate_event->id,
			]);
	}

	/**
	 * Test add_from_event method as a coordinator
	 */
	public function testAddFromEventAsCoordinator(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'coordinator' => $volunteer,
		]);

		// Create an individual event and registrations
		/** @var Event $event */
		$event = EventFactory::make(['event_type_id' => EVENT_TYPE_ID_INDIVIDUALS_FOR_LEAGUES, 'affiliate_id' => $admin->affiliates[0]->id, 'division_id' => $team->division_id])
			->with('Prices')
			->persist();

		$this->createRegistrations($event, [
			[$player, 'Paid'],
		]);

		/** @var Team $other_team */
		$other_team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		// Coordinators are allowed to add players from events to teams in their divisions
		$this->assertPostAsAccessOk(['controller' => 'Teams', 'action' => 'add_from_event', '?' => ['team' => $team->id]],
			$volunteer->id, ['event' => $event->id]);
		$this->assertResponseContains('<span id="people_person_' .  $player->id . '" class="trigger">' . $player->full_name . '</span>');
		$this->assertResponseRegExp('#<input type="radio" name="player\[' .  $player->id . '\]\[role\]" value="captain" id="player-' .  $player->id . '-role-captain" class="form-check-input">.*Captain#ms');

		// But not other divisions
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'add_from_event', '?' => ['team' => $other_team->id]],
			$volunteer->id, [
				'event' => $event->id,
			]);
	}

	/**
	 * Test add_from_event method as others
	 */
	public function testAddFromEventAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'captain' => $player,
		]);

		// Create an individual event and registrations
		/** @var Event $event */
		$event = EventFactory::make(['event_type_id' => EVENT_TYPE_ID_INDIVIDUALS_FOR_LEAGUES, 'affiliate_id' => $admin->affiliates[0]->id, 'division_id' => $team->division_id])
			->with('Prices')
			->persist();

		$this->createRegistrations($event, [
			[$player, 'Paid'],
		]);

		// Make sure that we're before the roster deadline for adding players
		FrozenDate::setTestNow($team->division->rosterDeadline()->subDays(1));

		// Others are not allowed to add players from events
		$this->assertPostAsAccessDenied(['controller' => 'Teams', 'action' => 'add_from_event', '?' => ['team' => $team->id]],
			$player->id, [
				'event' => $event->id,
			]);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'add_from_event', '?' => ['team' => $team->id]]);
	}

	/**
	 * Test roster_role method as an admin
	 */
	public function testRosterRoleAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'player' => $player,
			],
		]);

		// Admins are allowed to change roster roles
		$this->assertPostAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'roster_role', '?' => ['person' => $player->id, 'team' => $team->id]],
			$admin->id, ['role' => 'captain']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test roster_role method as a manager
	 */
	public function testRosterRoleAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'player' => $player,
			],
		]);

		// Managers are allowed to change roster roles for teams in their affiliate
		$this->assertPostAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'roster_role', '?' => ['person' => $player->id, 'team' => $team->id]],
			$manager->id, ['role' => 'captain']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test roster_role method as a coordinator
	 */
	public function testRosterRoleAsCoordinator(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'coordinator' => $volunteer,
			'roles' => [
				'player' => $player,
			],
		]);

		// Make sure that we're before the roster deadline
		FrozenDate::setTestNow($team->division->rosterDeadline()->subDays(1));

		// Coordinators are allowed to change roster roles for teams in their divisions
		$this->assertPostAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'roster_role', '?' => ['person' => $player->id, 'team' => $team->id]],
			$volunteer->id, ['role' => 'captain']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test roster_role method as a captain
	 */
	public function testRosterRoleAsCaptain(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'captain' => true,
				'player' => [$player, ['status' => ROSTER_INVITED]],
			],
		]);
		[$captain, , $invited] = $team->people;

		// Make sure that we're before the roster deadline
		FrozenDate::setTestNow($team->division->rosterDeadline()->subDays(1));

		$this->assertPostAjaxAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_role', '?' => ['person' => $volunteer->id, 'team' => $team->id]],
			$captain->id, ['role' => 'substitute'], ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'This person is not on this team.');

		$this->assertPostAjaxAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_role', '?' => ['person' => $invited->id, 'team' => $team->id]],
			$captain->id, ['role' => 'substitute'], ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'A player\'s role on a team cannot be changed until they have been approved on the roster.');

		$this->assertPostAjaxAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_role', '?' => ['person' => $captain->id, 'team' => $team->id]],
			$captain->id, ['role' => 'substitute'], ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'All teams must have at least one player as coach or captain.');

		$this->assertPostAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'roster_role', '?' => ['person' => $player->id, 'team' => $team->id]],
			$captain->id, ['role' => 'substitute']);
		$this->assertResponseRegExp('#\\\\/teams\\\\/roster_role\?team=' . $team->id . '&amp;person=' . $player->id . '.*Substitute player#ms');
	}

	/**
	 * Test roster_role method as a player
	 */
	public function testRosterRoleAsPlayer(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'player' => $player,
			],
		]);

		// Cannot make changes after the roster deadline!
		FrozenDate::setTestNow($team->division->rosterDeadline()->addDays(1));

		$this->assertPostAjaxAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_role', '?' => ['person' => $player->id, 'team' => $team->id]],
			$player->id, ['role' => 'substitute'], ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'The roster deadline for this division has already passed.');

		// Make sure that we're before the roster deadline
		FrozenDate::setTestNow($team->division->rosterDeadline()->subDays(1));

		$this->assertPostAjaxAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_role', '?' => ['person' => $player->id, 'team' => $team->id]],
			$player->id, ['role' => 'captain'], ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'You do not have permission to set that role.');

		$this->assertPostAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'roster_role', '?' => ['person' => $player->id, 'team' => $team->id]],
			$player->id, ['role' => 'substitute']);
		$this->assertResponseRegExp('#\\\\/teams\\\\/roster_role\?team=' . $team->id . '&amp;person=' . $player->id . '.*Substitute player#ms');
	}

	/**
	 * Test roster_role method as someone else
	 */
	public function testRosterRoleAsVisitor(): void {
		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'player' => [$player, true],
			],
		]);
		$other = $team->people[1];

		// Others are not allowed to change roster roles
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'roster_role', '?' => ['person' => $player->id, 'team' => $team->id]], $other->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'roster_role', '?' => ['person' => $player->id, 'team' => $team->id]]);
	}

	/**
	 * Test roster_position method as an admin
	 */
	public function testRosterPositionAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'player' => $player,
			],
		]);

		// Admins are allowed to change roster positions
		$this->assertPostAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'roster_position', '?' => ['person' => $player->id, 'team' => $team->id]],
			$admin->id, ['position' => 'handler']);
		$this->assertResponseRegExp('#\\\\/teams\\\\/roster_position\?team=' . $team->id . '&amp;person=' . $player->id . '.*Handler#ms');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test roster_position method as a manager
	 */
	public function testRosterPositionAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'player' => $player,
			],
		]);

		// Managers are allowed to change roster positions
		$this->assertPostAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'roster_position', '?' => ['person' => $player->id, 'team' => $team->id]],
			$manager->id, ['position' => 'handler']);
		$this->assertResponseRegExp('#\\\\/teams\\\\/roster_position\?team=' . $team->id . '&amp;person=' . $player->id . '.*Handler#ms');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test roster_position method as a coordinator
	 */
	public function testRosterPositionAsCoordinator(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'coordinator' => $volunteer,
			'roles' => [
				'player' => $player,
			],
		]);

		// Coordinators are allowed to change roster positions
		$this->assertPostAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'roster_position', '?' => ['person' => $player->id, 'team' => $team->id]],
			$volunteer->id, ['position' => 'handler']);
		$this->assertResponseRegExp('#\\\\/teams\\\\/roster_position\?team=' . $team->id . '&amp;person=' . $player->id . '.*Handler#ms');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test roster_position method as a captain
	 */
	public function testRosterPositionAsCaptain(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'captain' => true,
				'player' => $player,
			],
		]);
		$captain = $team->people[0];

		// Cannot make changes after the roster deadline!
		FrozenDate::setTestNow($team->division->rosterDeadline()->addDays(1));

		$this->assertPostAjaxAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_position', '?' => ['person' => $player->id, 'team' => $team->id]],
			$captain->id, ['position' => 'handler'], ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'The roster deadline for this division has already passed.');

		// Make sure that we're before the roster deadline
		FrozenDate::setTestNow($team->division->rosterDeadline()->subDays(1));

		$this->assertPostAjaxAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_position', '?' => ['person' => $volunteer->id, 'team' => $team->id]],
			$captain->id, ['position' => 'handler'], ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'This person is not on this team.');

		$this->assertPostAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'roster_position', '?' => ['person' => $player->id, 'team' => $team->id]],
			$captain->id, ['position' => 'handler']);
		$this->assertResponseRegExp('#\\\\/teams\\\\/roster_position\?team=' . $team->id . '&amp;person=' . $player->id . '.*Handler#ms');
	}

	/**
	 * Test roster_position method as a player
	 */
	public function testRosterPositionAsPlayer(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'player' => $player,
			],
		]);

		// Cannot make changes after the roster deadline!
		FrozenDate::setTestNow($team->division->rosterDeadline()->addDays(1));

		$this->assertPostAjaxAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_position', '?' => ['person' => $player->id, 'team' => $team->id]],
			$player->id, ['position' => 'handler'], ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'The roster deadline for this division has already passed.');

		// Make sure that we're before the roster deadline
		FrozenDate::setTestNow($team->division->rosterDeadline()->subDays(1));

		$this->assertPostAjaxAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_position', '?' => ['person' => $player->id, 'team' => $team->id]],
			$player->id, ['position' => 'xyz'], ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'That is not a valid position.');

		$this->assertPostAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'roster_position', '?' => ['person' => $player->id, 'team' => $team->id]],
			$player->id, ['position' => 'handler']);
		$this->assertResponseRegExp('#\\\\/teams\\\\/roster_position\?team=' . $team->id . '&amp;person=' . $player->id . '.*Handler#ms');
	}

	/**
	 * Test roster_position method as others
	 */
	public function testRosterPositionAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'player' => [$player, true],
			],
		]);
		$other = $team->people[1];

		// Others are not allowed to change roster positions
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Teams', 'action' => 'roster_position', '?' => ['person' => $player->id, 'team' => $team->id]],
			$other->id, ['position' => 'handler']);
		$this->assertPostAjaxAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'roster_position', '?' => ['person' => $player->id, 'team' => $team->id]],
			['position' => 'handler']);
	}

	/**
	 * Test roster_add method as an admin
	 */
	public function testRosterAddAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		// Admins are allowed to add players to teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'roster_add', '?' => ['person' => $player->id, 'team' => $team->id]], $admin->id);
		$this->assertResponseContains('/teams/roster_add?person=' . $player->id . '&amp;team=' . $team->id);

		// Submit an empty add form
		$this->assertPostAsAccessOk(['controller' => 'Teams', 'action' => 'roster_add', '?' => ['person' => $player->id, 'team' => $team->id]], $admin->id, []);
		$this->assertResponseContains('You must select a role for this person.');

		// Submit the add form
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_add', '?' => ['person' => $player->id, 'team' => $team->id]],
			$admin->id, [
				'role' => 'player',
				'position' => 'unspecified',
			], ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]]);

		// Confirm the roster email
		$this->assertMailCount(1);
		$this->assertMailSentFrom('admin@zuluru.org');
		$this->assertMailSentWith([$admin->user->email => $admin->full_name], 'ReplyTo');
		$this->assertMailSentTo($player->user->email);
		$this->assertMailSentWith([], 'CC');
		// TODO: Why is this an invitation, when add_from_event is a direct add?
		$this->assertMailSentWith('Invitation to join ' . $team->name, 'Subject');
		$this->assertMailContains($admin->full_name . ' has invited you to join the roster of the Test Zuluru Affiliate team ' . $team->name . ' as a Regular player.');
		$this->assertMailContains($team->name . ' plays in the ' . $team->division->name . ' division of the ' . $team->division->league->name . ' league');
		$this->assertMailContains('More details about ' . $team->name . ' may be found at');
		$this->assertMailContains(Configure::read('App.fullBaseUrl') . Configure::read('App.base') . '/teams/view?team=' . $team->id);

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], $admin->id);
		$this->assertResponseContains('Regular player [invited:');
		$this->assertResponseContains('/teams/roster_accept?team=' . $team->id . '&amp;person=' . $player->id);
		$this->assertResponseContains('/teams/roster_decline?team=' . $team->id . '&amp;person=' . $player->id);

		// TODO: Check all the potential emails and different states that can be generated in other situations: add vs invite, admin vs captain, etc.
	}

	/**
	 * Test roster_add method as a manager
	 */
	public function testRosterAddAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		/** @var Team $affiliate_team */
		$affiliate_team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[1],
		]);

		// Managers are allowed to add players to teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'roster_add', '?' => ['person' => $player->id, 'team' => $team->id]], $manager->id);
		$this->assertResponseContains('/teams/roster_add?person=' . $player->id . '&amp;team=' . $team->id);

		// Submit the add form
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_add', '?' => ['person' => $player->id, 'team' => $team->id]],
			$manager->id, [
				'role' => 'player',
				'position' => 'unspecified',
			], ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]]);

		// But not teams in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'roster_add', '?' => ['person' => $player->id, 'team' => $affiliate_team->id]], $manager->id);
	}

	/**
	 * Test roster_add method as a coordinator
	 */
	public function testRosterAddAsCoordinator(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'coordinator' => $volunteer,
		]);

		/** @var Team $other_team */
		$other_team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		// Coordinators are allowed to add players to teams in their divisions
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'roster_add', '?' => ['person' => $player->id, 'team' => $team->id]], $volunteer->id);
		$this->assertResponseContains('/teams/roster_add?person=' . $player->id . '&amp;team=' . $team->id);

		// Submit the add form
		$this->assertPostAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_add', '?' => ['person' => $player->id, 'team' => $team->id]],
			$volunteer->id, [
				'role' => 'player',
				'position' => 'unspecified',
			], ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]]);

		// But not other divisions
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'roster_add', '?' => ['person' => $player->id, 'team' => $other_team->id]], $volunteer->id);
	}

	/**
	 * Test roster_add method as a captain
	 */
	public function testRosterAddAsCaptain(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'captain' => true,
			],
		]);
		$captain = $team->people[0];

		/** @var Team $other_team */
		$other_team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		// Make sure that we're before the roster deadline for captains to add players
		FrozenDate::setTestNow($team->division->rosterDeadline()->subDays(1));

		// Captains are allowed to add players to their own teams
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'roster_add', '?' => ['person' => $player->id, 'team' => $team->id]], $captain->id);
		$this->assertResponseContains('/teams/roster_add?person=' . $player->id . '&amp;team=' . $team->id);

		// Submit the add form
		$this->assertPostAjaxAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_add', '?' => ['person' => $player->id, 'team' => $team->id]], $captain->id, [
			'role' => 'player',
			'position' => 'unspecified',
		], ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]]);

		// But not other teams
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'roster_add', '?' => ['person' => $player->id, 'team' => $other_team->id]], $captain->id);
	}

	/**
	 * Test roster_add method as others
	 */
	public function testRosterAddAsOthers(): void {
		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		// Others are not allowed to add players to teams
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'roster_add', '?' => ['person' => $player->id, 'team' => $team->id]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'roster_add', '?' => ['person' => $player->id, 'team' => $team->id]]);
	}

	/**
	 * Test roster_request method as a player
	 */
	public function testRosterRequestAsPlayer(): void {
		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'team_details' => ['open_roster' => true],
		]);

		// Make sure that we're before the roster deadline
		FrozenDate::setTestNow($team->division->rosterDeadline()->subDays(1));

		// Players are allowed to request to join a team
		$this->assertGetAsAccessOk(['controller' => 'Teams', 'action' => 'roster_request', '?' => ['team' => $team->id]], $player->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test roster_request method as others
	 */
	public function testRosterRequestAsOthers(): void {
		[$admin, $manager, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager', 'volunteer']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'team_details' => ['open_roster' => true],
		]);

		// Make sure that we're before the roster deadline
		FrozenDate::setTestNow($team->division->rosterDeadline()->subDays(1));

		// Others (any non-players) are not allowed to request to join a team
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'roster_request', '?' => ['team' => $team->id]], $admin->id);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'roster_request', '?' => ['team' => $team->id]], $manager->id);
		$this->assertGetAsAccessDenied(['controller' => 'Teams', 'action' => 'roster_request', '?' => ['team' => $team->id]], $volunteer->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'roster_request', '?' => ['team' => $team->id]]);
	}

	/**
	 * Test roster_accept method as an admin
	 */
	public function testRosterAcceptAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'captain' => true,
				'player' => ['status' => ROSTER_INVITED],
			],
		]);
		$invitee = $team->people[1];

		// Admins are allowed to accept roster invitations
		$this->assertGetAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_accept', '?' => ['person' => $invitee->id, 'team' => $team->id]],
			$admin->id, ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'You have accepted this roster invitation.');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test roster_accept method as a manager
	 */
	public function testRosterAcceptAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'captain' => true,
				'player' => ['status' => ROSTER_INVITED],
			],
		]);
		$invitee = $team->people[1];

		// Managers are allowed to accept roster invitations
		$this->assertGetAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_accept', '?' => ['person' => $invitee->id, 'team' => $team->id]],
			$manager->id, ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'You have accepted this roster invitation.');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test roster_accept method as a coordinator
	 */
	public function testRosterAcceptAsCoordinator(): void {
		[$admin, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'coordinator' => $volunteer,
			'roles' => [
				'captain' => true,
				'player' => ['status' => ROSTER_INVITED],
			],
		]);
		$invitee = $team->people[1];

		// Coordinators are allowed to accept roster invitations
		$this->assertGetAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_accept', '?' => ['person' => $invitee->id, 'team' => $team->id]],
			$volunteer->id, ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'You have accepted this roster invitation.');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test roster_accept method as a captain
	 */
	public function testRosterAcceptAsCaptain(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'captain' => true,
				'player' => ['status' => ROSTER_INVITED],
			],
		]);
		[$captain, $invitee] = $team->people;

		// Make sure that we're before the roster deadline
		FrozenDate::setTestNow($team->division->rosterDeadline()->subDays(1));

		// Captains are not allowed to accept roster invitations to their players
		$this->assertGetAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_accept', '?' => ['person' => $invitee->id, 'team' => $team->id]],
			$captain->id, ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'You are not allowed to accept this roster invitation.');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test roster_accept method as a player
	 */
	public function testRosterAcceptAsPlayer(): void {
		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'captain' => true,
				'player' => [['status' => ROSTER_INVITED], $player],
			],
		]);
		$invitee = $team->people[1];

		// Cannot accept invites after the roster deadline!
		FrozenDate::setTestNow($team->division->rosterDeadline()->addDays(1));

		$this->assertGetAjaxAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_accept', '?' => ['person' => $invitee->id, 'team' => $team->id]],
			$invitee->id, ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'The roster deadline for this division has already passed.');

		// Make sure that we're before the roster deadline
		FrozenDate::setTestNow($team->division->rosterDeadline()->subDays(1));

		$this->assertGetAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'roster_accept', '?' => ['person' => $invitee->id, 'team' => $team->id]],
			$invitee->id);
		$this->assertResponseRegExp('#\\\\/teams\\\\/roster_role\?team=' . $team->id . '&amp;person=' . $invitee->id . '.*Regular player#ms');

		$this->assertGetAjaxAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_accept', '?' => ['person' => $player->id, 'team' => $team->id]],
			$player->id, ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'This person has already been added to the roster.');
	}

	/**
	 * Test roster_accept method with a code
	 */
	public function testRosterAcceptWithCode(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'captain' => true,
				'player' => ['status' => ROSTER_INVITED],
			],
		]);
		$invitee = $team->people[1];

		// Make sure that we're before the roster deadline
		FrozenDate::setTestNow($team->division->rosterDeadline()->subDays(1));

		/** @var TeamsPerson $roster */
		$roster = TableRegistry::getTableLocator()->get('TeamsPeople')->find()->where(['person_id' => $invitee->id, 'team_id' => $team->id])->firstOrFail();
		$this->assertGetAnonymousAccessRedirect(
			[
				'controller' => 'Teams',
				'action' => 'roster_accept',
				'?' => [
					'person' => $invitee->id, 'team' => $team->id, 'code' => $this->_makeHash([$roster->id, $team->id, $invitee->id, $roster->role, $roster->created])
				],
			],
			['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'You have accepted this roster invitation.');
	}

	/**
	 * Test roster_accept method as others
	 */
	public function testRosterAcceptAsOthers(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'captain' => true,
				'player' => ['status' => ROSTER_INVITED],
			],
		]);
		$invitee = $team->people[1];

		// Others are not allowed to accept roster invitations
		$this->assertGetAnonymousAccessRedirect(['controller' => 'Teams', 'action' => 'roster_accept', '?' => ['person' => $invitee->id, 'team' => $team->id, 'code' => 'wrong']],
			['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'The authorization code is invalid.');
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'roster_accept', '?' => ['person' => $invitee->id, 'team' => $team->id]]);
	}

	/**
	 * Test roster_decline method as an admin
	 */
	public function testRosterDeclineAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'captain' => true,
				'player' => ['status' => ROSTER_INVITED],
			],
		]);
		$invitee = $team->people[1];

		// Admins are allowed to decline roster invitations
		$this->assertGetAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_decline', '?' => ['person' => $invitee->id, 'team' => $team->id]],
			$admin->id, ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'You have declined this roster invitation.');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test roster_decline method as a manager
	 */
	public function testRosterDeclineAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'captain' => true,
				'player' => ['status' => ROSTER_INVITED],
			],
		]);
		$invitee = $team->people[1];

		// Managers are allowed to decline roster invitations
		$this->assertGetAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_decline', '?' => ['person' => $invitee->id, 'team' => $team->id]],
			$manager->id, ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'You have declined this roster invitation.');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test roster_decline method as a coordinator
	 */
	public function testRosterDeclineAsCoordinator(): void {
		[$admin, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'coordinator' => $volunteer,
			'roles' => [
				'captain' => true,
				'player' => ['status' => ROSTER_INVITED],
			],
		]);
		$invitee = $team->people[1];

		// Coordinators are allowed to decline roster invitations
		$this->assertGetAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_decline', '?' => ['person' => $invitee->id, 'team' => $team->id]],
			$volunteer->id, ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'You have declined this roster invitation.');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test roster_decline method as a captain
	 */
	public function testRosterDeclineAsCaptain(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'captain' => true,
				'player' => ['status' => ROSTER_INVITED],
			],
		]);
		[$captain, $invitee] = $team->people;

		// Make sure that we're before the roster deadline
		FrozenDate::setTestNow($team->division->rosterDeadline()->subDays(1));

		// Captains are allowed to remove roster invitations to their players
		$this->assertGetAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_decline', '?' => ['person' => $invitee->id, 'team' => $team->id]],
			$captain->id, ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'You have declined this roster invitation.');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test roster_decline method as a player
	 */
	public function testRosterDeclineAsPlayer(): void {
		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'captain' => true,
				'player' => [['status' => ROSTER_INVITED], $player],
			],
		]);
		$invitee = $team->people[1];

		// Cannot decline invites after the roster deadline!
		FrozenDate::setTestNow($team->division->rosterDeadline()->addDays(1));

		$this->assertGetAjaxAsAccessRedirect(['controller' => 'Teams', 'action' => 'roster_decline', '?' => ['person' => $invitee->id, 'team' => $team->id]],
			$invitee->id, ['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'The roster deadline for this division has already passed.');

		// Make sure that we're before the roster deadline
		FrozenDate::setTestNow($team->division->rosterDeadline()->subDays(1));

		$this->assertGetAjaxAsAccessOk(['controller' => 'Teams', 'action' => 'roster_decline', '?' => ['person' => $invitee->id, 'team' => $team->id]],
			$invitee->id);
	}

	/**
	 * Test roster_decline method with a code
	 */
	public function testRosterDeclineWithCode(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'captain' => true,
				'player' => ['status' => ROSTER_INVITED],
			],
		]);
		$invitee = $team->people[1];

		// Make sure that we're before the roster deadline
		FrozenDate::setTestNow($team->division->rosterDeadline()->subDays(1));

		/** @var TeamsPerson $roster */
		$roster = TableRegistry::getTableLocator()->get('TeamsPeople')->find()->where(['person_id' => $invitee->id, 'team_id' => $team->id])->firstOrFail();
		$this->assertGetAnonymousAccessRedirect(
			[
				'controller' => 'Teams',
				'action' => 'roster_decline',
				'?' => [
					'person' => $invitee->id, 'team' => $team->id, 'code' => $this->_makeHash([$roster->id, $team->id, $invitee->id, $roster->role, $roster->created])
				],
			],
			['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'You have declined this roster invitation.');
	}

	/**
	 * Test roster_decline method as others
	 */
	public function testRosterDeclineAsOthers(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'roles' => [
				'captain' => true,
				'player' => ['status' => ROSTER_INVITED],
			],
		]);
		$invitee = $team->people[1];

		// Others are not allowed to decline roster invitations
		$this->assertGetAnonymousAccessRedirect(['controller' => 'Teams', 'action' => 'roster_decline', '?' => ['person' => $invitee->id, 'team' => $team->id, 'code' => 'wrong']],
			['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]],
			'The authorization code is invalid.');
		$this->assertGetAnonymousAccessDenied(['controller' => 'Teams', 'action' => 'roster_decline', '?' => ['person' => $invitee->id, 'team' => $team->id]]);
	}

}
