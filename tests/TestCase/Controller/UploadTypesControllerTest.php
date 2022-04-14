<?php
namespace App\Test\TestCase\Controller;

/**
 * App\Controller\UploadTypesController Test Case
 */
class UploadTypesControllerTest extends ControllerTestCase {

	/**
	 * Test index method
	 */
	public function testIndex(): void {
		// Admins are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'UploadTypes', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertResponseContains('/upload_types/edit?type=' . UPLOAD_TYPE_ID_JUNIOR_WAIVER);
		$this->assertResponseContains('/upload_types/delete?type=' . UPLOAD_TYPE_ID_JUNIOR_WAIVER);
		$this->assertResponseContains('/upload_types/edit?type=' . UPLOAD_TYPE_ID_JUNIOR_WAIVER_SUB);
		$this->assertResponseContains('/upload_types/delete?type=' . UPLOAD_TYPE_ID_JUNIOR_WAIVER_SUB);

		// Managers are allowed to see the index, but don't see upload types in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'UploadTypes', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertResponseContains('/upload_types/edit?type=' . UPLOAD_TYPE_ID_JUNIOR_WAIVER);
		$this->assertResponseContains('/upload_types/delete?type=' . UPLOAD_TYPE_ID_JUNIOR_WAIVER);
		$this->assertResponseNotContains('/upload_types/edit?type=' . UPLOAD_TYPE_ID_JUNIOR_WAIVER_SUB);
		$this->assertResponseNotContains('/upload_types/delete?type=' . UPLOAD_TYPE_ID_JUNIOR_WAIVER_SUB);

		// Others are not allowed to see the index
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'index'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'index'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'index'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'index'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'UploadTypes', 'action' => 'index']);
	}

	/**
	 * Test view method
	 */
	public function testView(): void {
		// Admins are allowed to view upload_types
		$this->assertGetAsAccessOk(['controller' => 'UploadTypes', 'action' => 'view', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER], PERSON_ID_ADMIN);
		$this->assertResponseContains('/upload_types/edit?type=' . UPLOAD_TYPE_ID_JUNIOR_WAIVER);
		$this->assertResponseContains('/upload_types/delete?type=' . UPLOAD_TYPE_ID_JUNIOR_WAIVER);

		$this->assertGetAsAccessOk(['controller' => 'UploadTypes', 'action' => 'view', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER_SUB], PERSON_ID_ADMIN);
		$this->assertResponseContains('/upload_types/edit?type=' . UPLOAD_TYPE_ID_JUNIOR_WAIVER_SUB);
		$this->assertResponseContains('/upload_types/delete?type=' . UPLOAD_TYPE_ID_JUNIOR_WAIVER_SUB);

		// Managers are allowed to view upload_types
		$this->assertGetAsAccessOk(['controller' => 'UploadTypes', 'action' => 'view', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER], PERSON_ID_MANAGER);
		$this->assertResponseContains('/upload_types/edit?type=' . UPLOAD_TYPE_ID_JUNIOR_WAIVER);
		$this->assertResponseContains('/upload_types/delete?type=' . UPLOAD_TYPE_ID_JUNIOR_WAIVER);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'view', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER_SUB], PERSON_ID_MANAGER);

		// Others are not allowed to view upload_types
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'view', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'view', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'view', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'view', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'UploadTypes', 'action' => 'view', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER]);
	}

	/**
	 * Test add method as an admin
	 */
	public function testAddAsAdmin(): void {
		// Admins are allowed to add upload_types
		$this->assertGetAsAccessOk(['controller' => 'UploadTypes', 'action' => 'add'], PERSON_ID_ADMIN);
	}

	/**
	 * Test add method as a manager
	 */
	public function testAddAsManager(): void {
		// Managers are allowed to add upload_types
		$this->assertGetAsAccessOk(['controller' => 'UploadTypes', 'action' => 'add'], PERSON_ID_MANAGER);
	}

	/**
	 * Test add method as others
	 */
	public function testAddAsOthers(): void {
		// Others are not allowed to add upload_types
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'add'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'add'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'add'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'add'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'UploadTypes', 'action' => 'add']);
	}

	/**
	 * Test edit method as an admin
	 */
	public function testEditAsAdmin(): void {
		// Admins are allowed to edit upload types
		$this->assertGetAsAccessOk(['controller' => 'UploadTypes', 'action' => 'edit', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'UploadTypes', 'action' => 'edit', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER_SUB], PERSON_ID_ADMIN);
	}

	/**
	 * Test edit method as a manager
	 */
	public function testEditAsManager(): void {
		// Managers are allowed to edit upload types
		$this->assertGetAsAccessOk(['controller' => 'UploadTypes', 'action' => 'edit', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER], PERSON_ID_MANAGER);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'edit', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test edit method as others
	 */
	public function testEditAsOthers(): void {
		// Others are not allowed to edit upload types
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'edit', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'edit', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'edit', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'edit', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'UploadTypes', 'action' => 'edit', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER]);
	}

	/**
	 * Test delete method as an admin
	 */
	public function testDeleteAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to delete upload types
		$this->assertPostAsAccessRedirect(['controller' => 'UploadTypes', 'action' => 'delete', 'type' => UPLOAD_TYPE_ID_UNUSED_WAIVER],
			PERSON_ID_ADMIN, [], ['controller' => 'UploadTypes', 'action' => 'index'],
			'The upload type has been deleted.');

		// But not ones with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'UploadTypes', 'action' => 'delete', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER],
			PERSON_ID_ADMIN, [], ['controller' => 'UploadTypes', 'action' => 'index'],
			'#The following records reference this upload type, so it cannot be deleted#');
	}

	/**
	 * Test delete method as a manager
	 */
	public function testDeleteAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to delete upload types in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'UploadTypes', 'action' => 'delete', 'type' => UPLOAD_TYPE_ID_UNUSED_WAIVER],
			PERSON_ID_MANAGER, [], ['controller' => 'UploadTypes', 'action' => 'index'],
			'The upload type has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'delete', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER_SUB],
			PERSON_ID_MANAGER);
	}

	/**
	 * Test delete method as others
	 */
	public function testDeleteAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Others are not allowed to delete
		$this->assertPostAjaxAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'delete', 'type' => UPLOAD_TYPE_ID_UNUSED_WAIVER], PERSON_ID_COORDINATOR);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'delete', 'type' => UPLOAD_TYPE_ID_UNUSED_WAIVER], PERSON_ID_CAPTAIN);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'delete', 'type' => UPLOAD_TYPE_ID_UNUSED_WAIVER], PERSON_ID_PLAYER);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'delete', 'type' => UPLOAD_TYPE_ID_UNUSED_WAIVER], PERSON_ID_VISITOR);
		$this->assertPostAjaxAnonymousAccessDenied(['controller' => 'UploadTypes', 'action' => 'delete', 'type' => UPLOAD_TYPE_ID_UNUSED_WAIVER]);
	}

}
