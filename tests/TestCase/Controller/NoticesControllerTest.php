<?php
namespace App\Test\TestCase\Controller;

/**
 * App\Controller\NoticesController Test Case
 */
class NoticesControllerTest extends ControllerTestCase {

	/**
	 * Test viewed method as an admin
	 *
	 * @return void
	 */
	public function testViewedAsAdmin(): void {
		// Admins are allowed to mark a notice as viewed
		$this->assertGetAsAccessOk(['controller' => 'Notices', 'action' => 'viewed', 1], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test viewed method as a manager
	 *
	 * @return void
	 */
	public function testViewedAsManager(): void {
		// Managers are allowed to mark a notice as viewed
		$this->assertGetAsAccessOk(['controller' => 'Notices', 'action' => 'viewed', 1], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test viewed method as a coordinator
	 *
	 * @return void
	 */
	public function testViewedAsCoordinator(): void {
		// Coordinators are allowed to mark a notice as viewed
		$this->assertGetAsAccessOk(['controller' => 'Notices', 'action' => 'viewed', 1], PERSON_ID_COORDINATOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test viewed method as a captain
	 *
	 * @return void
	 */
	public function testViewedAsCaptain(): void {
		// Captains are allowed to mark a notice as viewed
		$this->assertGetAsAccessOk(['controller' => 'Notices', 'action' => 'viewed', 1], PERSON_ID_CAPTAIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test viewed method as a player
	 *
	 * @return void
	 */
	public function testViewedAsPlayer(): void {
		// Players are allowed to mark a notice as viewed
		$this->assertGetAsAccessOk(['controller' => 'Notices', 'action' => 'viewed', 1], PERSON_ID_PLAYER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test viewed method as someone else
	 *
	 * @return void
	 */
	public function testViewedAsVisitor(): void {
		// Visitors are allowed to mark a notice as viewed
		$this->assertGetAsAccessOk(['controller' => 'Notices', 'action' => 'viewed', 1], PERSON_ID_VISITOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test viewed method without being logged in
	 *
	 * @return void
	 */
	public function testViewedAsAnonymous(): void {
		// Others are allowed to mark a notice as viewed
		$this->assertGetAnonymousAccessOk(['controller' => 'Notices', 'action' => 'viewed', 1]);
		$this->markTestIncomplete('Not implemented yet.');
	}

}
