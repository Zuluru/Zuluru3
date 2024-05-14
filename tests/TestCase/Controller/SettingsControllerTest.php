<?php
namespace App\Test\TestCase\Controller;

use App\Test\Scenario\DiverseUsersScenario;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\SettingsController Test Case
 */
class SettingsControllerTest extends ControllerTestCase {

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
	 * Test edit method as an admin
	 */
	public function testEditAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		// Admins are allowed to edit settings
		$this->assertGetAsAccessOk(['controller' => 'Settings', 'action' => 'edit', 'organization'], $admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test edit method as a manager
	 */
	public function testEditAsManager(): void {
		[$manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['manager']);

		// Managers are allowed to edit settings
		$this->assertGetAsAccessOk(['controller' => 'Settings', 'action' => 'edit', 'organization'], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test edit method as others
	 */
	public function testEditAsOthers(): void {
		[$volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['volunteer', 'player']);

		// Others are not allowed to edit
		$this->assertGetAsAccessDenied(['controller' => 'Settings', 'action' => 'edit', 'organization'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Settings', 'action' => 'edit', 'organization'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Settings', 'action' => 'edit', 'organization']);
	}

}
