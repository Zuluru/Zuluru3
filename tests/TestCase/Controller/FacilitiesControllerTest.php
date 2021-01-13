<?php
namespace App\Test\TestCase\Controller;

use Cake\ORM\TableRegistry;

/**
 * App\Controller\FacilitiesController Test Case
 */
class FacilitiesControllerTest extends ControllerTestCase {

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex() {
		// Admins are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertResponseContains('/facilities/view?facility=' . FACILITY_ID_SUNNYBROOK);
		$this->assertResponseContains('/maps/view?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseContains('/facilities/edit?facility=' . FACILITY_ID_SUNNYBROOK);
		$this->assertResponseContains('/facilities/delete?facility=' . FACILITY_ID_SUNNYBROOK);
		$this->assertResponseContains('/facilities/close?facility=' . FACILITY_ID_SUNNYBROOK);
		$this->assertResponseContains('/facilities/view?facility=' . FACILITY_ID_CENTRAL_TECH);
		$this->assertResponseContains('/maps/view?field=' . FIELD_ID_CENTRAL_TECH);
		$this->assertResponseContains('/facilities/edit?facility=' . FACILITY_ID_CENTRAL_TECH);
		$this->assertResponseContains('/facilities/delete?facility=' . FACILITY_ID_CENTRAL_TECH);
		$this->assertResponseContains('/facilities/close?facility=' . FACILITY_ID_CENTRAL_TECH);

		// Managers are allowed to see the index, but don't see facilities in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertResponseContains('/facilities/view?facility=' . FACILITY_ID_SUNNYBROOK);
		$this->assertResponseContains('/maps/view?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseContains('/facilities/edit?facility=' . FACILITY_ID_SUNNYBROOK);
		$this->assertResponseContains('/facilities/delete?facility=' . FACILITY_ID_SUNNYBROOK);
		$this->assertResponseContains('/facilities/close?facility=' . FACILITY_ID_SUNNYBROOK);
		$this->assertResponseNotContains('/facilities/view?facility=' . FACILITY_ID_CENTRAL_TECH);
		$this->assertResponseNotContains('/maps/view?field=' . FIELD_ID_CENTRAL_TECH);
		$this->assertResponseNotContains('/facilities/edit?facility=' . FACILITY_ID_CENTRAL_TECH);
		$this->assertResponseNotContains('/facilities/delete?facility=' . FACILITY_ID_CENTRAL_TECH);
		$this->assertResponseNotContains('/facilities/close?facility=' . FACILITY_ID_CENTRAL_TECH);

		// Coordinators are allowed to see the index, but no edit options
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'index'], PERSON_ID_COORDINATOR);
		$this->assertResponseContains('/facilities/view?facility=' . FACILITY_ID_SUNNYBROOK);
		$this->assertResponseContains('/maps/view?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseNotContains('/facilities/edit?facility=' . FACILITY_ID_SUNNYBROOK);
		$this->assertResponseNotContains('/facilities/delete?facility=' . FACILITY_ID_SUNNYBROOK);
		$this->assertResponseNotContains('/facilities/close?facility=' . FACILITY_ID_SUNNYBROOK);

		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'index'], PERSON_ID_CAPTAIN);

