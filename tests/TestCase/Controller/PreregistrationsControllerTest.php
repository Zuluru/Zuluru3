<?php
namespace App\Test\TestCase\Controller;

use App\Test\Factory\PreregistrationFactory;
use App\Test\Scenario\DiverseUsersScenario;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\PreregistrationsController Test Case
 */
class PreregistrationsControllerTest extends ControllerTestCase {

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
		$affiliates = $admin->affiliates;

		$prereg = PreregistrationFactory::make()
			->with('Events', ['affiliate_id' => $affiliates[0]->id])
			->with('People', $player)
			->persist();

		$affiliate_prereg = PreregistrationFactory::make()
			->with('Events', ['affiliate_id' => $affiliates[1]->id])
			->with('People', $player)
			->persist();

		// Admins are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Preregistrations', 'action' => 'index'], $admin->id);
		$this->assertResponseContains('/preregistrations/delete?preregistration=' . $prereg->id);
		$this->assertResponseContains('/preregistrations/delete?preregistration=' . $affiliate_prereg->id);

		// Managers are allowed to see the index, but don't see preregistrations in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'Preregistrations', 'action' => 'index'], $manager->id);
		$this->assertResponseContains('/preregistrations/delete?preregistration=' . $prereg->id);
		$this->assertResponseNotContains('/preregistrations/delete?preregistration=' . $affiliate_prereg->id);

		// Others are not allowed to see the index
		$this->assertGetAsAccessDenied(['controller' => 'Preregistrations', 'action' => 'index'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Preregistrations', 'action' => 'index'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Preregistrations', 'action' => 'index']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add method as an admin
	 */
	public function testAddAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		// Admins are allowed to add preregistrations
		$this->assertGetAsAccessOk(['controller' => 'Preregistrations', 'action' => 'add'], $admin->id);
	}

	/**
	 * Test add method as a manager
	 */
	public function testAddAsManager(): void {
		[$manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['manager']);

		// Managers are allowed to add preregistrations
		$this->assertGetAsAccessOk(['controller' => 'Preregistrations', 'action' => 'add'], $manager->id);
	}

	/**
	 * Test add method as others
	 */
	public function testAddAsOthers(): void {
		[$volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['volunteer', 'player']);

		// Others are not allowed to add preregistrations
		$this->assertGetAsAccessDenied(['controller' => 'Preregistrations', 'action' => 'add'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Preregistrations', 'action' => 'add'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Preregistrations', 'action' => 'add']);
	}

	/**
	 * Test delete method as an admin
	 */
	public function testDeleteAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);
		$affiliates = $admin->affiliates;

		$prereg = PreregistrationFactory::make()
			->with('Events', ['affiliate_id' => $affiliates[0]->id])
			->with('People', $player)
			->persist();

		// Admins are allowed to delete preregistrations
		$this->assertPostAsAccessRedirect(['controller' => 'Preregistrations', 'action' => 'delete', 'preregistration' => $prereg->id],
			$admin->id, [], ['controller' => 'Preregistrations', 'action' => 'index'],
			'The preregistration has been deleted.');
	}

	/**
	 * Test delete method as a manager
	 */
	public function testDeleteAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager', 'player']);
		$affiliates = $admin->affiliates;

		$prereg = PreregistrationFactory::make()
			->with('Events', ['affiliate_id' => $affiliates[0]->id])
			->with('People', $player)
			->persist();

		$affiliate_prereg = PreregistrationFactory::make()
			->with('Events', ['affiliate_id' => $affiliates[1]->id])
			->with('People', $player)
			->persist();

		// Managers are allowed to delete preregistrations in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Preregistrations', 'action' => 'delete', 'preregistration' => $prereg->id],
			$manager->id, [], ['controller' => 'Preregistrations', 'action' => 'index'],
			'The preregistration has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Preregistrations', 'action' => 'delete', 'preregistration' => $affiliate_prereg->id],
			$manager->id);
	}

	/**
	 * Test delete method as others
	 */
	public function testDeleteAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);
		$affiliates = $admin->affiliates;

		$prereg = PreregistrationFactory::make()
			->with('Events', ['affiliate_id' => $affiliates[0]->id])
			->with('People', $admin)
			->persist();

		// Others are not allowed to delete preregistrations
		$this->assertPostAsAccessDenied(['controller' => 'Preregistrations', 'action' => 'delete', 'preregistration' => $prereg->id],
			$volunteer->id);
		$this->assertPostAsAccessDenied(['controller' => 'Preregistrations', 'action' => 'delete', 'preregistration' => $prereg->id],
			$player->id);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Preregistrations', 'action' => 'delete', 'preregistration' => $prereg->id]);

		// Except for their own
		$affiliate_prereg = PreregistrationFactory::make()
			->with('Events', ['affiliate_id' => $affiliates[1]->id])
			->with('People', $player)
			->persist();

		$this->assertPostAsAccessRedirect(['controller' => 'Preregistrations', 'action' => 'delete', 'preregistration' => $affiliate_prereg->id],
			$player->id, [], ['controller' => 'Preregistrations', 'action' => 'index'],
			'The preregistration has been deleted.');
	}

}
