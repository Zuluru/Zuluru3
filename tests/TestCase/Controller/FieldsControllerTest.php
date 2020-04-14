<?php
namespace App\Test\TestCase\Controller;

use Cake\Http\Client\Message;

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
		'app.Affiliates',
			'app.Users',
				'app.People',
					'app.AffiliatesPeople',
					'app.PeoplePeople',
			'app.Groups',
				'app.GroupsPeople',
			'app.Regions',
				'app.Facilities',
					'app.Fields',
			'app.Leagues',
				'app.Divisions',
					'app.Teams',
					'app.DivisionsDays',
					'app.GameSlots',
						'app.DivisionsGameslots',
					'app.DivisionsPeople',
					'app.Pools',
						'app.PoolsTeams',
					'app.Games',
			'app.Notes',
			'app.Settings',
		'app.I18n',
	];

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex() {
		// Anyone that gets the index gets redirected to facilities index
		$this->login(PERSON_ID_ADMIN);
		$this->get(['controller' => 'Fields', 'action' => 'index']);
		$this->assertResponseCode(Message::STATUS_MOVED_PERMANENTLY);
		$this->assertRedirectEquals(['controller' => 'Facilities', 'action' => 'index']);

		$this->login(PERSON_ID_ADMIN);
		$this->get(['controller' => 'Fields', 'action' => 'index']);
		$this->assertResponseCode(Message::STATUS_MOVED_PERMANENTLY);
		$this->assertRedirectEquals(['controller' => 'Facilities', 'action' => 'index']);

		$this->login(PERSON_ID_COORDINATOR);
		$this->get(['controller' => 'Fields', 'action' => 'index']);
		$this->assertResponseCode(Message::STATUS_MOVED_PERMANENTLY);
		$this->assertRedirectEquals(['controller' => 'Facilities', 'action' => 'index']);

		$this->login(PERSON_ID_CAPTAIN);
		$this->get(['controller' => 'Fields', 'action' => 'index']);
		$this->assertResponseCode(Message::STATUS_MOVED_PERMANENTLY);
		$this->assertRedirectEquals(['controller' => 'Facilities', 'action' => 'index']);

		$this->login(PERSON_ID_PLAYER);
		$this->get(['controller' => 'Fields', 'action' => 'index']);
		$this->assertResponseCode(Message::STATUS_MOVED_PERMANENTLY);
		$this->assertRedirectEquals(['controller' => 'Facilities', 'action' => 'index']);

		$this->login(PERSON_ID_VISITOR);
		$this->get(['controller' => 'Fields', 'action' => 'index']);
		$this->assertResponseCode(Message::STATUS_MOVED_PERMANENTLY);
		$this->assertRedirectEquals(['controller' => 'Facilities', 'action' => 'index']);

		$this->logout();
		$this->get(['controller' => 'Fields', 'action' => 'index']);
		$this->assertResponseCode(Message::STATUS_MOVED_PERMANENTLY);
		$this->assertRedirectEquals(['controller' => 'Facilities', 'action' => 'index']);
	}

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testView() {
		// Anyone that gets the view gets redirected to facility view
		$this->login(PERSON_ID_ADMIN);
		$this->get(['controller' => 'Fields', 'action' => 'view', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1]);
		$this->assertResponseCode(Message::STATUS_MOVED_PERMANENTLY);
		$this->assertRedirectEquals(['controller' => 'Facilities', 'action' => 'view', 'facility' => FACILITY_ID_SUNNYBROOK]);

		$this->login(PERSON_ID_ADMIN);
		$this->get(['controller' => 'Fields', 'action' => 'view', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1]);
		$this->assertResponseCode(Message::STATUS_MOVED_PERMANENTLY);
		$this->assertRedirectEquals(['controller' => 'Facilities', 'action' => 'view', 'facility' => FACILITY_ID_SUNNYBROOK]);

		$this->login(PERSON_ID_COORDINATOR);
		$this->get(['controller' => 'Fields', 'action' => 'view', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1]);
		$this->assertResponseCode(Message::STATUS_MOVED_PERMANENTLY);
		$this->assertRedirectEquals(['controller' => 'Facilities', 'action' => 'view', 'facility' => FACILITY_ID_SUNNYBROOK]);

		$this->login(PERSON_ID_CAPTAIN);
		$this->get(['controller' => 'Fields', 'action' => 'view', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1]);
		$this->assertResponseCode(Message::STATUS_MOVED_PERMANENTLY);
		$this->assertRedirectEquals(['controller' => 'Facilities', 'action' => 'view', 'facility' => FACILITY_ID_SUNNYBROOK]);

		$this->login(PERSON_ID_PLAYER);
		$this->get(['controller' => 'Fields', 'action' => 'view', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1]);
		$this->assertResponseCode(Message::STATUS_MOVED_PERMANENTLY);
		$this->assertRedirectEquals(['controller' => 'Facilities', 'action' => 'view', 'facility' => FACILITY_ID_SUNNYBROOK]);

		$this->login(PERSON_ID_VISITOR);
		$this->get(['controller' => 'Fields', 'action' => 'view', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1]);
		$this->assertResponseCode(Message::STATUS_MOVED_PERMANENTLY);
		$this->assertRedirectEquals(['controller' => 'Facilities', 'action' => 'view', 'facility' => FACILITY_ID_SUNNYBROOK]);

		$this->logout();
		$this->get(['controller' => 'Fields', 'action' => 'view', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1]);
		$this->assertResponseCode(Message::STATUS_MOVED_PERMANENTLY);
		$this->assertRedirectEquals(['controller' => 'Facilities', 'action' => 'view', 'facility' => FACILITY_ID_SUNNYBROOK]);
	}

	/**
	 * Test tooltip method
	 *
	 * @return void
	 */
	public function testTooltip() {
		// Anyone is allowed to view field tooltips
		$this->assertGetAjaxAsAccessOk(['controller' => 'Fields', 'action' => 'tooltip', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1],
			PERSON_ID_ADMIN);
		$this->assertResponseContains('/maps\\/view?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);

		$this->assertGetAjaxAsAccessRedirect(['controller' => 'Fields', 'action' => 'tooltip', 'field' => 0],
			PERSON_ID_ADMIN, ['controller' => 'Facilities', 'action' => 'index'],
			'Invalid field.');

		$this->assertGetAjaxAsAccessOk(['controller' => 'Fields', 'action' => 'tooltip', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1],
			PERSON_ID_MANAGER);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Fields', 'action' => 'tooltip', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1],
			PERSON_ID_COORDINATOR);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Fields', 'action' => 'tooltip', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1],
			PERSON_ID_CAPTAIN);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Fields', 'action' => 'tooltip', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1],
			PERSON_ID_PLAYER);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Fields', 'action' => 'tooltip', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1],
			PERSON_ID_VISITOR);

		$this->assertGetAjaxAnonymousAccessOk(['controller' => 'Fields', 'action' => 'tooltip', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test open method as an admin
	 *
	 * @return void
	 */
	public function testOpenAsAdmin() {
		// Admins are allowed to open fields
		$this->assertGetAjaxAsAccessOk(['controller' => 'Fields', 'action' => 'open', 'field' => FIELD_ID_SUNNYBROOK_GREENSPACE],
			PERSON_ID_ADMIN);
		$this->assertResponseContains('/fields\\/close?field=' . FIELD_ID_SUNNYBROOK_GREENSPACE);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test open method as a manager
	 *
	 * @return void
	 */
	public function testOpenAsManager() {
		// Managers are allowed to open fields
		$this->assertGetAjaxAsAccessOk(['controller' => 'Fields', 'action' => 'open', 'field' => FIELD_ID_MARILYN_BELL],
			PERSON_ID_MANAGER);
		$this->assertResponseContains('/fields\\/close?field=' . FIELD_ID_MARILYN_BELL);

		// But not ones in other affiliates
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Fields', 'action' => 'open', 'field' => FIELD_ID_CENTRAL_TECH],
			PERSON_ID_MANAGER);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test open method as others
	 *
	 * @return void
	 */
	public function testOpenAsOthers() {
		// Others are not allowed to open fields
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Fields', 'action' => 'open', 'field' => FIELD_ID_MARILYN_BELL],
			PERSON_ID_COORDINATOR);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Fields', 'action' => 'open', 'field' => FIELD_ID_MARILYN_BELL],
			PERSON_ID_CAPTAIN);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Fields', 'action' => 'open', 'field' => FIELD_ID_MARILYN_BELL],
			PERSON_ID_PLAYER);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Fields', 'action' => 'open', 'field' => FIELD_ID_MARILYN_BELL],
			PERSON_ID_VISITOR);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Fields', 'action' => 'open', 'field' => FIELD_ID_MARILYN_BELL]);
	}

	/**
	 * Test close method as an admin
	 *
	 * @return void
	 */
	public function testCloseAsAdmin() {
		// Admins are allowed to close fields
		$this->assertGetAjaxAsAccessOk(['controller' => 'Fields', 'action' => 'close', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1],
			PERSON_ID_ADMIN);
		$this->assertResponseContains('/fields\\/open?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test close method as a manager
	 *
	 * @return void
	 */
	public function testCloseAsManager() {
		// Managers are allowed to close fields
		$this->assertGetAjaxAsAccessOk(['controller' => 'Fields', 'action' => 'close', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_2],
			PERSON_ID_MANAGER);
		$this->assertResponseContains('/fields\\/open?field=' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_2);

		// But not ones in other affiliates
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Fields', 'action' => 'close', 'field' => FIELD_ID_CENTRAL_TECH],
			PERSON_ID_MANAGER);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test close method as others
	 *
	 * @return void
	 */
	public function testCloseAsOthers() {
		// Others are not allowed to close fields
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Fields', 'action' => 'close', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1],
			PERSON_ID_COORDINATOR);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Fields', 'action' => 'close', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1],
			PERSON_ID_CAPTAIN);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Fields', 'action' => 'close', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_3],
			PERSON_ID_PLAYER);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Fields', 'action' => 'close', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1],
			PERSON_ID_VISITOR);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Fields', 'action' => 'close', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1]);
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
		$this->assertPostAsAccessRedirect(['controller' => 'Fields', 'action' => 'delete', 'field' => FIELD_ID_BLOOR],
			PERSON_ID_ADMIN, [], ['controller' => 'Facilities', 'action' => 'index'],
			'The field has been deleted.');

		// But not the last field at a facility (Bloor 2 will be last, now that Bloor 1 is gone)
		$this->assertPostAsAccessRedirect(['controller' => 'Fields', 'action' => 'delete', 'field' => FIELD_ID_BLOOR2],
			PERSON_ID_ADMIN, [], ['controller' => 'Facilities', 'action' => 'index'],
			'You cannot delete the only field at a facility.');

		// And not ones with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'Fields', 'action' => 'delete', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1],
			PERSON_ID_ADMIN, [], ['controller' => 'Facilities', 'action' => 'index'],
			'#The following records reference this field, so it cannot be deleted#');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to delete fields in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Fields', 'action' => 'delete', 'field' => FIELD_ID_BLOOR],
			PERSON_ID_MANAGER, [], ['controller' => 'Facilities', 'action' => 'index'],
			'The field has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Fields', 'action' => 'delete', 'field' => FIELD_ID_CENTRAL_TECH],
			PERSON_ID_MANAGER);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test delete method as others
	 *
	 * @return void
	 */
	public function testDeleteAsOthers() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Others are not allowed to delete fields
		$this->assertPostAsAccessDenied(['controller' => 'Fields', 'action' => 'delete', 'field' => FIELD_ID_BLOOR],
			PERSON_ID_COORDINATOR);
		$this->assertPostAsAccessDenied(['controller' => 'Fields', 'action' => 'delete', 'field' => FIELD_ID_BLOOR],
			PERSON_ID_CAPTAIN);
		$this->assertPostAsAccessDenied(['controller' => 'Fields', 'action' => 'delete', 'field' => FIELD_ID_BLOOR],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'Fields', 'action' => 'delete', 'field' => FIELD_ID_BLOOR],
			PERSON_ID_VISITOR);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Fields', 'action' => 'delete', 'field' => FIELD_ID_BLOOR]);
	}

	/**
	 * Test bookings method
	 *
	 * @return void
	 */
	public function testBookings() {
		// Anyone logged in is allowed to see the bookings list
		$this->assertGetAsAccessOk(['controller' => 'Fields', 'action' => 'bookings', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Fields', 'action' => 'bookings', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'Fields', 'action' => 'bookings', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessOk(['controller' => 'Fields', 'action' => 'bookings', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessOk(['controller' => 'Fields', 'action' => 'bookings', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1], PERSON_ID_PLAYER);
		$this->assertGetAsAccessOk(['controller' => 'Fields', 'action' => 'bookings', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1], PERSON_ID_VISITOR);

		$this->assertGetAnonymousAccessDenied(['controller' => 'Fields', 'action' => 'bookings', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

}
