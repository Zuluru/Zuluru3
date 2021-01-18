<?php
namespace App\Test\TestCase\Controller;

/**
 * App\Controller\PagesController Test Case
 */
class PagesControllerTest extends ControllerTestCase {

	/**
	 * Test display method
	 *
	 * @return void
	 */
	public function testDisplay() {
		// Anyone is allowed to display pages
		$this->assertGetAsAccessOk(['controller' => 'Pages', 'action' => 'display', 'privacy'], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Pages', 'action' => 'display', 'privacy'], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'Pages', 'action' => 'display', 'privacy'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessOk(['controller' => 'Pages', 'action' => 'display', 'privacy'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessOk(['controller' => 'Pages', 'action' => 'display', 'privacy'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessOk(['controller' => 'Pages', 'action' => 'display', 'privacy'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessOk(['controller' => 'Pages', 'action' => 'display', 'privacy']);
	}

}
