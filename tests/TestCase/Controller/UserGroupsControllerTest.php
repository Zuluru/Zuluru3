<?php
namespace App\Test\TestCase\Controller;

use App\Test\Scenario\DiverseUsersScenario;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\UserGroupsController Test Case
 */
class UserGroupsControllerTest extends ControllerTestCase {

	use ScenarioAwareTrait;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.UserGroups',
		'app.Settings',
	];

	/**
	 * Test index method
	 */
	public function testIndex(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Admins are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'UserGroups', 'action' => 'index'], $admin->id);
		$this->assertResponseContains('/user_groups/deactivate?group=' . GROUP_PLAYER);
		$this->assertResponseContains('/user_groups/activate?group=' . GROUP_OFFICIAL);

		// Others are not allowed to see the index
		$this->assertGetAsAccessDenied(['controller' => 'UserGroups', 'action' => 'index'], $manager->id);
		$this->assertGetAsAccessDenied(['controller' => 'UserGroups', 'action' => 'index'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'UserGroups', 'action' => 'index'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'UserGroups', 'action' => 'index']);
	}

	/**
	 * Test activate method as an admin
	 */
	public function testActivateAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		// Admins are allowed to activate groups
		$this->assertGetAjaxAsAccessOk(['controller' => 'UserGroups', 'action' => 'activate', '?' => ['group' => GROUP_OFFICIAL]],
			$admin->id);
		$this->assertResponseContains('/user_groups\\/deactivate?group=' . GROUP_OFFICIAL);
	}

	/**
	 * Test activate method as others
	 */
	public function testActivateAsOthers(): void {
		[$manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['manager', 'volunteer', 'player']);

		// Others are not allowed to activate groups
		$this->assertGetAjaxAsAccessDenied(['controller' => 'UserGroups', 'action' => 'activate', '?' => ['group' => GROUP_OFFICIAL]],
			$manager->id);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'UserGroups', 'action' => 'activate', '?' => ['group' => GROUP_OFFICIAL]],
			$volunteer->id);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'UserGroups', 'action' => 'activate', '?' => ['group' => GROUP_OFFICIAL]],
			$player->id);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'UserGroups', 'action' => 'activate', '?' => ['group' => GROUP_OFFICIAL]]);
	}

	/**
	 * Test deactivate method as an admin
	 */
	public function testDeactivateAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		// Admins are allowed to deactivate groups
		$this->assertGetAjaxAsAccessOk(['controller' => 'UserGroups', 'action' => 'deactivate', '?' => ['group' => GROUP_VOLUNTEER]],
			$admin->id);
		$this->assertResponseContains('/user_groups\\/activate?group=' . GROUP_VOLUNTEER);
	}

	/**
	 * Test deactivate method as others
	 */
	public function testDeactivateAsOthers(): void {
		[$manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['manager', 'volunteer', 'player']);

		// Others are not allowed to deactivate groups
		$this->assertGetAjaxAsAccessDenied(['controller' => 'UserGroups', 'action' => 'deactivate', '?' => ['group' => GROUP_VOLUNTEER]],
			$manager->id);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'UserGroups', 'action' => 'deactivate', '?' => ['group' => GROUP_VOLUNTEER]],
			$volunteer->id);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'UserGroups', 'action' => 'deactivate', '?' => ['group' => GROUP_VOLUNTEER]],
			$player->id);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'UserGroups', 'action' => 'deactivate', '?' => ['group' => GROUP_VOLUNTEER]]);
	}

}
