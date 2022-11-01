<?php
namespace App\Test\TestCase\Controller;

use App\Test\Scenario\DiverseUsersScenario;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\HelpController Test Case
 */
class HelpControllerTest extends ControllerTestCase {

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
	 * Test view method
	 */
	public function testView(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Anyone is allowed to view
		$this->assertGetAsAccessOk(['controller' => 'Help', 'action' => 'view'], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Help', 'action' => 'view'], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'Help', 'action' => 'view'], $volunteer->id);
		$this->assertGetAsAccessOk(['controller' => 'Help', 'action' => 'view'], $player->id);
		$this->assertGetAnonymousAccessOk(['controller' => 'Help', 'action' => 'view']);
	}

}
