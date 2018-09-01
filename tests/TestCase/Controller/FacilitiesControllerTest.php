<?php
namespace App\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

/**
 * App\Controller\FacilitiesController Test Case
 */
class FacilitiesControllerTest extends ControllerTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.affiliates',
			'app.users',
				'app.people',
					'app.affiliates_people',
					'app.people_people',
			'app.groups',
				'app.groups_people',
			'app.regions',
				'app.facilities',
					'app.fields',
			'app.leagues',
				'app.divisions',
					'app.teams',
						'app.teams_facilities',
					'app.game_slots',
					'app.pools',
						'app.pools_teams',
					'app.games',
			'app.notes',
			'app.settings',
	];

	/**
	 * Test index method as an admin
	 *
	 * @return void
	 */
	public function testIndexAsAdmin() {
		// Admins are allowed to get the index
		$this->assertAccessOk(['controller' => 'Facilities', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '#ms');
		$this->assertResponseRegExp('#/maps/view\?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1 . '#ms');
		$this->assertResponseRegExp('#/facilities/edit\?facility=' . FACILITY_ID_SUNNYBROOK . '#ms');
		$this->assertResponseRegExp('#/facilities/delete\?facility=' . FACILITY_ID_SUNNYBROOK . '#ms');
		$this->assertResponseRegExp('#/facilities/close\?facility=' . FACILITY_ID_SUNNYBROOK . '#ms');
		$this->assertResponseRegExp('#/facilities/view\?facility=' . FACILITY_ID_CENTRAL_TECH . '#ms');
		$this->assertResponseRegExp('#/maps/view\?field=' . FIELD_ID_CENTRAL_TECH . '#ms');
		$this->assertResponseRegExp('#/facilities/edit\?facility=' . FACILITY_ID_CENTRAL_TECH . '#ms');
		$this->assertResponseRegExp('#/facilities/delete\?facility=' . FACILITY_ID_CENTRAL_TECH . '#ms');
		$this->assertResponseRegExp('#/facilities/close\?facility=' . FACILITY_ID_CENTRAL_TECH . '#ms');
	}

	/**
	 * Test index method as a manager
	 *
	 * @return void
	 */
	public function testIndexAsManager() {
		// Managers are allowed to get the index, but don't see facilities in other affiliates
		$this->assertAccessOk(['controller' => 'Facilities', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '#ms');
		$this->assertResponseRegExp('#/maps/view\?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1 . '#ms');
		$this->assertResponseRegExp('#/facilities/edit\?facility=' . FACILITY_ID_SUNNYBROOK . '#ms');
		$this->assertResponseRegExp('#/facilities/delete\?facility=' . FACILITY_ID_SUNNYBROOK . '#ms');
		$this->assertResponseRegExp('#/facilities/close\?facility=' . FACILITY_ID_SUNNYBROOK . '#ms');
		$this->assertResponseNotRegExp('#/facilities/view\?facility=' . FACILITY_ID_CENTRAL_TECH . '#ms');
		$this->assertResponseNotRegExp('#/maps/view\?field=' . FIELD_ID_CENTRAL_TECH . '#ms');
		$this->assertResponseNotRegExp('#/facilities/edit\?facility=' . FACILITY_ID_CENTRAL_TECH . '#ms');
		$this->assertResponseNotRegExp('#/facilities/delete\?facility=' . FACILITY_ID_CENTRAL_TECH . '#ms');
		$this->assertResponseNotRegExp('#/facilities/close\?facility=' . FACILITY_ID_CENTRAL_TECH . '#ms');
	}

	/**
	 * Test index method as a coordinator
	 *
	 * @return void
	 */
	public function testIndexAsCoordinator() {
		// Coordinators are allowed to get the index, but no edit options
		$this->assertAccessOk(['controller' => 'Facilities', 'action' => 'index'], PERSON_ID_COORDINATOR);
		$this->assertResponseRegExp('#/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '#ms');
		$this->assertResponseRegExp('#/maps/view\?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1 . '#ms');
		$this->assertResponseNotRegExp('#/facilities/edit\?facility=' . FACILITY_ID_SUNNYBROOK . '#ms');
		$this->assertResponseNotRegExp('#/facilities/delete\?facility=' . FACILITY_ID_SUNNYBROOK . '#ms');
		$this->assertResponseNotRegExp('#/facilities/close\?facility=' . FACILITY_ID_SUNNYBROOK . '#ms');
	}

	/**
	 * Test index method as a captain
	 *
	 * @return void
	 */
	public function testIndexAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test index method as a player
	 *
	 * @return void
	 */
	public function testIndexAsPlayer() {
		// Players are allowed to get the index, but no edit options
		$this->assertAccessOk(['controller' => 'Facilities', 'action' => 'index'], PERSON_ID_DUPLICATE);
		$this->assertResponseRegExp('#/facilities/view\?facility=' . FACILITY_ID_CENTRAL_TECH . '#ms');
		$this->assertResponseRegExp('#/maps/view\?field=' . FIELD_ID_CENTRAL_TECH . '#ms');
		$this->assertResponseNotRegExp('#/facilities/edit\?facility=' . FACILITY_ID_CENTRAL_TECH . '#ms');
		$this->assertResponseNotRegExp('#/facilities/delete\?facility=' . FACILITY_ID_CENTRAL_TECH . '#ms');
		$this->assertResponseNotRegExp('#/facilities/close\?facility=' . FACILITY_ID_CENTRAL_TECH . '#ms');
	}

	/**
	 * Test index method as someone else
	 *
	 * @return void
	 */
	public function testIndexAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test index method without being logged in
	 *
	 * @return void
	 */
	public function testIndexAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test closed method as an admin
	 *
	 * @return void
	 */
	public function testClosedAsAdmin() {
		// Admins are allowed to get the closed index
		$this->assertAccessOk(['controller' => 'Facilities', 'action' => 'closed'], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/facilities/view\?facility=' . FACILITY_ID_MARILYN_BELL . '#ms');
		// This field doesn't have a layout defined yet, so no map link
		$this->assertResponseNotRegExp('#/maps/view\?field=' . FIELD_ID_MARILYN_BELL . '#ms');
		$this->assertResponseRegExp('#/facilities/edit\?facility=' . FACILITY_ID_MARILYN_BELL . '#ms');
		$this->assertResponseRegExp('#/facilities/delete\?facility=' . FACILITY_ID_MARILYN_BELL . '#ms');
		$this->assertResponseRegExp('#/facilities/open\?facility=' . FACILITY_ID_MARILYN_BELL . '#ms');
	}

	/**
	 * Test closed method as a manager
	 *
	 * @return void
	 */
	public function testClosedAsManager() {
		// Managers are allowed to get the closed index
		$this->assertAccessOk(['controller' => 'Facilities', 'action' => 'closed'], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/facilities/view\?facility=' . FACILITY_ID_MARILYN_BELL . '#ms');
		// This field doesn't have a layout defined yet, so no map link
		$this->assertResponseNotRegExp('#/maps/view\?field=' . FIELD_ID_MARILYN_BELL . '#ms');
		$this->assertResponseRegExp('#/facilities/edit\?facility=' . FACILITY_ID_MARILYN_BELL . '#ms');
		$this->assertResponseRegExp('#/facilities/delete\?facility=' . FACILITY_ID_MARILYN_BELL . '#ms');
		$this->assertResponseRegExp('#/facilities/open\?facility=' . FACILITY_ID_MARILYN_BELL . '#ms');
	}

	/**
	 * Test closed method as a coordinator
	 *
	 * @return void
	 */
	public function testClosedAsCoordinator() {
		// Others are not allowed to get the closed index
		$this->assertAccessRedirect(['controller' => 'Facilities', 'action' => 'closed'], PERSON_ID_COORDINATOR);
	}

	/**
	 * Test closed method as a captain
	 *
	 * @return void
	 */
	public function testClosedAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test closed method as a player
	 *
	 * @return void
	 */
	public function testClosedAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test closed method as someone else
	 *
	 * @return void
	 */
	public function testClosedAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test closed method without being logged in
	 *
	 * @return void
	 */
	public function testClosedAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test view method as an admin
	 *
	 * @return void
	 */
	public function testViewAsAdmin() {
		// Admins are allowed to view facilities
		$this->assertAccessOk(['controller' => 'Facilities', 'action' => 'view', 'facility' => FACILITY_ID_SUNNYBROOK], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/facilities/edit\?facility=' . FACILITY_ID_SUNNYBROOK . '#ms');
		$this->assertResponseRegExp('#/facilities/delete\?facility=' . FACILITY_ID_SUNNYBROOK . '#ms');
		$this->assertResponseRegExp('#/maps/view\?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1 . '#ms');
		$this->assertResponseRegExp('#/maps/edit\?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1 . '#ms');
		$this->assertResponseRegExp('#/fields/delete\?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1 . '#ms');
		$this->assertResponseRegExp('#/fields/close\?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1 . '#ms');
		$this->assertResponseRegExp('#/fields/bookings\?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1 . '#ms');
		$this->assertResponseRegExp('#/game_slots/add\?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1 . '#ms');

		$this->assertAccessOk(['controller' => 'Facilities', 'action' => 'view', 'facility' => FACILITY_ID_CENTRAL_TECH], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/facilities/edit\?facility=' . FACILITY_ID_CENTRAL_TECH . '#ms');
		$this->assertResponseRegExp('#/facilities/delete\?facility=' . FACILITY_ID_CENTRAL_TECH . '#ms');
		$this->assertResponseRegExp('#/maps/view\?field=' . FIELD_ID_CENTRAL_TECH . '#ms');
		$this->assertResponseRegExp('#/maps/edit\?field=' . FIELD_ID_CENTRAL_TECH . '#ms');
		$this->assertResponseRegExp('#/fields/delete\?field=' . FIELD_ID_CENTRAL_TECH . '#ms');
		$this->assertResponseRegExp('#/fields/close\?field=' . FIELD_ID_CENTRAL_TECH . '#ms');
		$this->assertResponseRegExp('#/fields/bookings\?field=' . FIELD_ID_CENTRAL_TECH . '#ms');
		$this->assertResponseRegExp('#/game_slots/add\?field=' . FIELD_ID_CENTRAL_TECH . '#ms');

		// Admins are allowed to view closed facilities
		$this->assertAccessOk(['controller' => 'Facilities', 'action' => 'view', 'facility' => FACILITY_ID_MARILYN_BELL], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/facilities/edit\?facility=' . FACILITY_ID_MARILYN_BELL . '#ms');
		$this->assertResponseRegExp('#/facilities/delete\?facility=' . FACILITY_ID_MARILYN_BELL . '#ms');
		// This field doesn't have a layout defined yet, so no map link
		$this->assertResponseNotRegExp('#/maps/view\?field=' . FIELD_ID_MARILYN_BELL . '#ms');
		$this->assertResponseRegExp('#/maps/edit\?field=' . FIELD_ID_MARILYN_BELL . '#ms');
		$this->assertResponseRegExp('#/fields/delete\?field=' . FIELD_ID_MARILYN_BELL . '#ms');
		$this->assertResponseRegExp('#/fields/open\?field=' . FIELD_ID_MARILYN_BELL . '#ms');
		$this->assertResponseRegExp('#/fields/bookings\?field=' . FIELD_ID_MARILYN_BELL . '#ms');
		$this->assertResponseRegExp('#/game_slots/add\?field=' . FIELD_ID_MARILYN_BELL . '#ms');
	}

	/**
	 * Test view method as a manager
	 *
	 * @return void
	 */
	public function testViewAsManager() {
		// Managers are allowed to view facilities
		$this->assertAccessOk(['controller' => 'Facilities', 'action' => 'view', 'facility' => FACILITY_ID_SUNNYBROOK], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/facilities/edit\?facility=' . FACILITY_ID_SUNNYBROOK . '#ms');
		$this->assertResponseRegExp('#/facilities/delete\?facility=' . FACILITY_ID_SUNNYBROOK . '#ms');
		$this->assertResponseRegExp('#/maps/view\?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1 . '#ms');
		$this->assertResponseRegExp('#/maps/edit\?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1 . '#ms');
		$this->assertResponseRegExp('#/fields/delete\?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1 . '#ms');
		$this->assertResponseRegExp('#/fields/close\?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1 . '#ms');
		$this->assertResponseRegExp('#/fields/bookings\?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1 . '#ms');
		$this->assertResponseRegExp('#/game_slots/add\?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1 . '#ms');

		// Managers are allowed to view facilities from other affiliates, but have no edit options
		$this->assertAccessOk(['controller' => 'Facilities', 'action' => 'view', 'facility' => FACILITY_ID_CENTRAL_TECH], PERSON_ID_MANAGER);
		$this->assertResponseNotRegExp('#/facilities/edit\?facility=' . FACILITY_ID_CENTRAL_TECH . '#ms');
		$this->assertResponseNotRegExp('#/facilities/delete\?facility=' . FACILITY_ID_CENTRAL_TECH . '#ms');
		$this->assertResponseRegExp('#/maps/view\?field=' . FIELD_ID_CENTRAL_TECH . '#ms');
		$this->assertResponseNotRegExp('#/maps/edit\?field=' . FIELD_ID_CENTRAL_TECH . '#ms');
		$this->assertResponseNotRegExp('#/fields/delete\?field=' . FIELD_ID_CENTRAL_TECH . '#ms');
		$this->assertResponseNotRegExp('#/fields/close\?field=' . FIELD_ID_CENTRAL_TECH . '#ms');
		$this->assertResponseRegExp('#/fields/bookings\?field=' . FIELD_ID_CENTRAL_TECH . '#ms');
		$this->assertResponseNotRegExp('#/game_slots/add\?field=' . FIELD_ID_CENTRAL_TECH . '#ms');

		// Managers are allowed to view closed facilities
		$this->assertAccessOk(['controller' => 'Facilities', 'action' => 'view', 'facility' => FACILITY_ID_MARILYN_BELL], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/facilities/edit\?facility=' . FACILITY_ID_MARILYN_BELL . '#ms');
		$this->assertResponseRegExp('#/facilities/delete\?facility=' . FACILITY_ID_MARILYN_BELL . '#ms');
		// This field doesn't have a layout defined yet, so no map link
		$this->assertResponseNotRegExp('#/maps/view\?field=' . FIELD_ID_MARILYN_BELL . '#ms');
		$this->assertResponseRegExp('#/maps/edit\?field=' . FIELD_ID_MARILYN_BELL . '#ms');
		$this->assertResponseRegExp('#/fields/delete\?field=' . FIELD_ID_MARILYN_BELL . '#ms');
		$this->assertResponseRegExp('#/fields/open\?field=' . FIELD_ID_MARILYN_BELL . '#ms');
		$this->assertResponseRegExp('#/fields/bookings\?field=' . FIELD_ID_MARILYN_BELL . '#ms');
		$this->assertResponseRegExp('#/game_slots/add\?field=' . FIELD_ID_MARILYN_BELL . '#ms');
	}

	/**
	 * Test view method as a coordinator
	 *
	 * @return void
	 */
	public function testViewAsCoordinator() {
		// Others are allowed to view facilities, but no edit options
		$this->assertAccessOk(['controller' => 'Facilities', 'action' => 'view', 'facility' => FACILITY_ID_SUNNYBROOK], PERSON_ID_COORDINATOR);
		$this->assertResponseNotRegExp('#/facilities/edit\?facility=' . FACILITY_ID_SUNNYBROOK . '#ms');
		$this->assertResponseNotRegExp('#/facilities/delete\?facility=' . FACILITY_ID_SUNNYBROOK . '#ms');
		$this->assertResponseRegExp('#/maps/view\?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1 . '#ms');
		$this->assertResponseNotRegExp('#/maps/edit\?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1 . '#ms');
		$this->assertResponseNotRegExp('#/fields/delete\?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1 . '#ms');
		$this->assertResponseNotRegExp('#/fields/close\?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1 . '#ms');
		$this->assertResponseRegExp('#/fields/bookings\?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1 . '#ms');
		$this->assertResponseNotRegExp('#/game_slots/add\?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1 . '#ms');
	}

	/**
	 * Test view method as a captain
	 *
	 * @return void
	 */
	public function testViewAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test view method as a player
	 *
	 * @return void
	 */
	public function testViewAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test view method as someone else
	 *
	 * @return void
	 */
	public function testViewAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test view method without being logged in
	 *
	 * @return void
	 */
	public function testViewAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as an admin
	 *
	 * @return void
	 */
	public function testAddAsAdmin() {
		// Admins are allowed to add facilities
		$this->assertAccessOk(['controller' => 'Facilities', 'action' => 'add'], PERSON_ID_ADMIN);
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers are allowed to add facilities
		$this->assertAccessOk(['controller' => 'Facilities', 'action' => 'add'], PERSON_ID_MANAGER);
	}

	/**
	 * Test add method as a coordinator
	 *
	 * @return void
	 */
	public function testAddAsCoordinator() {
		// Others are not allowed to add facilities
		$this->assertAccessRedirect(['controller' => 'Facilities', 'action' => 'add'], PERSON_ID_COORDINATOR);
	}

	/**
	 * Test add method as a captain
	 *
	 * @return void
	 */
	public function testAddAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as a player
	 *
	 * @return void
	 */
	public function testAddAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as someone else
	 *
	 * @return void
	 */
	public function testAddAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method without being logged in
	 *
	 * @return void
	 */
	public function testAddAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as an admin
	 *
	 * @return void
	 */
	public function testEditAsAdmin() {
		// Admins are allowed to edit facilities
		$this->assertAccessOk(['controller' => 'Facilities', 'action' => 'edit', 'facility' => FACILITY_ID_SUNNYBROOK], PERSON_ID_ADMIN);
		$this->assertAccessOk(['controller' => 'Facilities', 'action' => 'edit', 'facility' => FACILITY_ID_CENTRAL_TECH], PERSON_ID_ADMIN);
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers are allowed to edit facilities
		$this->assertAccessOk(['controller' => 'Facilities', 'action' => 'edit', 'facility' => FACILITY_ID_SUNNYBROOK], PERSON_ID_MANAGER);

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Facilities', 'action' => 'edit', 'facility' => FACILITY_ID_CENTRAL_TECH], PERSON_ID_MANAGER);
	}

	/**
	 * Test edit method as a coordinator
	 *
	 * @return void
	 */
	public function testEditAsCoordinator() {
		// Others are not allowed to edit facilities
		$this->assertAccessRedirect(['controller' => 'Facilities', 'action' => 'edit', 'facility' => FACILITY_ID_SUNNYBROOK], PERSON_ID_COORDINATOR);
	}

	/**
	 * Test edit method as a captain
	 *
	 * @return void
	 */
	public function testEditAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as a player
	 *
	 * @return void
	 */
	public function testEditAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as someone else
	 *
	 * @return void
	 */
	public function testEditAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method without being logged in
	 *
	 * @return void
	 */
	public function testEditAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_field method as an admin
	 *
	 * @return void
	 */
	public function testAddFieldAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_field method as a manager
	 *
	 * @return void
	 */
	public function testAddFieldAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_field method as a coordinator
	 *
	 * @return void
	 */
	public function testAddFieldAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_field method as a captain
	 *
	 * @return void
	 */
	public function testAddFieldAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_field method as a player
	 *
	 * @return void
	 */
	public function testAddFieldAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_field method as someone else
	 *
	 * @return void
	 */
	public function testAddFieldAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_field method without being logged in
	 *
	 * @return void
	 */
	public function testAddFieldAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test open method as an admin
	 *
	 * @return void
	 */
	public function testOpenAsAdmin() {
		// Admins are allowed to open facilities
		$this->assertAccessOk(['controller' => 'Facilities', 'action' => 'open', 'facility' => FACILITY_ID_MARILYN_BELL], PERSON_ID_ADMIN, 'getajax');
		$this->assertResponseRegExp('#/facilities\\\\/close\?facility=' . FACILITY_ID_MARILYN_BELL . '#ms');
		// Confirm that all related fields remain closed
		$fields = TableRegistry::get('Fields');
		$query = $fields->find()->where(['facility_id' => FACILITY_ID_MARILYN_BELL, 'is_open' => true]);
		$this->assertEquals(0, $query->count());
	}

	/**
	 * Test open method as a manager
	 *
	 * @return void
	 */
	public function testOpenAsManager() {
		// Managers are allowed to open facilities
		$this->assertAccessOk(['controller' => 'Facilities', 'action' => 'open', 'facility' => FACILITY_ID_MARILYN_BELL], PERSON_ID_MANAGER, 'getajax');
		$this->assertResponseRegExp('#/facilities\\\\/close\?facility=' . FACILITY_ID_MARILYN_BELL . '#ms');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Facilities', 'action' => 'open', 'facility' => FACILITY_ID_CENTRAL_TECH], PERSON_ID_MANAGER, 'getajax');
	}

	/**
	 * Test open method as a coordinator
	 *
	 * @return void
	 */
	public function testOpenAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test open method as a captain
	 *
	 * @return void
	 */
	public function testOpenAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test open method as a player
	 *
	 * @return void
	 */
	public function testOpenAsPlayer() {
		// Others are not allowed to open facilities
		$this->assertAccessRedirect(['controller' => 'Facilities', 'action' => 'open', 'facility' => FACILITY_ID_MARILYN_BELL], PERSON_ID_PLAYER, 'getajax');
	}

	/**
	 * Test open method as someone else
	 *
	 * @return void
	 */
	public function testOpenAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test open method without being logged in
	 *
	 * @return void
	 */
	public function testOpenAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test close method as an admin
	 *
	 * @return void
	 */
	public function testCloseAsAdmin() {
		// Admins are allowed to close facilities
		$this->assertAccessOk(['controller' => 'Facilities', 'action' => 'close', 'facility' => FACILITY_ID_SUNNYBROOK], PERSON_ID_ADMIN, 'getajax');
		$this->assertResponseRegExp('#/facilities\\\\/open\?facility=' . FACILITY_ID_SUNNYBROOK . '#ms');
		// Confirm that all related fields were also closed
		$fields = TableRegistry::get('Fields');
		$query = $fields->find()->where(['facility_id' => FACILITY_ID_SUNNYBROOK, 'is_open' => true]);
		$this->assertEquals(0, $query->count());
	}

	/**
	 * Test close method as a manager
	 *
	 * @return void
	 */
	public function testCloseAsManager() {
		// Managers are allowed to close facilities
		$this->assertAccessOk(['controller' => 'Facilities', 'action' => 'close', 'facility' => FACILITY_ID_SUNNYBROOK], PERSON_ID_MANAGER, 'getajax');
		$this->assertResponseRegExp('#/facilities\\\\/open\?facility=' . FACILITY_ID_SUNNYBROOK . '#ms');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Facilities', 'action' => 'close', 'facility' => FACILITY_ID_CENTRAL_TECH], PERSON_ID_MANAGER, 'getajax');
	}

	/**
	 * Test close method as a coordinator
	 *
	 * @return void
	 */
	public function testCloseAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test close method as a captain
	 *
	 * @return void
	 */
	public function testCloseAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test close method as a player
	 *
	 * @return void
	 */
	public function testCloseAsPlayer() {
		// Others are not allowed to close facilities
		$this->assertAccessRedirect(['controller' => 'Facilities', 'action' => 'close', 'facility' => FACILITY_ID_SUNNYBROOK], PERSON_ID_PLAYER, 'getajax');
	}

	/**
	 * Test close method as someone else
	 *
	 * @return void
	 */
	public function testCloseAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test close method without being logged in
	 *
	 * @return void
	 */
	public function testCloseAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete method as an admin
	 *
	 * @return void
	 */
	public function testDeleteAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to delete facilities
		$this->assertAccessRedirect(['controller' => 'Facilities', 'action' => 'delete', 'facility' => FACILITY_ID_BLOOR],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Facilities', 'action' => 'index'],
			'The facility has been deleted.', 'Flash.flash.0.message');

		// But not ones with dependencies
		$this->assertAccessRedirect(['controller' => 'Facilities', 'action' => 'delete', 'facility' => FACILITY_ID_SUNNYBROOK],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Facilities', 'action' => 'index'],
			'#The following records reference this facility, so it cannot be deleted#', 'Flash.flash.0.message');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers can delete facilities in their affiliate
		$this->assertAccessRedirect(['controller' => 'Facilities', 'action' => 'delete', 'facility' => FACILITY_ID_BLOOR],
			PERSON_ID_MANAGER, 'post', [], ['controller' => 'Facilities', 'action' => 'index'],
			'The facility has been deleted.', 'Flash.flash.0.message');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Facilities', 'action' => 'delete', 'facility' => FACILITY_ID_CENTRAL_TECH],
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
