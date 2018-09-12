<?php
namespace App\Test\TestCase\Controller;

/**
 * App\Controller\AffiliatesController Test Case
 */
class AffiliatesControllerTest extends ControllerTestCase {

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
					'app.credits',
			'app.groups',
				'app.groups_people',
			'app.upload_types',
			'app.regions',
			'app.leagues',
				'app.divisions',
					'app.teams',
			'app.franchises',
			'app.questions',
			'app.questionnaires',
			'app.events',
			'app.categories',
			'app.badges',
			'app.contacts',
			'app.holidays',
			'app.mailing_lists',
			'app.settings',
			'app.waivers',
	];

	/**
	 * Test index method as an admin
	 *
	 * @return void
	 */
	public function testIndexAsAdmin() {
		// Admins are allowed to get the index
		$this->assertAccessOk(['controller' => 'Affiliates', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/affiliates/edit\?affiliate=' . AFFILIATE_ID_CLUB . '#ms');
		$this->assertResponseRegExp('#/affiliates/delete\?affiliate=' . AFFILIATE_ID_CLUB . '#ms');
	}

	/**
	 * Test index method as a manager
	 *
	 * @return void
	 */
	public function testIndexAsManager() {
		$this->assertAccessRedirect(['controller' => 'Affiliates', 'action' => 'index'], PERSON_ID_MANAGER);
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
	 * Test view method as an admin
	 *
	 * @return void
	 */
	public function testViewAsAdmin() {
		// Admins are allowed to view affiliates
		$this->assertAccessOk(['controller' => 'Affiliates', 'action' => 'view', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/affiliates/edit\?affiliate=' . AFFILIATE_ID_CLUB . '#ms');
		$this->assertResponseRegExp('#/affiliates/delete\?affiliate=' . AFFILIATE_ID_CLUB . '#ms');
	}

	/**
	 * Test view method as a manager
	 *
	 * @return void
	 */
	public function testViewAsManager() {
		// Others are not allowed to view affiliates
		$this->assertAccessRedirect(['controller' => 'Affiliates', 'action' => 'view', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test view method as a coordinator
	 *
	 * @return void
	 */
	public function testViewAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
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
		// Admins are allowed to add affiliates
		$this->assertAccessOk(['controller' => 'Affiliates', 'action' => 'add'], PERSON_ID_ADMIN);
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Others are not allowed to add affiliates
		$this->assertAccessRedirect(['controller' => 'Affiliates', 'action' => 'add'], PERSON_ID_MANAGER);
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
		// Admins are allowed to edit affiliates
		$this->assertAccessOk(['controller' => 'Affiliates', 'action' => 'edit', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_ADMIN);
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Others are not allowed to edit affiliates
		$this->assertAccessRedirect(['controller' => 'Affiliates', 'action' => 'edit', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test edit method as a coordinator
	 *
	 * @return void
	 */
	public function testEditAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
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

		// Admins are allowed to delete affiliates
		$this->assertAccessRedirect(['controller' => 'Affiliates', 'action' => 'delete', 'affiliate' => AFFILIATE_ID_EMPTY],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Affiliates', 'action' => 'index'],
			'The affiliate has been deleted.', 'Flash.flash.0.message');
		// TODOLATER: Add checks for success messages everywhere

		// But not ones with dependencies
		$this->assertAccessRedirect(['controller' => 'Affiliates', 'action' => 'delete', 'affiliate' => AFFILIATE_ID_CLUB],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Affiliates', 'action' => 'index'],
			'#The following records reference this affiliate, so it cannot be deleted#', 'Flash.flash.0.message');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers cannot delete affiliates
		$this->assertAccessRedirect(['controller' => 'Affiliates', 'action' => 'delete', 'affiliate' => AFFILIATE_ID_CLUB],
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
	 * Test add_manager method as an admin
	 *
	 * @return void
	 */
	public function testAddManagerAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to add managers
		$this->assertAccessOk(['controller' => 'Affiliates', 'action' => 'add_manager', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_ADMIN);

		// Try the search page
		$this->assertAccessOk(['controller' => 'Affiliates', 'action' => 'add_manager', 'affiliate' => AFFILIATE_ID_CLUB],
			PERSON_ID_ADMIN, 'post', [
			'affiliate_id' => '1',
			'first_name' => 'pam',
			'last_name' => '',
			'sort' => 'last_name',
			'direction' => 'asc',
		]);
		$return = urlencode(\App\Lib\base64_url_encode('/affiliates/add_manager?affiliate=' . AFFILIATE_ID_CLUB));
		$this->assertResponseRegExp('#/affiliates/add_manager\?person=' . PERSON_ID_PLAYER . '&amp;return=' . $return . '&amp;affiliate=' . AFFILIATE_ID_CLUB . '#ms');

		// Try to add the manager
		$this->assertAccessRedirect(['controller' => 'Affiliates', 'action' => 'add_manager', 'person' => PERSON_ID_PLAYER, 'affiliate' => AFFILIATE_ID_CLUB],
			PERSON_ID_ADMIN, 'get', [], ['controller' => 'Affiliates', 'action' => 'view', 'affiliate' => AFFILIATE_ID_CLUB],
			'Added Pam Player as manager.', 'Flash.flash.0.message');

		// Make sure they were added successfully
		$this->assertAccessOk(['controller' => 'Affiliates', 'action' => 'view', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/affiliates/remove_manager\?affiliate=' . AFFILIATE_ID_CLUB . '&amp;person=' . PERSON_ID_PLAYER . '#ms');
	}

	/**
	 * Test add_manager method as a manager
	 *
	 * @return void
	 */
	public function testAddManagerAsManager() {
		// Others are not allowed to add managers
		$this->assertAccessRedirect(['controller' => 'Affiliates', 'action' => 'add_manager', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test add_manager method as a coordinator
	 *
	 * @return void
	 */
	public function testAddManagerAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_manager method as a captain
	 *
	 * @return void
	 */
	public function testAddManagerAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_manager method as a player
	 *
	 * @return void
	 */
	public function testAddManagerAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_manager method as someone else
	 *
	 * @return void
	 */
	public function testAddManagerAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_manager method without being logged in
	 *
	 * @return void
	 */
	public function testAddManagerAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_manager method as an admin
	 *
	 * @return void
	 */
	public function testRemoveManagerAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to remove managers
		$this->assertAccessOk(['controller' => 'Affiliates', 'action' => 'view', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/affiliates/remove_manager\?affiliate=' . AFFILIATE_ID_CLUB . '&amp;person=' . PERSON_ID_MANAGER . '#ms');

		$this->assertAccessRedirect(['controller' => 'Affiliates', 'action' => 'remove_manager', 'affiliate' => AFFILIATE_ID_CLUB, 'person' => PERSON_ID_MANAGER],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Affiliates', 'action' => 'view', 'affiliate' => AFFILIATE_ID_CLUB],
			'Successfully removed manager.', 'Flash.flash.0.message');
		$this->assertEquals('If this person is no longer going to be managing anything, you should also edit their profile and deselect the "Manager" option.', $this->_requestSession->read('Flash.flash.1.message'));

		// Make sure they were removed successfully
		$this->assertAccessOk(['controller' => 'Affiliates', 'action' => 'view', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_ADMIN);
		$this->assertResponseNotRegExp('#/affiliates/remove_manager\?affiliate=' . AFFILIATE_ID_CLUB . '&amp;person=' . PERSON_ID_MANAGER . '#ms');
	}

	/**
	 * Test remove_manager method as a manager
	 *
	 * @return void
	 */
	public function testRemoveManagerAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Others are not allowed to remove managers
		$this->assertAccessRedirect(['controller' => 'Affiliates', 'action' => 'remove_manager', 'affiliate' => AFFILIATE_ID_CLUB, 'person' => PERSON_ID_MANAGER], PERSON_ID_MANAGER, 'post');
	}

	/**
	 * Test remove_manager method as a coordinator
	 *
	 * @return void
	 */
	public function testRemoveManagerAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_manager method as a captain
	 *
	 * @return void
	 */
	public function testRemoveManagerAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_manager method as a player
	 *
	 * @return void
	 */
	public function testRemoveManagerAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_manager method as someone else
	 *
	 * @return void
	 */
	public function testRemoveManagerAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_manager method without being logged in
	 *
	 * @return void
	 */
	public function testRemoveManagerAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test select method as an admin
	 *
	 * @return void
	 */
	public function testSelectAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test select method as a manager
	 *
	 * @return void
	 */
	public function testSelectAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test select method as a coordinator
	 *
	 * @return void
	 */
	public function testSelectAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test select method as a captain
	 *
	 * @return void
	 */
	public function testSelectAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test select method as a player
	 *
	 * @return void
	 */
	public function testSelectAsPlayer() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Anyone can select an affiliate
		$this->assertAccessOk(['controller' => 'Affiliates', 'action' => 'select'], PERSON_ID_PLAYER);
		$this->assertResponseRegExp('#<option value="1">Club</option><option value="2">Sub</option>#ms');

		$this->assertAccessRedirect(['controller' => 'Affiliates', 'action' => 'select'],
			PERSON_ID_PLAYER, 'post', [
				'affiliate' => '1',
			], null, false);
		$this->assertSession('1', 'Zuluru.CurrentAffiliate');
	}

	/**
	 * Test select method as someone else
	 *
	 * @return void
	 */
	public function testSelectAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test select method without being logged in
	 *
	 * @return void
	 */
	public function testSelectAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test view_all method as an admin
	 *
	 * @return void
	 */
	public function testViewAllAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test view_all method as a manager
	 *
	 * @return void
	 */
	public function testViewAllAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test view_all method as a coordinator
	 *
	 * @return void
	 */
	public function testViewAllAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test view_all method as a captain
	 *
	 * @return void
	 */
	public function testViewAllAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test view_all method as a player
	 *
	 * @return void
	 */
	public function testViewAllAsPlayer() {
		// Anyone can reset to showing all affiliates
		$this->session(['Zuluru.CurrentAffiliate' => 1]);
		$this->assertAccessRedirect(['controller' => 'Affiliates', 'action' => 'view_all'],
			PERSON_ID_PLAYER, 'get', [], null, false);
		$this->assertSession(null, 'Zuluru.CurrentAffiliate');
	}

	/**
	 * Test view_all method as someone else
	 *
	 * @return void
	 */
	public function testViewAllAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test view_all method without being logged in
	 *
	 * @return void
	 */
	public function testViewAllAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
