<?php
namespace App\Test\TestCase\Controller;

/**
 * App\Controller\HolidaysController Test Case
 */
class HolidaysControllerTest extends ControllerTestCase {

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
			'app.holidays',
			'app.settings',
	];

	/**
	 * Test index method as an admin
	 *
	 * @return void
	 */
	public function testIndexAsAdmin() {
		// Admins are allowed to get the index
		$this->assertAccessOk(['controller' => 'Holidays', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/holidays/edit\?holiday=' . HOLIDAY_ID_CHRISTMAS . '#ms');
		$this->assertResponseRegExp('#/holidays/delete\?holiday=' . HOLIDAY_ID_CHRISTMAS . '#ms');
		$this->assertResponseRegExp('#/holidays/edit\?holiday=' . HOLIDAY_ID_CHRISTMAS_SUB . '#ms');
		$this->assertResponseRegExp('#/holidays/delete\?holiday=' . HOLIDAY_ID_CHRISTMAS_SUB . '#ms');
	}

	/**
	 * Test index method as a manager
	 *
	 * @return void
	 */
	public function testIndexAsManager() {
		// Managers are allowed to get the index, but don't see holidays in other affiliates
		$this->assertAccessOk(['controller' => 'Holidays', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/holidays/edit\?holiday=' . HOLIDAY_ID_CHRISTMAS . '#ms');
		$this->assertResponseRegExp('#/holidays/delete\?holiday=' . HOLIDAY_ID_CHRISTMAS . '#ms');
		$this->assertResponseNotRegExp('#/holidays/edit\?holiday=' . HOLIDAY_ID_CHRISTMAS_SUB . '#ms');
		$this->assertResponseNotRegExp('#/holidays/delete\?holiday=' . HOLIDAY_ID_CHRISTMAS_SUB . '#ms');
	}

	/**
	 * Test index method as a coordinator
	 *
	 * @return void
	 */
	public function testIndexAsCoordinator() {
		// Others are not allowed to get the index
		$this->assertAccessRedirect(['controller' => 'Holidays', 'action' => 'index'], PERSON_ID_COORDINATOR);
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
		// Admins are allowed to add holidays
		$this->assertAccessOk(['controller' => 'Holidays', 'action' => 'add'], PERSON_ID_ADMIN);
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers are allowed to add holidays
		$this->assertAccessOk(['controller' => 'Holidays', 'action' => 'add'], PERSON_ID_MANAGER);
	}

	/**
	 * Test add method as a coordinator
	 *
	 * @return void
	 */
	public function testAddAsCoordinator() {
		// Others are not allowed to add holidays
		$this->assertAccessRedirect(['controller' => 'Holidays', 'action' => 'add'], PERSON_ID_COORDINATOR);
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
		// Admins are allowed to edit holidays
		$this->assertAccessOk(['controller' => 'Holidays', 'action' => 'edit', 'holiday' => HOLIDAY_ID_CHRISTMAS], PERSON_ID_ADMIN);
		$this->assertAccessOk(['controller' => 'Holidays', 'action' => 'edit', 'holiday' => HOLIDAY_ID_CHRISTMAS_SUB], PERSON_ID_ADMIN);
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers are allowed to edit holidays
		$this->assertAccessOk(['controller' => 'Holidays', 'action' => 'edit', 'holiday' => HOLIDAY_ID_CHRISTMAS], PERSON_ID_MANAGER);

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Holidays', 'action' => 'edit', 'holiday' => HOLIDAY_ID_CHRISTMAS_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test edit method as a coordinator
	 *
	 * @return void
	 */
	public function testEditAsCoordinator() {
		// Others are not allowed to edit holidays
		$this->assertAccessRedirect(['controller' => 'Holidays', 'action' => 'edit', 'holiday' => HOLIDAY_ID_CHRISTMAS], PERSON_ID_COORDINATOR);
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

		// Admins are allowed to delete holidays
		$this->assertAccessRedirect(['controller' => 'Holidays', 'action' => 'delete', 'holiday' => HOLIDAY_ID_CHRISTMAS],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Holidays', 'action' => 'index'],
			'The holiday has been deleted.', 'Flash.flash.0.message');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers can delete holidays in their own affiliate
		$this->assertAccessRedirect(['controller' => 'Holidays', 'action' => 'delete', 'holiday' => HOLIDAY_ID_BOXING_DAY],
			PERSON_ID_MANAGER, 'post', [], ['controller' => 'Holidays', 'action' => 'index'],
			'The holiday has been deleted.', 'Flash.flash.0.message');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Holidays', 'action' => 'delete', 'holiday' => HOLIDAY_ID_CHRISTMAS_SUB],
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
