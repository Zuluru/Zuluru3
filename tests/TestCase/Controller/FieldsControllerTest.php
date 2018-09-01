<?php
namespace App\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\Routing\Router;

/**
 * App\Controller\FieldsController Test Case
 */
class FieldsControllerTest extends ControllerTestCase {

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
		// Anyone that gets the index gets redirected to facilities index
		$this->session(['Auth.User.id' => PERSON_ID_ADMIN, 'Zuluru.zuluru_person_id' => PERSON_ID_ADMIN]);
		$this->get(['controller' => 'Fields', 'action' => 'index']);
		$this->assertResponseCode(301);
		$this->assertRedirect(['controller' => 'Facilities', 'action' => 'index']);
	}

	/**
	 * Test index method as a manager
	 *
	 * @return void
	 */
	public function testIndexAsManager() {
		// Anyone that gets the index gets redirected to facilities index
		$this->session(['Auth.User.id' => PERSON_ID_MANAGER, 'Zuluru.zuluru_person_id' => PERSON_ID_MANAGER]);
		$this->get(['controller' => 'Fields', 'action' => 'index']);
		$this->assertResponseCode(301);
		$this->assertRedirect(['controller' => 'Facilities', 'action' => 'index']);
	}

	/**
	 * Test index method as a coordinator
	 *
	 * @return void
	 */
	public function testIndexAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
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
		$this->markTestIncomplete('Not implemented yet.');
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
	 * Test view method as an admin
	 *
	 * @return void
	 */
	public function testViewAsAdmin() {
		// Anyone that gets the view gets redirected to facility view
		$this->session(['Auth.User.id' => PERSON_ID_ADMIN, 'Zuluru.zuluru_person_id' => PERSON_ID_ADMIN]);
		$this->get(['controller' => 'Fields', 'action' => 'view', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1]);
		$this->assertResponseCode(301);
		$this->assertRedirect(['controller' => 'Facilities', 'action' => 'view', 'facility' => FACILITY_ID_SUNNYBROOK]);
	}

	/**
	 * Test view method as a manager
	 *
	 * @return void
	 */
	public function testViewAsManager() {
		// Anyone that gets the view gets redirected to facility view
		$this->session(['Auth.User.id' => PERSON_ID_MANAGER, 'Zuluru.zuluru_person_id' => PERSON_ID_MANAGER]);
		$this->get(['controller' => 'Fields', 'action' => 'view', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1]);
		$this->assertResponseCode(301);
		$this->assertRedirect(['controller' => 'Facilities', 'action' => 'view', 'facility' => FACILITY_ID_SUNNYBROOK]);
	}

	/**
	 * Test view method as a coordinator
	 *
	 * @return void
	 */
	public function testViewAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
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
	 * Test tooltip method as an admin
	 *
	 * @return void
	 */
	public function testTooltipAsAdmin() {
		// Everyone is allowed to view field tooltips
		$this->assertAccessOk(['controller' => 'Fields', 'action' => 'tooltip', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1], PERSON_ID_ADMIN, 'getajax');
		$this->assertResponseRegExp('#/maps\\\\/view\?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1 . '#ms');

		$this->assertAccessRedirect(['controller' => 'Fields', 'action' => 'tooltip', 'field' => 0],
			PERSON_ID_ADMIN, 'getajax', [], ['controller' => 'Facilities', 'action' => 'index'],
			'Invalid field.');
	}

	/**
	 * Test tooltip method as a manager
	 *
	 * @return void
	 */
	public function testTooltipAsManager() {
		$this->assertAccessOk(['controller' => 'Fields', 'action' => 'tooltip', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1], PERSON_ID_MANAGER, 'getajax');
		$this->assertResponseRegExp('#/maps\\\\/view\?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1 . '#ms');
	}

	/**
	 * Test tooltip method as a coordinator
	 *
	 * @return void
	 */
	public function testTooltipAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test tooltip method as a captain
	 *
	 * @return void
	 */
	public function testTooltipAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test tooltip method as a player
	 *
	 * @return void
	 */
	public function testTooltipAsPlayer() {
		$this->assertAccessOk(['controller' => 'Fields', 'action' => 'tooltip', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1], PERSON_ID_PLAYER, 'getajax');
		$this->assertResponseRegExp('#/maps\\\\/view\?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1 . '#ms');
	}

	/**
	 * Test tooltip method as someone else
	 *
	 * @return void
	 */
	public function testTooltipAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test tooltip method without being logged in
	 *
	 * @return void
	 */
	public function testTooltipAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test open method as an admin
	 *
	 * @return void
	 */
	public function testOpenAsAdmin() {
		// Admins are allowed to open fields
		$this->assertAccessOk(['controller' => 'Fields', 'action' => 'open', 'field' => FIELD_ID_SUNNYBROOK_GREENSPACE], PERSON_ID_ADMIN, 'getajax');
		$this->assertResponseRegExp('#/fields\\\\/close\?field=' . FIELD_ID_SUNNYBROOK_GREENSPACE . '#ms');
	}

	/**
	 * Test open method as a manager
	 *
	 * @return void
	 */
	public function testOpenAsManager() {
		// Managers are allowed to open fields
		$this->assertAccessOk(['controller' => 'Fields', 'action' => 'open', 'field' => FIELD_ID_MARILYN_BELL], PERSON_ID_MANAGER, 'getajax');
		$this->assertResponseRegExp('#/fields\\\\/close\?field=' . FIELD_ID_MARILYN_BELL . '#ms');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Fields', 'action' => 'open', 'field' => FIELD_ID_CENTRAL_TECH], PERSON_ID_MANAGER, 'getajax');
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
		// Others are not allowed to open fields
		$this->assertAccessRedirect(['controller' => 'Fields', 'action' => 'open', 'field' => FIELD_ID_MARILYN_BELL], PERSON_ID_PLAYER, 'getajax');
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
		// Admins are allowed to close fields
		$this->assertAccessOk(['controller' => 'Fields', 'action' => 'close', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1], PERSON_ID_ADMIN, 'getajax');
		$this->assertResponseRegExp('#/fields\\\\/open\?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1 . '#ms');
	}

	/**
	 * Test close method as a manager
	 *
	 * @return void
	 */
	public function testCloseAsManager() {
		// Managers are allowed to close fields
		$this->assertAccessOk(['controller' => 'Fields', 'action' => 'close', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_2], PERSON_ID_MANAGER, 'getajax');
		$this->assertResponseRegExp('#/fields\\\\/open\?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_2 . '#ms');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Fields', 'action' => 'close', 'field' => FIELD_ID_CENTRAL_TECH], PERSON_ID_MANAGER, 'getajax');
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
		// Others are not allowed to close fields
		$this->assertAccessRedirect(['controller' => 'Fields', 'action' => 'close', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_3], PERSON_ID_PLAYER, 'getajax');
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

		// Admins are allowed to delete fields
		$this->assertAccessRedirect(['controller' => 'Fields', 'action' => 'delete', 'field' => FIELD_ID_BLOOR],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Facilities', 'action' => 'index'],
			'The field has been deleted.', 'Flash.flash.0.message');

		// But not the last field at a facility (Bloor 2 will be last, now that Bloor 1 is gone)
		$this->assertAccessRedirect(['controller' => 'Fields', 'action' => 'delete', 'field' => FIELD_ID_BLOOR2],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Facilities', 'action' => 'index'],
			'You cannot delete the only field at a facility.', 'Flash.flash.0.message');

		// And not ones with dependencies
		$this->assertAccessRedirect(['controller' => 'Fields', 'action' => 'delete', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Facilities', 'action' => 'index'],
			'#The following records reference this field, so it cannot be deleted#', 'Flash.flash.0.message');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers can delete fields in their affiliate
		$this->assertAccessRedirect(['controller' => 'Fields', 'action' => 'delete', 'field' => FIELD_ID_BLOOR],
			PERSON_ID_MANAGER, 'post', [], ['controller' => 'Facilities', 'action' => 'index'],
			'The field has been deleted.', 'Flash.flash.0.message');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Fields', 'action' => 'delete', 'field' => FIELD_ID_CENTRAL_TECH],
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

	/**
	 * Test bookings method as an admin
	 *
	 * @return void
	 */
	public function testBookingsAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test bookings method as a manager
	 *
	 * @return void
	 */
	public function testBookingsAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test bookings method as a coordinator
	 *
	 * @return void
	 */
	public function testBookingsAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test bookings method as a captain
	 *
	 * @return void
	 */
	public function testBookingsAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test bookings method as a player
	 *
	 * @return void
	 */
	public function testBookingsAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test bookings method as someone else
	 *
	 * @return void
	 */
	public function testBookingsAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test bookings method without being logged in
	 *
	 * @return void
	 */
	public function testBookingsAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
