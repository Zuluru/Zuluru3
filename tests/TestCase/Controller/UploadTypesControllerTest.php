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
			'app.settings',
	];

	/**
	 * Test index method as an admin
	 *
	 * @return void
	 */
	public function testIndexAsAdmin() {
		// Admins are allowed to get the index
		$this->assertAccessOk(['controller' => 'UploadTypes', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/upload_types/edit\?type=' . UPLOAD_TYPE_ID_JUNIOR_WAIVER . '#ms');
		$this->assertResponseRegExp('#/upload_types/delete\?type=' . UPLOAD_TYPE_ID_JUNIOR_WAIVER . '#ms');
		$this->assertResponseRegExp('#/upload_types/edit\?type=' . UPLOAD_TYPE_ID_JUNIOR_WAIVER_SUB . '#ms');
		$this->assertResponseRegExp('#/upload_types/delete\?type=' . UPLOAD_TYPE_ID_JUNIOR_WAIVER_SUB . '#ms');
	}

	/**
	 * Test index method as a manager
	 *
	 * @return void
	 */
	public function testIndexAsManager() {
		// Managers are allowed to get the index, but don't see upload types in other affiliates
		$this->assertAccessOk(['controller' => 'UploadTypes', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/upload_types/edit\?type=' . UPLOAD_TYPE_ID_JUNIOR_WAIVER . '#ms');
		$this->assertResponseRegExp('#/upload_types/delete\?type=' . UPLOAD_TYPE_ID_JUNIOR_WAIVER . '#ms');
		$this->assertResponseNotRegExp('#/upload_types/edit\?type=' . UPLOAD_TYPE_ID_JUNIOR_WAIVER_SUB . '#ms');
		$this->assertResponseNotRegExp('#/upload_types/delete\?type=' . UPLOAD_TYPE_ID_JUNIOR_WAIVER_SUB . '#ms');
	}

	/**
	 * Test index method as a coordinator
	 *
	 * @return void
	 */
	public function testIndexAsCoordinator() {
		// Others are not allowed to get the index
		$this->assertAccessRedirect(['controller' => 'UploadTypes', 'action' => 'index'], PERSON_ID_COORDINATOR);
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
		// Admins are allowed to view upload_types
		$this->assertAccessOk(['controller' => 'UploadTypes', 'action' => 'view', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/upload_types/edit\?type=' . UPLOAD_TYPE_ID_JUNIOR_WAIVER . '#ms');
		$this->assertResponseRegExp('#/upload_types/delete\?type=' . UPLOAD_TYPE_ID_JUNIOR_WAIVER . '#ms');

		$this->assertAccessOk(['controller' => 'UploadTypes', 'action' => 'view', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER_SUB], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/upload_types/edit\?type=' . UPLOAD_TYPE_ID_JUNIOR_WAIVER_SUB . '#ms');
		$this->assertResponseRegExp('#/upload_types/delete\?type=' . UPLOAD_TYPE_ID_JUNIOR_WAIVER_SUB . '#ms');
	}

	/**
	 * Test view method as a manager
	 *
	 * @return void
	 */
	public function testViewAsManager() {
		// Managers are allowed to view upload_types
		$this->assertAccessOk(['controller' => 'UploadTypes', 'action' => 'view', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/upload_types/edit\?type=' . UPLOAD_TYPE_ID_JUNIOR_WAIVER . '#ms');
		$this->assertResponseRegExp('#/upload_types/delete\?type=' . UPLOAD_TYPE_ID_JUNIOR_WAIVER . '#ms');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'UploadTypes', 'action' => 'view', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test view method as a coordinator
	 *
	 * @return void
	 */
	public function testViewAsCoordinator() {
		// Others are not allowed to view upload_types
		$this->assertAccessRedirect(['controller' => 'UploadTypes', 'action' => 'view', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER], PERSON_ID_COORDINATOR);
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
		// Admins are allowed to add upload_types
		$this->assertAccessOk(['controller' => 'UploadTypes', 'action' => 'add'], PERSON_ID_ADMIN);
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers are allowed to add upload_types
		$this->assertAccessOk(['controller' => 'UploadTypes', 'action' => 'add'], PERSON_ID_MANAGER);
	}

	/**
	 * Test add method as a coordinator
	 *
	 * @return void
	 */
	public function testAddAsCoordinator() {
		// Others are not allowed to add upload_types
		$this->assertAccessRedirect(['controller' => 'UploadTypes', 'action' => 'add'], PERSON_ID_COORDINATOR);
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
		// Admins are allowed to edit upload_types
		$this->assertAccessOk(['controller' => 'UploadTypes', 'action' => 'edit', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER], PERSON_ID_ADMIN);
		$this->assertAccessOk(['controller' => 'UploadTypes', 'action' => 'edit', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER_SUB], PERSON_ID_ADMIN);
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers are allowed to edit upload_types
		$this->assertAccessOk(['controller' => 'UploadTypes', 'action' => 'edit', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER], PERSON_ID_MANAGER);

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'UploadTypes', 'action' => 'edit', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test edit method as a coordinator
	 *
	 * @return void
	 */
	public function testEditAsCoordinator() {
		// Others are not allowed to edit upload_types
		$this->assertAccessRedirect(['controller' => 'UploadTypes', 'action' => 'edit', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER], PERSON_ID_COORDINATOR);
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
	 * Test delete method as an admin
	 *
	 * @return void
	 */
	public function testDeleteAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to delete upload types
		$this->assertAccessRedirect(['controller' => 'UploadTypes', 'action' => 'delete', 'type' => UPLOAD_TYPE_ID_DOG_WAIVER],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'UploadTypes', 'action' => 'index'],
			'The upload type has been deleted.', 'Flash.flash.0.message');

		// But not ones with dependencies
		$this->assertAccessRedirect(['controller' => 'UploadTypes', 'action' => 'delete', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'UploadTypes', 'action' => 'index'],
			'#The following records reference this upload type, so it cannot be deleted#', 'Flash.flash.0.message');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers can delete upload types in their affiliate
		$this->assertAccessRedirect(['controller' => 'UploadTypes', 'action' => 'delete', 'type' => UPLOAD_TYPE_ID_DOG_WAIVER],
			PERSON_ID_MANAGER, 'post', [], ['controller' => 'UploadTypes', 'action' => 'index'],
			'The upload type has been deleted.', 'Flash.flash.0.message');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'UploadTypes', 'action' => 'delete', 'type' => UPLOAD_TYPE_ID_JUNIOR_WAIVER_SUB],
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
