<?php
namespace App\Test\TestCase\Controller;

use App\Test\Scenario\DiverseUsersScenario;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\NoticesController Test Case
 */
class NoticesControllerTest extends ControllerTestCase {

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
	 * Test viewed method
	 */
	public function testViewed(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Everyone is allowed to mark a notice as viewed
		$this->assertGetAsAccessOk(['controller' => 'Notices', 'action' => 'viewed', 1], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Notices', 'action' => 'viewed', 1], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'Notices', 'action' => 'viewed', 1], $volunteer->id);
		$this->assertGetAsAccessOk(['controller' => 'Notices', 'action' => 'viewed', 1], $player->id);
		$this->assertGetAnonymousAccessOk(['controller' => 'Notices', 'action' => 'viewed', 1]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

}
