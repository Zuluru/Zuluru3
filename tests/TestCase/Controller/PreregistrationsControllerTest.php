<?php
namespace App\Test\TestCase\Controller;

/**
 * App\Controller\PreregistrationsController Test Case
 */
class PreregistrationsControllerTest extends ControllerTestCase {

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex() {
		// Admins are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Preregistrations', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertResponseContains('/preregistrations/delete?preregistration=' . PREREGISTRATION_ID_ADMIN_MEMBERSHIP);
		$this->assertResponseContains('/preregistrations/delete?preregistration=' . PREREGISTRATION_ID_DUPLICATE_LEAGUE_INDIVIDUAL_SUB);
		$this->markTestIncomplete('Not implemented yet.');

		// Managers are allowed to see the index, but don't see preregistrations in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'Preregistrations', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertResponseContains('/preregistrations/delete?preregistration=' . PREREGISTRATION_ID_ADMIN_MEMBERSHIP);
		$this->assertResponseNotContains('/preregistrations/delete?preregistration=' . PREREGISTRATION_ID_DUPLICATE_LEAGUE_INDIVIDUAL_SUB);

		// Others are not allowed to see the index
		$this->assertGetAsAccessDenied(['controller' => 'Preregistrations', 'action' => 'index'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Preregistrations', 'action' => 'index'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Preregistrations', 'action' => 'index'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Preregistrations', 'action' => 'index'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Preregistrations', 'action' => 'index']);
	}

	/**
	 * Test add method as an admin
	 *
	 * @return void
	 */
	public function testAddAsAdmin() {
		// Admins are allowed to add preregistrations
		$this->assertGetAsAccessOk(['controller' => 'Preregistrations', 'action' => 'add'], PERSON_ID_ADMIN);
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers are allowed to add preregistrations
		$this->assertGetAsAccessOk(['controller' => 'Preregistrations', 'action' => 'add'], PERSON_ID_MANAGER);
	}

	/**
	 * Test add method as others
	 *
	 * @return void
	 */
	public function testAddAsOthers() {
		// Others are not allowed to add preregistrations
		$this->assertGetAsAccessDenied(['controller' => 'Preregistrations', 'action' => 'add'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Preregistrations', 'action' => 'add'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Preregistrations', 'action' => 'add'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Preregistrations', 'action' => 'add'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Preregistrations', 'action' => 'add']);
	}

	/**
	 * Test delete method as an admin
	 *
	 * @return void
	 */
	public function testDeleteAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to delete preregistrations
		$this->assertPostAsAccessRedirect(['controller' => 'Preregistrations', 'action' => 'delete', 'preregistration' => PREREGISTRATION_ID_ADMIN_MEMBERSHIP],
			PERSON_ID_ADMIN, [], ['controller' => 'Preregistrations', 'action' => 'index'],
			'The preregistration has been deleted.');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to delete preregistrations in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Preregistrations', 'action' => 'delete', 'preregistration' => PREREGISTRATION_ID_ADMIN_MEMBERSHIP],
			PERSON_ID_MANAGER, [], ['controller' => 'Preregistrations', 'action' => 'index'],
			'The preregistration has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Preregistrations', 'action' => 'delete', 'preregistration' => PREREGISTRATION_ID_DUPLICATE_LEAGUE_INDIVIDUAL_SUB],
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

		// Others are not allowed to delete preregistrations
		$this->assertPostAsAccessDenied(['controller' => 'Preregistrations', 'action' => 'delete', 'preregistration' => PREREGISTRATION_ID_ADMIN_MEMBERSHIP],
			PERSON_ID_COORDINATOR);
		$this->assertPostAsAccessDenied(['controller' => 'Preregistrations', 'action' => 'delete', 'preregistration' => PREREGISTRATION_ID_ADMIN_MEMBERSHIP],
			PERSON_ID_CAPTAIN);
		$this->assertPostAsAccessDenied(['controller' => 'Preregistrations', 'action' => 'delete', 'preregistration' => PREREGISTRATION_ID_ADMIN_MEMBERSHIP],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'Preregistrations', 'action' => 'delete', 'preregistration' => PREREGISTRATION_ID_ADMIN_MEMBERSHIP],
			PERSON_ID_VISITOR);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Preregistrations', 'action' => 'delete', 'preregistration' => PREREGISTRATION_ID_ADMIN_MEMBERSHIP]);
	}

}
