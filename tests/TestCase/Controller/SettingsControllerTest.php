<?php
namespace App\Test\TestCase\Controller;

/**
 * App\Controller\SettingsController Test Case
 */
class SettingsControllerTest extends ControllerTestCase {

	/**
	 * Test edit method as an admin
	 */
	public function testEditAsAdmin(): void {
		// Admins are allowed to edit settings
		$this->assertGetAsAccessOk(['controller' => 'Settings', 'action' => 'edit', 'organization'], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as a manager
	 */
	public function testEditAsManager(): void {
		// Managers are allowed to edit settings
		$this->assertGetAsAccessOk(['controller' => 'Settings', 'action' => 'edit', 'organization'], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as others
	 */
	public function testEditAsOthers(): void {
		// Others are not allowed to edit
		$this->assertGetAsAccessDenied(['controller' => 'Settings', 'action' => 'edit', 'organization'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Settings', 'action' => 'edit', 'organization'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Settings', 'action' => 'edit', 'organization'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Settings', 'action' => 'edit', 'organization'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Settings', 'action' => 'edit', 'organization']);
	}

}
