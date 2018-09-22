<?php
namespace App\Test\TestCase\Controller;

/**
 * App\Controller\RegionsController Test Case
 */
class RegionsControllerTest extends ControllerTestCase {

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
			'app.regions',
				'app.facilities',
					'app.fields',
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
		$this->assertAccessOk(['controller' => 'Regions', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/regions/edit\?region=' . REGION_ID_EAST . '#ms');
		$this->assertResponseRegExp('#/regions/delete\?region=' . REGION_ID_EAST . '#ms');
		$this->assertResponseRegExp('#/regions/edit\?region=' . REGION_ID_SOUTH . '#ms');
		$this->assertResponseRegExp('#/regions/delete\?region=' . REGION_ID_SOUTH . '#ms');
	}

	/**
	 * Test index method as a manager
	 *
	 * @return void
	 */
	public function testIndexAsManager() {
		// Managers are allowed to get the index, but don't see regions in other affiliates
		$this->assertAccessOk(['controller' => 'Regions', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/regions/edit\?region=' . REGION_ID_EAST . '#ms');
		$this->assertResponseRegExp('#/regions/delete\?region=' . REGION_ID_EAST . '#ms');
		$this->assertResponseNotRegExp('#/regions/edit\?region=' . REGION_ID_SOUTH . '#ms');
		$this->assertResponseNotRegExp('#/regions/delete\?region=' . REGION_ID_SOUTH . '#ms');
	}

	/**
	 * Test index method as a coordinator
	 *
	 * @return void
	 */
	public function testIndexAsCoordinator() {
		// Others are not allowed to get the index
		$this->assertAccessRedirect(['controller' => 'Regions', 'action' => 'index'], PERSON_ID_COORDINATOR);
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
		// Admins are allowed to view regions
		$this->assertAccessOk(['controller' => 'Regions', 'action' => 'view', 'region' => REGION_ID_EAST], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/regions/edit\?region=' . REGION_ID_EAST . '#ms');
		$this->assertResponseRegExp('#/regions/delete\?region=' . REGION_ID_EAST . '#ms');
	}

	/**
	 * Test view method as a manager
	 *
	 * @return void
	 */
	public function testViewAsManager() {
		// Managers are allowed to view regions
		$this->assertAccessOk(['controller' => 'Regions', 'action' => 'view', 'region' => REGION_ID_EAST], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/regions/edit\?region=' . REGION_ID_EAST . '#ms');
		$this->assertResponseRegExp('#/regions/delete\?region=' . REGION_ID_EAST . '#ms');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Regions', 'action' => 'view', 'region' => REGION_ID_SOUTH], PERSON_ID_MANAGER);
	}

	/**
	 * Test view method as a coordinator
	 *
	 * @return void
	 */
	public function testViewAsCoordinator() {
		// Others are not allowed to view regions
		$this->assertAccessRedirect(['controller' => 'Regions', 'action' => 'view', 'region' => REGION_ID_EAST], PERSON_ID_COORDINATOR);
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
		// Admins are allowed to add regions
		$this->assertAccessOk(['controller' => 'Regions', 'action' => 'add'], PERSON_ID_ADMIN);
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers are allowed to add regions
		$this->assertAccessOk(['controller' => 'Regions', 'action' => 'add'], PERSON_ID_MANAGER);
	}

	/**
	 * Test add method as a coordinator
	 *
	 * @return void
	 */
	public function testAddAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
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
		// Others are not allowed to add regions
		$this->assertAccessRedirect(['controller' => 'Regions', 'action' => 'add'], PERSON_ID_COORDINATOR);
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
		// Admins are allowed to edit regions
		$this->assertAccessOk(['controller' => 'Regions', 'action' => 'edit', 'region' => REGION_ID_EAST], PERSON_ID_ADMIN);
		$this->assertAccessOk(['controller' => 'Regions', 'action' => 'edit', 'region' => REGION_ID_SOUTH], PERSON_ID_ADMIN);
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers are allowed to edit regions
		$this->assertAccessOk(['controller' => 'Regions', 'action' => 'edit', 'region' => REGION_ID_EAST], PERSON_ID_MANAGER);

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Regions', 'action' => 'edit', 'region' => REGION_ID_SOUTH], PERSON_ID_MANAGER);
	}

	/**
	 * Test edit method as a coordinator
	 *
	 * @return void
	 */
	public function testEditAsCoordinator() {
		// Others are not allowed to edit regions
		$this->assertAccessRedirect(['controller' => 'Regions', 'action' => 'edit', 'region' => REGION_ID_EAST], PERSON_ID_COORDINATOR);
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

		// Admins are allowed to delete regions
		$this->assertAccessRedirect(['controller' => 'Regions', 'action' => 'delete', 'region' => REGION_ID_NORTH],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Regions', 'action' => 'index'],
			'The region has been deleted.', 'Flash.flash.0.message');

		// But not ones with dependencies
		$this->assertAccessRedirect(['controller' => 'Regions', 'action' => 'delete', 'region' => REGION_ID_EAST],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Regions', 'action' => 'index'],
			'#The following records reference this region, so it cannot be deleted#', 'Flash.flash.0.message');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers can delete regions in their own affiliate
		$this->assertAccessRedirect(['controller' => 'Regions', 'action' => 'delete', 'region' => REGION_ID_NORTH],
			PERSON_ID_MANAGER, 'post', [], ['controller' => 'Regions', 'action' => 'index'],
			'The region has been deleted.', 'Flash.flash.0.message');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Regions', 'action' => 'delete', 'region' => REGION_ID_SOUTH],
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
