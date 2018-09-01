<?php
namespace App\Test\TestCase\Controller;

/**
 * App\Controller\PricesController Test Case
 */
class PricesControllerTest extends ControllerTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.event_types',
		'app.affiliates',
			'app.users',
				'app.people',
					'app.affiliates_people',
					'app.people_people',
			'app.groups',
				'app.groups_people',
			'app.leagues',
				'app.divisions',
			'app.events',
				'app.prices',
					'app.registrations',
			'app.settings',
	];

	/**
	 * Test delete method as an admin
	 *
	 * @return void
	 */
	public function testDeleteAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to delete prices
		$this->assertAccessRedirect(['controller' => 'Prices', 'action' => 'delete', 'price' => PRICE_ID_LEAGUE_TEAM],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Events', 'action' => 'view', 'event' => EVENT_ID_LEAGUE_TEAM],
			'The price point has been deleted.', 'Flash.flash.0.message');

		// But not the last price on an event (league team 2 will be last, now that league team is gone)
		$this->assertAccessRedirect(['controller' => 'Prices', 'action' => 'delete', 'price' => PRICE_ID_LEAGUE_TEAM2],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Events', 'action' => 'view', 'event' => EVENT_ID_LEAGUE_TEAM],
			'You cannot delete the only price point on an event.', 'Flash.flash.0.message');

		// And not ones with dependencies
		$this->assertAccessRedirect(['controller' => 'Prices', 'action' => 'delete', 'price' => PRICE_ID_MEMBERSHIP],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Events', 'action' => 'view', 'event' => EVENT_ID_MEMBERSHIP],
			'#The following records reference this price point, so it cannot be deleted#', 'Flash.flash.0.message');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers can delete prices in their affiliate
		$this->assertAccessRedirect(['controller' => 'Prices', 'action' => 'delete', 'price' => PRICE_ID_LEAGUE_TEAM],
			PERSON_ID_MANAGER, 'post', [], ['controller' => 'Events', 'action' => 'view', 'event' => EVENT_ID_LEAGUE_TEAM],
			'The price point has been deleted.', 'Flash.flash.0.message');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Prices', 'action' => 'delete', 'price' => PRICE_ID_LEAGUE_INDIVIDUAL_SUB],
			PERSON_ID_MANAGER, 'post');
	}

	/**
	 * Test delete method as a coordinator
	 *
	 * @return void
	 */
	public function testDeleteAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete method as a captain
	 *
	 * @return void
	 */
	public function testDeleteAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete method as a player
	 *
	 * @return void
	 */
	public function testDeleteAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete method as someone else
	 *
	 * @return void
	 */
	public function testDeleteAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete method without being logged in
	 *
	 * @return void
	 */
	public function testDeleteAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
