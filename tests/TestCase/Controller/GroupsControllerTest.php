<?php
namespace App\Test\TestCase\Controller;

use App\Test\Scenario\DiverseUsersScenario;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\GroupsController Test Case
 */
class GroupsControllerTest extends ControllerTestCase {

	use ScenarioAwareTrait;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.Groups',
		'app.Settings',
	];

	/**
	 * Test index method
	 */
	public function testIndex(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Admins are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Groups', 'action' => 'index'], $admin->id);
		$this->assertResponseContains('/groups/deactivate?group=' . GROUP_PLAYER);
		$this->assertResponseContains('/groups/activate?group=' . GROUP_OFFICIAL);

		// Others are not allowed to see the index
		$this->assertGetAsAccessDenied(['controller' => 'Groups', 'action' => 'index'], $manager->id);
		$this->assertGetAsAccessDenied(['controller' => 'Groups', 'action' => 'index'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Groups', 'action' => 'index'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Groups', 'action' => 'index']);
	}

	/**
	 * Test activate method as an admin
	 */
	public function testActivateAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		// Admins are allowed to activate groups
		$this->assertGetAjaxAsAccessOk(['controller' => 'Groups', 'action' => 'activate', 'group' => GROUP_OFFICIAL],
			$admin->id);
		$this->assertResponseContains('/groups\\/deactivate?group=' . GROUP_OFFICIAL);
	}

	/**
	 * Test activate method as others
	 */
	public function testActivateAsOthers(): void {
		[$manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['manager', 'volunteer', 'player']);

		// Others are not allowed to activate groups
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Groups', 'action' => 'activate', 'group' => GROUP_OFFICIAL],
			$manager->id);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Groups', 'action' => 'activate', 'group' => GROUP_OFFICIAL],
			$volunteer->id);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Groups', 'action' => 'activate', 'group' => GROUP_OFFICIAL],
			$player->id);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Groups', 'action' => 'activate', 'group' => GROUP_OFFICIAL]);
	}

	/**
	 * Test deactivate method as an admin
	 */
	public function testDeactivateAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		// Admins are allowed to deactivate groups
		$this->assertGetAjaxAsAccessOk(['controller' => 'Groups', 'action' => 'deactivate', 'group' => GROUP_VOLUNTEER],
			$admin->id);
		$this->assertResponseContains('/groups\\/activate?group=' . GROUP_VOLUNTEER);
	}

	/**
	 * Test deactivate method as others
	 */
	public function testDeactivateAsOthers(): void {
		[$manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['manager', 'volunteer', 'player']);

		// Others are not allowed to deactivate groups
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Groups', 'action' => 'deactivate', 'group' => GROUP_VOLUNTEER],
			$manager->id);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Groups', 'action' => 'deactivate', 'group' => GROUP_VOLUNTEER],
			$volunteer->id);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Groups', 'action' => 'deactivate', 'group' => GROUP_VOLUNTEER],
			$player->id);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Groups', 'action' => 'deactivate', 'group' => GROUP_VOLUNTEER]);
	}

}
