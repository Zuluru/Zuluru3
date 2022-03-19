<?php
namespace App\Test\TestCase\Controller;

use App\Model\Entity\Event;
use App\Test\Factory\EventFactory;
use App\Test\Factory\RegistrationFactory;
use App\Test\Scenario\DiverseUsersScenario;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\PricesController Test Case
 */
class PricesControllerTest extends ControllerTestCase {

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
	 * Test delete method as an admin
	 *
	 * @return void
	 */
	public function testDeleteAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var Event $event */
		$event = EventFactory::make(['affiliate_id' => $affiliates[0]->id])
			->with('Prices[2]')
			->persist();

		/** @var Event $single_event */
		$single_event = EventFactory::make(['affiliate_id' => $affiliates[0]->id])
			->with('Prices')
			->persist();

		/** @var Event $dependent_event */
		$dependent_event = EventFactory::make(['affiliate_id' => $affiliates[0]->id])
			->with('Prices')
			->persist();
		RegistrationFactory::make(['event_id' => $dependent_event->id, 'price_id' => $dependent_event->prices[0]->id])
			->persist();

		// Admins are allowed to delete prices
		$this->assertPostAsAccessRedirect(['controller' => 'Prices', 'action' => 'delete', 'price' => $event->prices[0]->id],
			$admin->id, [], ['controller' => 'Events', 'action' => 'view', 'event' => $event->id],
			'The price point has been deleted.');

		// But not the last price on an event
		$this->assertPostAsAccessRedirect(['controller' => 'Prices', 'action' => 'delete', 'price' => $single_event->prices[0]->id],
			$admin->id, [], ['controller' => 'Events', 'action' => 'view', 'event' => $single_event->id],
			'You cannot delete the only price point on an event.');

		// And not ones with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'Prices', 'action' => 'delete', 'price' => $dependent_event->prices[0]->id],
			$admin->id, [], ['controller' => 'Events', 'action' => 'view', 'event' => $dependent_event->id],
			'#The following records reference this price point, so it cannot be deleted#');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var Event $event */
		$event = EventFactory::make(['affiliate_id' => $affiliates[0]->id])
			->with('Prices[2]')
			->persist();

		/** @var Event $other_event */
		$other_event = EventFactory::make(['affiliate_id' => $affiliates[1]->id])
			->with('Prices[2]')
			->persist();

		// Managers are allowed to delete prices in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Prices', 'action' => 'delete', 'price' => $event->prices[0]->id],
			$manager->id, [], ['controller' => 'Events', 'action' => 'view', 'event' => $event->id],
			'The price point has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Prices', 'action' => 'delete', 'price' => $other_event->prices[0]->id],
			$manager->id);
	}

	/**
	 * Test delete method as others
	 *
	 * @return void
	 */
	public function testDeleteAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var Event $event */
		$event = EventFactory::make(['affiliate_id' => $affiliates[0]->id])
			->with('Prices[2]')
			->persist();

		// Others are not allowed to delete prices
		$this->assertPostAsAccessDenied(['controller' => 'Prices', 'action' => 'delete', 'price' => $event->prices[0]->id],
			$volunteer->id);
		$this->assertPostAsAccessDenied(['controller' => 'Prices', 'action' => 'delete', 'price' => $event->prices[0]->id],
			$player->id);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Prices', 'action' => 'delete', 'price' => $event->prices[0]->id]);
	}

}
