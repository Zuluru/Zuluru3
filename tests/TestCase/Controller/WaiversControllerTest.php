<?php
namespace App\Test\TestCase\Controller;

/**
 * App\Controller\WaiversController Test Case
 */
class WaiversControllerTest extends ControllerTestCase {

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
			'app.leagues',
				'app.divisions',
			'app.settings',
			'app.waivers',
				'app.waivers_people',
	];

	/**
	 * Test index method as an admin
	 *
	 * @return void
	 */
	public function testIndexAsAdmin() {
		// Admins are allowed to get the index
		$this->assertAccessOk(['controller' => 'Waivers', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/waivers/edit\?waiver=' . WAIVER_ID_ANNUAL . '#ms');
		$this->assertResponseRegExp('#/waivers/delete\?waiver=' . WAIVER_ID_ANNUAL . '#ms');
		$this->assertResponseRegExp('#/waivers/edit\?waiver=' . WAIVER_ID_PERPETUAL . '#ms');
		$this->assertResponseRegExp('#/waivers/delete\?waiver=' . WAIVER_ID_PERPETUAL . '#ms');
	}

	/**
	 * Test index method as a manager
	 *
	 * @return void
	 */
	public function testIndexAsManager() {
		// Managers are allowed to get the index, but don't see waivers in other affiliates
		$this->assertAccessOk(['controller' => 'Waivers', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/waivers/edit\?waiver=' . WAIVER_ID_ANNUAL . '#ms');
		$this->assertResponseRegExp('#/waivers/delete\?waiver=' . WAIVER_ID_ANNUAL . '#ms');
		$this->assertResponseNotRegExp('#/waivers/edit\?waiver=' . WAIVER_ID_PERPETUAL . '#ms');
		$this->assertResponseNotRegExp('#/waivers/delete\?waiver=' . WAIVER_ID_PERPETUAL . '#ms');
	}

	/**
	 * Test index method as a coordinator
	 *
	 * @return void
	 */
	public function testIndexAsCoordinator() {
		// Others are not allowed to get the index
		$this->assertAccessRedirect(['controller' => 'Waivers', 'action' => 'index'], PERSON_ID_COORDINATOR);
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
		// Admins are allowed to view waivers
		$this->assertAccessOk(['controller' => 'Waivers', 'action' => 'view', 'waiver' => WAIVER_ID_ANNUAL], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/waivers/edit\?waiver=' . WAIVER_ID_ANNUAL . '#ms');
		$this->assertResponseRegExp('#/waivers/delete\?waiver=' . WAIVER_ID_ANNUAL . '#ms');

		$this->assertAccessOk(['controller' => 'Waivers', 'action' => 'view', 'waiver' => WAIVER_ID_PERPETUAL], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/waivers/edit\?waiver=' . WAIVER_ID_PERPETUAL . '#ms');
		$this->assertResponseRegExp('#/waivers/delete\?waiver=' . WAIVER_ID_PERPETUAL . '#ms');
	}

	/**
	 * Test view method as a manager
	 *
	 * @return void
	 */
	public function testViewAsManager() {
		// Managers are allowed to view waivers
		$this->assertAccessOk(['controller' => 'Waivers', 'action' => 'view', 'waiver' => WAIVER_ID_ANNUAL], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/waivers/edit\?waiver=' . WAIVER_ID_ANNUAL . '#ms');
		$this->assertResponseRegExp('#/waivers/delete\?waiver=' . WAIVER_ID_ANNUAL . '#ms');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Waivers', 'action' => 'view', 'waiver' => WAIVER_ID_PERPETUAL], PERSON_ID_MANAGER);
	}

	/**
	 * Test view method as a coordinator
	 *
	 * @return void
	 */
	public function testViewAsCoordinator() {
		// Others are not allowed to view waivers
		$this->assertAccessRedirect(['controller' => 'Waivers', 'action' => 'view', 'waiver' => WAIVER_ID_ANNUAL], PERSON_ID_COORDINATOR);
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
		// Admins are allowed to add waivers
		$this->assertAccessOk(['controller' => 'Waivers', 'action' => 'add'], PERSON_ID_ADMIN);
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers are allowed to add waivers
		$this->assertAccessOk(['controller' => 'Waivers', 'action' => 'add'], PERSON_ID_MANAGER);
	}

	/**
	 * Test add method as a coordinator
	 *
	 * @return void
	 */
	public function testAddAsCoordinator() {
		// Others are not allowed to add waivers
		$this->assertAccessRedirect(['controller' => 'Waivers', 'action' => 'add'], PERSON_ID_COORDINATOR);
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
		// Admins are allowed to edit waivers
		$this->assertAccessOk(['controller' => 'Waivers', 'action' => 'edit', 'waiver' => WAIVER_ID_ANNUAL], PERSON_ID_ADMIN);
		$this->assertAccessOk(['controller' => 'Waivers', 'action' => 'edit', 'waiver' => WAIVER_ID_PERPETUAL], PERSON_ID_ADMIN);
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers are allowed to edit waivers
		$this->assertAccessOk(['controller' => 'Waivers', 'action' => 'edit', 'waiver' => WAIVER_ID_ANNUAL], PERSON_ID_MANAGER);

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Waivers', 'action' => 'edit', 'waiver' => WAIVER_ID_PERPETUAL], PERSON_ID_MANAGER);
	}

	/**
	 * Test edit method as a coordinator
	 *
	 * @return void
	 */
	public function testEditAsCoordinator() {
		// Others are not allowed to edit waivers
		$this->assertAccessRedirect(['controller' => 'Waivers', 'action' => 'edit', 'waiver' => WAIVER_ID_ANNUAL], PERSON_ID_COORDINATOR);
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

		// Admins are allowed to delete waivers
		$this->assertAccessRedirect(['controller' => 'Waivers', 'action' => 'delete', 'waiver' => WAIVER_ID_EVENT],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Waivers', 'action' => 'index'],
			'The waiver has been deleted.', 'Flash.flash.0.message');

		// But not ones with dependencies
		$this->assertAccessRedirect(['controller' => 'Waivers', 'action' => 'delete', 'waiver' => WAIVER_ID_ANNUAL],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Waivers', 'action' => 'index'],
			'#The following records reference this waiver, so it cannot be deleted#', 'Flash.flash.0.message');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers can delete waivers in their affiliate
		$this->assertAccessRedirect(['controller' => 'Waivers', 'action' => 'delete', 'waiver' => WAIVER_ID_EVENT],
			PERSON_ID_MANAGER, 'post', [], ['controller' => 'Waivers', 'action' => 'index'],
			'The waiver has been deleted.', 'Flash.flash.0.message');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Waivers', 'action' => 'delete', 'waiver' => WAIVER_ID_PERPETUAL],
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

	/**
	 * Test sign method as an admin
	 *
	 * @return void
	 */
	public function testSignAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test sign method as a manager
	 *
	 * @return void
	 */
	public function testSignAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test sign method as a coordinator
	 *
	 * @return void
	 */
	public function testSignAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test sign method as a captain
	 *
	 * @return void
	 */
	public function testSignAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test sign method as a player
	 *
	 * @return void
	 */
	public function testSignAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test sign method as someone else
	 *
	 * @return void
	 */
	public function testSignAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test sign method without being logged in
	 *
	 * @return void
	 */
	public function testSignAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test review method as an admin
	 *
	 * @return void
	 */
	public function testReviewAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test review method as a manager
	 *
	 * @return void
	 */
	public function testReviewAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test review method as a coordinator
	 *
	 * @return void
	 */
	public function testReviewAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test review method as a captain
	 *
	 * @return void
	 */
	public function testReviewAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test review method as a player
	 *
	 * @return void
	 */
	public function testReviewAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test review method as someone else
	 *
	 * @return void
	 */
	public function testReviewAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test review method without being logged in
	 *
	 * @return void
	 */
	public function testReviewAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
