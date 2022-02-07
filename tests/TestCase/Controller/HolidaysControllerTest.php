<?php
namespace App\Test\TestCase\Controller;

/**
 * App\Controller\HolidaysController Test Case
 */
class HolidaysControllerTest extends ControllerTestCase {

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex(): void {
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
	public function testAddAsAdmin(): void {
		// Admins are allowed to add holidays
		$this->assertGetAsAccessOk(['controller' => 'Holidays', 'action' => 'add'], PERSON_ID_ADMIN);
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager(): void {
		// Managers are allowed to add holidays
		$this->assertGetAsAccessOk(['controller' => 'Holidays', 'action' => 'add'], PERSON_ID_MANAGER);
	}

	/**
	 * Test add method as others
	 *
	 * @return void
	 */
	public function testAddAsOthers(): void {
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
	public function testEditAsAdmin(): void {
		// Admins are allowed to edit holidays
		$this->assertGetAsAccessOk(['controller' => 'Holidays', 'action' => 'edit', 'holiday' => HOLIDAY_ID_CHRISTMAS], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Holidays', 'action' => 'edit', 'holiday' => HOLIDAY_ID_CHRISTMAS_SUB], PERSON_ID_ADMIN);
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager(): void {
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
	public function testEditAsOthers(): void {
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
	public function testDeleteAsAdmin(): void {
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
	public function testDeleteAsManager(): void {
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
	public function testDeleteAsOthers(): void {
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
