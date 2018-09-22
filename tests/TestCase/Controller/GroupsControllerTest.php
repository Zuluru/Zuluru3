<?php
namespace App\Test\TestCase\Controller;

use Cake\Core\Configure;

/**
 * App\Controller\GroupsController Test Case
 */
class GroupsControllerTest extends ControllerTestCase {

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
	];

	/**
	 * Test index method as an admin
	 *
	 * @return void
	 */
	public function testIndexAsAdmin() {
		// Admins are allowed to get the index
		$this->assertAccessOk(['controller' => 'Groups', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/groups/deactivate\?group=' . GROUP_ID_PLAYER . '#ms');
		$this->assertResponseRegExp('#/groups/activate\?group=' . GROUP_ID_OFFICIAL . '#ms');
	}

	/**
	 * Test index method as a manager
	 *
	 * @return void
	 */
	public function testIndexAsManager() {
		// Others are not allowed to get the index
		$this->assertAccessRedirect(['controller' => 'Groups', 'action' => 'index'], PERSON_ID_MANAGER);
	}

	/**
	 * Test index method as a coordinator
	 *
	 * @return void
	 */
	public function testIndexAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
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
	 * Test activate method as an admin
	 *
	 * @return void
	 */
	public function testActivateAsAdmin() {
		// Admins are allowed to activate groups
		$this->assertAccessOk(['controller' => 'Groups', 'action' => 'activate', 'group' => GROUP_ID_OFFICIAL], PERSON_ID_ADMIN, 'getajax');
		$this->assertResponseRegExp('#/groups\\\\/deactivate\?group=' . GROUP_ID_OFFICIAL . '#ms');
	}

	/**
	 * Test activate method as a manager
	 *
	 * @return void
	 */
	public function testActivateAsManager() {
		// Others are not allowed to activate groups
		$this->assertAccessRedirect(['controller' => 'Groups', 'action' => 'activate', 'group' => GROUP_ID_OFFICIAL], PERSON_ID_MANAGER, 'getajax');
	}

	/**
	 * Test activate method as a coordinator
	 *
	 * @return void
	 */
	public function testActivateAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test activate method as a captain
	 *
	 * @return void
	 */
	public function testActivateAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test activate method as a player
	 *
	 * @return void
	 */
	public function testActivateAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test activate method as someone else
	 *
	 * @return void
	 */
	public function testActivateAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test activate method without being logged in
	 *
	 * @return void
	 */
	public function testActivateAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test deactivate method as an admin
	 *
	 * @return void
	 */
	public function testDeactivateAsAdmin() {
		// Admins are allowed to deactivate groups
		$this->assertAccessOk(['controller' => 'Groups', 'action' => 'deactivate', 'group' => GROUP_ID_VOLUNTEER], PERSON_ID_ADMIN, 'getajax');
		$this->assertResponseRegExp('#/groups\\\\/activate\?group=' . GROUP_ID_VOLUNTEER . '#ms');
	}

	/**
	 * Test deactivate method as a manager
	 *
	 * @return void
	 */
	public function testDeactivateAsManager() {
		// Others are not allowed to deactivate groups
		$this->assertAccessRedirect(['controller' => 'Groups', 'action' => 'deactivate', 'group' => GROUP_ID_VOLUNTEER], PERSON_ID_MANAGER, 'getajax');
	}

	/**
	 * Test deactivate method as a coordinator
	 *
	 * @return void
	 */
	public function testDeactivateAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test deactivate method as a captain
	 *
	 * @return void
	 */
	public function testDeactivateAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test deactivate method as a player
	 *
	 * @return void
	 */
	public function testDeactivateAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test deactivate method as someone else
	 *
	 * @return void
	 */
	public function testDeactivateAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test deactivate method without being logged in
	 *
	 * @return void
	 */
	public function testDeactivateAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
