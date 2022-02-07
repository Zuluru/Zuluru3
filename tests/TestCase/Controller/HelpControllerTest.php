<?php
namespace App\Test\TestCase\Controller;

/**
 * App\Controller\HelpController Test Case
 */
class HelpControllerTest extends ControllerTestCase {

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testView(): void {
		// Anyone is allowed to view
		$this->assertGetAsAccessOk(['controller' => 'Help', 'action' => 'view'], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Help', 'action' => 'view'], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'Help', 'action' => 'view'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessOk(['controller' => 'Help', 'action' => 'view'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessOk(['controller' => 'Help', 'action' => 'view'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessOk(['controller' => 'Help', 'action' => 'view'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessOk(['controller' => 'Help', 'action' => 'view']);
	}

}
