<?php
namespace App\Test\TestCase\Controller;

use Cake\Core\Configure;

/**
 * App\Controller\GroupsController Test Case
 */
class GroupsControllerTest extends ControllerTestCase {

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex(): void {
		// Admins are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Groups', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertResponseContains('/groups/deactivate?group=' . GROUP_PLAYER);
		$this->assertResponseContains('/groups/activate?group=' . GROUP_OFFICIAL);

		// Others are not allowed to see the index
		$this->assertGetAsAccessDenied(['controller' => 'Groups', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertGetAsAccessDenied(['controller' => 'Groups', 'action' => 'index'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Groups', 'action' => 'index'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Groups', 'action' => 'index'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Groups', 'action' => 'index'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Groups', 'action' => 'index']);
	}

	/**
	 * Test activate method as an admin
	 *
	 * @return void
	 */
	public function testActivateAsAdmin(): void {
		// Admins are allowed to activate groups
		$this->assertGetAjaxAsAccessOk(['controller' => 'Groups', 'action' => 'activate', 'group' => GROUP_OFFICIAL],
			PERSON_ID_ADMIN);
		$this->assertResponseContains('/groups\\/deactivate?group=' . GROUP_OFFICIAL);
	}

	/**
	 * Test activate method as others
	 *
	 * @return void
	 */
	public function testActivateAsOthers(): void {
		// Others are not allowed to activate groups
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Groups', 'action' => 'activate', 'group' => GROUP_OFFICIAL],
			PERSON_ID_MANAGER);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Groups', 'action' => 'activate', 'group' => GROUP_OFFICIAL],
			PERSON_ID_COORDINATOR);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Groups', 'action' => 'activate', 'group' => GROUP_OFFICIAL],
			PERSON_ID_CAPTAIN);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Groups', 'action' => 'activate', 'group' => GROUP_OFFICIAL],
			PERSON_ID_PLAYER);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Groups', 'action' => 'activate', 'group' => GROUP_OFFICIAL],
			PERSON_ID_VISITOR);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Groups', 'action' => 'activate', 'group' => GROUP_OFFICIAL]);
	}

	/**
	 * Test deactivate method as an admin
	 *
	 * @return void
	 */
	public function testDeactivateAsAdmin(): void {
		// Admins are allowed to deactivate groups
		$this->assertGetAjaxAsAccessOk(['controller' => 'Groups', 'action' => 'deactivate', 'group' => GROUP_VOLUNTEER],
			PERSON_ID_ADMIN);
		$this->assertResponseContains('/groups\\/activate?group=' . GROUP_VOLUNTEER);
	}

	/**
	 * Test deactivate method as others
	 *
	 * @return void
	 */
	public function testDeactivateAsOthers(): void {
		// Others are not allowed to deactivate groups
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Groups', 'action' => 'deactivate', 'group' => GROUP_VOLUNTEER],
			PERSON_ID_MANAGER);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Groups', 'action' => 'deactivate', 'group' => GROUP_VOLUNTEER],
			PERSON_ID_COORDINATOR);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Groups', 'action' => 'deactivate', 'group' => GROUP_VOLUNTEER],
			PERSON_ID_CAPTAIN);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Groups', 'action' => 'deactivate', 'group' => GROUP_VOLUNTEER],
			PERSON_ID_PLAYER);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Groups', 'action' => 'deactivate', 'group' => GROUP_VOLUNTEER],
			PERSON_ID_VISITOR);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Groups', 'action' => 'deactivate', 'group' => GROUP_VOLUNTEER]);
	}

}
