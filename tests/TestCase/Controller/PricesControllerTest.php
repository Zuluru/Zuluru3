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
					'app.teams',
					'app.divisions_people',
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
		$this->assertPostAsAccessRedirect(['controller' => 'Prices', 'action' => 'delete', 'price' => PRICE_ID_LEAGUE_TEAM],
			PERSON_ID_ADMIN, [], ['controller' => 'Events', 'action' => 'view', 'event' => EVENT_ID_LEAGUE_TEAM],
			'The price point has been deleted.');

		// But not the last price on an event (league team 2 will be last, now that league team is gone)
		$this->assertPostAsAccessRedirect(['controller' => 'Prices', 'action' => 'delete', 'price' => PRICE_ID_LEAGUE_TEAM2],
			PERSON_ID_ADMIN, [], ['controller' => 'Events', 'action' => 'view', 'event' => EVENT_ID_LEAGUE_TEAM],
			'You cannot delete the only price point on an event.');

		// And not ones with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'Prices', 'action' => 'delete', 'price' => PRICE_ID_MEMBERSHIP],
			PERSON_ID_ADMIN, [], ['controller' => 'Events', 'action' => 'view', 'event' => EVENT_ID_MEMBERSHIP],
			'#The following records reference this price point, so it cannot be deleted#');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to delete prices in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Prices', 'action' => 'delete', 'price' => PRICE_ID_LEAGUE_TEAM],
			PERSON_ID_MANAGER, [], ['controller' => 'Events', 'action' => 'view', 'event' => EVENT_ID_LEAGUE_TEAM],
			'The price point has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Prices', 'action' => 'delete', 'price' => PRICE_ID_LEAGUE_INDIVIDUAL_SUB],
			PERSON_ID_MANAGER);
	}

	/**
	 * Test delete method as others
	 *
	 * @return void
	 */
	public function testDeleteAsOthers() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Others are not allowed to delete prices
		$this->assertPostAsAccessDenied(['controller' => 'Prices', 'action' => 'delete', 'price' => PRICE_ID_LEAGUE_TEAM],
			PERSON_ID_COORDINATOR);
		$this->assertPostAsAccessDenied(['controller' => 'Prices', 'action' => 'delete', 'price' => PRICE_ID_LEAGUE_TEAM],
			PERSON_ID_CAPTAIN);
		$this->assertPostAsAccessDenied(['controller' => 'Prices', 'action' => 'delete', 'price' => PRICE_ID_LEAGUE_TEAM],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'Prices', 'action' => 'delete', 'price' => PRICE_ID_LEAGUE_TEAM],
			PERSON_ID_VISITOR);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Prices', 'action' => 'delete', 'price' => PRICE_ID_LEAGUE_TEAM]);
	}

}
