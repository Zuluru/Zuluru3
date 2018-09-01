<?php
namespace App\Test\TestCase\Controller;

/**
 * App\Controller\MailingListsController Test Case
 */
class MailingListsControllerTest extends ControllerTestCase {

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
			'app.mailing_lists',
				'app.newsletters',
				'app.subscriptions',
			'app.settings',
	];

	/**
	 * Test index method as an admin
	 *
	 * @return void
	 */
	public function testIndexAsAdmin() {
		// Admins are allowed to get the index
		$this->assertAccessOk(['controller' => 'MailingLists', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/mailing_lists/edit\?mailing_list=' . MAILING_LIST_ID_JUNIORS . '#ms');
		$this->assertResponseRegExp('#/mailing_lists/delete\?mailing_list=' . MAILING_LIST_ID_JUNIORS . '#ms');
		$this->assertResponseRegExp('#/mailing_lists/edit\?mailing_list=' . MAILING_LIST_ID_WOMEN_SUB . '#ms');
		$this->assertResponseRegExp('#/mailing_lists/delete\?mailing_list=' . MAILING_LIST_ID_WOMEN_SUB . '#ms');
	}

	/**
	 * Test index method as a manager
	 *
	 * @return void
	 */
	public function testIndexAsManager() {
		// Managers are allowed to get the index, but don't see mailing lists in other affiliates
		$this->assertAccessOk(['controller' => 'MailingLists', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/mailing_lists/edit\?mailing_list=' . MAILING_LIST_ID_JUNIORS . '#ms');
		$this->assertResponseRegExp('#/mailing_lists/delete\?mailing_list=' . MAILING_LIST_ID_JUNIORS . '#ms');
		$this->assertResponseNotRegExp('#/mailing_lists/edit\?mailing_list=' . MAILING_LIST_ID_WOMEN_SUB . '#ms');
		$this->assertResponseNotRegExp('#/mailing_lists/delete\?mailing_list=' . MAILING_LIST_ID_WOMEN_SUB . '#ms');
	}

	/**
	 * Test index method as a coordinator
	 *
	 * @return void
	 */
	public function testIndexAsCoordinator() {
		// Others are not allowed to get the index
		$this->assertAccessRedirect(['controller' => 'MailingLists', 'action' => 'index'], PERSON_ID_COORDINATOR);
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
		// Admins are allowed to view mailing_lists
		$this->assertAccessOk(['controller' => 'MailingLists', 'action' => 'view', 'mailing_list' => MAILING_LIST_ID_JUNIORS], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/mailing_lists/edit\?mailing_list=' . MAILING_LIST_ID_JUNIORS . '#ms');
		$this->assertResponseRegExp('#/mailing_lists/delete\?mailing_list=' . MAILING_LIST_ID_JUNIORS . '#ms');

		$this->assertAccessOk(['controller' => 'MailingLists', 'action' => 'view', 'mailing_list' => MAILING_LIST_ID_WOMEN_SUB], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/mailing_lists/edit\?mailing_list=' . MAILING_LIST_ID_WOMEN_SUB . '#ms');
		$this->assertResponseRegExp('#/mailing_lists/delete\?mailing_list=' . MAILING_LIST_ID_WOMEN_SUB . '#ms');
	}

	/**
	 * Test view method as a manager
	 *
	 * @return void
	 */
	public function testViewAsManager() {
		// Managers are allowed to view mailing_lists
		$this->assertAccessOk(['controller' => 'MailingLists', 'action' => 'view', 'mailing_list' => MAILING_LIST_ID_JUNIORS], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/mailing_lists/edit\?mailing_list=' . MAILING_LIST_ID_JUNIORS . '#ms');
		$this->assertResponseRegExp('#/mailing_lists/delete\?mailing_list=' . MAILING_LIST_ID_JUNIORS . '#ms');
	}

	/**
	 * Test view method as a coordinator
	 *
	 * @return void
	 */
	public function testViewAsCoordinator() {
		// Others are not allowed to view mailing_lists
		$this->assertAccessRedirect(['controller' => 'MailingLists', 'action' => 'view', 'mailing_list' => MAILING_LIST_ID_JUNIORS], PERSON_ID_COORDINATOR);
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
	 * Test preview method as an admin
	 *
	 * @return void
	 */
	public function testPreviewAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test preview method as a manager
	 *
	 * @return void
	 */
	public function testPreviewAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test preview method as a coordinator
	 *
	 * @return void
	 */
	public function testPreviewAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test preview method as a captain
	 *
	 * @return void
	 */
	public function testPreviewAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test preview method as a player
	 *
	 * @return void
	 */
	public function testPreviewAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test preview method as someone else
	 *
	 * @return void
	 */
	public function testPreviewAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test preview method without being logged in
	 *
	 * @return void
	 */
	public function testPreviewAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as an admin
	 *
	 * @return void
	 */
	public function testAddAsAdmin() {
		// Admins are allowed to add mailing_lists
		$this->assertAccessOk(['controller' => 'MailingLists', 'action' => 'add'], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#<option value="1" selected="selected">Club</option>#ms');
		$this->assertResponseRegExp('#<option value="2">Sub</option>#ms');
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers are allowed to add mailing_lists
		$this->assertAccessOk(['controller' => 'MailingLists', 'action' => 'add'], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#<input type="hidden" name="affiliate_id" value="1"/>#ms');
		$this->assertResponseNotRegExp('#<option value="2">Sub</option>#ms');
	}

	/**
	 * Test add method as a coordinator
	 *
	 * @return void
	 */
	public function testAddAsCoordinator() {
		// Others are not allowed to add mailing_lists
		$this->assertAccessRedirect(['controller' => 'MailingLists', 'action' => 'add'], PERSON_ID_COORDINATOR);
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
		// Admins are allowed to edit mailing_lists
		$this->assertAccessOk(['controller' => 'MailingLists', 'action' => 'edit', 'mailing_list' => MAILING_LIST_ID_JUNIORS], PERSON_ID_ADMIN);
		$this->assertAccessOk(['controller' => 'MailingLists', 'action' => 'edit', 'mailing_list' => MAILING_LIST_ID_WOMEN_SUB], PERSON_ID_ADMIN);
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers are allowed to edit mailing_lists
		$this->assertAccessOk(['controller' => 'MailingLists', 'action' => 'edit', 'mailing_list' => MAILING_LIST_ID_JUNIORS], PERSON_ID_MANAGER);

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'MailingLists', 'action' => 'edit', 'mailing_list' => MAILING_LIST_ID_WOMEN_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test edit method as a coordinator
	 *
	 * @return void
	 */
	public function testEditAsCoordinator() {
		// Others are not allowed to edit mailing_lists
		$this->assertAccessRedirect(['controller' => 'MailingLists', 'action' => 'edit', 'mailing_list' => MAILING_LIST_ID_JUNIORS], PERSON_ID_COORDINATOR);
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

		// Admins are allowed to delete mailing lists
		$this->assertAccessRedirect(['controller' => 'MailingLists', 'action' => 'delete', 'mailing_list' => MAILING_LIST_ID_ACTIVE],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'MailingLists', 'action' => 'index'],
			'The mailing list has been deleted.', 'Flash.flash.0.message');

		// But not ones with dependencies
		$this->assertAccessRedirect(['controller' => 'MailingLists', 'action' => 'delete', 'mailing_list' => MAILING_LIST_ID_JUNIORS],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'MailingLists', 'action' => 'index'],
			'#The following records reference this mailing list, so it cannot be deleted#', 'Flash.flash.0.message');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers can delete mailing lists in their affiliate
		$this->assertAccessRedirect(['controller' => 'MailingLists', 'action' => 'delete', 'mailing_list' => MAILING_LIST_ID_ACTIVE],
			PERSON_ID_MANAGER, 'post', [], ['controller' => 'MailingLists', 'action' => 'index'],
			'The mailing list has been deleted.', 'Flash.flash.0.message');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'MailingLists', 'action' => 'delete', 'mailing_list' => MAILING_LIST_ID_WOMEN_SUB],
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
	 * Test unsubscribe method as an admin
	 *
	 * @return void
	 */
	public function testUnsubscribeAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unsubscribe method as a manager
	 *
	 * @return void
	 */
	public function testUnsubscribeAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unsubscribe method as a coordinator
	 *
	 * @return void
	 */
	public function testUnsubscribeAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unsubscribe method as a captain
	 *
	 * @return void
	 */
	public function testUnsubscribeAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unsubscribe method as a player
	 *
	 * @return void
	 */
	public function testUnsubscribeAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unsubscribe method as someone else
	 *
	 * @return void
	 */
	public function testUnsubscribeAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unsubscribe method without being logged in
	 *
	 * @return void
	 */
	public function testUnsubscribeAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _makeHash method
	 *
	 * @return void
	 */
	public function testMakeHash() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _checkHash method
	 *
	 * @return void
	 */
	public function testCheckHash() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
