<?php
namespace App\Test\TestCase\Controller;

use App\Shell\Task\InitializeBadgeTask;
use App\Test\Factory\AffiliateFactory;
use App\Test\Factory\AffiliatesPersonFactory;
use App\Test\Factory\BadgeFactory;
use App\Test\Factory\BadgesPersonFactory;
use App\Test\Factory\PersonFactory;
use App\Test\Scenario\DiverseUsersScenario;
use App\Test\Scenario\LeagueWithRostersScenario;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\EmailTrait;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\BadgesController Test Case
 */
class BadgesControllerTest extends ControllerTestCase {

	use EmailTrait;
	use ScenarioAwareTrait;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.UserGroups',
		'app.RosterRoles',
		'app.Settings',
	];

	public function tearDown(): void {
		// Cleanup any emails that were sent
		$this->cleanupEmailTrait();

		parent::tearDown();
	}

	/**
	 * Test index method
	 */
	public function testIndex(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$badges = BadgeFactory::make([
			['visibility' => BADGE_VISIBILITY_HIGH, 'affiliate_id' => $affiliates[0]->id],
			['visibility' => BADGE_VISIBILITY_HIGH, 'affiliate_id' => $affiliates[1]->id],
			['visibility' => BADGE_VISIBILITY_ADMIN, 'affiliate_id' => $affiliates[0]->id],
			['visibility' => BADGE_VISIBILITY_HIGH, 'affiliate_id' => $affiliates[0]->id, 'active' => false],
		])->persist();

		// Admins are allowed to see the index, with full edit controls, but deactivated badges aren't on this list
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'index'], $admin->id);
		$this->assertResponseContains('/badges/view?badge=' . $badges[0]->id);
		$this->assertResponseContains('/badges/edit?badge=' . $badges[0]->id);
		$this->assertResponseContains('/badges/delete?badge=' . $badges[0]->id);
		$this->assertResponseContains('/badges/deactivate?badge=' . $badges[0]->id);
		$this->assertResponseContains('/badges/view?badge=' . $badges[1]->id);
		$this->assertResponseContains('/badges/edit?badge=' . $badges[1]->id);
		$this->assertResponseContains('/badges/delete?badge=' . $badges[1]->id);
		$this->assertResponseContains('/badges/deactivate?badge=' . $badges[1]->id);
		$this->assertResponseContains('/badges/view?badge=' . $badges[2]->id);
		$this->assertResponseContains('/badges/edit?badge=' . $badges[2]->id);
		$this->assertResponseContains('/badges/delete?badge=' . $badges[2]->id);
		$this->assertResponseContains('/badges/deactivate?badge=' . $badges[2]->id);
		$this->assertResponseNotContains('/badges/view?badge=' . $badges[3]->id);

		// Managers are allowed to see the index, including admin-only badges, but not badges in other affiliates or deactivated badges
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'index'], $manager->id);
		$this->assertResponseContains('/badges/view?badge=' . $badges[0]->id);
		$this->assertResponseContains('/badges/edit?badge=' . $badges[0]->id);
		$this->assertResponseContains('/badges/delete?badge=' . $badges[0]->id);
		$this->assertResponseContains('/badges/deactivate?badge=' . $badges[0]->id);
		$this->assertResponseNotContains('/badges/view?badge=' . $badges[1]->id);
		$this->assertResponseContains('/badges/view?badge=' . $badges[2]->id);
		$this->assertResponseContains('/badges/edit?badge=' . $badges[2]->id);
		$this->assertResponseContains('/badges/delete?badge=' . $badges[2]->id);
		$this->assertResponseContains('/badges/deactivate?badge=' . $badges[2]->id);
		$this->assertResponseNotContains('/badges/view?badge=' . $badges[3]->id);

		// Others are allowed to see the index, but only badges in their affiliates, and not admin-only badges, and they don't have edit options
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'index'], $volunteer->id);
		$this->assertResponseContains('/badges/view?badge=' . $badges[0]->id);
		$this->assertResponseNotContains('/badges/edit?badge=' . $badges[0]->id);
		$this->assertResponseNotContains('/badges/delete?badge=' . $badges[0]->id);
		$this->assertResponseNotContains('/badges/deactivate?badge=' . $badges[0]->id);
		$this->assertResponseNotContains('/badges/view?badge=' . $badges[1]->id);
		$this->assertResponseNotContains('/badges/view?badge=' . $badges[2]->id);
		$this->assertResponseNotContains('/badges/view?badge=' . $badges[3]->id);

		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'index'], $player->id);
		$this->assertResponseContains('/badges/view?badge=' . $badges[0]->id);
		$this->assertResponseNotContains('/badges/edit?badge=' . $badges[0]->id);
		$this->assertResponseNotContains('/badges/delete?badge=' . $badges[0]->id);
		$this->assertResponseNotContains('/badges/deactivate?badge=' . $badges[0]->id);
		$this->assertResponseNotContains('/badges/view?badge=' . $badges[1]->id);
		$this->assertResponseNotContains('/badges/view?badge=' . $badges[2]->id);
		$this->assertResponseNotContains('/badges/view?badge=' . $badges[3]->id);

		// Others are not allowed to see the index
		$this->assertGetAnonymousAccessDenied(['controller' => 'Badges', 'action' => 'index']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test deactivated method
	 */
	public function testDeactivated(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliate = $admin->affiliates[0];
		$badges = BadgeFactory::make([
			['visibility' => BADGE_VISIBILITY_HIGH, 'affiliate_id' => $affiliate->id],
			['visibility' => BADGE_VISIBILITY_HIGH, 'affiliate_id' => $affiliate->id, 'active' => false],
		])->persist();

		// Admins are allowed to view the list of deactivated badges
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'deactivated'], $admin->id);
		$this->assertResponseNotContains('/badges/view?badge=' . $badges[0]->id);
		$this->assertResponseContains('/badges/view?badge=' . $badges[1]->id);
		$this->assertResponseContains('/badges/edit?badge=' . $badges[1]->id);
		$this->assertResponseContains('/badges/delete?badge=' . $badges[1]->id);
		$this->assertResponseContains('/badges/activate?badge=' . $badges[1]->id);

		// Managers are allowed to view the list of deactivated badges
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'deactivated'], $manager->id);
		$this->assertResponseNotContains('/badges/view?badge=' . $badges[0]->id);
		$this->assertResponseContains('/badges/view?badge=' . $badges[1]->id);
		$this->assertResponseContains('/badges/edit?badge=' . $badges[1]->id);
		$this->assertResponseContains('/badges/delete?badge=' . $badges[1]->id);
		$this->assertResponseContains('/badges/activate?badge=' . $badges[1]->id);

		// Others are not allowed to view the list of deactivated badges
		$this->assertGetAsAccessDenied(['controller' => 'Badges', 'action' => 'deactivated'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Badges', 'action' => 'deactivated'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Badges', 'action' => 'deactivated']);
	}

	/**
	 * Test view method
	 */
	public function testView(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;
		$badges = BadgeFactory::make([
			['visibility' => BADGE_VISIBILITY_HIGH, 'affiliate_id' => $affiliates[0]->id],
			['visibility' => BADGE_VISIBILITY_HIGH, 'affiliate_id' => $affiliates[1]->id],
			['visibility' => BADGE_VISIBILITY_ADMIN, 'affiliate_id' => $affiliates[0]->id],
			['visibility' => BADGE_VISIBILITY_HIGH, 'affiliate_id' => $affiliates[0]->id, 'active' => false],
		])->persist();

		// Admins are allowed to view and edit badges
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'view', '?' => ['badge' => $badges[0]->id]], $admin->id);
		$this->assertResponseContains('/badges/edit?badge=' . $badges[0]->id);
		$this->assertResponseContains('/badges/delete?badge=' . $badges[0]->id);

		// Admins are also allowed to view deactivated badges
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'view', '?' => ['badge' => $badges[3]->id]], $admin->id);
		$this->assertResponseContains('/badges/edit?badge=' . $badges[3]->id);
		$this->assertResponseContains('/badges/delete?badge=' . $badges[3]->id);

		// Or anything with admin-only access
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'view', '?' => ['badge' => $badges[2]->id]], $admin->id);
		$this->assertResponseContains('/badges/edit?badge=' . $badges[2]->id);
		$this->assertResponseContains('/badges/delete?badge=' . $badges[2]->id);

		// And badges from all affiliates
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'view', '?' => ['badge' => $badges[1]->id]], $admin->id);
		$this->assertResponseContains('/badges/edit?badge=' . $badges[1]->id);
		$this->assertResponseContains('/badges/delete?badge=' . $badges[1]->id);

		// Managers are allowed to view and edit badges
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'view', '?' => ['badge' => $badges[0]->id]], $manager->id);
		$this->assertResponseContains('/badges/edit?badge=' . $badges[0]->id);
		$this->assertResponseContains('/badges/delete?badge=' . $badges[0]->id);

		// Managers are also allowed to view deactivated badges
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'view', '?' => ['badge' => $badges[3]->id]], $manager->id);
		$this->assertResponseContains('/badges/edit?badge=' . $badges[3]->id);
		$this->assertResponseContains('/badges/delete?badge=' . $badges[3]->id);

		// Or anything with admin-only access
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'view', '?' => ['badge' => $badges[2]->id]], $manager->id);
		$this->assertResponseContains('/badges/edit?badge=' . $badges[2]->id);
		$this->assertResponseContains('/badges/delete?badge=' . $badges[2]->id);

		// Managers have no edit options on ones in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'view', '?' => ['badge' => $badges[1]->id]], $manager->id);
		$this->assertResponseNotContains('/badges/edit?badge=' . $badges[1]->id);
		$this->assertResponseNotContains('/badges/delete?badge=' . $badges[1]->id);

		// Others are allowed to view badges
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'view', '?' => ['badge' => $badges[0]->id]], $volunteer->id);
		$this->assertResponseNotContains('/badges/edit?badge=' . $badges[0]->id);
		$this->assertResponseNotContains('/badges/delete?badge=' . $badges[0]->id);
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'view', '?' => ['badge' => $badges[0]->id]], $player->id);
		$this->assertResponseNotContains('/badges/edit?badge=' . $badges[0]->id);
		$this->assertResponseNotContains('/badges/delete?badge=' . $badges[0]->id);

		// But they are not allowed to view deactivated badges
		$this->assertGetAsAccessRedirect(['controller' => 'Badges', 'action' => 'view', '?' => ['badge' => $badges[3]->id]],
			$volunteer->id, ['controller' => 'Badges', 'action' => 'index'],
			'Invalid badge.');
		$this->assertGetAsAccessRedirect(['controller' => 'Badges', 'action' => 'view', '?' => ['badge' => $badges[3]->id]],
			$player->id, ['controller' => 'Badges', 'action' => 'index'],
			'Invalid badge.');

		// Or anything with admin-only access
		$this->assertGetAsAccessRedirect(['controller' => 'Badges', 'action' => 'view', '?' => ['badge' => $badges[2]->id]],
			$volunteer->id, ['controller' => 'Badges', 'action' => 'index'],
			'Invalid badge.');
		$this->assertGetAsAccessRedirect(['controller' => 'Badges', 'action' => 'view', '?' => ['badge' => $badges[2]->id]],
			$player->id, ['controller' => 'Badges', 'action' => 'index'],
			'Invalid badge.');

		// Others are not allowed to view badges
		$this->assertGetAnonymousAccessDenied(['controller' => 'Badges', 'action' => 'view', '?' => ['badge' => $badges[0]->id]]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test initialize_awards method as an admin
	 */
	public function testInitializeAwardsAsAdmin(): void {
		$league = $this->loadFixtureScenario(LeagueWithRostersScenario::class);
		$affiliate = $league->affiliate;
		$admin = PersonFactory::make()->admin()->with('Affiliates', $affiliate)->persist();
		$badge = BadgeFactory::make(['category' => 'team', 'handler' => 'player_active', 'affiliate_id' => $affiliate->id])->persist();
		$this->assertEquals(0, $badge->refresh_from);

		// Admins are allowed to initialize the awarding of badges
		$this->assertGetAsAccessRedirect(['controller' => 'Badges', 'action' => 'initialize_awards', '?' => ['badge' => $badge->id]],
			$admin->id, ['controller' => 'Badges', 'action' => 'view', '?' => ['badge' => $badge->id]],
			'This badge has been scheduled for re-initialization.');

		$badge = BadgeFactory::get($badge->id);
		$this->assertEquals(1, $badge->refresh_from);

		// Run the badge initialization task
		$task = new InitializeBadgeTask();
		$task->main();
		$this->assertNoMailSent();

		// At this point, the refresh_from will be set to one past the last team in the database
		$badge = BadgeFactory::get($badge->id);
		$this->assertEquals($league->divisions[0]->teams[3]->id + 1, $badge->refresh_from);

		// Run the task again
		$task->main();
		$this->assertNoMailSent();

		// Now, the refresh_from will be back to 0
		$badge = BadgeFactory::get($badge->id);
		$this->assertEquals(0, $badge->refresh_from);

		// Four teams. Each have a captain and two approved full-time players.
		$this->assertEquals(12, TableRegistry::getTableLocator()->get('BadgesPeople')->find()
			->where(['badge_id' => $badge->id])
			->count()
		);
	}

	/**
	 * Test initialize_awards method as a manager
	 */
	public function testInitializeAwardsAsManager(): void {
		$affiliate = AffiliateFactory::make()->persist();
		$manager = PersonFactory::make()->manager()
			->with('AffiliatesPeople', AffiliatesPersonFactory::make(['position' => 'manager', 'affiliate_id' => $affiliate->id]))
			->persist();
		$badge = BadgeFactory::make(['category' => 'team', 'handler' => 'player_active', 'affiliate_id' => $affiliate->id])->persist();

		// Managers are allowed to initialize awards
		$this->assertGetAsAccessRedirect(['controller' => 'Badges', 'action' => 'initialize_awards', '?' => ['badge' => $badge->id]],
			$manager->id, ['controller' => 'Badges', 'action' => 'view', '?' => ['badge' => $badge->id]],
			'This badge has been scheduled for re-initialization.');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test initialize_awards method as others
	 */
	public function testInitializeAwardsAsOthers(): void {
		$affiliate = AffiliateFactory::make()->persist();
		$player = PersonFactory::make()->player()->with('Affiliates', $affiliate)->persist();
		$badge = BadgeFactory::make(['category' => 'team', 'handler' => 'player_active', 'affiliate_id' => $affiliate->id])->persist();

		// Others are not allowed to initialize awards
		$this->assertGetAsAccessDenied(['controller' => 'Badges', 'action' => 'initialize_awards', '?' => ['badge' => $badge->id]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Badges', 'action' => 'initialize_awards', '?' => ['badge' => $badge->id]]);
	}

	/**
	 * Test tooltip method
	 */
	public function testTooltip(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;
		$badges = BadgeFactory::make([
			['visibility' => BADGE_VISIBILITY_HIGH, 'affiliate_id' => $affiliates[0]->id],
			['visibility' => BADGE_VISIBILITY_HIGH, 'affiliate_id' => $affiliates[1]->id],
			['visibility' => BADGE_VISIBILITY_ADMIN, 'affiliate_id' => $affiliates[0]->id],
			['visibility' => BADGE_VISIBILITY_HIGH, 'affiliate_id' => $affiliates[0]->id, 'active' => false],
		])->persist();

		$this->assertGetAjaxAsAccessOk(['controller' => 'Badges', 'action' => 'tooltip', '?' => ['badge' => $badges[0]->id]],
			$admin->id);
		$this->assertGetAjaxAsAccessOk(['controller' => 'Badges', 'action' => 'tooltip', '?' => ['badge' => $badges[1]->id]],
			$admin->id);
		$this->assertGetAjaxAsAccessOk(['controller' => 'Badges', 'action' => 'tooltip', '?' => ['badge' => $badges[0]->id]],
			$admin->id);
		$this->assertGetAjaxAsAccessOk(['controller' => 'Badges', 'action' => 'tooltip', '?' => ['badge' => $badges[3]->id]],
			$admin->id);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Badges', 'action' => 'tooltip', '?' => ['badge' => $badges[0]->id]],
			$manager->id);
		$this->assertGetAjaxAsAccessOk(['controller' => 'Badges', 'action' => 'tooltip', '?' => ['badge' => $badges[1]->id]],
			$manager->id);
		$this->assertGetAjaxAsAccessOk(['controller' => 'Badges', 'action' => 'tooltip', '?' => ['badge' => $badges[2]->id]],
			$manager->id);
		$this->assertGetAjaxAsAccessOk(['controller' => 'Badges', 'action' => 'tooltip', '?' => ['badge' => $badges[3]->id]],
			$manager->id);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Badges', 'action' => 'tooltip', '?' => ['badge' => $badges[0]->id]],
			$volunteer->id);
		$this->assertGetAjaxAsAccessOk(['controller' => 'Badges', 'action' => 'tooltip', '?' => ['badge' => $badges[1]->id]],
			$volunteer->id);
		$this->assertGetAjaxAsAccessRedirect(['controller' => 'Badges', 'action' => 'tooltip', '?' => ['badge' => $badges[2]->id]],
			$volunteer->id, ['controller' => 'Badges', 'action' => 'index'],
			'Invalid badge.');
		$this->assertGetAjaxAsAccessRedirect(['controller' => 'Badges', 'action' => 'tooltip', '?' => ['badge' => $badges[3]->id]],
			$volunteer->id, ['controller' => 'Badges', 'action' => 'index'],
			'Invalid badge.');

		$this->assertGetAjaxAsAccessOk(['controller' => 'Badges', 'action' => 'tooltip', '?' => ['badge' => $badges[0]->id]],
			$player->id);
		$this->assertGetAjaxAsAccessOk(['controller' => 'Badges', 'action' => 'tooltip', '?' => ['badge' => $badges[1]->id]],
			$player->id);
		$this->assertGetAjaxAsAccessRedirect(['controller' => 'Badges', 'action' => 'tooltip', '?' => ['badge' => $badges[2]->id]],
			$player->id, ['controller' => 'Badges', 'action' => 'index'],
			'Invalid badge.');
		$this->assertGetAjaxAsAccessRedirect(['controller' => 'Badges', 'action' => 'tooltip', '?' => ['badge' => $badges[3]->id]],
			$player->id, ['controller' => 'Badges', 'action' => 'index'],
			'Invalid badge.');

		$this->assertGetAnonymousAccessDenied(['controller' => 'Badges', 'action' => 'tooltip', '?' => ['badge' => $badges[0]->id]]);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Badges', 'action' => 'tooltip', '?' => ['badge' => $badges[1]->id]]);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Badges', 'action' => 'tooltip', '?' => ['badge' => $badges[2]->id]]);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Badges', 'action' => 'tooltip', '?' => ['badge' => $badges[3]->id]]);
	}

	/**
	 * Test add method
	 */
	public function testAdd(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Admins are allowed to add badges
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'add'], $admin->id);

		// Managers are allowed to add badges
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'add'], $manager->id);

		// Others are not allowed to add badges
		$this->assertGetAsAccessDenied(['controller' => 'Badges', 'action' => 'add'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Badges', 'action' => 'add'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Badges', 'action' => 'add']);
	}

	/**
	 * Test edit method
	 */
	public function testEdit(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;
		$badges = BadgeFactory::make([
			['visibility' => BADGE_VISIBILITY_HIGH, 'affiliate_id' => $affiliates[0]->id],
			['visibility' => BADGE_VISIBILITY_HIGH, 'affiliate_id' => $affiliates[1]->id],
		])->persist();

		// Admins are allowed to edit badges
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'edit', '?' => ['badge' => $badges[0]->id]], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'edit', '?' => ['badge' => $badges[1]->id]], $admin->id);

		// Managers are allowed to edit badges
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'edit', '?' => ['badge' => $badges[0]->id]], $manager->id);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Badges', 'action' => 'edit', '?' => ['badge' => $badges[1]->id]], $manager->id);

		// Others are not allowed to edit badges
		$this->assertGetAsAccessDenied(['controller' => 'Badges', 'action' => 'edit', '?' => ['badge' => $badges[0]->id]], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Badges', 'action' => 'edit', '?' => ['badge' => $badges[1]->id]], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Badges', 'action' => 'edit', '?' => ['badge' => $badges[0]->id]], $player->id);
		$this->assertGetAsAccessDenied(['controller' => 'Badges', 'action' => 'edit', '?' => ['badge' => $badges[1]->id]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Badges', 'action' => 'edit', '?' => ['badge' => $badges[0]->id]]);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Badges', 'action' => 'edit', '?' => ['badge' => $badges[1]->id]]);
	}

	/**
	 * Test deactivate method as an admin
	 */
	public function testDeactivateAsAdmin(): void {
		$affiliates = AffiliateFactory::make(2)->persist();
		$admin = PersonFactory::make()->admin()->with('Affiliates', $affiliates)->persist();
		$badges = BadgeFactory::make([
			['affiliate_id' => $affiliates[0]->id],
			['affiliate_id' => $affiliates[1]->id],
		])->persist();

		// Admins are allowed to deactivate badges
		$this->assertGetAjaxAsAccessOk(['controller' => 'Badges', 'action' => 'deactivate', '?' => ['badge' => $badges[0]->id]],
			$admin->id);
		$this->assertResponseContains('/badges\\/activate?badge=' . $badges[0]->id);
		$this->assertGetAjaxAsAccessOk(['controller' => 'Badges', 'action' => 'deactivate', '?' => ['badge' => $badges[1]->id]],
			$admin->id);
		$this->assertResponseContains('/badges\\/activate?badge=' . $badges[1]->id);
	}

	/**
	 * Test deactivate method as a manager
	 */
	public function testDeactivateAsManager(): void {
		$affiliates = AffiliateFactory::make(2)->persist();
		$manager = PersonFactory::make()->manager()
			->with('AffiliatesPeople', AffiliatesPersonFactory::make(['position' => 'manager', 'affiliate_id' => $affiliates[0]->id]))
			->persist();
		$badges = BadgeFactory::make([
			['affiliate_id' => $affiliates[0]->id],
			['affiliate_id' => $affiliates[1]->id],
		])->persist();

		// Managers are allowed to deactivate badges
		$this->assertGetAjaxAsAccessOk(['controller' => 'Badges', 'action' => 'deactivate', '?' => ['badge' => $badges[0]->id]],
			$manager->id);
		$this->assertResponseContains('/badges\\/activate?badge=' . $badges[0]->id);

		// But not ones in other affiliates
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Badges', 'action' => 'deactivate', '?' => ['badge' => $badges[1]->id]],
			$manager->id);
	}

	/**
	 * Test deactivate method as others
	 */
	public function testDeactivateAsOthers(): void {
		$affiliates = AffiliateFactory::make(2)->persist();
		$player = PersonFactory::make()->player()->with('Affiliates', $affiliates[0])->persist();
		$badges = BadgeFactory::make([
			['affiliate_id' => $affiliates[0]->id],
			['affiliate_id' => $affiliates[1]->id],
		])->persist();

		// Others are not allowed to deactivate badges
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Badges', 'action' => 'deactivate', '?' => ['badge' => $badges[0]->id]],
			$player->id);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Badges', 'action' => 'deactivate', '?' => ['badge' => $badges[1]->id]],
			$player->id);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Badges', 'action' => 'deactivate', '?' => ['badge' => $badges[0]->id]]);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Badges', 'action' => 'deactivate', '?' => ['badge' => $badges[0]->id]]);
	}

	/**
	 * Test activate method as an admin
	 */
	public function testActivateAsAdmin(): void {
		$affiliates = AffiliateFactory::make(2)->persist();
		$admin = PersonFactory::make()->admin()->with('Affiliates', $affiliates)->persist();
		$badges = BadgeFactory::make([
			['affiliate_id' => $affiliates[0]->id, 'active' => false],
			['affiliate_id' => $affiliates[1]->id, 'active' => false],
		])->persist();

		// Admins are allowed to activate badges
		$this->assertGetAjaxAsAccessOk(['controller' => 'Badges', 'action' => 'activate', '?' => ['badge' => $badges[0]->id]],
			$admin->id);
		$this->assertResponseContains('/badges\\/deactivate?badge=' . $badges[0]->id);
		$this->assertGetAjaxAsAccessOk(['controller' => 'Badges', 'action' => 'activate', '?' => ['badge' => $badges[1]->id]],
			$admin->id);
		$this->assertResponseContains('/badges\\/deactivate?badge=' . $badges[1]->id);
	}

	/**
	 * Test activate method as a manager
	 */
	public function testActivateAsManager(): void {
		$affiliates = AffiliateFactory::make(2)->persist();
		$manager = PersonFactory::make()->manager()
			->with('AffiliatesPeople', AffiliatesPersonFactory::make(['position' => 'manager', 'affiliate_id' => $affiliates[0]->id]))
			->persist();
		$badges = BadgeFactory::make([
			['affiliate_id' => $affiliates[0]->id, 'active' => false],
			['affiliate_id' => $affiliates[1]->id, 'active' => false],
		])->persist();

		// Managers are allowed to activate badges
		$this->assertGetAjaxAsAccessOk(['controller' => 'Badges', 'action' => 'activate', '?' => ['badge' => $badges[0]->id]],
			$manager->id);
		$this->assertResponseContains('/badges\\/deactivate?badge=' . $badges[0]->id);

		// But not ones in other affiliates
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Badges', 'action' => 'activate', '?' => ['badge' => $badges[1]->id]],
			$manager->id);
	}

	/**
	 * Test activate method as others
	 */
	public function testActivateAsOthers(): void {
		$affiliates = AffiliateFactory::make(2)->persist();
		$player = PersonFactory::make()->player()->with('Affiliates', $affiliates[0])->persist();
		$badges = BadgeFactory::make([
			['affiliate_id' => $affiliates[0]->id, 'active' => false],
			['affiliate_id' => $affiliates[1]->id, 'active' => false],
		])->persist();

		// Others are not allowed to activate badges
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Badges', 'action' => 'activate', '?' => ['badge' => $badges[0]->id]],
			$player->id);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Badges', 'action' => 'activate', '?' => ['badge' => $badges[0]->id]],
			$player->id);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Badges', 'action' => 'activate', '?' => ['badge' => $badges[1]->id]]);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Badges', 'action' => 'activate', '?' => ['badge' => $badges[1]->id]]);
	}

	/**
	 * Test delete method as an admin
	 */
	public function testDeleteAsAdmin(): void {
		$this->enableSecurityToken();

		$affiliates = AffiliateFactory::make(2)->persist();
		$admin = PersonFactory::make()->admin()->with('Affiliates', $affiliates)->persist();
		$badges = BadgeFactory::make([
			['visibility' => BADGE_VISIBILITY_HIGH, 'affiliate_id' => $affiliates[0]->id],
			['visibility' => BADGE_VISIBILITY_HIGH, 'affiliate_id' => $affiliates[1]->id],
		])->persist();
		BadgesPersonFactory::make()->with('Badges', $badges[1])->persist();

		// Admins are allowed to delete badges
		$this->assertPostAsAccessRedirect(['controller' => 'Badges', 'action' => 'delete', '?' => ['badge' => $badges[0]->id]],
			$admin->id, [], ['controller' => 'Badges', 'action' => 'index'],
			'The badge has been deleted.');

		// But not ones with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'Badges', 'action' => 'delete', '?' => ['badge' => $badges[1]->id]],
			$admin->id, [], ['controller' => 'Badges', 'action' => 'index'],
			'#The following records reference this badge, so it cannot be deleted#');
	}

	/**
	 * Test delete method as a manager
	 */
	public function testDeleteAsManager(): void {
		$this->enableSecurityToken();

		$affiliates = AffiliateFactory::make(2)->persist();
		$manager = PersonFactory::make()->manager()
			->with('AffiliatesPeople', AffiliatesPersonFactory::make(['position' => 'manager', 'affiliate_id' => $affiliates[0]->id]))
			->persist();
		$badges = BadgeFactory::make([
			['visibility' => BADGE_VISIBILITY_HIGH, 'affiliate_id' => $affiliates[0]->id],
			['visibility' => BADGE_VISIBILITY_HIGH, 'affiliate_id' => $affiliates[1]->id],
		])->persist();

		// Managers are allowed to delete badges
		$this->assertPostAsAccessRedirect(['controller' => 'Badges', 'action' => 'delete', '?' => ['badge' => $badges[0]->id]],
			$manager->id, [], ['controller' => 'Badges', 'action' => 'index'],
			'The badge has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Badges', 'action' => 'delete', '?' => ['badge' => $badges[1]->id]],
			$manager->id);
	}

	/**
	 * Test delete method as others
	 */
	public function testDeleteAsOthers(): void {
		$this->enableSecurityToken();

		$affiliates = AffiliateFactory::make(2)->persist();
		$player = PersonFactory::make()->player()->with('Affiliates', $affiliates[0])->persist();
		$badges = BadgeFactory::make([
			['visibility' => BADGE_VISIBILITY_HIGH, 'affiliate_id' => $affiliates[0]->id],
			['visibility' => BADGE_VISIBILITY_HIGH, 'affiliate_id' => $affiliates[1]->id],
		])->persist();

		// Others are not allowed to delete badges
		$this->assertPostAsAccessDenied(['controller' => 'Badges', 'action' => 'delete', '?' => ['badge' => $badges[0]->id]],
			$player->id);
		$this->assertPostAsAccessDenied(['controller' => 'Badges', 'action' => 'delete', '?' => ['badge' => $badges[1]->id]],
			$player->id);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Badges', 'action' => 'delete', '?' => ['badge' => $badges[0]->id]]);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Badges', 'action' => 'delete', '?' => ['badge' => $badges[1]->id]]);
	}

}