		// Players are allowed to see the index, but no edit options
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'index'], PERSON_ID_DUPLICATE);
		$this->assertResponseContains('/facilities/view?facility=' . FACILITY_ID_CENTRAL_TECH);
		$this->assertResponseContains('/maps/view?field=' . FIELD_ID_CENTRAL_TECH);
		$this->assertResponseNotContains('/facilities/edit?facility=' . FACILITY_ID_CENTRAL_TECH);
		$this->assertResponseNotContains('/facilities/delete?facility=' . FACILITY_ID_CENTRAL_TECH);
		$this->assertResponseNotContains('/facilities/close?facility=' . FACILITY_ID_CENTRAL_TECH);

		// Visitors are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'index'], PERSON_ID_VISITOR);

		// Others are allowed to see the index
		$this->assertGetAnonymousAccessOk(['controller' => 'Facilities', 'action' => 'index']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test closed method
	 *
	 * @return void
	 */
	public function testClosed() {
		// Admins are allowed to see the closed index
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'closed'], PERSON_ID_ADMIN);
		$this->assertResponseContains('/facilities/view?facility=' . FACILITY_ID_MARILYN_BELL);
		// This field doesn't have a layout defined yet, so no map link
		$this->assertResponseNotContains('/maps/view?field=' . FIELD_ID_MARILYN_BELL);
		$this->assertResponseContains('/facilities/edit?facility=' . FACILITY_ID_MARILYN_BELL);
		$this->assertResponseContains('/facilities/delete?facility=' . FACILITY_ID_MARILYN_BELL);
		$this->assertResponseContains('/facilities/open?facility=' . FACILITY_ID_MARILYN_BELL);

		// Managers are allowed to see the closed index
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'closed'], PERSON_ID_MANAGER);
		$this->assertResponseContains('/facilities/view?facility=' . FACILITY_ID_MARILYN_BELL);
		// This field doesn't have a layout defined yet, so no map link
		$this->assertResponseNotContains('/maps/view?field=' . FIELD_ID_MARILYN_BELL);
		$this->assertResponseContains('/facilities/edit?facility=' . FACILITY_ID_MARILYN_BELL);
		$this->assertResponseContains('/facilities/delete?facility=' . FACILITY_ID_MARILYN_BELL);
		$this->assertResponseContains('/facilities/open?facility=' . FACILITY_ID_MARILYN_BELL);

		// Others are not allowed to see the closed index
		$this->assertGetAsAccessDenied(['controller' => 'Facilities', 'action' => 'closed'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Facilities', 'action' => 'closed'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Facilities', 'action' => 'closed'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Facilities', 'action' => 'closed'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Facilities', 'action' => 'closed']);
	}

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testView() {
		// Admins are allowed to view facilities
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'view', 'facility' => FACILITY_ID_SUNNYBROOK], PERSON_ID_ADMIN);
		$this->assertResponseContains('/facilities/edit?facility=' . FACILITY_ID_SUNNYBROOK);
		$this->assertResponseContains('/facilities/delete?facility=' . FACILITY_ID_SUNNYBROOK);
		$this->assertResponseContains('/maps/view?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseContains('/maps/edit?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseContains('/fields/delete?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseContains('/fields/close?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseContains('/fields/bookings?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseContains('/game_slots/add?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);

		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'view', 'facility' => FACILITY_ID_CENTRAL_TECH], PERSON_ID_ADMIN);
		$this->assertResponseContains('/facilities/edit?facility=' . FACILITY_ID_CENTRAL_TECH);
		$this->assertResponseContains('/facilities/delete?facility=' . FACILITY_ID_CENTRAL_TECH);
		$this->assertResponseContains('/maps/view?field=' . FIELD_ID_CENTRAL_TECH);
		$this->assertResponseContains('/maps/edit?field=' . FIELD_ID_CENTRAL_TECH);
		$this->assertResponseContains('/fields/delete?field=' . FIELD_ID_CENTRAL_TECH);
		$this->assertResponseContains('/fields/close?field=' . FIELD_ID_CENTRAL_TECH);
		$this->assertResponseContains('/fields/bookings?field=' . FIELD_ID_CENTRAL_TECH);
		$this->assertResponseContains('/game_slots/add?field=' . FIELD_ID_CENTRAL_TECH);

		// Admins are allowed to view closed facilities
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'view', 'facility' => FACILITY_ID_MARILYN_BELL], PERSON_ID_ADMIN);
		$this->assertResponseContains('/facilities/edit?facility=' . FACILITY_ID_MARILYN_BELL);
		$this->assertResponseContains('/facilities/delete?facility=' . FACILITY_ID_MARILYN_BELL);
		// This field doesn't have a layout defined yet, so no map link
		$this->assertResponseNotContains('/maps/view?field=' . FIELD_ID_MARILYN_BELL);
		$this->assertResponseContains('/maps/edit?field=' . FIELD_ID_MARILYN_BELL);
		$this->assertResponseContains('/fields/delete?field=' . FIELD_ID_MARILYN_BELL);
		$this->assertResponseContains('/fields/open?field=' . FIELD_ID_MARILYN_BELL);
		$this->assertResponseContains('/fields/bookings?field=' . FIELD_ID_MARILYN_BELL);
		$this->assertResponseContains('/game_slots/add?field=' . FIELD_ID_MARILYN_BELL);

		// Managers are allowed to view facilities
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'view', 'facility' => FACILITY_ID_SUNNYBROOK], PERSON_ID_MANAGER);
		$this->assertResponseContains('/facilities/edit?facility=' . FACILITY_ID_SUNNYBROOK);
		$this->assertResponseContains('/facilities/delete?facility=' . FACILITY_ID_SUNNYBROOK);
		$this->assertResponseContains('/maps/view?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseContains('/maps/edit?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseContains('/fields/delete?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseContains('/fields/close?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseContains('/fields/bookings?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseContains('/game_slots/add?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);

		// Managers are allowed to view facilities from other affiliates, but have no edit options
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'view', 'facility' => FACILITY_ID_CENTRAL_TECH], PERSON_ID_MANAGER);
		$this->assertResponseNotContains('/facilities/edit?facility=' . FACILITY_ID_CENTRAL_TECH);
		$this->assertResponseNotContains('/facilities/delete?facility=' . FACILITY_ID_CENTRAL_TECH);
		$this->assertResponseContains('/maps/view?field=' . FIELD_ID_CENTRAL_TECH);
		$this->assertResponseNotContains('/maps/edit?field=' . FIELD_ID_CENTRAL_TECH);
		$this->assertResponseNotContains('/fields/delete?field=' . FIELD_ID_CENTRAL_TECH);
		$this->assertResponseNotContains('/fields/close?field=' . FIELD_ID_CENTRAL_TECH);
		$this->assertResponseContains('/fields/bookings?field=' . FIELD_ID_CENTRAL_TECH);
		$this->assertResponseNotContains('/game_slots/add?field=' . FIELD_ID_CENTRAL_TECH);

		// Managers are allowed to view closed facilities
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'view', 'facility' => FACILITY_ID_MARILYN_BELL], PERSON_ID_MANAGER);
		$this->assertResponseContains('/facilities/edit?facility=' . FACILITY_ID_MARILYN_BELL);
		$this->assertResponseContains('/facilities/delete?facility=' . FACILITY_ID_MARILYN_BELL);
		// This field doesn't have a layout defined yet, so no map link
		$this->assertResponseNotContains('/maps/view?field=' . FIELD_ID_MARILYN_BELL);
		$this->assertResponseContains('/maps/edit?field=' . FIELD_ID_MARILYN_BELL);
		$this->assertResponseContains('/fields/delete?field=' . FIELD_ID_MARILYN_BELL);
		$this->assertResponseContains('/fields/open?field=' . FIELD_ID_MARILYN_BELL);
		$this->assertResponseContains('/fields/bookings?field=' . FIELD_ID_MARILYN_BELL);
		$this->assertResponseContains('/game_slots/add?field=' . FIELD_ID_MARILYN_BELL);

		// Others are allowed to view facilities, but no edit options
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'view', 'facility' => FACILITY_ID_SUNNYBROOK], PERSON_ID_COORDINATOR);
		$this->assertResponseNotContains('/facilities/edit?facility=' . FACILITY_ID_SUNNYBROOK);
		$this->assertResponseNotContains('/facilities/delete?facility=' . FACILITY_ID_SUNNYBROOK);
		$this->assertResponseContains('/maps/view?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseNotContains('/maps/edit?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseNotContains('/fields/delete?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseNotContains('/fields/close?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseContains('/fields/bookings?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseNotContains('/game_slots/add?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);

		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'view', 'facility' => FACILITY_ID_SUNNYBROOK], PERSON_ID_CAPTAIN);
		$this->assertResponseNotContains('/facilities/edit?facility=' . FACILITY_ID_SUNNYBROOK);
		$this->assertResponseNotContains('/facilities/delete?facility=' . FACILITY_ID_SUNNYBROOK);
		$this->assertResponseContains('/maps/view?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseNotContains('/maps/edit?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseNotContains('/fields/delete?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseNotContains('/fields/close?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseContains('/fields/bookings?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseNotContains('/game_slots/add?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);

		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'view', 'facility' => FACILITY_ID_SUNNYBROOK], PERSON_ID_PLAYER);
		$this->assertResponseNotContains('/facilities/edit?facility=' . FACILITY_ID_SUNNYBROOK);
		$this->assertResponseNotContains('/facilities/delete?facility=' . FACILITY_ID_SUNNYBROOK);
		$this->assertResponseContains('/maps/view?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseNotContains('/maps/edit?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseNotContains('/fields/delete?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseNotContains('/fields/close?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseContains('/fields/bookings?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseNotContains('/game_slots/add?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);

		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'view', 'facility' => FACILITY_ID_SUNNYBROOK], PERSON_ID_VISITOR);
		$this->assertResponseNotContains('/facilities/edit?facility=' . FACILITY_ID_SUNNYBROOK);
		$this->assertResponseNotContains('/facilities/delete?facility=' . FACILITY_ID_SUNNYBROOK);
		$this->assertResponseContains('/maps/view?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseNotContains('/maps/edit?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseNotContains('/fields/delete?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseNotContains('/fields/close?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseContains('/fields/bookings?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseNotContains('/game_slots/add?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);

		$this->assertGetAnonymousAccessOk(['controller' => 'Facilities', 'action' => 'view', 'facility' => FACILITY_ID_SUNNYBROOK]);
		$this->assertResponseNotContains('/facilities/edit?facility=' . FACILITY_ID_SUNNYBROOK);
		$this->assertResponseNotContains('/facilities/delete?facility=' . FACILITY_ID_SUNNYBROOK);
		$this->assertResponseContains('/maps/view?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseNotContains('/maps/edit?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseNotContains('/fields/delete?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseNotContains('/fields/close?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseContains('/fields/bookings?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
		$this->assertResponseNotContains('/game_slots/add?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
	}

	/**
	 * Test add method as an admin
	 *
	 * @return void
	 */
	public function testAddAsAdmin() {
		// Admins are allowed to add facilities
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'add'], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers are allowed to add facilities
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'add'], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as others
	 *
	 * @return void
	 */
	public function testAddAsOthers() {
		// Others are not allowed to add facilities
		$this->assertGetAsAccessDenied(['controller' => 'Facilities', 'action' => 'add'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Facilities', 'action' => 'add'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Facilities', 'action' => 'add'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Facilities', 'action' => 'add'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Facilities', 'action' => 'add']);
	}

	/**
	 * Test edit method as an admin
	 *
	 * @return void
	 */
	public function testEditAsAdmin() {
		// Admins are allowed to edit facilities
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'edit', 'facility' => FACILITY_ID_SUNNYBROOK], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'edit', 'facility' => FACILITY_ID_CENTRAL_TECH], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers are allowed to edit facilities
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'edit', 'facility' => FACILITY_ID_SUNNYBROOK], PERSON_ID_MANAGER);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Facilities', 'action' => 'edit', 'facility' => FACILITY_ID_CENTRAL_TECH], PERSON_ID_MANAGER);

		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as others
	 *
	 * @return void
	 */
	public function testEditAsOthers() {
		// Others are not allowed to edit facilities
		$this->assertGetAsAccessDenied(['controller' => 'Facilities', 'action' => 'edit', 'facility' => FACILITY_ID_SUNNYBROOK], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Facilities', 'action' => 'edit', 'facility' => FACILITY_ID_SUNNYBROOK], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Facilities', 'action' => 'edit', 'facility' => FACILITY_ID_SUNNYBROOK], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Facilities', 'action' => 'edit', 'facility' => FACILITY_ID_SUNNYBROOK], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Facilities', 'action' => 'edit', 'facility' => FACILITY_ID_SUNNYBROOK]);
	}

	/**
	 * Test add_field method as an admin
	 *
	 * @return void
	 */
	public function testAddFieldAsAdmin() {
		// Admins are allowed to add field
		$this->assertGetAjaxAsAccessOk(['controller' => 'Facilities', 'action' => 'add_field'],
			PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_field method as a manager
	 *
	 * @return void
	 */
	public function testAddFieldAsManager() {
		// Managers are allowed to add field
		$this->assertGetAjaxAsAccessOk(['controller' => 'Facilities', 'action' => 'add_field'],
			PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_field method as others
	 *
	 * @return void
	 */
	public function testAddFieldAsOthers() {
		// Others are not allowed to add fields
		$this->assertGetAsAccessDenied(['controller' => 'Facilities', 'action' => 'add_field'],
			PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Facilities', 'action' => 'add_field'],
			PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Facilities', 'action' => 'add_field'],
			PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Facilities', 'action' => 'add_field'],
			PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Facilities', 'action' => 'add_field']);
	}

	/**
	 * Test open method as an admin
	 *
	 * @return void
	 */
	public function testOpenAsAdmin() {
		// Admins are allowed to open facilities
		$this->assertGetAjaxAsAccessOk(['controller' => 'Facilities', 'action' => 'open', 'facility' => FACILITY_ID_MARILYN_BELL],
			PERSON_ID_ADMIN);
		$this->assertResponseContains('/facilities\\/close?facility=' . FACILITY_ID_MARILYN_BELL);
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
		$this->assertGetAjaxAsAccessOk(['controller' => 'Facilities', 'action' => 'open', 'facility' => FACILITY_ID_MARILYN_BELL],
			PERSON_ID_MANAGER);
		$this->assertResponseContains('/facilities\\/close?facility=' . FACILITY_ID_MARILYN_BELL);

		// But not ones in other affiliates
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Facilities', 'action' => 'open', 'facility' => FACILITY_ID_CENTRAL_TECH],
			PERSON_ID_MANAGER);
	}

	/**
	 * Test open method as others
	 *
	 * @return void
	 */
	public function testOpenAsOthers() {
		// Others are not allowed to open facilities
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Facilities', 'action' => 'open', 'facility' => FACILITY_ID_MARILYN_BELL],
			PERSON_ID_COORDINATOR);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Facilities', 'action' => 'open', 'facility' => FACILITY_ID_MARILYN_BELL],
			PERSON_ID_CAPTAIN);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Facilities', 'action' => 'open', 'facility' => FACILITY_ID_MARILYN_BELL],
			PERSON_ID_PLAYER);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Facilities', 'action' => 'open', 'facility' => FACILITY_ID_MARILYN_BELL],
			PERSON_ID_VISITOR);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Facilities', 'action' => 'open', 'facility' => FACILITY_ID_MARILYN_BELL]);
	}

	/**
	 * Test close method as an admin
	 *
	 * @return void
	 */
	public function testCloseAsAdmin() {
		// Admins are allowed to close facilities
		$this->assertGetAjaxAsAccessOk(['controller' => 'Facilities', 'action' => 'close', 'facility' => FACILITY_ID_SUNNYBROOK],
			PERSON_ID_ADMIN);
		$this->assertResponseContains('/facilities\\/open?facility=' . FACILITY_ID_SUNNYBROOK);
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
		$this->assertGetAjaxAsAccessOk(['controller' => 'Facilities', 'action' => 'close', 'facility' => FACILITY_ID_SUNNYBROOK],
			PERSON_ID_MANAGER);
		$this->assertResponseContains('/facilities\\/open?facility=' . FACILITY_ID_SUNNYBROOK);

		// But not ones in other affiliates
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Facilities', 'action' => 'close', 'facility' => FACILITY_ID_CENTRAL_TECH],
			PERSON_ID_MANAGER);
	}

	/**
	 * Test close method as others
	 *
	 * @return void
	 */
	public function testCloseAsOthers() {
		// Others are not allowed to close facilities
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Facilities', 'action' => 'close', 'facility' => FACILITY_ID_SUNNYBROOK],
			PERSON_ID_COORDINATOR);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Facilities', 'action' => 'close', 'facility' => FACILITY_ID_SUNNYBROOK],
			PERSON_ID_CAPTAIN);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Facilities', 'action' => 'close', 'facility' => FACILITY_ID_SUNNYBROOK],
			PERSON_ID_PLAYER);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Facilities', 'action' => 'close', 'facility' => FACILITY_ID_SUNNYBROOK],
			PERSON_ID_VISITOR);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Facilities', 'action' => 'close', 'facility' => FACILITY_ID_SUNNYBROOK]);
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
		$this->assertPostAsAccessRedirect(['controller' => 'Facilities', 'action' => 'delete', 'facility' => FACILITY_ID_BLOOR],
			PERSON_ID_ADMIN, [], ['controller' => 'Facilities', 'action' => 'index'],
			'The facility has been deleted.');

		// But not ones with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'Facilities', 'action' => 'delete', 'facility' => FACILITY_ID_SUNNYBROOK],
			PERSON_ID_ADMIN, [], ['controller' => 'Facilities', 'action' => 'index'],
			'#The following records reference this facility, so it cannot be deleted#');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to delete facilities in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Facilities', 'action' => 'delete', 'facility' => FACILITY_ID_BLOOR],
			PERSON_ID_MANAGER, [], ['controller' => 'Facilities', 'action' => 'index'],
			'The facility has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Facilities', 'action' => 'delete', 'facility' => FACILITY_ID_CENTRAL_TECH],
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

		// Others are not allowed to delete facilities
		$this->assertPostAsAccessDenied(['controller' => 'Facilities', 'action' => 'delete', 'facility' => FACILITY_ID_BLOOR],
			PERSON_ID_COORDINATOR);
		$this->assertPostAsAccessDenied(['controller' => 'Facilities', 'action' => 'delete', 'facility' => FACILITY_ID_BLOOR],
			PERSON_ID_CAPTAIN);
		$this->assertPostAsAccessDenied(['controller' => 'Facilities', 'action' => 'delete', 'facility' => FACILITY_ID_BLOOR],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'Facilities', 'action' => 'delete', 'facility' => FACILITY_ID_BLOOR],
			PERSON_ID_VISITOR);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Facilities', 'action' => 'delete', 'facility' => FACILITY_ID_BLOOR]);
	}

}
