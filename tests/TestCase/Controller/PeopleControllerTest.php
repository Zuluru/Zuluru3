<?php
namespace App\Test\TestCase\Controller;

use App\Model\Entity\Affiliate;
use App\Model\Entity\Badge;
use App\Model\Entity\Person;
use App\Model\Entity\Upload;
use App\Model\Table\PeopleTable;
use App\Test\Factory\BadgeFactory;
use App\Test\Factory\BadgesPersonFactory;
use App\Test\Factory\NoteFactory;
use App\Test\Factory\PeoplePersonFactory;
use App\Test\Factory\PersonFactory;
use App\Test\Factory\SettingFactory;
use App\Test\Factory\SkillFactory;
use App\Test\Factory\UploadFactory;
use App\Test\Scenario\DiverseUsersScenario;
use App\Test\Scenario\LeagueWithRostersScenario;
use App\Test\Scenario\SingleGameScenario;
use Cake\Core\Configure;
use Cake\Filesystem\Folder;
use Cake\I18n\FrozenDate;
use Cake\I18n\I18n;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\EmailTrait;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\PeopleController Test Case
 */
class PeopleControllerTest extends ControllerTestCase {

	use EmailTrait;
	use ScenarioAwareTrait;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.Countries',
		'app.Groups',
		'app.Provinces',
		'app.RosterRoles',
		'app.Settings',
	];

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();

		$folder = new Folder(TESTS . 'test_app' . DS . 'upload', true);
		Configure::write('App.paths.uploads', $folder->path);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		// Delete the temporary uploads
		$upload_path = Configure::read('App.paths.uploads');
		$folder = new Folder($upload_path);
		$folder->delete();

		// Cleanup any emails that were sent
		$this->cleanupEmailTrait();

		parent::tearDown();
	}

	/**
	 * Test index method
	 */
	public function testIndex(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Admins are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'index'], $admin->id);

		// Managers are allowed to see the index, but don't see people in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'index'], $manager->id);

		// Anyone else is not allowed to get the index
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'index'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'index'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'index']);
	}

	/**
	 * Test statistics method
	 */
	public function testStatistics(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliate = $admin->affiliates[0];
		SkillFactory::make(['person_id' => $player->id])->persist();

		// Admins are allowed to view the statistics page
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'statistics'], $admin->id);
		$this->assertResponseRegExp('#<h4 class="affiliate">' . $affiliate->name . '</h4>.*<td>Ultimate</td>[\s]*<td>' . $player->roster_designation . '</td>[\s]*<td>1</td>#ms');

		// Managers are allowed to view the statistics page
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'statistics'], $manager->id);

		// Others are not allowed to statistics
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'statistics'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'statistics'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'statistics']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test participation method
	 */
	public function testParticipation(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Admins are allowed to view the participation report
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'participation'], $admin->id);

		// Managers are allowed to view the participation report
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'participation'], $manager->id);

		// Others are not allowed to view the participation report
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'participation'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'participation'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'participation']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test retention method
	 */
	public function testRetention(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Admins are allowed to view the retention report
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'retention'], $admin->id);

		// Managers are allowed to view the retention report
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'retention'], $manager->id);

		// Others are not allowed to view the retention report
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'retention'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'retention'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'retention']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test view method
	 */
	public function testView(): void {
		[$admin, $manager, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$league = $this->loadFixtureScenario(LeagueWithRostersScenario::class, [
			'affiliate' => $affiliates[0],
			'coordinator' => $volunteer,
			'teams' => 2,
		]);
		$team = $league->divisions[0]->teams[0];
		$captain = $team->people[LeagueWithRostersScenario::$CAPTAIN];
		$player = $team->people[LeagueWithRostersScenario::$PLAYER1];
		$invited = $team->people[LeagueWithRostersScenario::$INVITED];

		// Also add a relative (in a different affiliate) to the player
		/** @var Person $relative */
		$relative = PersonFactory::make()
			->withGroup(GROUP_PLAYER)
			->with('Affiliates', $affiliates[1])
			->with('Users')
			->persist();
		PeoplePersonFactory::make(['person_id' => $player->id, 'relative_id' => $relative->id])->persist();

		// Admins are allowed to see all data
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => $player->id], $admin->id);
		$this->assertResponseContains('Phone (home)');
		$this->assertResponseContains('Email Address');
		$this->assertResponseContains('Birthdate');

		// Admins are allowed to see and manipulate relatives
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => $player->id], $admin->id);
		$this->assertResponseContains('/people/remove_relative?person=' . $player->id . '&amp;relative=' . $relative->id);
		$this->assertResponseRegExp('#<td>' . $player->first_name . ' can control <a[^>]*>' . $relative->full_name . '</a></td>\s*<td>Yes</td>#ms');
		$this->assertResponseNotContains('/people/remove_relative?person=' . $relative->id . '&amp;relative=' . $player->id);
		$this->assertResponseNotRegExp('#<td><a[^>]*>' . $relative->full_name . '</a> can control ' . $player->first_name . '</td>\s*<td>No</td>#ms');

		// Managers are allowed to see all data, including manipulating relatives, for people in their affiliate
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => $player->id], $manager->id);
		$this->assertResponseContains('Phone (home)');
		$this->assertResponseContains('Email Address');
		$this->assertResponseContains('Birthdate');
		$this->assertResponseContains('/people/remove_relative?person=' . $player->id . '&amp;relative=' . $relative->id);
		$this->assertResponseRegExp('#<td>' . $player->first_name . ' can control <a[^>]*>' . $relative->full_name . '</a></td>\s*<td>Yes</td>#ms');

		// But only regular data for people not in their own
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => $relative->id], $manager->id);
		$this->assertResponseNotContains('Phone (home)');
		$this->assertResponseNotContains('Email Address');
		$this->assertResponseNotContains('Birthdate');

		// Coordinators are allowed to see contact info for their captains
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => $captain->id], $volunteer->id);
		$this->assertResponseContains('Phone (home)');
		$this->assertResponseContains('Email Address');
		$this->assertResponseNotContains('Birthdate');

		// ...but not regular players
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => $player->id], $volunteer->id);
		$this->assertResponseNotContains('Phone (home)');
		$this->assertResponseNotContains('Email Address');
		$this->assertResponseNotContains('Birthdate');

		// Captains are allowed to see contact info for their players
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => $player->id], $captain->id);
		$this->assertResponseContains('Phone (home)');
		$this->assertResponseContains('Email Address');
		$this->assertResponseNotContains('Birthdate');

		// ...and their coordinator
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => $volunteer->id], $captain->id);
		$this->assertResponseContains('Phone (home)');
		$this->assertResponseContains('Email Address');

		// ...but not people who haven't confirmed the invite
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => $invited->id], $captain->id);
		$this->assertResponseNotContains('Phone (home)');
		$this->assertResponseNotContains('Email Address');

		// ...and not others
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => $manager->id], $captain->id);
		$this->assertResponseNotContains('Phone (home)');
		$this->assertResponseNotContains('Email Address');

		// Players are allowed to see contact info for their captains
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => $captain->id], $player->id);
		$this->assertResponseContains('Phone (home)');
		$this->assertResponseContains('Email Address');
		$this->assertResponseNotContains('Birthdate');

		// ...but not others
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => $volunteer->id], $player->id);
		$this->assertResponseNotContains('Phone (home)');
		$this->assertResponseNotContains('Email Address');
		$this->assertResponseNotContains('Birthdate');

		// ...and this is still true if it's an admin acting as the player
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => $volunteer->id], [$admin->id, $player->id]);
		$this->assertResponseNotContains('Phone (home)');
		$this->assertResponseNotContains('Email Address');
		$this->assertResponseNotContains('Birthdate');

		// ...or if it's a player acting as a (presumably related, though that's not checked here) admin
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => $volunteer->id], [$player->id, $admin->id]);
		$this->assertResponseNotContains('Phone (home)');
		$this->assertResponseNotContains('Email Address');
		$this->assertResponseNotContains('Birthdate');

		// Others are allowed to view
		$this->assertGetAnonymousAccessOk(['controller' => 'People', 'action' => 'view', 'person' => $player->id]);
		$this->assertResponseNotContains('Phone');
		$this->assertResponseNotContains('Email Address');
		$this->assertResponseNotContains('Birthdate');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test tooltip method
	 */
	public function testTooltip(): void {
		[$admin, $manager, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$league = $this->loadFixtureScenario(LeagueWithRostersScenario::class, [
			'affiliate' => $affiliates[0],
			'coordinator' => $volunteer,
			'teams' => 2,
		]);
		$team = $league->divisions[0]->teams[0];
		$captain = $team->people[LeagueWithRostersScenario::$CAPTAIN];
		/** @var Person $player */
		$player = $team->people[LeagueWithRostersScenario::$PLAYER1];
		$invited = $team->people[LeagueWithRostersScenario::$INVITED];

		// Also add a relative (in a different affiliate) to the player
		/** @var Person $relative */
		$relative = PersonFactory::make()
			->withGroup(GROUP_PLAYER)
			->with('Affiliates', $affiliates[1])
			->with('Users')
			->persist();
		PeoplePersonFactory::make(['person_id' => $player->id, 'relative_id' => $relative->id])->persist();

		// Admins are allowed to view person tooltips, and have all information and options
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => $player->id], $admin->id);
		$this->assertResponseContains('mailto:' . $player->email);
		$this->assertResponseContains($player->home_phone . ' (home)');
		$this->assertResponseContains('/people\\/vcf?person=' . $player->id);
		$this->assertResponseContains('/people\\/note?person=' . $player->id);
		$this->assertResponseContains('/people\\/act_as?person=' . $player->id);

		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => $relative->id], $admin->id);
		$this->assertResponseContains('mailto:' . $relative->email);
		$this->assertResponseContains($relative->home_phone . ' (home)');
		$this->assertResponseContains('/people\\/vcf?person=' . $relative->id);
		$this->assertResponseContains('/people\\/note?person=' . $relative->id);
		$this->assertResponseContains('/people\\/act_as?person=' . $relative->id);

		$this->assertGetAjaxAsAccessRedirect(['controller' => 'People', 'action' => 'tooltip', 'person' => 10000],
			$admin->id, '/',
			'Invalid person.');

		// Managers are allowed to view person tooltips, and have all information and options
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => $player->id], $manager->id);
		$this->assertResponseContains('mailto:' . $player->email);
		$this->assertResponseContains($player->home_phone . ' (home)');
		$this->assertResponseContains('/people\\/vcf?person=' . $player->id);
		$this->assertResponseContains('/people\\/note?person=' . $player->id);
		$this->assertResponseContains('/people\\/act_as?person=' . $player->id);

		// But are restricted when viewing tooltip of people not in their affiliate
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => $relative->id], $manager->id);
		$this->assertResponseNotContains('mailto');
		$this->assertResponseNotContains('(home)');
		$this->assertResponseNotContains('(work)');
		$this->assertResponseNotContains('(mobile)');
		$this->assertResponseNotContains('/people\\/vcf?person=' . $relative->id);
		$this->assertResponseContains('/people\\/note?person=' . $relative->id);
		$this->assertResponseNotContains('/people\\/act_as?person=' . $relative->id);

		// Coordinator gets to see contact info for their captains
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => $captain->id], $volunteer->id);
		$this->assertResponseContains('mailto:' . $captain->email);
		$this->assertResponseContains($captain->home_phone . ' (home)');
		$this->assertResponseContains('/people\\/vcf?person=' . $captain->id);
		$this->assertResponseContains('/people\\/note?person=' . $captain->id);
		$this->assertResponseNotContains('/people\\/act_as?person=' . $captain->id);

		// Captain gets to see contact info for their players
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => $player->id], $captain->id);
		$this->assertResponseContains('mailto:' . $player->email);
		$this->assertResponseContains($player->home_phone . ' (home)');
		$this->assertResponseContains('/people\\/vcf?person=' . $player->id);
		$this->assertResponseContains('/people\\/note?person=' . $player->id);
		$this->assertResponseNotContains('/people\\/act_as?person=' . $player->id);

		// ...but not people who haven't confirmed the invite
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => $invited->id], $captain->id);
		$this->assertResponseNotContains('mailto');
		$this->assertResponseNotContains('(home)');
		$this->assertResponseNotContains('(work)');
		$this->assertResponseNotContains('(mobile)');
		$this->assertResponseNotContains('/people\\/vcf');

		// And for their coordinator
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => $volunteer->id], $captain->id);
		$this->assertResponseContains('mailto:' . $volunteer->email);
		$this->assertResponseContains($volunteer->home_phone . ' (home)');
		$this->assertResponseContains('/people\\/vcf?person=' . $volunteer->id);
		$this->assertResponseContains('/people\\/note?person=' . $volunteer->id);
		$this->assertResponseNotContains('/people\\/act_as?person=' . $volunteer->id);

		// Player gets to see contact info for their own captain
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => $captain->id], $player->id);
		$this->assertResponseContains('mailto:' . $captain->email);
		$this->assertResponseContains($captain->homme_phone . ' (home)');
		$this->assertResponseContains('/people\\/vcf?person=' . $captain->id);
		$this->assertResponseContains('/people\\/note?person=' . $captain->id);
		$this->assertResponseNotContains('/people\\/act_as?person=' . $captain->id);

		// And are allowed to act as their relatives
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => $relative->id], $player->id);
		$this->assertResponseContains('/people\\/note?person=' . $relative->id);
		$this->assertResponseContains('/people\\/act_as?person=' . $relative->id);

		// But sees less about other people
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => $invited->id], $player->id);
		$this->assertResponseNotContains('mailto');
		$this->assertResponseNotContains('(home)');
		$this->assertResponseNotContains('(work)');
		$this->assertResponseNotContains('(mobile)');
		$this->assertResponseNotContains('/people\\/vcf?person=' . $invited->id);
		$this->assertResponseContains('/people\\/note?person=' . $invited->id);
		$this->assertResponseNotContains('/people\\/act_as?person=' . $invited->id);

		// Including inactive people, even if they've published info
		$inactive = PersonFactory::make([
			'status' => 'inactive',
			'publish_home_phone' => true,
			'publish_email' => true,
		])->persist();
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => $inactive->id], $player->id);
		$this->assertResponseNotContains('mailto');
		$this->assertResponseNotContains('(home)');
		$this->assertResponseNotContains('(work)');
		$this->assertResponseNotContains('(mobile)');
		$this->assertResponseNotContains('/people\\/vcf?person=' . $inactive->id);
		$this->assertResponseContains('/people\\/note?person=' . $inactive->id);
		$this->assertResponseNotContains('/people\\/act_as?person=' . $inactive->id);

		$this->assertGetAjaxAnonymousAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => $player->id]);
	}

	/**
	 * Test edit method as an admin
	 */
	public function testEditAsAdmin(): void {
		[$admin, , , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Admins are allowed to edit
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'edit'], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'edit', 'person' => $player->id], $admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test edit method as a manager
	 */
	public function testEditAsManager(): void {
		[, $manager, , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Managers are allowed to edit
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'edit'], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'edit', 'person' => $player->id], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test edit method as a coordinator
	 */
	public function testEditAsCoordinator(): void {
		[, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Coordinators are allowed to edit themselves only
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'edit'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'edit', 'person' => $player->id], $volunteer->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test edit method as a player
	 */
	public function testEditAsPlayer(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Also add a relative to the player
		/** @var Person $relative */
		$relative = PersonFactory::make()
			->withGroup(GROUP_PLAYER)
			->with('Affiliates', $admin->affiliates[0])
			->persist();
		PeoplePersonFactory::make(['person_id' => $player->id, 'relative_id' => $relative->id])->persist();

		// Players are allowed to edit themselves and their relatives only
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'edit'], $player->id);
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'edit'],
			$player->id, ['shirt_size' => 'Mens Large'], '/', 'Your profile has been saved.');

		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'edit', 'person' => $relative->id], $player->id);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'edit', 'person' => $volunteer->id], $player->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test edit method without being logged in
	 */
	public function testEditAsAnonymous(): void {
		[, , , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Others are not allowed to edit people
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'edit']);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'edit', 'person' => $player->id]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test deactivate method as an admin
	 */
	public function testDeactivateAsAdmin(): void {
		[$admin, , , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Admins are allowed to deactivate people
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'deactivate', 'person' => $player->id], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'deactivate'], $admin->id);

		$this->enableCsrfToken();
		$this->enableSecurityToken();
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'deactivate'],
			$admin->id, [], '/', 'Your profile has been deactivated; sorry to see you go. If you ever change your mind, you can just return to the site and reactivate your profile; we\'ll be happy to have you back!');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test deactivate method as a manager
	 */
	public function testDeactivateAsManager(): void {
		[, $manager, , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Managers are allowed to deactivate people
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'deactivate', 'person' => $player->id], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'deactivate'], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test deactivate method as a coordinator
	 */
	public function testDeactivateAsCoordinator(): void {
		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$this->loadFixtureScenario(LeagueWithRostersScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'coordinator' => $volunteer,
			'teams' => 0,
		]);

		// Coordinators are not allowed to deactivate others, or themselves while they're actively running a league
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'deactivate', 'person' => $player->id], $volunteer->id);
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'deactivate'],
			$volunteer->id, '/',
			'You cannot deactivate your account while you are coordinating an active division.');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test deactivate method as a player
	 */
	public function testDeactivateAsPlayer(): void {
		$league = $this->loadFixtureScenario(LeagueWithRostersScenario::class, [
			'teams' => 1,
		]);
		$team = $league->divisions[0]->teams[0];
		$captain = $team->people[LeagueWithRostersScenario::$CAPTAIN];
		$player = $team->people[LeagueWithRostersScenario::$PLAYER1];

		// Players are not allowed to deactivate others, or themselves while they're on an active team
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'deactivate', 'person' => $captain->id], $player->id);
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'deactivate'],
			$player->id, '/',
			'You cannot deactivate your account while you are on an active team.');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test deactivate method without being logged in
	 */
	public function testDeactivateAsAnonymous(): void {
		[, , , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Others are not allowed to deactivate people
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'deactivate']);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'deactivate', 'person' => $player->id]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test reactivate method as an admin
	 */
	public function testReactivateAsAdmin(): void {
		[$admin, , , ] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		$inactive = PersonFactory::make(['status' => 'inactive'])
			->with('Affiliates', $admin->affiliates[0])
			->persist();

		// Admins are allowed to reactivate
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'reactivate', 'person' => $inactive->id], $admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test reactivate method as a manager
	 */
	public function testReactivateAsManager(): void {
		[$admin, $manager, , ] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		$inactive = PersonFactory::make(['status' => 'inactive'])
			->with('Affiliates', $admin->affiliates[0])
			->persist();

		// Managers are allowed to reactivate
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'reactivate', 'person' => $inactive->id], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test reactivate method as others
	 */
	public function testReactivateAsOthers(): void {
		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		$inactive = PersonFactory::make(['status' => 'inactive'])
			->with('Affiliates', $admin->affiliates[0])
			->with('Users')
			->persist();

		// Coordinators are not allowed to reactivate others
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'reactivate', 'person' => $inactive->id], $volunteer->id);

		// Players are not allowed to reactivate others
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'reactivate', 'person' => $inactive->id], $player->id);

		// Anyone lot logged in is not allowed to reactivate people
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'reactivate', 'person' => $inactive->id]);

		// Inactive accounts are allowed to reactivate themselves
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'reactivate', 'person' => $inactive->id], $inactive->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test confirm method
	 */
	public function testConfirmAsAdmin(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Admins are allowed to confirm
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'confirm'], $admin->id);

		// Managers are allowed to confirm
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'confirm'], $manager->id);

		// Coordinators are allowed to confirm
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'confirm'], $volunteer->id);

		// Players are allowed to confirm
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'confirm'], $player->id);

		// Others are not allowed to confirm
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'confirm']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test note method as an admin
	 */
	public function testNoteAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager, , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Admins are allowed to add notes
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'note', 'person' => $player->id], $admin->id);

		// Empty notes don't get added
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'note', 'person' => $player->id],
			$admin->id, [
				'person_id' => $player->id,
				'visibility' => VISIBILITY_PRIVATE,
				'note' => '',
			], ['action' => 'view', 'person' => $player->id], 'You entered no text, so no note was added.');

		// Add a private note
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'note', 'person' => $player->id],
			$admin->id, [
				'person_id' => $player->id,
				'visibility' => VISIBILITY_PRIVATE,
				'note' => 'This is a private note.',
			], ['action' => 'view', 'person' => $player->id], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertNoMailSent();

		// Add a note for all admins to see
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'note', 'person' => $player->id],
			$admin->id, [
				'person_id' => $player->id,
				'visibility' => VISIBILITY_ADMIN,
				'note' => 'This is an admin note.',
			], ['action' => 'view', 'person' => $player->id], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertNoMailSent();

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => $player->id], $admin->id);
		$this->assertResponseContains('This is a private note.');
		$this->assertResponseContains('This is an admin note.');

		// Check the manager can also see the admin one
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => $player->id], $manager->id);
		$this->assertResponseNotContains('This is a private note.');
		$this->assertResponseContains('This is an admin note.');

		// Empty notes don't get added
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'note', 'person' => $player->id],
			$admin->id, [
				'person_id' => $player->id,
				'visibility' => VISIBILITY_PRIVATE,
				'note' => '',
			], ['action' => 'view', 'person' => $player->id], 'You entered no text, so no note was added.');

		// Add a private note
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'note', 'person' => $player->id],
			$admin->id, [
				'person_id' => $player->id,
				'visibility' => VISIBILITY_PRIVATE,
				'note' => 'This is a private note.',
			], ['action' => 'view', 'person' => $player->id], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertNoMailSent();

		// Add a note for all admins to see
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'note', 'person' => $player->id],
			$admin->id, [
				'person_id' => $player->id,
				'visibility' => VISIBILITY_ADMIN,
				'note' => 'This is an admin note.',
			], ['action' => 'view', 'person' => $player->id], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertNoMailSent();

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => $player->id], $admin->id);
		$this->assertResponseContains('This is a private note.');
		$this->assertResponseContains('This is an admin note.');

		// Check the manager can also see the admin one
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => $player->id], $manager->id);
		$this->assertResponseNotContains('This is a private note.');
		$this->assertResponseContains('This is an admin note.');
	}

	/**
	 * Test note method as a manager
	 */
	public function testNoteAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager, , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Managers are allowed to add notes
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'note', 'person' => $player->id], $manager->id);
		// Add a private note
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'note', 'person' => $player->id],
			$manager->id, [
				'person_id' => $player->id,
				'visibility' => VISIBILITY_PRIVATE,
				'note' => 'This is a private note.',
			], ['action' => 'view', 'person' => $player->id], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertNoMailSent();

		// Add a note for all admins to see
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'note', 'person' => $player->id],
			$manager->id, [
				'person_id' => $player->id,
				'visibility' => VISIBILITY_ADMIN,
				'note' => 'This is an admin note.',
			], ['action' => 'view', 'person' => $player->id], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertNoMailSent();

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => $player->id], $manager->id);
		$this->assertResponseContains('This is a private note.');
		$this->assertResponseContains('This is an admin note.');

		// Check the admin can also see the admin one
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => $player->id], $admin->id);
		$this->assertResponseNotContains('This is a private note.');
		$this->assertResponseContains('This is an admin note.');
	}

	/**
	 * Test note method as a coordinator
	 */
	public function testNoteAsCoordinator(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Coordinators are allowed to add notes
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'note', 'person' => $player->id], $volunteer->id);

		// Add a private note
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'note', 'person' => $player->id],
			$volunteer->id, [
				'person_id' => $player->id,
				'visibility' => VISIBILITY_PRIVATE,
				'note' => 'This is a private note.',
			], ['action' => 'view', 'person' => $player->id], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertNoMailSent();

		// Make sure it was added successfully
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => $player->id], $volunteer->id);
		$this->assertResponseContains('This is a private note.');
	}

	/**
	 * Test note method as a player
	 */
	public function testNoteAsPlayer(): void {
		[, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Players are allowed to add notes
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'note', 'person' => $volunteer->id], $player->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test note method without being logged in
	 */
	public function testNoteAsAnonymous(): void {
		[, , , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Others are not allowed to add notes
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'note', 'person' => $player->id]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test delete_note method as an admin
	 */
	public function testDeleteNoteAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		$notes = NoteFactory::make([
			[
				'person_id' => $player->id,
				'visibility' => VISIBILITY_ADMIN,
				'created_person_id' => $admin->id,
				'note' => 'Admin note from admin about player.',
			],
			[
				'person_id' => $player->id,
				'visibility' => VISIBILITY_PRIVATE,
				'created_person_id' => $manager->id,
				'note' => 'Private note from manager about player.',
			],
			[
				'person_id' => $player->id,
				'visibility' => VISIBILITY_PRIVATE,
				'created_person_id' => $volunteer->id,
				'note' => 'Private note from volunteer about player.',
			],
			[
				'person_id' => $admin->id,
				'visibility' => VISIBILITY_PRIVATE,
				'created_person_id' => $player->id,
				'note' => 'Private note from player about admin.',
			],
		])->persist();

		// Admins are allowed to delete admin notes
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'delete_note', 'note' => $notes[0]->id],
			$admin->id, [], ['controller' => 'People', 'action' => 'view', 'person' => $player->id],
			'The note has been deleted.');

		// But not other notes
		$this->assertPostAsAccessDenied(['controller' => 'People', 'action' => 'delete_note', 'note' => $notes[1]->id],
			$admin->id);
		$this->assertPostAsAccessDenied(['controller' => 'People', 'action' => 'delete_note', 'note' => $notes[2]->id],
			$admin->id);
		$this->assertPostAsAccessDenied(['controller' => 'People', 'action' => 'delete_note', 'note' => $notes[3]->id],
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

		$notes = NoteFactory::make([
			[
				'person_id' => $player->id,
				'visibility' => VISIBILITY_ADMIN,
				'created_person_id' => $admin->id,
				'note' => 'Admin note from admin about player.',
			],
			[
				'person_id' => $player->id,
				'visibility' => VISIBILITY_PRIVATE,
				'created_person_id' => $manager->id,
				'note' => 'Private note from manager about player.',
			],
			[
				'person_id' => $player->id,
				'visibility' => VISIBILITY_PRIVATE,
				'created_person_id' => $volunteer->id,
				'note' => 'Private note from volunteer about player.',
			],
			[
				'person_id' => $admin->id,
				'visibility' => VISIBILITY_PRIVATE,
				'created_person_id' => $player->id,
				'note' => 'Private note from player about admin.',
			],
		])->persist();

		// Managers are allowed to delete admin notes
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'delete_note', 'note' => $notes[0]->id],
			$manager->id, [], ['controller' => 'People', 'action' => 'view', 'person' => $player->id],
			'The note has been deleted.');

		// And private notes they created
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'delete_note', 'note' => $notes[1]->id],
			$manager->id, [], ['controller' => 'People', 'action' => 'view', 'person' => $player->id],
			'The note has been deleted.');

		// But not other notes
		$this->assertPostAsAccessDenied(['controller' => 'People', 'action' => 'delete_note', 'note' => $notes[2]->id],
			$manager->id);
		$this->assertPostAsAccessDenied(['controller' => 'People', 'action' => 'delete_note', 'note' => $notes[3]->id],
			$manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test delete_note method as a player
	 */
	public function testDeleteNoteAsPlayer(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		$notes = NoteFactory::make([
			[
				'person_id' => $player->id,
				'visibility' => VISIBILITY_ADMIN,
				'created_person_id' => $admin->id,
				'note' => 'Admin note from admin about player.',
			],
			[
				'person_id' => $player->id,
				'visibility' => VISIBILITY_PRIVATE,
				'created_person_id' => $manager->id,
				'note' => 'Private note from manager about player.',
			],
			[
				'person_id' => $player->id,
				'visibility' => VISIBILITY_PRIVATE,
				'created_person_id' => $volunteer->id,
				'note' => 'Private note from volunteer about player.',
			],
			[
				'person_id' => $admin->id,
				'visibility' => VISIBILITY_PRIVATE,
				'created_person_id' => $player->id,
				'note' => 'Private note from player about admin.',
			],
		])->persist();

		// Players are only allowed to delete notes they created
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'delete_note', 'note' => $notes[3]->id],
			$player->id, [], ['controller' => 'People', 'action' => 'view', 'person' => $admin->id],
			'The note has been deleted.');

		// But not other notes
		$this->assertPostAsAccessDenied(['controller' => 'People', 'action' => 'delete_note', 'note' => $notes[0]->id],
			$player->id);
		$this->assertPostAsAccessDenied(['controller' => 'People', 'action' => 'delete_note', 'note' => $notes[1]->id],
			$player->id);
		$this->assertPostAsAccessDenied(['controller' => 'People', 'action' => 'delete_note', 'note' => $notes[2]->id],
			$player->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test delete_note method without being logged in
	 */
	public function testDeleteNoteAsAnonymous(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, , , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		$note = NoteFactory::make([
			'person_id' => $admin->id,
			'visibility' => VISIBILITY_PRIVATE,
			'created_person_id' => $player->id,
			'note' => 'Private note from player about admin.',
		])->persist();

		// Others are not allowed to delete notes
		$this->assertPostAjaxAnonymousAccessDenied(['controller' => 'People', 'action' => 'delete_note', 'note' => $note->id]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test preferences method
	 */
	public function testPreferences(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Admins are allowed to edit their preferences
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'preferences'], $admin->id);

		// Managers are allowed to edit their preferences
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'preferences'], $manager->id);

		// Coordinators are allowed to edit their preferences
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'preferences'], $volunteer->id);

		// Players are allowed to edit their preferences
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'preferences'], $player->id);
		$this->assertCookieNotSet('ZuluruLocale');

		$this->enableCsrfToken();
		$this->enableSecurityToken();

		$this->assertPostAsAccessOk(['controller' => 'People', 'action' => 'preferences'],
			$player->id, [
				MIN_FAKE_ID => [
					'category' => 'personal',
					'name' => 'language',
					'person_id' => $player->id,
					'value' => 'fr',
				],
			]);
		$this->assertCookie('fr', 'ZuluruLocale');
		$this->assertEquals('fr', I18n::getLocale());
		// Check the flash message. Since this wasn't a redirect, it's been rendered into the page.
		$this->assertResponseContains(__('The preferences have been saved.'));

		// A request for some other person doesn't set the cookie
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'preferences'], $admin->id);
		$this->assertCookieNotSet('ZuluruLocale');

		// Another request for the person with the preference does set it
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'preferences'], $player->id);
		$this->assertCookie('fr', 'ZuluruLocale');

		$this->assertPostAsAccessOk(['controller' => 'People', 'action' => 'preferences'],
			$player->id, [
				MIN_FAKE_ID => [
					'category' => 'personal',
					'name' => 'language',
					'person_id' => $player->id,
					'value' => '',
				],
			]);
		$this->assertCookieNotSet('ZuluruLocale');
		$this->assertResponseContains('The preferences have been saved.');

		// Others are allowed to edit their preferences
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'preferences']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add_relative method
	 */
	public function testAddRelative(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		/** @var Person $parent */
		$parent = PersonFactory::makeParent()
			->with('Affiliates')
			->persist();

		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'add_relative'],
			$parent->id, [
				'groups' => ['_ids' => [GROUP_PLAYER]],
				'affiliates' => [['id' => $parent->affiliates[0]->id]],
				'first_name' => 'Young',
				'last_name' => 'Test',
				'gender' => 'Woman',
				'gender_description' => null,
				'roster_designation' => 'Woman',
				'pronouns' => 'She, Her, Hers',
				'birthdate' => ['year' => FrozenDate::now()->year - 10, 'month' => '01', 'day' => '01'],
				'height' => 50,
				'shirt_size' => 'Youth Large',
				'skills' => [
					[
						'enabled' => false,
						'sport' => 'baseball',
					],
					[
						'enabled' => true,
						'sport' => 'ultimate',
						'year_started' => [
							'year' => FrozenDate::now()->year - 1
						],
						'skill_level' => 3,
					],
				],
				'action' => 'create',
			],
			'/', 'The new profile has been saved. It must be approved by an administrator before you will have full access to the site.'
		);

		// Parent ID is randomly assigned, but the child should be the next one thanks to autoincrement
		/** @var Person $child */
		$child = TableRegistry::getTableLocator()->get('People')->get($parent->id + 1, ['contain' => [
			'Affiliates',
			'Groups',
			'Skills',
		]]);
		$this->assertEquals('Young', $child->first_name);
		$this->assertEquals('new', $child->status);
		$this->assertEquals(true, $child->complete);
		$this->assertEquals(FrozenDate::now(), $child->modified);
		$this->assertCount(1, $child->affiliates);
		$this->assertEquals($parent->affiliates[0]->id, $child->affiliates[0]->id);
		$this->assertCount(1, $child->groups);
		$this->assertEquals(GROUP_PLAYER, $child->groups[0]->id);
		$this->assertCount(2, $child->skills);
		$this->assertEquals('baseball', $child->skills[0]->sport);
		$this->assertFalse($child->skills[0]->enabled);
		$this->assertEquals('ultimate', $child->skills[1]->sport);
		$this->assertTrue($child->skills[1]->enabled);
		$this->assertEquals(FrozenDate::now()->year - 1, $child->skills[1]->year_started);
		$this->assertEquals(3, $child->skills[1]->skill_level);
	}

	/**
	 * Test link_relative method
	 */
	public function testLinkRelative(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliate = $admin->affiliates[0];

		// Anyone is allowed to link relatives
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'link_relative'], $player->id);

		// Try the search page
		$this->assertPostAsAccessOk(['controller' => 'People', 'action' => 'link_relative'],
			$player->id, [
				'affiliate_id' => $affiliate->id,
				'first_name' => '',
				'last_name' => $volunteer->last_name,
				'sort' => 'last_name',
				'direction' => 'asc',
			]
		);
		$this->assertResponseContains('/people/link_relative?relative=' . $volunteer->id . '&amp;person=' . $player->id);

		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'link_relative', 'relative' => $volunteer->id, 'person' => $player->id],
			$player->id, '/',
			"Linked {$volunteer->full_name} as relative; you will not have access to their information until they have approved this.");

		// Confirm the notification email
		$this->assertMailCount(1);
		$this->assertMailSentFrom('admin@zuluru.org');
		$this->assertMailSentWith([$player->email => $player->full_name], 'ReplyTo');
		$this->assertMailSentTo($volunteer->email);
		$this->assertMailSentWith([], 'CC');
		$this->assertMailSentWith('You have been linked as a relative', 'Subject');
		$this->assertMailContains("{$player->full_name} has indicated on the Test Zuluru Affiliate web site that you are related to them.");
		$this->assertMailContains("If you accept, {$player->first_name} will be granted access");
		$this->assertMailContains(Configure::read('App.fullBaseUrl') . Configure::read('App.base') . '/people/approve_relative?person=' . $volunteer->id . '&relative=' . $player->id);
		$this->assertMailContains(Configure::read('App.fullBaseUrl') . Configure::read('App.base') . '/people/remove_relative?person=' . $volunteer->id . '&relative=' . $player->id);

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view'], $player->id);
		$this->assertResponseContains('/people/remove_relative?person=' . $player->id . '&amp;relative=' . $volunteer->id);
	}

	/**
	 * Test link_relative method without being logged in
	 */
	public function testLinkRelativeAsAnonymous(): void {
		// Others are not allowed to link relatives
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'link_relative']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test approve_relative method
	 */
	public function testApproveRelative(): void {
		[, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Add an unapproved relative to the player
		PeoplePersonFactory::make(['person_id' => $player->id, 'relative_id' => $volunteer->id, 'approved' => false])->persist();

		// The person that sent the request is not allowed to approve the request
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'approve_relative', 'person' => $player->id, 'relative' => $volunteer->id],
			$player->id);

		// The invited relative is allowed to approve the request
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'approve_relative', 'person' => $player->id, 'relative' => $volunteer->id],
			$volunteer->id, ['controller' => 'People', 'action' => 'view', 'person' => $volunteer->id],
			'Approved the relative request.');

		// Confirm the notification email
		$this->assertMailCount(1);
		$this->assertMailSentFrom('admin@zuluru.org');
		$this->assertMailSentWith([$volunteer->email => $volunteer->full_name], 'ReplyTo');
		$this->assertMailSentTo($player->email);
		$this->assertMailSentWith([], 'CC');
		$this->assertMailSentWith("{$volunteer->full_name} approved your relative request", 'Subject');
		$this->assertMailContains("Your relative request to {$volunteer->full_name} on the Test Zuluru Affiliate web site has been approved.");

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => $player->id], $player->id);
		$this->assertResponseRegExp('#<td>You can control <a[^>]*>' . $volunteer->full_name . '</a></td>\s*<td>Yes</td>#ms');
		$this->assertResponseNotRegExp('#<td><a[^>]*>' . $volunteer->full_name . '</a> can control you</td>\s*<td>Yes</td>#ms');

		$this->markTestIncomplete('Test with codes.');
	}

	/**
	 * Test remove_relative method as the person
	 */
	public function testRemoveRelativeAsPerson(): void {
		[, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Add an approved relative to the player
		PeoplePersonFactory::make(['person_id' => $player->id, 'relative_id' => $volunteer->id])->persist();

		// A person is allowed to remove their relations
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'remove_relative', 'person' => $player->id, 'relative' => $volunteer->id],
			$player->id, ['controller' => 'People', 'action' => 'view'],
			'Removed the relation.');

		// Confirm the notification email
		$this->assertMailCount(1);
		$this->assertMailSentFrom('admin@zuluru.org');
		$this->assertMailSentWith([$player->email => $player->full_name], 'ReplyTo');
		$this->assertMailSentTo($volunteer->email);
		$this->assertMailSentWith([], 'CC');
		$this->assertMailSentWith("{$player->full_name} removed your relation", 'Subject');
		$this->assertMailContains("{$player->full_name} has removed you as a relative on the Test Zuluru Affiliate web site.");

		// Make sure they were removed successfully
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => $player->id], $player->id);
		$this->assertResponseNotRegExp('#<td>You can control <a[^>]*>' . $volunteer->full_name . '</a></td>#ms');
	}

	/**
	 * Test remove_relative method as the relative
	 */
	public function testRemoveRelativeAsRelative(): void {
		[, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Add an approved relative to the player
		PeoplePersonFactory::make(['person_id' => $player->id, 'relative_id' => $volunteer->id])->persist();

		// A person is allowed to remove relations in either direction
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'remove_relative', 'person' => $player->id, 'relative' => $volunteer->id],
			$volunteer->id, ['controller' => 'People', 'action' => 'view'],
			'Removed the relation.');

		// Confirm the notification email
		$this->assertMailCount(1);
		$this->assertMailSentFrom('admin@zuluru.org');
		$this->assertMailSentWith([$volunteer->email => $volunteer->full_name], 'ReplyTo');
		$this->assertMailSentTo($player->email);
		$this->assertMailSentWith([], 'CC');
		$this->assertMailSentWith("{$volunteer->full_name} removed your relation", 'Subject');
		$this->assertMailContains("{$volunteer->full_name} has removed you as a relative on the Test Zuluru Affiliate web site.");

		// Make sure they were removed successfully
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => $volunteer->id], $volunteer->id);
		$this->assertResponseNotRegExp('#<a[^>]*>' . $volunteer->full_name . '</a> can control you</td>#ms');
	}

	/**
	 * Test remove_relative method as someone else
	 */
	public function testRemoveRelativeAsOthers(): void {
		[, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Add an approved relative to the player
		PeoplePersonFactory::make(['person_id' => $player->id, 'relative_id' => $manager->id])->persist();

		// Others are not allowed to remove relatives
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'remove_relative', 'person' => $player->id, 'relative' => $volunteer->id],
			$volunteer->id, ['controller' => 'People', 'action' => 'view', 'person' => $player->id],
			'The authorization code is invalid.');

		$this->markTestIncomplete('Test with codes, and as admin / manager.');
	}

	/**
	 * Test authorize_twitter method as an admin
	 */
	public function testAuthorizeTwitterAsAdmin(): void {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test authorize_twitter method as a manager
	 */
	public function testAuthorizeTwitterAsManager(): void {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test authorize_twitter method as a coordinator
	 */
	public function testAuthorizeTwitterAsCoordinator(): void {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test authorize_twitter method as a captain
	 */
	public function testAuthorizeTwitterAsCaptain(): void {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test authorize_twitter method as a player
	 */
	public function testAuthorizeTwitterAsPlayer(): void {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test authorize_twitter method as someone else
	 */
	public function testAuthorizeTwitterAsVisitor(): void {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test authorize_twitter method without being logged in
	 */
	public function testAuthorizeTwitterAsAnonymous(): void {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test revoke_twitter method as an admin
	 */
	public function testRevokeTwitterAsAdmin(): void {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test revoke_twitter method as a manager
	 */
	public function testRevokeTwitterAsManager(): void {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test revoke_twitter method as a coordinator
	 */
	public function testRevokeTwitterAsCoordinator(): void {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test revoke_twitter method as a captain
	 */
	public function testRevokeTwitterAsCaptain(): void {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test revoke_twitter method as a player
	 */
	public function testRevokeTwitterAsPlayer(): void {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test revoke_twitter method as someone else
	 */
	public function testRevokeTwitterAsVisitor(): void {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test revoke_twitter method without being logged in
	 */
	public function testRevokeTwitterAsAnonymous(): void {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test photo method
	 */
	public function testPhoto(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		UploadFactory::make(['person_id' => $player->id])->persist();

		// Anyone logged in is allowed to view photos
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'photo', 'person' => $player->id], $admin->id);
		$this->assertHeader('Content-Type', 'image/png');
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'photo', 'person' => $player->id], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'photo', 'person' => $player->id], $volunteer->id);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'photo', 'person' => $player->id], $player->id);

		// Others are not allowed to view photos
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'photo', 'person' => $player->id]);

		$this->markTestIncomplete('Test viewing of unapproved photos.');
	}

	/**
	 * Test photo_upload method
	 */
	public function testPhotoUpload(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Admins are allowed to upload photos
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'photo_upload'], $admin->id);

		// Managers are allowed to upload photos
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'photo_upload'], $manager->id);

		// Coordinators are allowed to upload photos
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'photo_upload'], $volunteer->id);

		// Players are allowed to upload photos
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'photo_upload'], $player->id);

		// Others are not allowed to upload photos
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'photo_upload']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test approve_photos method
	 */
	public function testApprovePhotos(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		UploadFactory::make(['person_id' => $player->id, 'approved' => false])->persist();

		// Admins are allowed to approve photos
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'approve_photos'], $admin->id);

		// Managers are allowed to approve photos
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'approve_photos'], $manager->id);

		// Others are not allowed to approve photos
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'approve_photos'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'approve_photos'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'approve_photos']);

		$this->markTestIncomplete('Test other affiliates, redirect when there are none waiting for approval.');
	}

	/**
	 * Test approve_photo method as an admin
	 */
	public function testApprovePhotoAsAdmin(): void {
		[$admin, , , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		UploadFactory::make(['person_id' => $player->id, 'approved' => false])->persist();

		// Admins are allowed to approve photo
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'approve_photo', 'person' => $player->id],
			$admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test approve_photo method as a manager
	 */
	public function testApprovePhotoAsManager(): void {
		[, $manager, , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		UploadFactory::make(['person_id' => $player->id, 'approved' => false])->persist();

		// Managers are allowed to approve photos
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'approve_photo', 'person' => $player->id],
			$manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test approve_photo method as others
	 */
	public function testApprovePhotoAsOthers(): void {
		[, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		UploadFactory::make(['person_id' => $player->id, 'approved' => false])->persist();

		// Others are not allowed to approve photos
		$this->assertGetAjaxAsAccessDenied(['controller' => 'People', 'action' => 'approve_photo', 'person' => $player->id],
			$volunteer->id);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'People', 'action' => 'approve_photo', 'person' => $player->id],
			$player->id);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'People', 'action' => 'approve_photo', 'person' => $player->id]);
	}

	/**
	 * Test delete_photo method as an admin
	 */
	public function testDeletePhotoAsAdmin(): void {
		[$admin, , , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		UploadFactory::make(['person_id' => $player->id, 'approved' => false])->persist();

		// Admins are allowed to delete photos
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'delete_photo', 'person' => $player->id],
			$admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test delete_photo method as a manager
	 */
	public function testDeletePhotoAsManager(): void {
		[, $manager, , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		UploadFactory::make(['person_id' => $player->id, 'approved' => false])->persist();

		// Managers are allowed to delete photos
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'delete_photo', 'person' => $player->id],
			$manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test delete_photo method as others
	 */
	public function testDeletePhotoAsOthers(): void {
		[, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		UploadFactory::make(['person_id' => $player->id, 'approved' => false])->persist();

		// Others are not allowed to delete photos
		$this->assertGetAjaxAsAccessDenied(['controller' => 'People', 'action' => 'delete_photo', 'person' => $player->id],
			$volunteer->id);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'People', 'action' => 'delete_photo', 'person' => $player->id],
			$player->id);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'People', 'action' => 'delete_photo', 'person' => $player->id]);
	}

	/**
	 * Test document method
	 */
	public function testDocument(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var Upload $upload */
		$upload = UploadFactory::make(['person_id' => $player->id])
			->with('UploadTypes', ['affiliate_id' => $admin->affiliates[0]->id])
			->persist();

		// Admins are allowed to view documents
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'document', 'document' => $upload->id], $admin->id);

		// Managers are allowed to view documents
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'document', 'document' => $upload->id], $manager->id);

		// Others are not allowed to view documents
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'document', 'document' => $upload->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'document', 'document' => $upload->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'document', 'document' => $upload->id]);
	}

	/**
	 * Test document_upload method as an admin
	 */
	public function testDocumentUploadAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Admins are allowed to upload documents
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'document_upload'], $admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test document_upload method as a manager
	 */
	public function testDocumentUploadAsManager(): void {
		[, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Managers are allowed to upload documents
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'document_upload'], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test document_upload method as a player
	 */
	public function testDocumentUploadAsPlayer(): void {
		[, , , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Players are allowed to upload documents
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'document_upload'], $player->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test document_upload method without being logged in
	 */
	public function testDocumentUploadAsAnonymous(): void {
		// Others are not allowed to upload documents
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'document_upload']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test approve_documents method
	 */
	public function testApproveDocuments(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var Upload $upload */
		$upload = UploadFactory::make(['person_id' => $player->id, 'approved' => false])
			->with('UploadTypes', ['affiliate_id' => $admin->affiliates[0]->id])
			->persist();

		// Admins are allowed to approve documents
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'approve_documents'], $admin->id);
		$this->assertResponseContains("approve_document?document={$upload->id}");

		// Managers are allowed to approve documents
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'approve_documents'], $manager->id);
		$this->assertResponseContains("approve_document?document={$upload->id}");

		// Others are not allowed to approve documents
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'approve_documents'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'approve_documents'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'approve_documents']);
	}

	/**
	 * Test approve_document method as an admin
	 */
	public function testApproveDocumentAsAdmin(): void {
		[$admin, , , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var Upload $upload */
		$upload = UploadFactory::make(['person_id' => $player->id, 'approved' => false])
			->with('UploadTypes', ['affiliate_id' => $admin->affiliates[0]->id])
			->persist();

		// Admins are allowed to approve documents
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'approve_document', 'document' => $upload->id], $admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test approve_document method as a manager
	 */
	public function testApproveDocumentAsManager(): void {
		[$admin, $manager, , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var Upload $upload */
		$upload = UploadFactory::make(['person_id' => $player->id, 'approved' => false])
			->with('UploadTypes', ['affiliate_id' => $admin->affiliates[0]->id])
			->persist();

		// Managers are allowed to approve documents
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'approve_document', 'document' => $upload->id], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test approve_document method as others
	 */
	public function testApproveDocumentAsOthers(): void {
		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var Upload $upload */
		$upload = UploadFactory::make(['person_id' => $player->id, 'approved' => false])
			->with('UploadTypes', ['affiliate_id' => $admin->affiliates[0]->id])
			->persist();

		// Others are not allowed to approve documents
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'approve_document', 'document' => $upload->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'approve_document', 'document' => $upload->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'approve_document', 'document' => $upload->id]);
	}

	/**
	 * Test edit_document method as an admin
	 */
	public function testEditDocumentAsAdmin(): void {
		[$admin, , , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var Upload $upload */
		$upload = UploadFactory::make(['person_id' => $player->id])
			->with('UploadTypes', ['affiliate_id' => $admin->affiliates[0]->id])
			->persist();

		// Admins are allowed to edit documents
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'edit_document', 'document' => $upload->id], $admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test edit_document method as a manager
	 */
	public function testEditDocumentAsManager(): void {
		[$admin, $manager, , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var Upload $upload */
		$upload = UploadFactory::make(['person_id' => $player->id])
			->with('UploadTypes', ['affiliate_id' => $admin->affiliates[0]->id])
			->persist();

		// Managers are allowed to edit documents
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'edit_document', 'document' => $upload->id], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test edit_document method as others
	 */
	public function testEditDocumentAsOthers(): void {
		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var Upload $upload */
		$upload = UploadFactory::make(['person_id' => $player->id])
			->with('UploadTypes', ['affiliate_id' => $admin->affiliates[0]->id])
			->persist();

		// Others are not allowed to edit documents
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'edit_document', 'document' => $upload->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'edit_document', 'document' => $upload->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'edit_document', 'document' => $upload->id]);
	}

	/**
	 * Test delete_document method as an admin
	 */
	public function testDeleteDocumentAsAdmin(): void {
		[$admin, , , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var Upload $upload */
		$upload = UploadFactory::make(['person_id' => $player->id])
			->with('UploadTypes', ['affiliate_id' => $admin->affiliates[0]->id])
			->persist();

		// Admins are allowed to delete documents
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'delete_document', 'document' => $upload->id],
			$admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test delete_document method as a manager
	 */
	public function testDeleteDocumentAsManager(): void {
		[$admin, $manager, , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var Upload $upload */
		$upload = UploadFactory::make(['person_id' => $player->id])
			->with('UploadTypes', ['affiliate_id' => $admin->affiliates[0]->id])
			->persist();

		// Managers are allowed to delete documents
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'delete_document', 'document' => $upload->id],
			$manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test delete_document method as others
	 */
	public function testDeleteDocumentAsOthers(): void {
		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var Upload $upload */
		$upload = UploadFactory::make(['person_id' => $player->id])
			->with('UploadTypes', ['affiliate_id' => $admin->affiliates[0]->id])
			->persist();

		// Others are not allowed to delete documents
		$this->assertGetAjaxAsAccessDenied(['controller' => 'People', 'action' => 'delete_document', 'document' => $upload->id],
			$volunteer->id);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'People', 'action' => 'delete_document', 'document' => $upload->id],
			$player->id);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'People', 'action' => 'delete_document', 'document' => $upload->id]);
	}

	/**
	 * Test nominate method as an admin
	 */
	public function testNominateAsAdmin(): void {
		[$admin, , , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var Badge $badge */
		$badge = BadgeFactory::make(['affiliate_id' => $admin->affiliates[0]->id, 'category' => 'nominated'])
			->persist();

		// Admins are allowed to nominate
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'nominate'], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'nominate_badge', 'badge' => $badge->id], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'nominate_badge_reason', 'badge' => $badge->id, 'person' => $player->id], $admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test nominate method as a manager
	 */
	public function testNominateAsManager(): void {
		[$admin, $manager, , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var Badge $badge */
		$badge = BadgeFactory::make(['affiliate_id' => $admin->affiliates[0]->id, 'category' => 'nominated'])
			->persist();

		// Managers are allowed to nominate
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'nominate'], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'nominate_badge', 'badge' => $badge->id], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'nominate_badge_reason', 'badge' => $badge->id, 'person' => $player->id], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test nominate method as a player
	 */
	public function testNominateAsPlayer(): void {
		[$admin, , , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var Badge $badge */
		$badge = BadgeFactory::make(['affiliate_id' => $admin->affiliates[0]->id, 'category' => 'nominated'])
			->persist();

		// Players are allowed to nominate
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'nominate'], $player->id);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'nominate_badge', 'badge' => $badge->id], $player->id);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'nominate_badge_reason', 'badge' => $badge->id, 'person' => $player->id], $player->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test nominate method without being logged in
	 */
	public function testNominateAsAnonymous(): void {
		[$admin, , , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var Badge $badge */
		$badge = BadgeFactory::make(['affiliate_id' => $admin->affiliates[0]->id, 'category' => 'nominated'])
			->persist();

		// Others are not allowed to nominate
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'nominate']);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'nominate_badge', 'badge' => $badge->id]);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'nominate_badge_reason', 'badge' => $badge->id, 'person' => $player->id]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test approve_badges method
	 */
	public function testApproveBadges(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var Badge $badge */
		$badge = BadgesPersonFactory::make(['person_id' => $player->id, 'nominated_by_id' => $volunteer->id])
			->with('Badges', ['affiliate_id' => $admin->affiliates[0]->id, 'category' => 'nominated'])
			->persist();

		// Admins are allowed to approve badges
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'approve_badges'], $admin->id);
		$this->assertResponseContains("approve_badge?badge={$badge->id}");

		// Managers are allowed to approve badges
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'approve_badges'], $manager->id);
		$this->assertResponseContains("approve_badge?badge={$badge->id}");

		// Others are not allowed to approve badges
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'approve_badges'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'approve_badges'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'approve_badges']);
	}

	/**
	 * Test approve_badge method as an admin
	 */
	public function testApproveBadgeAsAdmin(): void {
		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var Badge $badge */
		$badge = BadgesPersonFactory::make(['person_id' => $player->id, 'nominated_by_id' => $volunteer->id])
			->with('Badges', ['affiliate_id' => $admin->affiliates[0]->id, 'category' => 'nominated'])
			->persist();

		// Admins are allowed to approve badges
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'approve_badge', 'badge' => $badge->id],
			$admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test approve_badge method as a manager
	 */
	public function testApproveBadgeAsManager(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var Badge $badge */
		$badge = BadgesPersonFactory::make(['person_id' => $player->id, 'nominated_by_id' => $volunteer->id])
			->with('Badges', ['affiliate_id' => $admin->affiliates[0]->id, 'category' => 'nominated'])
			->persist();

		// Managers are allowed to approve badges
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'approve_badge', 'badge' => $badge->id],
			$manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test approve_badge method as others
	 */
	public function testApproveBadgeAsOthers(): void {
		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var Badge $badge */
		$badge = BadgesPersonFactory::make(['person_id' => $player->id, 'nominated_by_id' => $volunteer->id])
			->with('Badges', ['affiliate_id' => $admin->affiliates[0]->id, 'category' => 'nominated'])
			->persist();

		// Others are not allowed to approve badges
		$this->assertGetAjaxAsAccessDenied(['controller' => 'People', 'action' => 'approve_badge', 'badge' => $badge->id],
			$volunteer->id);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'People', 'action' => 'approve_badge', 'badge' => $badge->id],
			$player->id);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'People', 'action' => 'approve_badge', 'badge' => $badge->id]);
	}

	/**
	 * Test delete_badge method as an admin
	 */
	public function testDeleteBadgeAsAdmin(): void {
		$this->enableCsrfToken();

		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var Badge $badge */
		$badge = BadgesPersonFactory::make(['person_id' => $player->id, 'nominated_by_id' => $volunteer->id])
			->with('Badges', ['affiliate_id' => $admin->affiliates[0]->id, 'category' => 'nominated'])
			->persist();

		// Admins are allowed to delete badges
		$this->assertPostAjaxAsAccessOk(['controller' => 'People', 'action' => 'delete_badge', 'badge' => $badge->id],
			$admin->id, ['comment' => 'No badge for you.']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test delete_badge method as a manager
	 */
	public function testDeleteBadgeAsManager(): void {
		$this->enableCsrfToken();

		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var Badge $badge */
		$badge = BadgesPersonFactory::make(['person_id' => $player->id, 'nominated_by_id' => $volunteer->id])
			->with('Badges', ['affiliate_id' => $admin->affiliates[0]->id, 'category' => 'nominated'])
			->persist();

		// Managers are allowed to delete badges
		$this->assertPostAjaxAsAccessOk(['controller' => 'People', 'action' => 'delete_badge', 'badge' => $badge->id],
			$manager->id, ['comment' => 'No badge for you.']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test delete_badge method as others
	 */
	public function testDeleteBadgeAsOthers(): void {
		$this->enableCsrfToken();

		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var Badge $badge */
		$badge = BadgesPersonFactory::make(['person_id' => $player->id, 'nominated_by_id' => $volunteer->id])
			->with('Badges', ['affiliate_id' => $admin->affiliates[0]->id, 'category' => 'nominated'])
			->persist();

		// Others are not allowed to delete badges
		$this->assertPostAjaxAsAccessDenied(['controller' => 'People', 'action' => 'delete_badge', 'badge' => $badge->id],
			$volunteer->id, ['comment' => 'No badge for you.']);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'People', 'action' => 'delete_badge', 'badge' => $badge->id],
			$player->id, ['comment' => 'No badge for you.']);
		$this->assertPostAjaxAnonymousAccessDenied(['controller' => 'People', 'action' => 'delete_badge', 'badge' => $badge->id],
			['comment' => 'No badge for you.']);
	}

	/**
	 * Test delete method as an admin
	 */
	public function testDeleteAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, , , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Admins are allowed to delete people
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'delete', 'person' => $player->id],
			$admin->id, [], '/',
			'The person has been deleted.');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test delete method as a manager
	 */
	public function testDeleteAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[, $manager, , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Managers are allowed to delete people
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'delete', 'person' => $player->id],
			$manager->id, [], '/',
			'The person has been deleted.');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test delete method as others
	 */
	public function testDeleteAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Others are not allowed to delete people
		$this->assertPostAsAccessDenied(['controller' => 'People', 'action' => 'delete', 'person' => $player->id],
			$volunteer->id);
		$this->assertPostAsAccessDenied(['controller' => 'People', 'action' => 'delete', 'person' => $volunteer->id],
			$player->id);
		$this->assertPostAnonymousAccessDenied(['controller' => 'People', 'action' => 'delete', 'person' => $player->id]);
	}

	/**
	 * Test splash method
	 */
	public function testSplash(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		// Put the player on a team
		$this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'home_player' => $player,
		]);

		// Create a new account to approve
		PersonFactory::makePlayer(['status' => 'new'])
			->with('Affiliates', $affiliates[0])
			->persist();

		// Include all menu building in these tests
		Configure::write('feature.minimal_menus', false);

		// Anyone is allowed to get the splash page, different roles have different sets of messages
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'splash'], $admin->id);
		$this->assertResponseRegExp('#The following affiliates do not yet have managers assigned to them:.*/affiliates/edit\?affiliate=' . $affiliates[1]->id . '.*/affiliates/delete\?affiliate=' . $affiliates[1]->id . '#ms');
		$this->assertResponseContains('There are 1 new <a href="' . Configure::read('App.base') . '/people/list_new">accounts to approve</a>');
		$this->assertResponseNotContains('Recent and Upcoming Schedule');

		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'splash'], $manager->id);
		$this->assertResponseNotContains('The following affiliates do not yet have managers assigned to them');
		$this->assertResponseNotContains('affiliates/delete');
		$this->assertResponseContains('There are 1 new <a href="' . Configure::read('App.base') . '/people/list_new">accounts to approve</a>');
		$this->assertResponseNotContains('Recent and Upcoming Schedule');

		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'splash'], $volunteer->id);

		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'splash'], $player->id);
		$this->assertResponseNotContains('The following affiliates do not yet have managers assigned to them');
		$this->assertResponseNotContains('affiliates/delete');
		$this->assertResponseNotContains('accounts to approve');
		$this->assertResponseContains('Recent and Upcoming Schedule');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test schedule method
	 */
	public function testSchedule(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Put the player on a team
		$this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'coordinator' => $volunteer,
			'home_player' => $player,
		]);

		// Also add a relative to the player
		/** @var Person $relative */
		$relative = PersonFactory::make()
			->withGroup(GROUP_PLAYER)
			->with('Affiliates', $admin->affiliates[0])
			->with('Users')
			->persist();
		PeoplePersonFactory::make(['person_id' => $player->id, 'relative_id' => $relative->id])->persist();

		// Anyone logged in is allowed to see their own schedule, and that of confirmed relatives
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'schedule'],
			$admin->id);
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'schedule'],
			$manager->id);
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'schedule'],
			$volunteer->id);

		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'schedule'],
			$player->id);
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'schedule', 'person' => $relative->id],
			$player->id);
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'schedule'],
			[$player->id, $relative->id]);

		// Others are not allowed to get schedules
		$this->assertGetAjaxAsAccessDenied(['controller' => 'People', 'action' => 'schedule', 'person' => $player->id],
			$admin->id);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'People', 'action' => 'schedule']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test consolidated_schedule method
	 */
	public function testConsolidatedSchedule(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Anyone logged in is allowed to see their consolidated schedule
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'consolidated_schedule'],
			$admin->id);
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'consolidated_schedule'],
			$manager->id);
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'consolidated_schedule'],
			$volunteer->id);
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'consolidated_schedule'],
			$player->id);

		// Others are not allowed to see consolidated schedules
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'People', 'action' => 'consolidated_schedule']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test act_as method
	 */
	public function testActAs(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Also add a relative (in a different affiliate) to the player
		/** @var Person $relative */
		$relative = PersonFactory::make()
			->withGroup(GROUP_PLAYER)
			->with('Affiliates', $admin->affiliates[1])
			->with('Users')
			->persist();
		PeoplePersonFactory::make(['person_id' => $player->id, 'relative_id' => $relative->id])->persist();

		// Admins are allowed to act as anyone
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'act_as'],
			$admin->id, '/',
			'There is nobody else you can act as.');
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'act_as', 'person' => $manager->id],
			$admin->id, '/',
			'You are now acting as ' . $manager->full_name . '.');
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'act_as', 'person' => $volunteer->id],
			$admin->id, '/',
			'You are now acting as ' . $volunteer->full_name . '.');
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'act_as', 'person' => $player->id],
			$admin->id, '/',
			'You are now acting as ' . $player->full_name . '.');
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'act_as', 'person' => $relative->id],
			$admin->id, '/',
			'You are now acting as ' . $relative->full_name . '.');

		// Managers are allowed to act as anyone in their affiliate
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'act_as', 'person' => $admin->id],
			$manager->id, '/',
			'Managers cannot act as other managers.');
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'act_as', 'person' => $volunteer->id],
			$manager->id, '/',
			'You are now acting as ' . $volunteer->full_name . '.');
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'act_as', 'person' => $player->id],
			$manager->id, '/',
			'You are now acting as ' . $player->full_name . '.');
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'act_as', 'person' => $relative->id], $manager->id);

		// Others are allowed to act as themselves or their relatives only
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'act_as', 'person' => $relative->id],
			$player->id, '/',
			'You are now acting as ' . $relative->full_name . '.');
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'act_as', 'person' => $player->id], $relative->id);

		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'act_as', 'person' => $player->id]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test search method
	 */
	public function testSearch(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Anyone logged in is allowed to search
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'search'], $admin->id);

		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'search'], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'search'], $volunteer->id);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'search'], $player->id);

		// Others are not allowed to search
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'search']);

		// Check search results
		$data = [
			'first_name' => $player->first_name,
		];
		$this->assertPostAjaxAsAccessOk(['controller' => 'People', 'action' => 'search'], $admin->id, $data);
		$this->assertResponseContains('showing 1 records out of 1 total');
		$this->assertResponseContains('<a href=\"\/people\/view?person=' . $player->id . '\" id=\"people_person_' . $player->id . '\" class=\"trigger\">' . $player->first_name . '<\/a>');
		$this->assertResponseContains('<a href=\"\/people\/view?person=' . $player->id . '\" id=\"people_person_' . $player->id . '\" class=\"trigger\">' . $player->last_name . '<\/a>');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test rule_search method
	 */
	public function testRuleSearch(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Admins are allowed to rule search
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'rule_search'], $admin->id);

		// Managers are allowed to rule search
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'rule_search'], $manager->id);

		// Others are not allowed to rule search
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'rule_search'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'rule_search'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'rule_search']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test league_search method
	 */
	public function testLeagueSearch(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Admins are allowed to league search
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'league_search'], $admin->id);

		// Managers are allowed to league search
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'league_search'], $manager->id);

		// Others are not allowed to league search
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'league_search'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'league_search'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'league_search']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test inactive_search method
	 */
	public function testInactiveSearch(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Admins are allowed to inactive search
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'inactive_search'], $admin->id);

		// Managers are allowed to inactive search
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'inactive_search'], $manager->id);

		// Others are not allowed to inactive search
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'inactive_search'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'inactive_search'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'inactive_search']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test list_new method
	 */
	public function testListNew(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Create a new account to approve
		PersonFactory::makePlayer(['status' => 'new'])
			->with('Affiliates', $admin->affiliates[0])
			->persist();

		// Admins are allowed to list new users
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'list_new'], $admin->id);

		// Managers are allowed to list new users
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'list_new'], $manager->id);

		// Others are not allowed to list new users
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'list_new'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'list_new'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'list_new']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test approve method as an admin
	 */
	public function testApproveAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Create a new account to approve
		$new = PersonFactory::makePlayer(['status' => 'new'])
			->with('Affiliates', $admin->affiliates[0])
			->persist();

		// Admins are allowed to approve
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'approve', 'person' => $new->id], $admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test approve method as an admin, approving a duplicate
	 */
	public function testApproveAsAdminApprove(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, , , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Create a duplicate account to approve
		$new = $this->createDuplicate($player, $admin->affiliates[0]);

		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'approve', 'person' => $new->id],
			$admin->id, ['disposition' => 'approved'], ['controller' => 'People', 'action' => 'list_new']);

		// Confirm the notification email
		$this->assertMailCount(1);
		$this->assertMailSentFrom('admin@zuluru.org');
		$this->assertMailSentWith([], 'ReplyTo');
		$this->assertMailSentTo($new->email);
		$this->assertMailSentWith([], 'CC');
		$this->assertMailSentWith('Test Zuluru Affiliate Account Activation for ' . $new->user->user_name, 'Subject');
		$this->assertMailContains('Your TZA account has been approved.');

		// Make sure that everything is still there
		/** @var Person $person */
		$person = TableRegistry::getTableLocator()->get('People')->get($new->id, [
			'contain' => [Configure::read('Security.authModel'), 'Affiliates', 'Groups', 'Settings', 'Skills', 'Preregistrations', 'Franchises']
		]);
		$this->assertEquals('active', $person->status);
		$this->assertEquals($new->user_id, $person->user_id);
		$this->assertNotNull($person->user);
		$this->assertEquals($new->user_id, $person->user->id);
		$this->assertCount(1, $person->affiliates);
		$this->assertCount(1, $person->groups);
		$this->assertCount(2, $person->settings);
		$this->assertCount(2, $person->skills);
	}

	/**
	 * Test approve method as an admin, silently deleting the duplicate
	 */
	public function testApproveAsAdminDeleteSilently(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, , , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Create a duplicate account to delete
		$new = $this->createDuplicate($player, $admin->affiliates[0]);

		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'approve', 'person' => $new->id],
			$admin->id, ['disposition' => 'delete'], ['controller' => 'People', 'action' => 'list_new']);

		// Confirm there was no notification email
		$this->assertNoMailSent();

		// Make sure that everything is gone
		/** @var PeopleTable $table */
		$table = TableRegistry::getTableLocator()->get('People');
		$authModel = Configure::read('Security.authModel');
		$this->assertEquals(0, $table->find()->where(['id' => $new->id])->count());
		$this->assertEquals(0, $table->$authModel->find()->where(['id' => $new->id])->count());
		$this->assertEquals(0, $table->AffiliatesPeople->find()->where(['person_id' => $new->id])->count());
		$this->assertEquals(0, $table->GroupsPeople->find()->where(['person_id' => $new->id])->count());
		$this->assertEquals(0, $table->Settings->find()->where(['person_id' => $new->id])->count());
		$this->assertEquals(0, $table->Skills->find()->where(['person_id' => $new->id])->count());
	}

	/**
	 * Test approve method as an admin, deleting the duplicate with notice
	 */
	public function testApproveAsAdminDeleteNotice(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, , , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Create a duplicate account to delete
		$new = $this->createDuplicate($player, $admin->affiliates[0]);

		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'approve', 'person' => $new->id],
			$admin->id, ['disposition' => 'delete_duplicate:' . $player->id], ['controller' => 'People', 'action' => 'list_new']);

		// Confirm the notification email
		$this->assertMailCount(1);
		$this->assertMailSentFrom('admin@zuluru.org');
		$this->assertMailSentWith([], 'ReplyTo');
		$this->assertMailSentTo($player->email);
		$this->assertMailSentTo($new->email);
		$this->assertMailSentWith([], 'CC');
		$this->assertMailSentWith('Test Zuluru Affiliate Account Update', 'Subject');
		$this->assertMailContains('You seem to have created a duplicate TZA account. You already have an account.');
		$this->assertMailContains('Your second account has been deleted.');

		// Make sure that everything is gone
		/** @var PeopleTable $table */
		$table = TableRegistry::getTableLocator()->get('People');
		$authModel = Configure::read('Security.authModel');
		$this->assertEquals(0, $table->find()->where(['id' => $new->id])->count());
		$this->assertEquals(0, $table->$authModel->find()->where(['id' => $new->id])->count());
		$this->assertEquals(0, $table->AffiliatesPeople->find()->where(['person_id' => $new->id])->count());
		$this->assertEquals(0, $table->GroupsPeople->find()->where(['person_id' => $new->id])->count());
		$this->assertEquals(0, $table->Settings->find()->where(['person_id' => $new->id])->count());
		$this->assertEquals(0, $table->Skills->find()->where(['person_id' => $new->id])->count());
	}

	/**
	 * Test approve method as an admin, merging the duplicate with another
	 */
	public function testApproveAsAdminMerge(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, , , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Create a duplicate account to delete
		$new = $this->createDuplicate($player, $admin->affiliates[0]);

		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'approve', 'person' => $new->id],
			$admin->id, ['disposition' => 'merge_duplicate:' . $player->id], ['controller' => 'People', 'action' => 'list_new']);

		// Confirm the notification email
		$this->assertMailCount(1);
		$this->assertMailSentFrom('admin@zuluru.org');
		$this->assertMailSentWith([], 'ReplyTo');
		$this->assertMailSentTo($player->email);
		$this->assertMailSentTo($new->email);
		$this->assertMailSentWith([], 'CC');
		$this->assertMailSentWith('Test Zuluru Affiliate Account Update', 'Subject');
		$this->assertMailContains('You seem to have created a duplicate TZA account. You already have an account.');
		$this->assertMailContains('this old account has been merged with your new information');

		// Make sure that everything was correctly merged
		/** @var PeopleTable $table */
		$table = TableRegistry::getTableLocator()->get('People');
		$authModel = Configure::read('Security.authModel');
		/** @var Person $person */
		$person = $table->get($player->id, [
			'contain' => [$authModel, 'Affiliates', 'Groups', 'Settings', 'Skills', 'Preregistrations', 'Franchises']
		]);
		$this->assertEquals($new->last_name, $person->last_name);
		$this->assertEquals('active', $person->status);
		$this->assertFalse($person->publish_email);
		$this->assertEquals($new->home_phone, $person->home_phone);
		$this->assertEquals($new->mobile_phone, $person->mobile_phone);
		$this->assertEquals($new->work_phone, $person->work_phone);
		$this->assertEquals($new->addr_street, $person->addr_street);
		$this->assertEquals('Womens Large', $person->shirt_size);
		$this->assertEquals($player->user_id, $person->user_id);
		$this->assertNotNull($person->user);
		$this->assertEquals($player->user_id, $person->user->id);
		$this->assertCount(1, $person->affiliates);
		$this->assertCount(1, $person->groups);
		$this->assertCount(2, $person->settings);
		$this->assertCount(2, $person->skills);

		// And all the old stuff is gone
		$this->assertEquals(0, $table->find()->where(['id' => $new->id])->count());
		$this->assertEquals(0, $table->$authModel->find()->where(['id' => $new->id])->count());
		$this->assertEquals(0, $table->AffiliatesPeople->find()->where(['person_id' => $new->id])->count());
		$this->assertEquals(0, $table->GroupsPeople->find()->where(['person_id' => $new->id])->count());
		$this->assertEquals(0, $table->Settings->find()->where(['person_id' => $new->id])->count());
		$this->assertEquals(0, $table->Skills->find()->where(['person_id' => $new->id])->count());
	}

	// TODO: Test some more merging options above: child with adult, adult with child, parent with player, etc.

	/**
	 * Test approve method as a manager
	 */
	public function testApproveAsManager(): void {
		[$admin, $manager, , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Create a duplicate account to delete
		$new = $this->createDuplicate($player, $admin->affiliates[0]);

		// Managers are allowed to approve
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'approve', 'person' => $new->id], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test approve method as others
	 */
	public function testApproveAsOthers(): void {
		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Create a duplicate account to delete
		$new = $this->createDuplicate($player, $admin->affiliates[0]);

		// Others are not allowed to approve
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'approve', 'person' => $new->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'approve', 'person' => $new->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'approve', 'person' => $new->id]);
	}

	/**
	 * Test vcf method
	 */
	public function testVcf(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		$inactive = PersonFactory::make([
			'status' => 'inactive',
			'publish_home_phone' => true,
			'publish_email' => true,
		])->persist();

		// Anyone is allowed to download the VCF. Different people have different info available.
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'vcf', 'person' => $player->id], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'vcf', 'person' => $inactive->id], $admin->id);

		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'vcf', 'person' => $player->id], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'vcf', 'person' => $inactive->id], $manager->id);

		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'vcf', 'person' => $player->id], $volunteer->id);
		$this->assertResponseNotContains('EMAIL');
		$this->assertResponseNotContains('TEL;HOME');
		$this->assertResponseNotContains('TEL;WORK');
		$this->assertResponseNotContains('TEL;CELL');
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'vcf', 'person' => $inactive->id], $volunteer->id);
		$this->assertResponseNotContains('EMAIL');
		$this->assertResponseNotContains('TEL;HOME');
		$this->assertResponseNotContains('TEL;WORK');
		$this->assertResponseNotContains('TEL;CELL');

		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'vcf', 'person' => $player->id], $player->id);
		$this->assertResponseContains('EMAIL;PREF;INTERNET:' . $player->email);
		$this->assertResponseContains('TEL;HOME;VOICE:' . $player->home_phone);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'vcf', 'person' => $inactive->id], $player->id);
		$this->assertResponseNotContains('EMAIL');
		$this->assertResponseNotContains('TEL;HOME');
		$this->assertResponseNotContains('TEL;WORK');
		$this->assertResponseNotContains('TEL;CELL');

		$this->assertGetAnonymousAccessOk(['controller' => 'People', 'action' => 'vcf', 'person' => $player->id]);
		$this->assertResponseNotContains('EMAIL');
		$this->assertResponseNotContains('TEL;HOME');
		$this->assertResponseNotContains('TEL;WORK');
		$this->assertResponseNotContains('TEL;CELL');
		$this->assertGetAnonymousAccessOk(['controller' => 'People', 'action' => 'vcf', 'person' => $inactive->id]);
		$this->assertResponseNotContains('EMAIL');
		$this->assertResponseNotContains('TEL;HOME');
		$this->assertResponseNotContains('TEL;WORK');
		$this->assertResponseNotContains('TEL;CELL');

		$this->markTestIncomplete('Test coordinator / captain and captain / player interaction, managers cross-affiliate, published vs private details.');
	}

	/**
	 * Test ical method
	 */
	public function testIcal(): void {
		[$admin, , , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		SettingFactory::make([
			'person_id' => $player->id,
			'category' => 'personal',
			'name' => 'enable_ical',
			'value' => true,
		])
			->persist();

		// Can get the ical feed for anyone with the option enabled
		$this->get(['controller' => 'People', 'action' => 'ical', $admin->id]);
		$this->assertResponseCode(410);
		$this->assertGetAnonymousAccessOk(['controller' => 'People', 'action' => 'ical', $player->id]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test registrations method
	 */
	public function testRegistrations(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Anyone logged in is allowed to see the list of their personal registrations
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'registrations'], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'registrations'], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'registrations'], $volunteer->id);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'registrations'], $player->id);

		// Others are not allowed to see the list of their personal registrations
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'registrations']);
	}

	/**
	 * Test credits method
	 */
	public function testCredits(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Anyone logged in is allowed to see their list of credits
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'credits'], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'credits'], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'credits'], $volunteer->id);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'credits'], $player->id);

		// Others are not allowed to see their list of credits
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'credits']);
	}

	/**
	 * Test teams method
	 */
	public function testTeams(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Anyone logged in is allowed to see their team history
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'teams'], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'teams'], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'teams'], $volunteer->id);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'teams'], $player->id);

		// Others are not allowed to see team history
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'teams']);
	}

	/**
	 * Test waivers method
	 */
	public function testWaivers(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Anyone logged in is allowed to see their waiver history
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'waivers'], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'waivers'], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'waivers'], $volunteer->id);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'waivers'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'waivers']);
	}

	private function createDuplicate(Person $player, Affiliate $affiliate): Person {
		return PersonFactory::makePlayer([
			'first_name' => $player->first_name,
			'last_name' => $player->last_name,
			'shirt_size' => 'Womens Large', // required field to save
			'status' => 'new',
		])
			->with('Affiliates', $affiliate)
			->with('Skills[2]')
			->with('Settings[2]')
			->persist();
	}

}
