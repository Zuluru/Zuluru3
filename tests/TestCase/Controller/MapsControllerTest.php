<?php
namespace App\Test\TestCase\Controller;

/**
 * App\Controller\MapsController Test Case
 */
class MapsControllerTest extends ControllerTestCase {

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex() {
		// Anyone is allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Maps', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Maps', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'Maps', 'action' => 'index'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessOk(['controller' => 'Maps', 'action' => 'index'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessOk(['controller' => 'Maps', 'action' => 'index'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessOk(['controller' => 'Maps', 'action' => 'index'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessOk(['controller' => 'Maps', 'action' => 'index']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testView() {
		// Anyone is allowed to view maps
		$this->assertGetAsAccessOk(['controller' => 'Maps', 'action' => 'view', 'field' => FIELD_ID_SUNNYBROOK_GREENSPACE], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Maps', 'action' => 'view', 'field' => FIELD_ID_SUNNYBROOK_GREENSPACE], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'Maps', 'action' => 'view', 'field' => FIELD_ID_SUNNYBROOK_GREENSPACE], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessOk(['controller' => 'Maps', 'action' => 'view', 'field' => FIELD_ID_SUNNYBROOK_GREENSPACE], PERSON_ID_CAPTAIN);

		$this->assertGetAsAccessOk(['controller' => 'Maps', 'action' => 'view', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1], PERSON_ID_PLAYER);
		$this->assertResponseContains('fields[' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1 . '] = {');
		$this->assertResponseContains('fields[' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_2 . '] = {');
		$this->assertResponseContains('fields[' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_3 . '] = {');
		$this->assertResponseNotContains('fields[' . FIELD_ID_SUNNYBROOK_GREENSPACE . '] = {');

		// From any affiliate
		$this->assertGetAsAccessOk(['controller' => 'Maps', 'action' => 'view', 'field' => FIELD_ID_CENTRAL_TECH], PERSON_ID_PLAYER);

		// But not maps that haven't been created yet
		$this->assertGetAsAccessRedirect(['controller' => 'Maps', 'action' => 'view', 'field' => FIELD_ID_MARILYN_BELL],
			PERSON_ID_PLAYER, ['controller' => 'Facilities', 'action' => 'index'],
			'That field has not yet been laid out.');

		// When viewing closed fields, we get shown all fields at that facility, not just open ones
		$this->assertGetAsAccessOk(['controller' => 'Maps', 'action' => 'view', 'field' => FIELD_ID_SUNNYBROOK_GREENSPACE], PERSON_ID_PLAYER);
		$this->assertResponseContains('fields[' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1 . '] = {');
		$this->assertResponseContains('fields[' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_2 . '] = {');
		$this->assertResponseContains('fields[' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_3 . '] = {');
		$this->assertResponseContains('fields[' . FIELD_ID_SUNNYBROOK_GREENSPACE . '] = {');

		$this->assertGetAsAccessOk(['controller' => 'Maps', 'action' => 'view', 'field' => FIELD_ID_SUNNYBROOK_GREENSPACE], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessOk(['controller' => 'Maps', 'action' => 'view', 'field' => FIELD_ID_SUNNYBROOK_GREENSPACE]);
	}

	/**
	 * Test edit method as an admin
	 *
	 * @return void
	 */
	public function testEditAsAdmin() {
		// Admins are allowed to edit maps
		$this->assertGetAsAccessOk(['controller' => 'Maps', 'action' => 'edit', 'field' => FIELD_ID_SUNNYBROOK_GREENSPACE], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers are allowed to edit maps
		$this->assertGetAsAccessOk(['controller' => 'Maps', 'action' => 'edit', 'field' => FIELD_ID_SUNNYBROOK_GREENSPACE], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as others
	 *
	 * @return void
	 */
	public function testEditAsOthers() {
		// Others are not allowed to edit maps
		$this->assertGetAsAccessDenied(['controller' => 'Maps', 'action' => 'edit', 'field' => FIELD_ID_SUNNYBROOK_GREENSPACE], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Maps', 'action' => 'edit', 'field' => FIELD_ID_SUNNYBROOK_GREENSPACE], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Maps', 'action' => 'edit', 'field' => FIELD_ID_SUNNYBROOK_GREENSPACE], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Maps', 'action' => 'edit', 'field' => FIELD_ID_SUNNYBROOK_GREENSPACE], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Maps', 'action' => 'edit', 'field' => FIELD_ID_SUNNYBROOK_GREENSPACE]);
	}

}
