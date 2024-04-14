<?php
namespace App\Test\TestCase\Controller;

use App\Test\Factory\AffiliateFactory;
use App\Test\Factory\AffiliatesPersonFactory;
use App\Test\Factory\PersonFactory;
use App\Test\Scenario\DiverseUsersScenario;
use Cake\Core\Configure;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\AffiliatesController Test Case
 */
class AffiliatesControllerTest extends ControllerTestCase {

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
		'app.Settings',
	];

	/**
	 * Test index method
	 */
	public function testIndex(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliate = $admin->affiliates[0];

		// Admins are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'index'], $admin->id);
		$this->assertResponseContains('/affiliates/edit?affiliate=' . $affiliate->id);
		$this->assertResponseContains('/affiliates/delete?affiliate=' . $affiliate->id);

		// Others are not allowed to see the index
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'index'], $manager->id);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'index'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'index'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Affiliates', 'action' => 'index']);
	}

	/**
	 * Test view method
	 */
	public function testView(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliate = $admin->affiliates[0];

		// Admins are allowed to view affiliates
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'view', '?' => ['affiliate' => $affiliate->id]], $admin->id);
		$this->assertResponseContains('/affiliates/edit?affiliate=' . $affiliate->id);
		$this->assertResponseContains('/affiliates/delete?affiliate=' . $affiliate->id);

		// Others are not allowed to view affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'view', '?' => ['affiliate' => $affiliate->id]], $manager->id);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'view', '?' => ['affiliate' => $affiliate->id]], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'view', '?' => ['affiliate' => $affiliate->id]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Affiliates', 'action' => 'index']);
	}

	/**
	 * Test add method
	 */
	public function testAdd(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Admins are allowed to add affiliates
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'add'], $admin->id);

		// Others are not allowed to add affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'add'], $manager->id);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'add'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'add'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Affiliates', 'action' => 'add']);
	}

	/**
	 * Test edit method
	 */
	public function testEdit(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliate = $admin->affiliates[0];

		// Admins are allowed to edit affiliates
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'edit', '?' => ['affiliate' => $affiliate->id]], $admin->id);

		// Others are not allowed to edit affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'edit', '?' => ['affiliate' => $affiliate->id]], $manager->id);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'edit', '?' => ['affiliate' => $affiliate->id]], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'edit', '?' => ['affiliate' => $affiliate->id]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Affiliates', 'action' => 'edit', '?' => ['affiliate' => $affiliate->id]]);
	}

	/**
	 * Test delete method as an admin
	 */
	public function testDeleteAsAdmin(): void {
		$this->enableSecurityToken();

		$affiliate = AffiliateFactory::make()->persist();
		$admin = PersonFactory::make()->admin()->with('Affiliates', $affiliate)->persist();
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'edit', '?' => ['affiliate' => $affiliate->id]], $admin->id);

		// Admins are allowed to delete affiliates
		$this->assertPostAsAccessRedirect(['controller' => 'Affiliates', 'action' => 'delete', '?' => ['affiliate' => $affiliate->id]],
			$admin->id, [], ['controller' => 'Affiliates', 'action' => 'index'],
			'The affiliate has been deleted.');
		// TODOLATER: Add checks for success messages everywhere

		// But not ones with dependencies
		$affiliate = AffiliateFactory::make()->with('Leagues')->persist();
		$this->assertPostAsAccessRedirect(['controller' => 'Affiliates', 'action' => 'delete', '?' => ['affiliate' => $affiliate->id]],
			$admin->id, [], ['controller' => 'Affiliates', 'action' => 'index'],
			'#The following records reference this affiliate, so it cannot be deleted#');
	}

	/**
	 * Test delete method as others
	 */
	public function testDeleteAsOthers(): void {
		$this->enableSecurityToken();

		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliate = $admin->affiliates[0];

		// Others are not allowed to delete affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Affiliates', 'action' => 'delete', '?' => ['affiliate' => $affiliate->id]], $manager->id);
		$this->assertPostAsAccessDenied(['controller' => 'Affiliates', 'action' => 'delete', '?' => ['affiliate' => $affiliate->id]], $volunteer->id);
		$this->assertPostAsAccessDenied(['controller' => 'Affiliates', 'action' => 'delete', '?' => ['affiliate' => $affiliate->id]], $player->id);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Affiliates', 'action' => 'delete', '?' => ['affiliate' => $affiliate->id]]);
	}

	/**
	 * Test add_manager method as an admin
	 */
	public function testAddManagerAsAdmin(): void {
		$this->enableSecurityToken();

		// We don't use the DiverseUsersScenario here, as that creates the manager user already managing the affiliate
		$affiliate = AffiliateFactory::make()->persist();
		$admin = PersonFactory::make()->admin()->with('Affiliates', $affiliate)->persist();
		// TODOLATER: Shouldn't need gender fields to be specified for non-players
		$manager = PersonFactory::make()->manager(['gender' => 'Woman', 'roster_designation' => 'Woman'])->with('Affiliates', $affiliate)->persist();
		$volunteer = PersonFactory::make()->volunteer()->with('Affiliates', $affiliate)->persist();

		// Admins are allowed to add managers
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'add_manager', '?' => ['affiliate' => $affiliate->id]], $admin->id);

		// Try the search page for an ineligible person
		$this->assertPostAsAccessOk(['controller' => 'Affiliates', 'action' => 'add_manager', '?' => ['affiliate' => $affiliate->id]],
			$admin->id, [
				'affiliate_id' => $affiliate->id,
				'first_name' => $volunteer->first_name,
				'last_name' => '',
				'sort' => 'last_name',
				'direction' => 'asc',
			]
		);
		$this->assertResponseContains('showing 0 records out of 0 total');

		// Try someone that is eligible
		$this->assertPostAsAccessOk(['controller' => 'Affiliates', 'action' => 'add_manager', '?' => ['affiliate' => $affiliate->id]],
			$admin->id, [
				'affiliate_id' => $affiliate->id,
				'first_name' => $manager->first_name,
				'last_name' => '',
				'sort' => 'last_name',
				'direction' => 'asc',
			]
		);
		$this->assertResponseContains('showing 1 records out of 1 total');
		$return = urlencode(\App\Lib\base64_url_encode(Configure::read('App.base') . '/affiliates/add_manager?affiliate=' . $affiliate->id));
		$this->assertResponseContains('/affiliates/add_manager?person=' . $manager->id . '&amp;return=' . $return . '&amp;affiliate=' . $affiliate->id);

		// Try to add the manager
		$this->assertGetAsAccessRedirect(['controller' => 'Affiliates', 'action' => 'add_manager', '?' => ['person' => $manager->id, 'affiliate' => $affiliate->id]],
			$admin->id, ['controller' => 'Affiliates', 'action' => 'view', '?' => ['affiliate' => $affiliate->id]],
			'Added ' . $manager->full_name . ' as manager.');

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'view', '?' => ['affiliate' => $affiliate->id]], $admin->id);
		$this->assertResponseContains('/affiliates/remove_manager?affiliate=' . $affiliate->id . '&amp;person=' . $manager->id);
	}

	/**
	 * Test add_manager method as others
	 */
	public function testAddManagerAsOthers(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliate = $admin->affiliates[0];

		// Others are not allowed to add managers
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'add_manager', '?' => ['affiliate' => $affiliate->id]], $manager->id);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'add_manager', '?' => ['affiliate' => $affiliate->id]], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'add_manager', '?' => ['affiliate' => $affiliate->id]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Affiliates', 'action' => 'add_manager', '?' => ['affiliate' => $affiliate->id]]);
	}

	/**
	 * Test remove_manager method as an admin
	 */
	public function testRemoveManagerAsAdmin(): void {
		$this->enableSecurityToken();

		$affiliate = AffiliateFactory::make()->persist();
		$admin = PersonFactory::make()->admin()->with('Affiliates', $affiliate)->persist();
		$manager = PersonFactory::make()->manager()
			->with('Affiliates', $affiliate)
			->with('AffiliatesPeople', AffiliatesPersonFactory::make(['position' => 'manager', 'affiliate_id' => $affiliate->id]))
			->persist();

		// Admins are allowed to remove managers
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'view', '?' => ['affiliate' => $affiliate->id]], $admin->id);
		$this->assertResponseContains('/affiliates/remove_manager?affiliate=' . $affiliate->id . '&amp;person=' . $manager->id);

		$this->assertPostAsAccessRedirect(['controller' => 'Affiliates', 'action' => 'remove_manager', '?' => ['affiliate' => $affiliate->id, 'person' => $manager->id]],
			$admin->id, [], ['controller' => 'Affiliates', 'action' => 'view', '?' => ['affiliate' => $affiliate->id]],
			'Successfully removed manager.');
		$this->assertFlashMessage('If this person is no longer going to be managing anything, you should also edit their profile and deselect the "Manager" option.');

		// Make sure they were removed successfully
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'view', '?' => ['affiliate' => $affiliate->id]], $admin->id);
		$this->assertResponseNotContains('/affiliates/remove_manager?affiliate=' . $affiliate->id . '&amp;person=' . $manager->id);
	}

	/**
	 * Test remove_manager method as others
	 */
	public function testRemoveManagerAsOthers(): void {
		$this->enableSecurityToken();

		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliate = $admin->affiliates[0];

		// Others are not allowed to remove managers
		$this->assertPostAsAccessDenied(['controller' => 'Affiliates', 'action' => 'remove_manager', '?' => ['affiliate' => $affiliate->id, 'person' => $manager->id]], $manager->id);
		$this->assertPostAsAccessDenied(['controller' => 'Affiliates', 'action' => 'remove_manager', '?' => ['affiliate' => $affiliate->id, 'person' => $manager->id]], $volunteer->id);
		$this->assertPostAsAccessDenied(['controller' => 'Affiliates', 'action' => 'remove_manager', '?' => ['affiliate' => $affiliate->id, 'person' => $manager->id]], $player->id);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Affiliates', 'action' => 'remove_manager', '?' => ['affiliate' => $affiliate->id, 'person' => $manager->id]]);
	}

	/**
	 * Test select method
	 */
	public function testSelect(): void {
		$this->enableSecurityToken();

		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		// Anyone logged in is allowed to select their affiliate(s) for this session
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'select'], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'select'], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'select'], $volunteer->id);
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'select'], $player->id);
		$this->assertResponseContains('<option value="' . $affiliates[0]->id . '">' . $affiliates[0]->name . '</option><option value="' . $affiliates[1]->id . '">' . $affiliates[1]->name . '</option>');
		$this->assertPostAsAccessRedirect(['controller' => 'Affiliates', 'action' => 'select'],
			$player->id, [
				'affiliate' => $affiliates[0]->id,
			], '/', false);
		$this->assertSession((string)$affiliates[0]->id, 'Zuluru.CurrentAffiliate');

		// Others are not allowed to select affiliates
		$this->assertGetAnonymousAccessDenied(['controller' => 'Affiliates', 'action' => 'select']);
	}

	/**
	 * Test view_all method
	 */
	public function testViewAll(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliate = $admin->affiliates[0];

		// Anyone logged in is allowed to reset their affiliate selection for this session
		$this->session(['Zuluru.CurrentAffiliate' => $affiliate->id]);
		$this->assertGetAsAccessRedirect(['controller' => 'Affiliates', 'action' => 'view_all'],
			$admin->id, '/', false);
		$this->assertSession(null, 'Zuluru.CurrentAffiliate');

		$this->session(['Zuluru.CurrentAffiliate' => $affiliate->id]);
		$this->assertGetAsAccessRedirect(['controller' => 'Affiliates', 'action' => 'view_all'],
			$manager->id, '/', false);
		$this->assertSession(null, 'Zuluru.CurrentAffiliate');

		$this->session(['Zuluru.CurrentAffiliate' => $affiliate->id]);
		$this->assertGetAsAccessRedirect(['controller' => 'Affiliates', 'action' => 'view_all'],
			$volunteer->id, '/', false);
		$this->assertSession(null, 'Zuluru.CurrentAffiliate');

		$this->session(['Zuluru.CurrentAffiliate' => $affiliate->id]);
		$this->assertGetAsAccessRedirect(['controller' => 'Affiliates', 'action' => 'view_all'],
			$player->id, '/', false);
		$this->assertSession(null, 'Zuluru.CurrentAffiliate');

		// Others are not allowed to view all
		$this->assertGetAnonymousAccessDenied(['controller' => 'Affiliates', 'action' => 'view_all']);
	}

}
