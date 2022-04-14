<?php
namespace App\Test\TestCase\Controller;

use App\Test\Scenario\DiverseUsersScenario;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\PagesController Test Case
 */
class PagesControllerTest extends ControllerTestCase {

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
	 * Test display method
	 */
	public function testDisplay(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Anyone is allowed to display pages
		$this->assertGetAsAccessOk(['controller' => 'Pages', 'action' => 'display', 'privacy'], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Pages', 'action' => 'display', 'privacy'], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'Pages', 'action' => 'display', 'privacy'], $volunteer->id);
		$this->assertGetAsAccessOk(['controller' => 'Pages', 'action' => 'display', 'privacy'], $player->id);
		$this->assertGetAnonymousAccessOk(['controller' => 'Pages', 'action' => 'display', 'privacy']);
	}

}
