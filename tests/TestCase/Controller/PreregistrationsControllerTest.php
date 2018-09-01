<?php
namespace App\Test\TestCase\Controller;

/**
 * App\Controller\PreregistrationsController Test Case
 */
class PreregistrationsControllerTest extends ControllerTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.event_types',
		'app.affiliates',
			'app.users',
				'app.people',
					'app.affiliates_people',
					'app.people_people',
			'app.groups',
				'app.groups_people',
			'app.leagues',
				'app.divisions',
			'app.events',
				'app.preregistrations',
			'app.settings',
	];

	/**
	 * Test index method as an admin
	 *
	 * @return void
	 */
	public function testIndexAsAdmin() {
		// Admins are allowed to get the index
		$this->assertAccessOk(['controller' => 'Preregistrations', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/preregistrations/delete\?preregistration=' . PREREGISTRATION_ID_ADMIN_MEMBERSHIP . '#ms');
		$this->assertResponseRegExp('#/preregistrations/delete\?preregistration=' . PREREGISTRATION_ID_DUPLICATE_LEAGUE_INDIVIDUAL_SUB . '#ms');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test index method as a manager
	 *
	 * @return void
	 */
	public function testIndexAsManager() {
		// Managers are allowed to get the index, but don't see preregistrations in other affiliates
		$this->assertAccessOk(['controller' => 'Preregistrations', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/preregistrations/delete\?preregistration=' . PREREGISTRATION_ID_ADMIN_MEMBERSHIP . '#ms');
		$this->assertResponseNotRegExp('#/preregistrations/delete\?preregistration=' . PREREGISTRATION_ID_DUPLICATE_LEAGUE_INDIVIDUAL_SUB . '#ms');
	}

	/**
	 * Test index method as a coordinator
	 *
	 * @return void
	 */
	public function testIndexAsCoordinator() {
		// Others are not allowed to get the index
		$this->assertAccessRedirect(['controller' => 'Preregistrations', 'action' => 'index'], PERSON_ID_COORDINATOR);
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
	 * Test add method as an admin
	 *
	 * @return void
	 */
	public function testAddAsAdmin() {
		// Admins are allowed to add preregistrations
		$this->assertAccessOk(['controller' => 'Preregistrations', 'action' => 'add'], PERSON_ID_ADMIN);
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers are allowed to add preregistrations
		$this->assertAccessOk(['controller' => 'Preregistrations', 'action' => 'add'], PERSON_ID_MANAGER);
	}

	/**
	 * Test add method as a coordinator
	 *
	 * @return void
	 */
	public function testAddAsCoordinator() {
		// Others are not allowed to add preregistrations
		$this->assertAccessRedirect(['controller' => 'Preregistrations', 'action' => 'add'], PERSON_ID_COORDINATOR);
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
	 * Test delete method as an admin
	 *
	 * @return void
	 */
	public function testDeleteAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to delete preregistrations
		$this->assertAccessRedirect(['controller' => 'Preregistrations', 'action' => 'delete', 'preregistration' => PREREGISTRATION_ID_ADMIN_MEMBERSHIP],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Preregistrations', 'action' => 'index'],
			'The preregistration has been deleted.', 'Flash.flash.0.message');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers can delete preregistrations in their affiliate
		$this->assertAccessRedirect(['controller' => 'Preregistrations', 'action' => 'delete', 'preregistration' => PREREGISTRATION_ID_ADMIN_MEMBERSHIP],
			PERSON_ID_MANAGER, 'post', [], ['controller' => 'Preregistrations', 'action' => 'index'],
			'The preregistration has been deleted.', 'Flash.flash.0.message');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Preregistrations', 'action' => 'delete', 'preregistration' => PREREGISTRATION_ID_DUPLICATE_LEAGUE_INDIVIDUAL_SUB],
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
