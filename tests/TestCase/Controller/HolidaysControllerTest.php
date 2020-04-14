<?php
namespace App\Test\TestCase\Controller;

/**
 * App\Controller\HolidaysController Test Case
 */
class HolidaysControllerTest extends ControllerTestCase {

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
			'app.Leagues',
				'app.Divisions',
					'app.Teams',
					'app.DivisionsPeople',
			'app.Holidays',
			'app.Settings',
		'app.I18n',
	];

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex() {
		// Admins are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Holidays', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertResponseContains('/holidays/edit?holiday=' . HOLIDAY_ID_CHRISTMAS);
		$this->assertResponseContains('/holidays/delete?holiday=' . HOLIDAY_ID_CHRISTMAS);
		$this->assertResponseContains('/holidays/edit?holiday=' . HOLIDAY_ID_CHRISTMAS_SUB);
		$this->assertResponseContains('/holidays/delete?holiday=' . HOLIDAY_ID_CHRISTMAS_SUB);

		// Managers are allowed to see the index, but don't see holidays in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'Holidays', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertResponseContains('/holidays/edit?holiday=' . HOLIDAY_ID_CHRISTMAS);
		$this->assertResponseContains('/holidays/delete?holiday=' . HOLIDAY_ID_CHRISTMAS);
		$this->assertResponseNotContains('/holidays/edit?holiday=' . HOLIDAY_ID_CHRISTMAS_SUB);
		$this->assertResponseNotContains('/holidays/delete?holiday=' . HOLIDAY_ID_CHRISTMAS_SUB);

		// Others are not allowed to see the index
		$this->assertGetAsAccessDenied(['controller' => 'Holidays', 'action' => 'index'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Holidays', 'action' => 'index'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Holidays', 'action' => 'index'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Holidays', 'action' => 'index'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Holidays', 'action' => 'index']);
	}

	/**
	 * Test add method as an admin
	 *
	 * @return void
	 */
	public function testAddAsAdmin() {
		// Admins are allowed to add holidays
		$this->assertGetAsAccessOk(['controller' => 'Holidays', 'action' => 'add'], PERSON_ID_ADMIN);
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers are allowed to add holidays
		$this->assertGetAsAccessOk(['controller' => 'Holidays', 'action' => 'add'], PERSON_ID_MANAGER);
	}

	/**
	 * Test add method as others
	 *
	 * @return void
	 */
	public function testAddAsOthers() {
		// Others are not allowed to add holidays
		$this->assertGetAsAccessDenied(['controller' => 'Holidays', 'action' => 'add'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Holidays', 'action' => 'add'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Holidays', 'action' => 'add'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Holidays', 'action' => 'add'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Holidays', 'action' => 'add']);
	}

	/**
	 * Test edit method as an admin
	 *
	 * @return void
	 */
	public function testEditAsAdmin() {
		// Admins are allowed to edit holidays
		$this->assertGetAsAccessOk(['controller' => 'Holidays', 'action' => 'edit', 'holiday' => HOLIDAY_ID_CHRISTMAS], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Holidays', 'action' => 'edit', 'holiday' => HOLIDAY_ID_CHRISTMAS_SUB], PERSON_ID_ADMIN);
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers are allowed to edit holidays
		$this->assertGetAsAccessOk(['controller' => 'Holidays', 'action' => 'edit', 'holiday' => HOLIDAY_ID_CHRISTMAS], PERSON_ID_MANAGER);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Holidays', 'action' => 'edit', 'holiday' => HOLIDAY_ID_CHRISTMAS_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test edit method as others
	 *
	 * @return void
	 */
	public function testEditAsOthers() {
		// Others are not allowed to edit holidays
		$this->assertGetAsAccessDenied(['controller' => 'Holidays', 'action' => 'edit', 'holiday' => HOLIDAY_ID_CHRISTMAS], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Holidays', 'action' => 'edit', 'holiday' => HOLIDAY_ID_CHRISTMAS], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Holidays', 'action' => 'edit', 'holiday' => HOLIDAY_ID_CHRISTMAS], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Holidays', 'action' => 'edit', 'holiday' => HOLIDAY_ID_CHRISTMAS], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Holidays', 'action' => 'edit', 'holiday' => HOLIDAY_ID_CHRISTMAS]);
	}

	/**
	 * Test delete method as an admin
	 *
	 * @return void
	 */
	public function testDeleteAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to delete holidays
		$this->assertPostAsAccessRedirect(['controller' => 'Holidays', 'action' => 'delete', 'holiday' => HOLIDAY_ID_CHRISTMAS],
			PERSON_ID_ADMIN, [], ['controller' => 'Holidays', 'action' => 'index'],
			'The holiday has been deleted.');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to delete holidays in their own affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Holidays', 'action' => 'delete', 'holiday' => HOLIDAY_ID_BOXING_DAY],
			PERSON_ID_MANAGER, [], ['controller' => 'Holidays', 'action' => 'index'],
			'The holiday has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Holidays', 'action' => 'delete', 'holiday' => HOLIDAY_ID_CHRISTMAS_SUB],
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

		// Others are not allowed to delete holidays
		$this->assertPostAsAccessDenied(['controller' => 'Holidays', 'action' => 'delete', 'holiday' => HOLIDAY_ID_CHRISTMAS],
			PERSON_ID_COORDINATOR);
		$this->assertPostAsAccessDenied(['controller' => 'Holidays', 'action' => 'delete', 'holiday' => HOLIDAY_ID_CHRISTMAS],
			PERSON_ID_CAPTAIN);
		$this->assertPostAsAccessDenied(['controller' => 'Holidays', 'action' => 'delete', 'holiday' => HOLIDAY_ID_CHRISTMAS],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'Holidays', 'action' => 'delete', 'holiday' => HOLIDAY_ID_CHRISTMAS],
			PERSON_ID_VISITOR);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Holidays', 'action' => 'delete', 'holiday' => HOLIDAY_ID_CHRISTMAS]);
	}

}
