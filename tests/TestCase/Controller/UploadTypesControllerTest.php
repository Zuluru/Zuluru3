<?php
namespace App\Test\TestCase\Controller;

/**
 * App\Controller\UploadTypesController Test Case
 */
class UploadTypesControllerTest extends ControllerTestCase {

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
			'app.upload_types',
				'app.uploads',
			'app.leagues',
				'app.divisions',
					'app.teams',
					'app.divisions_people',
			'app.settings',
	];

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex() {
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
	 *
	 * @return void
	 */
	public function testView() {
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
	 *
	 * @return void
	 */
	public function testAddAsAdmin() {
		// Admins are allowed to add upload_types
		$this->assertGetAsAccessOk(['controller' => 'UploadTypes', 'action' => 'add'], PERSON_ID_ADMIN);
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers are allowed to add upload_types
		$this->assertGetAsAccessOk(['controller' => 'UploadTypes', 'action' => 'add'], PERSON_ID_MANAGER);
	}

	/**
	 * Test add method as others
	 *
	 * @return void
	 */
	public function testAddAsOthers() {
		// Others are not allowed to add upload_types
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'add'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'add'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'add'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'add'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'UploadTypes', 'action' => 'add']);
	}

	/**
	 * Test edit method as an admin
	 *
	 * @return void
	 */
	public function testEditAsAdmin() {
		// Admins are allowed to edit upload types
		$this->assertGetAsAccessOk(['controller' => 'UploadTypes', 'action' => 'edit', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'UploadTypes', 'action' => 'edit', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER_SUB], PERSON_ID_ADMIN);
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers are allowed to edit upload types
		$this->assertGetAsAccessOk(['controller' => 'UploadTypes', 'action' => 'edit', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER], PERSON_ID_MANAGER);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'edit', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test edit method as others
	 *
	 * @return void
	 */
	public function testEditAsOthers() {
		// Others are not allowed to edit upload types
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'edit', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'edit', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'edit', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'edit', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'UploadTypes', 'action' => 'edit', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER]);
	}

	/**
	 * Test delete method as an admin
	 *
	 * @return void
	 */
	public function testDeleteAsAdmin() {
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
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
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
	 *
	 * @return void
	 */
	public function testDeleteAsOthers() {
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
