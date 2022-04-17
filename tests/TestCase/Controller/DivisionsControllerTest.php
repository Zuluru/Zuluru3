<?php
namespace App\Test\TestCase\Controller;

use App\Model\Entity\Facility;
use App\Model\Entity\Team;
use App\Test\Factory\AffiliateFactory;
use App\Test\Factory\DivisionsDayFactory;
use App\Test\Factory\DivisionsPersonFactory;
use App\Test\Factory\GameFactory;
use App\Test\Factory\LeagueFactory;
use App\Test\Factory\PersonFactory;
use App\Test\Factory\TeamFactory;
use App\Test\Factory\TeamsPersonFactory;
use App\Test\Scenario\DiverseUsersScenario;
use App\Test\Scenario\LeagueWithFullScheduleScenario;
use App\Test\Scenario\LeagueWithMinimalScheduleScenario;
use Cake\Cache\Cache;
use Cake\Chronos\ChronosInterface;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\ORM\TableRegistry;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\DivisionsController Test Case
 */
class DivisionsControllerTest extends ControllerTestCase {

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
	 * Test view method
	 */
	public function testView(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliate = $admin->affiliates[0];
		/** @var \App\Model\Entity\League $league */
		$league = LeagueFactory::make(['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Divisions', ['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Affiliates', $affiliate)
			->persist();
		$division = $league->divisions[0];
		DivisionsPersonFactory::make(['person_id' => $volunteer->id, 'division_id' => $division->id])->persist();

		// Anyone is allowed to view the index; admins, managers and coordinators have extra options
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'view', 'division' => $division->id], $admin->id);
		$this->assertResponseContains('/divisions/edit?division=' . $division->id);
		$this->assertResponseContains('/divisions/delete?division=' . $division->id);

		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'view', 'division' => $division->id], $manager->id);
		$this->assertResponseContains('/divisions/edit?division=' . $division->id);
		$this->assertResponseContains('/divisions/delete?division=' . $division->id);

		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'view', 'division' => $division->id], $volunteer->id);
		$this->assertResponseContains('/divisions/edit?division=' . $division->id);
		$this->assertResponseNotContains('/divisions/delete?division=' . $division->id);

		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'view', 'division' => $division->id], $player->id);
		$this->assertResponseNotContains('/divisions/edit?division=' . $division->id);
		$this->assertResponseNotContains('/divisions/delete?division=' . $division->id);

		$this->assertGetAnonymousAccessOk(['controller' => 'Divisions', 'action' => 'view', 'division' => $division->id]);
		$this->assertResponseNotContains('/divisions/edit?division=' . $division->id);
		$this->assertResponseNotContains('/divisions/delete?division=' . $division->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test tooltip method
	 */
	public function testTooltip(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliate = $admin->affiliates[0];
		/** @var \App\Model\Entity\League $league */
		$league = LeagueFactory::make(['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Divisions', ['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Affiliates', $affiliate)
			->persist();
		$division = $league->divisions[0];
		DivisionsPersonFactory::make(['person_id' => $volunteer->id, 'division_id' => $division->id])->persist();

		// Anyone is allowed to view division tooltips
		$this->assertGetAjaxAsAccessOk(['controller' => 'Divisions', 'action' => 'tooltip', 'division' => $division->id], $admin->id);
		$this->assertResponseContains('/divisions\\/standings?division=' . $division->id);

		$this->assertGetAjaxAsAccessRedirect(['controller' => 'Divisions', 'action' => 'tooltip', 'division' => 0],
			$admin->id, ['controller' => 'Leagues', 'action' => 'index'],
			'Invalid division.');

		$this->assertGetAjaxAsAccessOk(['controller' => 'Divisions', 'action' => 'tooltip', 'division' => $division->id], $manager->id);
		$this->assertResponseContains('/divisions\\/standings?division=' . $division->id);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Divisions', 'action' => 'tooltip', 'division' => $division->id], $volunteer->id);
		$this->assertResponseContains('/divisions\\/standings?division=' . $division->id);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Divisions', 'action' => 'tooltip', 'division' => $division->id], $player->id);
		$this->assertResponseContains('/divisions\\/standings?division=' . $division->id);

		$this->assertGetAjaxAnonymousAccessOk(['controller' => 'Divisions', 'action' => 'tooltip', 'division' => $division->id]);
		$this->assertResponseContains('/divisions\\/standings?division=' . $division->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test stats method
	 */
	public function testStats(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliate = $admin->affiliates[0];
		/** @var \App\Model\Entity\League $league */
		$league = LeagueFactory::make(['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true, 'stat_tracking' => 'always'])
			->with('Divisions', ['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Affiliates', $affiliate)
			->persist();
		$division = $league->divisions[0];
		DivisionsPersonFactory::make(['person_id' => $volunteer->id, 'division_id' => $division->id])->persist();

		// Anyone is allowed to view the stats; admins, managers and coordinators have extra options
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'stats', 'division' => $division->id], $admin->id);
		$this->assertResponseContains('/divisions/edit?division=' . $division->id);
		$this->assertResponseContains('/divisions/delete?division=' . $division->id);

		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'stats', 'division' => $division->id], $manager->id);
		$this->assertResponseContains('/divisions/edit?division=' . $division->id);
		$this->assertResponseContains('/divisions/delete?division=' . $division->id);

		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'stats', 'division' => $division->id], $volunteer->id);
		$this->assertResponseContains('/divisions/edit?division=' . $division->id);
		$this->assertResponseNotContains('/divisions/delete?division=' . $division->id);

		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'stats', 'division' => $division->id], $player->id);
		$this->assertResponseNotContains('/divisions/edit?division=' . $division->id);
		$this->assertResponseNotContains('/divisions/delete?division=' . $division->id);

		// Non-public sites, stats are not available unless logged in
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'stats', 'division' => $division->id]);

		// With public sites, anyone is allowed to view the stats
		Cache::clear(false, 'long_term');
		TableRegistry::getTableLocator()->get('Settings')->updateAll(['value' => true], ['category' => 'feature', 'name' => 'public']);
		$this->assertGetAnonymousAccessOk(['controller' => 'Divisions', 'action' => 'stats', 'division' => $division->id]);
		$this->assertResponseNotContains('/divisions/edit?division=' . $division->id);
		$this->assertResponseNotContains('/divisions/delete?division=' . $division->id);
	}

	/**
	 * Test add method
	 */
	public function testAdd(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;
		/** @var \App\Model\Entity\League $league1 */
		$league1 = LeagueFactory::make(['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Divisions', ['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Affiliates', $affiliates[0])
			->persist();
		$division1 = $league1->divisions[0];
		DivisionsPersonFactory::make(['person_id' => $volunteer->id, 'division_id' => $division1->id])->persist();
		DivisionsDayFactory::make(['day_id' => ChronosInterface::MONDAY, 'division_id' => $division1->id])->persist();

		$league2 = LeagueFactory::make(['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Divisions', ['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Affiliates', $affiliates[1])
			->persist();

		// Admins are allowed to add new divisions anywhere
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'add', 'league' => $league1->id], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'add', 'league' => $league2->id], $admin->id);

		// If a division ID is given, we will clone that division
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'add', 'league' => $league1->id, 'division' => $division1->id], $admin->id);
		$this->assertResponseRegExp('#<input type="text" name="name"[^>]*value="' . $division1->name . '"#ms');
		$this->assertResponseContains('<input type="checkbox" name="days[_ids][]" value="1" checked="checked" id="days-ids-1">Monday');
		$this->assertResponseNotContains('<input type="checkbox" name="days[_ids][]" value="2" checked="checked" id="days-ids-2">Tuesday');
		$this->assertResponseNotContains('<input type="checkbox" name="days[_ids][]" value="3" checked="checked" id="days-ids-3">Wednesday');
		$this->assertResponseNotContains('<input type="checkbox" name="days[_ids][]" value="4" checked="checked" id="days-ids-4">Thursday');

		// Managers are allowed to add new divisions in their own affiliate, but not others
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'add', 'league' => $league1->id], $manager->id);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'add', 'league' => $league2->id], $manager->id);

		// Others are not allowed to add new divisions
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'add', 'league' => $league1->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'add', 'league' => $league1->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'add', 'league' => $league1->id]);
	}

	/**
	 * Test edit method
	 */
	public function testEdit(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;
		/** @var \App\Model\Entity\League $league1 */
		$league1 = LeagueFactory::make(['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Divisions[2]', ['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Affiliates', $affiliates[0])
			->persist();
		$division1a = $league1->divisions[0];
		$division1b = $league1->divisions[1];
		DivisionsPersonFactory::make(['person_id' => $volunteer->id, 'division_id' => $division1a->id])->persist();
		DivisionsDayFactory::make(['day_id' => ChronosInterface::MONDAY, 'division_id' => $division1a->id])->persist();

		/** @var \App\Model\Entity\League $league2 */
		$league2 = LeagueFactory::make(['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Divisions', ['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Affiliates', $affiliates[1])
			->persist();
		$division2 = $league2->divisions[0];

		// Admins are allowed to edit divisions anywhere
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'edit', 'division' => $division1a->id], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'edit', 'division' => $division1b->id], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'edit', 'division' => $division2->id], $admin->id);

		// Managers are allowed to edit divisions in their own affiliate, but not others
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'edit', 'division' => $division1a->id], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'edit', 'division' => $division1b->id], $manager->id);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'edit', 'division' => $division2->id], $manager->id);

		// Coordinators are allowed to edit their own divisions, but not others
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'edit', 'division' => $division1a->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'edit', 'division' => $division1b->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'edit', 'division' => $division2->id], $volunteer->id);

		// Others are not allowed to edit divisions
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'edit', 'division' => $division1a->id], $player->id);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'edit', 'division' => $division1b->id], $player->id);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'edit', 'division' => $division2->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'edit', 'division' => $division1a->id]);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'edit', 'division' => $division1b->id]);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'edit', 'division' => $division2->id]);
	}

	/**
	 * Test scheduling_fields method
	 */
	public function testSchedulingFields(): void {
		$this->enableCsrfToken();

		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliate = $admin->affiliates[0];
		/** @var \App\Model\Entity\League $league */
		$league = LeagueFactory::make(['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Divisions', ['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Affiliates', $affiliate)
			->persist();
		$division = $league->divisions[0];
		DivisionsPersonFactory::make(['person_id' => $volunteer->id, 'division_id' => $division->id])->persist();

		// Admins are allowed to get the scheduling fields
		$this->assertPostAjaxAsAccessOk(['controller' => 'Divisions', 'action' => 'scheduling_fields'],
			$admin->id, ['schedule_type' => 'ratings_ladder']);

		// Managers are allowed to get the scheduling fields
		$this->assertPostAjaxAsAccessOk(['controller' => 'Divisions', 'action' => 'scheduling_fields'],
			$manager->id, ['schedule_type' => 'ratings_ladder']);

		// Coordinators are allowed to get the scheduling fields
		$this->assertPostAjaxAsAccessOk(['controller' => 'Divisions', 'action' => 'scheduling_fields'],
			$volunteer->id, ['schedule_type' => 'ratings_ladder']);

		// Others are not allowed to get the scheduling fields
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Divisions', 'action' => 'scheduling_fields'],
			$player->id, ['schedule_type' => 'ratings_ladder']);
		$this->assertPostAjaxAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'scheduling_fields'],
			['schedule_type' => 'ratings_ladder']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add_coordinator method as an admin
	 */
	public function testAddCoordinatorAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, , $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliate = $admin->affiliates[0];
		/** @var \App\Model\Entity\League $league */
		$league = LeagueFactory::make(['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Divisions', ['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Affiliates', $affiliate)
			->persist();
		$division = $league->divisions[0];

		// Admins are allowed to add coordinators
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'add_coordinator', 'division' => $division->id], $admin->id);

		// Try the search page
		$this->assertPostAsAccessOk(['controller' => 'Divisions', 'action' => 'add_coordinator', 'division' => $division->id],
			$admin->id, [
				'affiliate_id' => $affiliate->id,
				'first_name' => '',
				'last_name' => $volunteer->last_name,
				'sort' => 'last_name',
				'direction' => 'asc',
			]
		);
		$this->assertResponseContains('/divisions/add_coordinator?person=' . $volunteer->id . '&amp;division=' . $division->id);

		// Try to add the coordinator
		$this->assertPostAsAccessRedirect(['controller' => 'Divisions', 'action' => 'add_coordinator', 'person' => $volunteer->id, 'division' => $division->id],
			$admin->id, [], ['controller' => 'Leagues', 'action' => 'view', 'league' => $league->id],
			"Added {$volunteer->full_name} as coordinator.");

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'view', 'division' => $division->id], $admin->id);
		$this->assertResponseContains('/divisions/remove_coordinator?division=' . $division->id . '&amp;person=' . $volunteer->id);
	}

	/**
	 * Test add_coordinator method as others
	 */
	public function testAddCoordinatorAsOthers(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliate = $admin->affiliates[0];
		/** @var \App\Model\Entity\League $league */
		$league = LeagueFactory::make(['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Divisions', ['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Affiliates', $affiliate)
			->persist();
		$division = $league->divisions[0];

		// Managers are allowed to add coordinators
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'add_coordinator', 'division' => $division->id], $manager->id);

		// Others are not allowed to add coordinators
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'add_coordinator', 'division' => $division->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'add_coordinator', 'division' => $division->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'add_coordinator', 'division' => $division->id]);
	}

	/**
	 * Test remove_coordinator method as an admin
	 */
	public function testRemoveCoordinatorAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, , $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliate = $admin->affiliates[0];
		/** @var \App\Model\Entity\League $league */
		$league = LeagueFactory::make(['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Divisions', ['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Affiliates', $affiliate)
			->persist();
		$division = $league->divisions[0];
		DivisionsPersonFactory::make(['person_id' => $volunteer->id, 'division_id' => $division->id])->persist();

		// Admins are allowed to remove coordinators
		$this->assertPostAsAccessRedirect(['controller' => 'Divisions', 'action' => 'remove_coordinator', 'division' => $division->id, 'person' => $volunteer->id],
			$admin->id, [], ['controller' => 'Leagues', 'action' => 'view', 'league' => $league->id],
			'Successfully removed coordinator.');
	}

	/**
	 * Test remove_coordinator method as a manager
	 */
	public function testRemoveCoordinatorAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliate = $admin->affiliates[0];
		/** @var \App\Model\Entity\League $league */
		$league = LeagueFactory::make(['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Divisions', ['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Affiliates', $affiliate)
			->persist();
		$division = $league->divisions[0];
		DivisionsPersonFactory::make(['person_id' => $volunteer->id, 'division_id' => $division->id])->persist();

		// Managers are allowed to remove coordinators
		$this->assertPostAsAccessRedirect(['controller' => 'Divisions', 'action' => 'remove_coordinator', 'division' => $division->id, 'person' => $volunteer->id],
			$manager->id, [], ['controller' => 'Leagues', 'action' => 'view', 'league' => $league->id],
			'Successfully removed coordinator.');
	}

	/**
	 * Test remove_coordinator method as others
	 */
	public function testRemoveCoordinatorAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliate = $admin->affiliates[0];
		/** @var \App\Model\Entity\League $league */
		$league = LeagueFactory::make(['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Divisions', ['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Affiliates', $affiliate)
			->persist();
		$division = $league->divisions[0];
		DivisionsPersonFactory::make(['person_id' => $volunteer->id, 'division_id' => $division->id])->persist();

		// Others are not allowed to remove coordinators
		$this->assertPostAsAccessDenied(['controller' => 'Divisions', 'action' => 'remove_coordinator', 'division' => $division->id, 'person' => $volunteer->id],
			$volunteer->id);
		$this->assertPostAsAccessDenied(['controller' => 'Divisions', 'action' => 'remove_coordinator', 'division' => $division->id, 'person' => $volunteer->id],
			$player->id);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'remove_coordinator', 'division' => $division->id, 'person' => $volunteer->id]);
	}

	/**
	 * Test add_teams method
	 */
	public function testAddTeams(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliate = $admin->affiliates[0];
		/** @var \App\Model\Entity\League $league */
		$league = LeagueFactory::make(['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Divisions', ['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Affiliates', $affiliate)
			->persist();
		$division = $league->divisions[0];
		DivisionsPersonFactory::make(['person_id' => $volunteer->id, 'division_id' => $division->id])->persist();

		// Admins are allowed to add teams
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'add_teams', 'division' => $division->id], $admin->id);

		// Managers are allowed to add teams
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'add_teams', 'division' => $division->id], $manager->id);

		// Coordinators are allowed to add teams
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'add_teams', 'division' => $division->id], $volunteer->id);

		// Captains are not allowed to add teams
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'add_teams', 'division' => $division->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'add_teams', 'division' => $division->id]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test ratings method
	 */
	public function testRatings(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliate = $admin->affiliates[0];
		/** @var \App\Model\Entity\League $league */
		$league = LeagueFactory::make(['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Divisions', ['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true, 'schedule_type' => 'ratings_ladder'])
			->with('Affiliates', $affiliate)
			->persist();
		$division = $league->divisions[0];
		DivisionsPersonFactory::make(['person_id' => $volunteer->id, 'division_id' => $division->id])->persist();
		TeamFactory::make()->with('Divisions', $division)->persist();

		// Admins are allowed to change ratings
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'ratings', 'division' => $division->id], $admin->id);

		// Managers are allowed to change ratings
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'ratings', 'division' => $division->id], $manager->id);

		// Coordinators are allowed to change ratings
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'ratings', 'division' => $division->id], $volunteer->id);

		// Others are not allowed to change ratings
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'ratings', 'division' => $division->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'ratings', 'division' => $division->id]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test seeds method
	 */
	public function testSeeds(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliate = $admin->affiliates[0];
		/** @var \App\Model\Entity\League $league */
		$league = LeagueFactory::make(['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Divisions', ['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true, 'schedule_type' => 'ratings_ladder'])
			->with('Affiliates', $affiliate)
			->persist();
		$division = $league->divisions[0];
		DivisionsPersonFactory::make(['person_id' => $volunteer->id, 'division_id' => $division->id])->persist();
		TeamFactory::make()->with('Divisions', $division)->persist();

		// Admins are allowed to update seeds
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'seeds', 'division' => $division->id], $admin->id);

		// Managers are allowed to update seeds
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'seeds', 'division' => $division->id], $manager->id);

		// Coordinators are allowed to update seeds
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'seeds', 'division' => $division->id], $volunteer->id);

		// Others are not allowed to update seeds
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'seeds', 'division' => $division->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'seeds', 'division' => $division->id]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test delete method as an admin
	 */
	public function testDeleteAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		$affiliates = AffiliateFactory::make(2)->persist();
		$admin = PersonFactory::makeAdmin()->with('Affiliates', $affiliates)->persist();
		/** @var \App\Model\Entity\League $league1 */
		$league1 = LeagueFactory::make(['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Divisions[2]', ['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Affiliates', $affiliates[0])
			->persist();
		$division1a = $league1->divisions[0];
		$division1b = $league1->divisions[1];
		TeamFactory::make()->with('Divisions', $division1a)->persist();

		/** @var \App\Model\Entity\League $league2 */
		$league2 = LeagueFactory::make(['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Divisions', ['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Affiliates', $affiliates[1])
			->persist();
		$division2 = $league2->divisions[0];

		// Cannot delete divisions with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'Divisions', 'action' => 'delete', 'division' => $division1a->id],
			$admin->id, [], ['controller' => 'Leagues', 'action' => 'index'],
			'#The following records reference this division, so it cannot be deleted#');

		// Or the last division in a league
		$this->assertPostAsAccessRedirect(['controller' => 'Divisions', 'action' => 'delete', 'division' => $division2->id],
			$admin->id, [], ['controller' => 'Leagues', 'action' => 'index'],
			'You cannot delete the only division in a league.');

		// But admins are allowed to delete divisions otherwise
		$this->assertPostAsAccessRedirect(['controller' => 'Divisions', 'action' => 'delete', 'division' => $division1b->id],
			$admin->id, [], ['controller' => 'Leagues', 'action' => 'index'],
			'The division has been deleted.');
	}

	/**
	 * Test delete method as a manager
	 */
	public function testDeleteAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;
		/** @var \App\Model\Entity\League $league1 */
		$league1 = LeagueFactory::make(['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Divisions[2]', ['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Affiliates', $affiliates[0])
			->persist();
		$division1 = $league1->divisions[0];

		/** @var \App\Model\Entity\League $league2 */
		$league2 = LeagueFactory::make(['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Divisions[2]', ['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Affiliates', $affiliates[1])
			->persist();
		$division2 = $league2->divisions[0];

		// Managers are allowed to delete divisions in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Divisions', 'action' => 'delete', 'division' => $division1->id],
			$manager->id, [], ['controller' => 'Leagues', 'action' => 'index'],
			'The division has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Divisions', 'action' => 'delete', 'division' => $division2->id],
			$manager->id);
	}

	/**
	 * Test delete method as others
	 */
	public function testDeleteAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;
		/** @var \App\Model\Entity\League $league */
		$league = LeagueFactory::make(['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Divisions[2]', ['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Affiliates', $affiliates[0])
			->persist();
		$division = $league->divisions[0];
		DivisionsPersonFactory::make(['person_id' => $volunteer->id, 'division_id' => $division->id])->persist();

		// Others are not allowed to delete divisions
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Divisions', 'action' => 'delete', 'division' => $division->id], $volunteer->id);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Divisions', 'action' => 'delete', 'division' => $division->id], $player->id);
		$this->assertPostAjaxAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'delete', 'division' => $division->id]);
	}

	private function gameRegex(int $game_id, string $time, Facility $facility, int $field, Team $home, Team $away, string $status, bool $edit, bool $submit = false) {
		static $base = null;
		if (!$base) {
			$base = Configure::read('App.base');
		}
		$home_icon = strtolower($home->shirt_colour);
		$away_icon = strtolower($away->shirt_colour);

		$game_str = "<td><a[^>]*href=\"$base/games/view\?game={$game_id}\"[^>]*>{$time}</a></td>";
		$facility_str = "<td><a[^>]*href=\"$base/facilities/view\?facility={$facility->id}\"[^>]*>{$facility->code} {$field}</a></td>";
		$home_str = "<td><a[^>]*href=\"$base/teams/view\?team={$home->id}\"[^>]*>{$home->name}</a> <span[^>]*title=\"Shirt Colour: {$home->shirt_colour}\"[^>]*><img src=\"$base/img/shirts/{$home_icon}.png\?\d+\"[^>]*></span></td>";
		$away_str = "<td><a[^>]*href=\"$base/teams/view\?team={$away->id}\"[^>]*>{$away->name}</a> <span[^>]*title=\"Shirt Colour: {$away->shirt_colour}\"[^>]*><img src=\"$base/img/shirts/{$away_icon}.png\?\d+\"[^>]*></span></td>";
		$actions_str = '';
		if ($edit) {
			$actions_str = "<td class=\"actions\">{$status}\s*<span class=\"actions\"><a href=\"$base/games/edit\?game={$game_id}[^>]*\"";
		} else if ($submit) {
			$actions_str = "<td class=\"actions\">{$status}\s*<span class=\"actions\"><a href=\"$base/games/submit_score\?game={$game_id}[^>]*\"";
		}

		return "#$game_str\s*$facility_str\s*$home_str\s*$away_str\s*$actions_str#ms";
	}

	/**
	 * Test schedule method as an admin
	 */
	public function testScheduleAsAdmin(): void {
		[$admin, , $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $affiliates[0], 'coordinator' => $volunteer, 'scores' => true, 'playoffs' => true]);
		[$season, $playoffs] = $league->divisions;
		$games = $season->games;
		$facility = $games[0]->game_slot->field->facility_record;
		[$red, $yellow, $green, $blue, $orange, $purple, $black, $white] = $season->teams;

		// Admins get the schedule with edit links
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => $season->id], $admin->id);

		// First week of games
		$this->assertResponseRegExp($this->gameRegex($games[0]->id, '7:00PM-9:00PM', $facility, 1, $red, $yellow, '17 - 5', true));
		$this->assertResponseRegExp($this->gameRegex($games[1]->id, '7:00PM-9:00PM', $facility, 2, $green, $blue, 'cancelled', true));
		$this->assertResponseRegExp($this->gameRegex($games[2]->id, '9:00PM-11:00PM', $facility, 1, $orange, $purple, '12 - 17', true));
		$this->assertResponseRegExp($this->gameRegex($games[3]->id, '9:00PM-11:00PM', $facility, 2, $black, $white, '15 - 15', true));

		// Second week
		$this->assertResponseRegExp($this->gameRegex($games[4]->id, '7:00PM-9:00PM', $facility, 1, $red, $green, '0 - 6\s*\(default\)', true));
		$this->assertResponseRegExp($this->gameRegex($games[5]->id, '7:00PM-9:00PM', $facility, 2, $yellow, $blue, '6 - 0\s*\(default\)', true));
		$this->assertResponseRegExp($this->gameRegex($games[6]->id, '9:00PM-11:00PM', $facility, 1, $orange, $white, '17 - 12', true));
		$this->assertResponseRegExp($this->gameRegex($games[7]->id, '9:00PM-11:00PM', $facility, 2, $black, $purple, '15 - 15', true));

		// Third week
		$this->assertResponseRegExp($this->gameRegex($games[8]->id, '7:00PM-9:00PM', $facility, 1, $red, $blue, '17 - 12\s*\(unofficial\)', true));
		$this->assertResponseRegExp($this->gameRegex($games[9]->id, '7:00PM-9:00PM', $facility, 2, $green, $yellow, 'score mismatch', true));
		$this->assertResponseRegExp($this->gameRegex($games[10]->id, '9:00PM-11:00PM', $facility, 1, $white, $purple, 'not entered', true));
		$this->assertResponseRegExp($this->gameRegex($games[11]->id, '9:00PM-11:00PM', $facility, 2, $black, $orange, 'not entered', true));

		// Week 4 games aren't published, but admins can see them
		$this->assertResponseRegExp($this->gameRegex($games[12]->id, '7:00PM-9:00PM', $facility, 1, $red, $green, '', true));
		$this->assertResponseRegExp($this->gameRegex($games[13]->id, '7:00PM-9:00PM', $facility, 2, $yellow, $blue, '', true));

		// Confirm that there are appropriate links for weeks with games that aren't yet finalized
		$date = FrozenDate::now()->next(ChronosInterface::MONDAY)->subWeeks(1)->toDateString();
		$this->assertResponseContains('/divisions/schedule?division=' . $season->id . '&amp;edit_date=' . $date);
		$this->assertResponseContains('/divisions/slots?division=' . $season->id . '&amp;date=' . $date);
		$this->assertResponseContains('/schedules/delete?division=' . $season->id . '&amp;date=' . $date);
		$this->assertResponseContains('/schedules/reschedule?division=' . $season->id . '&amp;date=' . $date);
		$this->assertResponseContains('/schedules/unpublish?division=' . $season->id . '&amp;date=' . $date);

		// Admins don't get to submit scores or do attendance
		$this->assertResponseNotContains('/games/submit_score');
		$this->assertResponseNotContains('/games/attendance');

		// Check for initialize dependencies link where appropriate
		$date = FrozenDate::now()->next(ChronosInterface::MONDAY)->subWeeks(3)->addWeeks(9);
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => $playoffs->id], $admin->id);
		$this->assertResponseRegExp('#/divisions/initialize_dependencies\?division=' . $playoffs->id . '&amp;date=' . $date . '[^>]*\"#ms');
		$this->assertResponseRegExp('#/divisions/initialize_dependencies\?division=' . $playoffs->id . '&amp;date=' . $date . '&amp;reset=1[^>]*\"#ms');

		// Admins are allowed to see schedules from any affiliate
		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithMinimalScheduleScenario::class, ['affiliate' => $affiliates[1], 'coordinator' => $volunteer]);
		$season = $league->divisions[0];
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => $season->id], $admin->id);
		$games = $season->games;
		$facility = $games[0]->game_slot->field->facility_record;
		[$bears, $lions] = $season->teams;
		$this->assertResponseRegExp($this->gameRegex($games[0]->id, '7:00PM-9:00PM', $facility, 1, $bears, $lions, 'not entered', true));
	}

	/**
	 * Test schedule method as a manager
	 */
	public function testScheduleAsManager(): void {
		[$admin, $manager, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $affiliates[0], 'coordinator' => $volunteer, 'scores' => true, 'playoffs' => true]);
		[$season, $playoffs] = $league->divisions;
		$games = $season->games;
		$facility = $games[0]->game_slot->field->facility_record;
		[$red, $yellow, $green, $blue, $orange, $purple, $black, $white] = $season->teams;

		// Managers get the schedule with edit links
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => $season->id], $manager->id);

		// First week of games
		$this->assertResponseRegExp($this->gameRegex($games[0]->id, '7:00PM-9:00PM', $facility, 1, $red, $yellow, '17 - 5', true));
		$this->assertResponseRegExp($this->gameRegex($games[1]->id, '7:00PM-9:00PM', $facility, 2, $green, $blue, 'cancelled', true));
		$this->assertResponseRegExp($this->gameRegex($games[2]->id, '9:00PM-11:00PM', $facility, 1, $orange, $purple, '12 - 17', true));
		$this->assertResponseRegExp($this->gameRegex($games[3]->id, '9:00PM-11:00PM', $facility, 2, $black, $white, '15 - 15', true));

		// Second week
		$this->assertResponseRegExp($this->gameRegex($games[4]->id, '7:00PM-9:00PM', $facility, 1, $red, $green, '0 - 6\s*\(default\)', true));
		$this->assertResponseRegExp($this->gameRegex($games[5]->id, '7:00PM-9:00PM', $facility, 2, $yellow, $blue, '6 - 0\s*\(default\)', true));
		$this->assertResponseRegExp($this->gameRegex($games[6]->id, '9:00PM-11:00PM', $facility, 1, $orange, $white, '17 - 12', true));
		$this->assertResponseRegExp($this->gameRegex($games[7]->id, '9:00PM-11:00PM', $facility, 2, $black, $purple, '15 - 15', true));

		// Third week
		$this->assertResponseRegExp($this->gameRegex($games[8]->id, '7:00PM-9:00PM', $facility, 1, $red, $blue, '17 - 12\s*\(unofficial\)', true));
		$this->assertResponseRegExp($this->gameRegex($games[9]->id, '7:00PM-9:00PM', $facility, 2, $green, $yellow, 'score mismatch', true));
		$this->assertResponseRegExp($this->gameRegex($games[10]->id, '9:00PM-11:00PM', $facility, 1, $white, $purple, 'not entered', true));
		$this->assertResponseRegExp($this->gameRegex($games[11]->id, '9:00PM-11:00PM', $facility, 2, $black, $orange, 'not entered', true));

		// Week 4 games aren't published, but managers can see them
		$this->assertResponseRegExp($this->gameRegex($games[12]->id, '7:00PM-9:00PM', $facility, 1, $red, $green, '', true));
		$this->assertResponseRegExp($this->gameRegex($games[13]->id, '7:00PM-9:00PM', $facility, 2, $yellow, $blue, '', true));

		// Confirm that there are appropriate links for weeks with games that aren't yet finalized
		$date = FrozenDate::now()->next(ChronosInterface::MONDAY)->subWeeks(1)->toDateString();
		$this->assertResponseContains('/divisions/schedule?division=' . $season->id . '&amp;edit_date=' . $date);
		$this->assertResponseContains('/divisions/slots?division=' . $season->id . '&amp;date=' . $date);
		$this->assertResponseContains('/schedules/delete?division=' . $season->id . '&amp;date=' . $date);
		$this->assertResponseContains('/schedules/reschedule?division=' . $season->id . '&amp;date=' . $date);
		$this->assertResponseContains('/schedules/unpublish?division=' . $season->id . '&amp;date=' . $date);

		// Managers don't get to submit scores or do attendance
		$this->assertResponseNotContains('/games/submit_score');
		$this->assertResponseNotContains('/games/attendance');

		// Check for initialize dependencies link where appropriate
		$date = FrozenDate::now()->next(ChronosInterface::MONDAY)->subWeeks(3)->addWeeks(9);
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => $playoffs->id], $manager->id);
		$this->assertResponseRegExp('#/divisions/initialize_dependencies\?division=' . $playoffs->id . '&amp;date=' . $date . '[^>]*\"#ms');
		$this->assertResponseRegExp('#/divisions/initialize_dependencies\?division=' . $playoffs->id . '&amp;date=' . $date . '&amp;reset=1[^>]*\"#ms');

		// Managers are allowed to see schedules from any affiliate, but no edit links
		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithMinimalScheduleScenario::class, ['affiliate' => $affiliates[1], 'coordinator' => $volunteer]);
		$season = $league->divisions[0];
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => $season->id], $manager->id);
		$games = $season->games;
		$facility = $games[0]->game_slot->field->facility_record;
		[$bears, $lions] = $season->teams;
		$this->assertResponseRegExp($this->gameRegex($games[0]->id, '7:00PM-9:00PM', $facility, 1, $bears, $lions, 'not entered', false));
		$this->assertResponseNotContains('/games/edit');
	}

	/**
	 * Test schedule method as a coordinator
	 */
	public function testScheduleAsCoordinator(): void {
		[$admin, , $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $affiliates[0], 'coordinator' => $volunteer, 'scores' => true, 'playoffs' => true]);
		[$season, $playoffs] = $league->divisions;
		$games = $season->games;
		$facility = $games[0]->game_slot->field->facility_record;
		[$red, $yellow, $green, $blue, $orange, $purple, $black, $white] = $season->teams;

		// Coordinators get the schedule with edit links
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => $season->id], $volunteer->id);

		// First week of games
		$this->assertResponseRegExp($this->gameRegex($games[0]->id, '7:00PM-9:00PM', $facility, 1, $red, $yellow, '17 - 5', true));
		$this->assertResponseRegExp($this->gameRegex($games[1]->id, '7:00PM-9:00PM', $facility, 2, $green, $blue, 'cancelled', true));
		$this->assertResponseRegExp($this->gameRegex($games[2]->id, '9:00PM-11:00PM', $facility, 1, $orange, $purple, '12 - 17', true));
		$this->assertResponseRegExp($this->gameRegex($games[3]->id, '9:00PM-11:00PM', $facility, 2, $black, $white, '15 - 15', true));

		// Second week
		$this->assertResponseRegExp($this->gameRegex($games[4]->id, '7:00PM-9:00PM', $facility, 1, $red, $green, '0 - 6\s*\(default\)', true));
		$this->assertResponseRegExp($this->gameRegex($games[5]->id, '7:00PM-9:00PM', $facility, 2, $yellow, $blue, '6 - 0\s*\(default\)', true));
		$this->assertResponseRegExp($this->gameRegex($games[6]->id, '9:00PM-11:00PM', $facility, 1, $orange, $white, '17 - 12', true));
		$this->assertResponseRegExp($this->gameRegex($games[7]->id, '9:00PM-11:00PM', $facility, 2, $black, $purple, '15 - 15', true));

		// Third week
		$this->assertResponseRegExp($this->gameRegex($games[8]->id, '7:00PM-9:00PM', $facility, 1, $red, $blue, '17 - 12\s*\(unofficial\)', true));
		$this->assertResponseRegExp($this->gameRegex($games[9]->id, '7:00PM-9:00PM', $facility, 2, $green, $yellow, 'score mismatch', true));
		$this->assertResponseRegExp($this->gameRegex($games[10]->id, '9:00PM-11:00PM', $facility, 1, $white, $purple, 'not entered', true));
		$this->assertResponseRegExp($this->gameRegex($games[11]->id, '9:00PM-11:00PM', $facility, 2, $black, $orange, 'not entered', true));

		// Week 4 games aren't published, but coordinators can see them
		$this->assertResponseRegExp($this->gameRegex($games[12]->id, '7:00PM-9:00PM', $facility, 1, $red, $green, '', true));
		$this->assertResponseRegExp($this->gameRegex($games[13]->id, '7:00PM-9:00PM', $facility, 2, $yellow, $blue, '', true));

		// Confirm that there are appropriate links for weeks with games that aren't yet finalized
		$date = FrozenDate::now()->next(ChronosInterface::MONDAY)->subWeeks(1)->toDateString();
		$this->assertResponseContains('/divisions/schedule?division=' . $season->id . '&amp;edit_date=' . $date);
		$this->assertResponseContains('/divisions/slots?division=' . $season->id . '&amp;date=' . $date);
		$this->assertResponseContains('/schedules/delete?division=' . $season->id . '&amp;date=' . $date);
		$this->assertResponseContains('/schedules/reschedule?division=' . $season->id . '&amp;date=' . $date);
		$this->assertResponseContains('/schedules/unpublish?division=' . $season->id . '&amp;date=' . $date);

		// Coordinators don't get to submit scores or do attendance
		$this->assertResponseNotContains('/games/submit_score');
		$this->assertResponseNotContains('/games/attendance');

		// Check for initialize dependencies link where appropriate
		$date = FrozenDate::now()->next(ChronosInterface::MONDAY)->subWeeks(3)->addWeeks(9);
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => $playoffs->id], $volunteer->id);
		$this->assertResponseRegExp('#/divisions/initialize_dependencies\?division=' . $playoffs->id . '&amp;date=' . $date . '[^>]*\"#ms');
		$this->assertResponseRegExp('#/divisions/initialize_dependencies\?division=' . $playoffs->id . '&amp;date=' . $date . '&amp;reset=1[^>]*\"#ms');

		// Coordinators are allowed to see schedules from any division, but no edit links, and can't see unpublished games there
		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $affiliates[0]]);
		$season = $league->divisions[0];
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => $season->id], $volunteer->id);
		$games = $season->games;
		$facility = $games[0]->game_slot->field->facility_record;
		[$red, $yellow] = $season->teams;
		$this->assertResponseRegExp($this->gameRegex($games[0]->id, '7:00PM-9:00PM', $facility, 1, $red, $yellow, '17 - 5', false));
		$this->assertResponseNotContains('/games/edit');
		$this->assertResponseNotContains("/games/view?game={$games[12]->id}");
		$this->assertResponseNotContains("/games/view?game={$games[13]->id}");

		// Coordinators are allowed to see schedules from any affiliate, but no edit links
		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithMinimalScheduleScenario::class, ['affiliate' => $affiliates[1]]);
		$season = $league->divisions[0];
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => $season->id], $volunteer->id);
		$games = $season->games;
		$facility = $games[0]->game_slot->field->facility_record;
		[$bears, $lions] = $season->teams;
		$this->assertResponseRegExp($this->gameRegex($games[0]->id, '7:00PM-9:00PM', $facility, 1, $bears, $lions, 'not entered', false));
		$this->assertResponseNotContains('/games/edit');
	}

	/**
	 * Test schedule method as a captain
	 */
	public function testScheduleAsCaptain(): void {
		[$admin, , $volunteer, $captain] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $affiliates[0], 'coordinator' => $volunteer, 'scores' => true, 'playoffs' => true]);
		$season = $league->divisions[0];
		$games = $season->games;
		$facility = $games[0]->game_slot->field->facility_record;
		[$red, $yellow, $green, $blue, $orange, $purple, $black, $white] = $season->teams;

		TeamsPersonFactory::make(['person_id' => $captain->id, 'team_id' => $red->id, 'role' => 'captain'])
			->persist();

		// Captains get the schedule with score submission, attendance and game note links where appropriate
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => $season->id], $captain->id);

		// First week of games
		$this->assertResponseRegExp($this->gameRegex($games[0]->id, '7:00PM-9:00PM', $facility, 1, $red, $yellow, '17 - 5', false));
		$this->assertResponseNotRegExp('#<a href="' . Configure::read('App.base') . '/games/submit_score\?game={$games[0]->id}[^>0-9]*"#ms');
		$this->assertResponseNotContains('stats');
		$this->assertResponseRegExp($this->gameRegex($games[1]->id, '7:00PM-9:00PM', $facility, 2, $green, $blue, 'cancelled', false));
		$this->assertResponseRegExp($this->gameRegex($games[2]->id, '9:00PM-11:00PM', $facility, 1, $orange, $purple, '12 - 17', false));
		$this->assertResponseRegExp($this->gameRegex($games[3]->id, '9:00PM-11:00PM', $facility, 2, $black, $white, '15 - 15', false));

		// TODO: Check attendance and note links

		// Third week, can submit the score
		$this->assertResponseRegExp($this->gameRegex($games[8]->id, '7:00PM-9:00PM', $facility, 1, $red, $blue, '17 - 12\s*\(unofficial\)', false, true));

		// Week 4 games aren't published, so captains can't see them
		$this->assertResponseNotContains("/games/view?game={$games[12]->id}");
		$this->assertResponseNotContains("/games/view?game={$games[13]->id}");

		// Captains don't get to edit games or do anything with schedules
		$this->assertResponseNotContains('/games/edit');
		$this->assertResponseNotRegExp('#/divisions/schedule\?division=\d+&amp;edit_date=#ms');
		$this->assertResponseNotContains('/divisions/slots');
		$this->assertResponseNotContains('/divisions/initialize_dependencies');
		$this->assertResponseNotContains('/schedules/delete');
		$this->assertResponseNotContains('/schedules/reschedule');
		$this->assertResponseNotContains('/schedules/unpublish');

		// Captains are allowed to see schedules from any division
		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $affiliates[0]]);
		$season = $league->divisions[0];
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => $season->id], $captain->id);
		$games = $season->games;
		$facility = $games[0]->game_slot->field->facility_record;
		[$red, $yellow] = $season->teams;
		$this->assertResponseRegExp($this->gameRegex($games[0]->id, '7:00PM-9:00PM', $facility, 1, $red, $yellow, '17 - 5', false));
		$this->assertResponseNotContains('/games/submit_score');

		// Captains are allowed to see schedules from any affiliate
		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithMinimalScheduleScenario::class, ['affiliate' => $affiliates[1]]);
		$season = $league->divisions[0];
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => $season->id], $captain->id);
		$games = $season->games;
		$facility = $games[0]->game_slot->field->facility_record;
		[$bears, $lions] = $season->teams;
		$this->assertResponseRegExp($this->gameRegex($games[0]->id, '7:00PM-9:00PM', $facility, 1, $bears, $lions, 'not entered', false));
		$this->assertResponseNotContains('/games/submit_score');
	}

	/**
	 * Test schedule method as a player
	 */
	public function testScheduleAsPlayer(): void {
		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $affiliates[0], 'coordinator' => $volunteer, 'scores' => true, 'playoffs' => true]);
		$season = $league->divisions[0];
		$games = $season->games;
		$facility = $games[0]->game_slot->field->facility_record;
		[$red, $yellow, $green, $blue, $orange, $purple, $black, $white] = $season->teams;

		TeamsPersonFactory::make(['person_id' => $player->id, 'team_id' => $red->id])
			->persist();

		// Players get the schedule with attendance and game note links where appropriate
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => $season->id], $player->id);

		// First week of games
		$this->assertResponseRegExp($this->gameRegex($games[0]->id, '7:00PM-9:00PM', $facility, 1, $red, $yellow, '17 - 5', false));
		$this->assertResponseNotRegExp('#<a href="' . Configure::read('App.base') . '/games/submit_score\?game={$games[0]->id}[^>0-9]*"#ms');
		$this->assertResponseRegExp($this->gameRegex($games[1]->id, '7:00PM-9:00PM', $facility, 2, $green, $blue, 'cancelled', false));
		$this->assertResponseRegExp($this->gameRegex($games[2]->id, '9:00PM-11:00PM', $facility, 1, $orange, $purple, '12 - 17', false));
		$this->assertResponseRegExp($this->gameRegex($games[3]->id, '9:00PM-11:00PM', $facility, 2, $black, $white, '15 - 15', false));

		// TODO: Check attendance and note links

		// Week 4 games aren't published, so players can't see them
		$this->assertResponseNotContains("/games/view?game={$games[12]->id}");
		$this->assertResponseNotContains("/games/view?game={$games[13]->id}");

		// Players don't get to edit games, submit scores or do anything with schedules
		$this->assertResponseNotContains('/games/edit');
		$this->assertResponseNotContains('/games/submit_score');
		$this->assertResponseNotContains('/games/submit_stats');
		$this->assertResponseNotRegExp('#/divisions/schedule\?division=\d+&amp;edit_date=#ms');
		$this->assertResponseNotContains('/divisions/slots');
		$this->assertResponseNotContains('/divisions/initialize_dependencies');
		$this->assertResponseNotContains('/schedules/delete');
		$this->assertResponseNotContains('/schedules/reschedule');
		$this->assertResponseNotContains('/schedules/unpublish');
	}

	/**
	 * Test schedule method as someone else
	 */
	public function testScheduleAsVisitor(): void {
		[$admin, , $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		// Intentionally not adding the volunteer on this league, so that they have zero extra permissions
		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $affiliates[0], 'scores' => true, 'playoffs' => true]);
		$season = $league->divisions[0];
		$games = $season->games;
		$facility = $games[0]->game_slot->field->facility_record;
		[$red, $yellow, $green, $blue, $orange, $purple, $black, $white] = $season->teams;

		// Visitors get the schedule with minimal links
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => $season->id], $volunteer->id);

		// First week of games
		$this->assertResponseRegExp($this->gameRegex($games[0]->id, '7:00PM-9:00PM', $facility, 1, $red, $yellow, '17 - 5', false));
		$this->assertResponseNotRegExp('#<a href="' . Configure::read('App.base') . '/games/submit_score\?game={$games[0]->id}[^>0-9]*"#ms');
		$this->assertResponseRegExp($this->gameRegex($games[1]->id, '7:00PM-9:00PM', $facility, 2, $green, $blue, 'cancelled', false));
		$this->assertResponseRegExp($this->gameRegex($games[2]->id, '9:00PM-11:00PM', $facility, 1, $orange, $purple, '12 - 17', false));
		$this->assertResponseRegExp($this->gameRegex($games[3]->id, '9:00PM-11:00PM', $facility, 2, $black, $white, '15 - 15', false));

		// Week 4 games aren't published, so visitors can't see them
		$this->assertResponseNotContains("/games/view?game={$games[12]->id}");
		$this->assertResponseNotContains("/games/view?game={$games[13]->id}");

		// Visitors don't get to edit games, submit scores, do attendance, or anything with schedules
		$this->assertResponseNotContains('/games/edit');
		$this->assertResponseNotContains('/games/submit_score');
		$this->assertResponseNotContains('/games/submit_stats');
		$this->assertResponseNotContains('/games/attendance');
		$this->assertResponseNotRegExp('#/divisions/schedule\?division=\d+&amp;edit_date=#ms');
		$this->assertResponseNotContains('/divisions/slots');
		$this->assertResponseNotContains('/divisions/initialize_dependencies');
		$this->assertResponseNotContains('/schedules/delete');
		$this->assertResponseNotContains('/schedules/reschedule');
		$this->assertResponseNotContains('/schedules/unpublish');
	}

	/**
	 * Test schedule method without being logged in
	 */
	public function testScheduleAsAnonymous(): void {
		$affiliate = AffiliateFactory::make()->persist();

		// Intentionally not adding the volunteer on this league, so that they have zero extra permissions
		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $affiliate, 'scores' => true, 'playoffs' => true]);
		$season = $league->divisions[0];
		$games = $season->games;
		$facility = $games[0]->game_slot->field->facility_record;
		[$red, $yellow, $green, $blue, $orange, $purple, $black, $white] = $season->teams;

		// Anonymous browsers get the schedule with minimal links
		$this->assertGetAnonymousAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => $season->id]);

		// First week of games
		$this->assertResponseRegExp($this->gameRegex($games[0]->id, '7:00PM-9:00PM', $facility, 1, $red, $yellow, '17 - 5', false));
		$this->assertResponseNotRegExp('#<a href="' . Configure::read('App.base') . '/games/submit_score\?game={$games[0]->id}[^>0-9]*"#ms');
		$this->assertResponseRegExp($this->gameRegex($games[1]->id, '7:00PM-9:00PM', $facility, 2, $green, $blue, 'cancelled', false));
		$this->assertResponseRegExp($this->gameRegex($games[2]->id, '9:00PM-11:00PM', $facility, 1, $orange, $purple, '12 - 17', false));
		$this->assertResponseRegExp($this->gameRegex($games[3]->id, '9:00PM-11:00PM', $facility, 2, $black, $white, '15 - 15', false));

		// Week 4 games aren't published, so anonymous users can't see them
		$this->assertResponseNotContains("/games/view?game={$games[12]->id}");
		$this->assertResponseNotContains("/games/view?game={$games[13]->id}");

		// Anonymous browsers don't get any actions
		$this->assertResponseNotContains('/games/edit');
		$this->assertResponseNotContains('/games/submit_score');
		$this->assertResponseNotContains('/games/submit_stats');
		$this->assertResponseNotContains('/games/attendance');
		$this->assertResponseNotRegExp('#/divisions/schedule\?division=\d+&amp;edit_date=#ms');
		$this->assertResponseNotContains('/divisions/slots');
		$this->assertResponseNotContains('/divisions/initialize_dependencies');
		$this->assertResponseNotContains('/schedules/delete');
		$this->assertResponseNotContains('/schedules/reschedule');
		$this->assertResponseNotContains('/schedules/unpublish');
	}

	/**
	 * Test standings method
	 */
	public function testStandings(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $affiliates[0], 'coordinator' => $volunteer]);
		$division = $league->divisions[0];
		[$red] = $division->teams;

		TeamsPersonFactory::make(['person_id' => $player->id, 'team_id' => $red->id])
			->persist();

		// Anyone logged in is allowed to view standings
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'standings', 'division' => $division->id], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'standings', 'division' => $division->id], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'standings', 'division' => $division->id], $volunteer->id);
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'standings', 'division' => $division->id], $player->id);
		$this->assertGetAnonymousAccessOk(['controller' => 'Divisions', 'action' => 'standings', 'division' => $division->id]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test scores method
	 */
	public function testScores(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $affiliates[0], 'coordinator' => $volunteer, 'scores' => true]);
		$division = $league->divisions[0];
		[$red] = $division->teams;

		TeamsPersonFactory::make(['person_id' => $player->id, 'team_id' => $red->id])
			->persist();

		// Anyone logged in is allowed to view scores
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'scores', 'division' => $division->id], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'scores', 'division' => $division->id], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'scores', 'division' => $division->id], $volunteer->id);
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'scores', 'division' => $division->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'scores', 'division' => $division->id]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test fields method
	 */
	public function testFields(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $affiliates[0], 'coordinator' => $volunteer]);
		$division = $league->divisions[0];

		// Admins are allowed to view the fields report
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'fields', 'division' => $division->id], $admin->id);

		// Managers are allowed to view the fields report
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'fields', 'division' => $division->id], $manager->id);

		// Coordinators are allowed to view the fields report
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'fields', 'division' => $division->id], $volunteer->id);

		// Others are not allowed to view the fields report
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'fields', 'division' => $division->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'fields', 'division' => $division->id]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test slots method
	 */
	public function testSlots(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $affiliates[0], 'coordinator' => $volunteer]);
		$division = $league->divisions[0];

		// Admins are allowed to view the slots report
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'slots', 'division' => $division->id], $admin->id);

		// Managers are allowed to view the slots report
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'slots', 'division' => $division->id], $manager->id);

		// Coordinators are allowed to view the slots report
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'slots', 'division' => $division->id], $volunteer->id);

		// Others are not allowed to view the slots report
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'slots', 'division' => $division->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'slots', 'division' => $division->id]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test status method
	 */
	public function testStatus(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $affiliates[0], 'coordinator' => $volunteer]);
		$division = $league->divisions[0];

		// Admins are allowed to view the status report
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'status', 'division' => $division->id], $admin->id);

		// Managers are allowed to view the status report
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'status', 'division' => $division->id], $manager->id);

		// Coordinators are allowed to view the status report
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'status', 'division' => $division->id], $volunteer->id);

		// Others are not allowed to view the status report
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'status', 'division' => $division->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'status', 'division' => $division->id]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test allstars method
	 */
	public function testAllstars(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $affiliates[0], 'coordinator' => $volunteer]);
		$division = $league->divisions[0];

		// Admins are allowed to view the allstars report
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'allstars', 'division' => $division->id], $admin->id);

		// Managers are allowed to view the allstars report
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'allstars', 'division' => $division->id], $manager->id);

		// Coordinators are allowed to view the allstars report
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'allstars', 'division' => $division->id], $volunteer->id);

		// Others are not allowed to view the allstars report
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'allstars', 'division' => $division->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'allstars', 'division' => $division->id]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test emails method
	 */
	public function testEmails(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $affiliates[0], 'coordinator' => $volunteer]);
		$division = $league->divisions[0];

		// Admins are allowed to view emails
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'emails', 'division' => $division->id], $admin->id);

		// Managers are allowed to view emails
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'emails', 'division' => $division->id], $manager->id);

		// Coordinators are allowed to view emails
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'emails', 'division' => $division->id], $volunteer->id);

		// Others are not allowed to view emails
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'emails', 'division' => $division->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'emails', 'division' => $division->id]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test spirit method
	 */
	public function testSpirit(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $affiliates[0], 'coordinator' => $volunteer, 'spirit' => true]);
		$division = $league->divisions[0];

		// Admins are allowed to view the spirit report
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'spirit', 'division' => $division->id], $admin->id);

		// Managers are allowed to view the spirit report
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'spirit', 'division' => $division->id], $manager->id);

		// Coordinators are allowed to view the spirit report
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'spirit', 'division' => $division->id], $volunteer->id);

		// Others are not allowed to view the spirit report
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'spirit', 'division' => $division->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'spirit', 'division' => $division->id]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test approve_scores method
	 */
	public function testApproveScores(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $affiliates[0], 'coordinator' => $volunteer, 'scores' => true]);
		$division = $league->divisions[0];

		// Admins are allowed to approve scores
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'approve_scores', 'division' => $division->id], $admin->id);

		// Managers are allowed to approve scores
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'approve_scores', 'division' => $division->id], $manager->id);

		// Coordinators are allowed to approve scores
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'approve_scores', 'division' => $division->id], $volunteer->id);

		// Others are not allowed to approve scores
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'approve_scores', 'division' => $division->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'approve_scores', 'division' => $division->id]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test initialize_ratings method as an admin
	 */
	public function testInitializeRatingsAsAdmin(): void {
		[$admin, , $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $affiliates[0], 'coordinator' => $volunteer, 'playoffs' => true]);
		$playoffs = $league->divisions[1];

		// Admins are allowed to initialize ratings
		$this->assertGetAsAccessRedirect(['controller' => 'Divisions', 'action' => 'initialize_ratings', 'division' => $playoffs->id],
			$admin->id, ['controller' => 'Divisions', 'action' => 'view', 'division' => $playoffs->id],
			'Team ratings have been initialized.');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test initialize_ratings method as a manager
	 */
	public function testInitializeRatingsAsManager(): void {
		[$admin, $manager, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $affiliates[0], 'coordinator' => $volunteer, 'playoffs' => true]);
		$playoffs = $league->divisions[1];

		// Managers are allowed to initialize ratings
		$this->assertGetAsAccessRedirect(['controller' => 'Divisions', 'action' => 'initialize_ratings', 'division' => $playoffs->id],
			$manager->id, ['controller' => 'Divisions', 'action' => 'view', 'division' => $playoffs->id],
			'Team ratings have been initialized.');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test initialize_ratings method as a coordinator
	 */
	public function testInitializeRatingsAsCoordinator(): void {
		[$admin, , $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $affiliates[0], 'coordinator' => $volunteer, 'playoffs' => true]);
		$playoffs = $league->divisions[1];

		// Coordinators are allowed to initialize ratings
		$this->assertGetAsAccessRedirect(['controller' => 'Divisions', 'action' => 'initialize_ratings', 'division' => $playoffs->id],
			$volunteer->id, ['controller' => 'Divisions', 'action' => 'view', 'division' => $playoffs->id],
			'Team ratings have been initialized.');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test initialize_ratings method as others
	 */
	public function testInitializeRatingsAsOthers(): void {
		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $affiliates[0], 'coordinator' => $volunteer, 'playoffs' => true]);
		$playoffs = $league->divisions[1];

		// Others are not allowed to initialize ratings
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'initialize_ratings', 'division' => $playoffs->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'initialize_ratings', 'division' => $playoffs->id]);
	}

	/**
	 * Test initialize_dependencies method as an admin
	 */
	public function testInitializeDependenciesAsAdmin(): void {
		[$admin, , $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $affiliates[0], 'coordinator' => $volunteer, 'playoffs' => true]);
		$playoffs = $league->divisions[1];

		// Admins are allowed to initialize dependencies
		$this->assertGetAsAccessRedirect(['controller' => 'Divisions', 'action' => 'initialize_dependencies', 'division' => $playoffs->id],
			$admin->id, ['controller' => 'Divisions', 'action' => 'schedule', 'division' => $playoffs->id],
			'Dependencies have been resolved.');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test initialize_dependencies method as a manager
	 */
	public function testInitializeDependenciesAsManager(): void {
		[$admin, $manager, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $affiliates[0], 'coordinator' => $volunteer, 'playoffs' => true]);
		$playoffs = $league->divisions[1];

		// Managers are allowed to initialize dependencies
		$this->assertGetAsAccessRedirect(['controller' => 'Divisions', 'action' => 'initialize_dependencies', 'division' => $playoffs->id],
			$manager->id, ['controller' => 'Divisions', 'action' => 'schedule', 'division' => $playoffs->id],
			'Dependencies have been resolved.');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test initialize_dependencies method as a coordinator
	 */
	public function testInitializeDependenciesAsCoordinator(): void {
		[$admin, , $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $affiliates[0], 'coordinator' => $volunteer, 'playoffs' => true]);
		$playoffs = $league->divisions[1];

		// Coordinators are allowed to initialize dependencies
		$this->assertGetAsAccessRedirect(['controller' => 'Divisions', 'action' => 'initialize_dependencies', 'division' => $playoffs->id],
			$volunteer->id, ['controller' => 'Divisions', 'action' => 'schedule', 'division' => $playoffs->id],
			'Dependencies have been resolved.');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test initialize_dependencies method as others
	 */
	public function testInitializeDependenciesAsOthers(): void {
		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $affiliates[0], 'coordinator' => $volunteer, 'playoffs' => true]);
		$playoffs = $league->divisions[1];

		// Others are not allowed to initialize dependencies
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'initialize_dependencies', 'division' => $playoffs->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'initialize_dependencies', 'division' => $playoffs->id]);
	}

	/**
	 * Test delete_stage method as an admin
	 */
	public function testDeleteStageAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, , $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $affiliates[0], 'coordinator' => $volunteer, 'playoffs' => true]);
		$playoffs = $league->divisions[1];

		// Admins are allowed to delete stages
		$this->assertGetAsAccessRedirect(['controller' => 'Divisions', 'action' => 'delete_stage', 'division' => $playoffs->id, 'stage' => 2],
			$admin->id, ['controller' => 'Schedules', 'action' => 'add', 'division' => $playoffs->id],
			'The pools in this stage have been deleted.');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test delete_stage method as a manager
	 */
	public function testDeleteStageAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $affiliates[0], 'coordinator' => $volunteer, 'playoffs' => true]);
		$playoffs = $league->divisions[1];

		// Managers are allowed to delete stages
		$this->assertGetAsAccessRedirect(['controller' => 'Divisions', 'action' => 'delete_stage', 'division' => $playoffs->id, 'stage' => 2],
			$manager->id, ['controller' => 'Schedules', 'action' => 'add', 'division' => $playoffs->id],
			'The pools in this stage have been deleted.');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test delete_stage method as a coordinator
	 */
	public function testDeleteStageAsCoordinator(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, , $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $affiliates[0], 'coordinator' => $volunteer, 'playoffs' => true]);
		$playoffs = $league->divisions[1];

		// Coordinators are allowed to delete stages
		$this->assertGetAsAccessRedirect(['controller' => 'Divisions', 'action' => 'delete_stage', 'division' => $playoffs->id, 'stage' => 2],
			$volunteer->id, ['controller' => 'Schedules', 'action' => 'add', 'division' => $playoffs->id],
			'The pools in this stage have been deleted.');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test delete_stage method as others
	 */
	public function testDeleteStageAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $affiliates[0], 'coordinator' => $volunteer, 'playoffs' => true]);
		$playoffs = $league->divisions[1];

		// Others are not allowed to delete stages
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'delete_stage', 'division' => $playoffs->id, 'stage' => 2], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'delete_stage', 'division' => $playoffs->id, 'stage' => 2]);
	}

	/**
	 * Test redirect method
	 */
	public function testRedirect(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test select method
	 */
	public function testSelect(): void {
		$this->enableCsrfToken();

		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\League $league */
		$this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $affiliates[0], 'coordinator' => $volunteer]);

		// Admins are allowed to select
		$now = FrozenDate::now();
		$this->assertPostAjaxAsAccessOk(['controller' => 'Divisions', 'action' => 'select', 'affiliate' => $affiliates[0]->id],
			$admin->id, ['game_date' => ['year' => $now->year, 'month' => $now->month, 'day' => $now->day], 'sport' => 'ultimate']);
		$this->assertPostAjaxAsAccessOk(['controller' => 'Divisions', 'action' => 'select', 'affiliate' => $affiliates[1]->id],
			$admin->id, ['game_date' => ['year' => $now->year, 'month' => $now->month, 'day' => $now->day], 'sport' => 'ultimate']);

		// Managers are allowed to select
		$this->assertPostAjaxAsAccessOk(['controller' => 'Divisions', 'action' => 'select', 'affiliate' => $affiliates[0]->id],
			$manager->id, ['game_date' => ['year' => $now->year, 'month' => $now->month, 'day' => $now->day], 'sport' => 'ultimate']);

		// Coordinators are allowed to select
		$this->assertPostAjaxAsAccessOk(['controller' => 'Divisions', 'action' => 'select', 'affiliate' => $affiliates[0]->id],
			$volunteer->id, ['game_date' => ['year' => $now->year, 'month' => $now->month, 'day' => $now->day], 'sport' => 'ultimate']);

		// Others are not allowed to select
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Divisions', 'action' => 'select', 'affiliate' => $affiliates[0]->id],
			$player->id, ['game_date' => ['year' => $now->year, 'month' => $now->month, 'day' => $now->day], 'sport' => 'ultimate']);
		$this->assertPostAjaxAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'select', 'affiliate' => $affiliates[0]->id],
			['game_date' => ['year' => $now->year, 'month' => $now->month, 'day' => $now->day], 'sport' => 'ultimate']);
	}

}
