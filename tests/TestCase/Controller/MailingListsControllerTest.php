<?php
namespace App\Test\TestCase\Controller;

use App\PasswordHasher\HasherTrait;

/**
 * App\Controller\MailingListsController Test Case
 */
class MailingListsControllerTest extends ControllerTestCase {

	use HasherTrait;

	private $unsubscribeMessage = 'You have successfully unsubscribed from this mailing list. Note that you may still be on other mailing lists for this site, and some emails (e.g. roster, attendance and score reminders) cannot be opted out of.';

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex() {
		// Admins are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'MailingLists', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertResponseContains('/mailing_lists/edit?mailing_list=' . MAILING_LIST_ID_JUNIORS);
		$this->assertResponseContains('/mailing_lists/delete?mailing_list=' . MAILING_LIST_ID_JUNIORS);
		$this->assertResponseContains('/mailing_lists/edit?mailing_list=' . MAILING_LIST_ID_WOMEN_SUB);
		$this->assertResponseContains('/mailing_lists/delete?mailing_list=' . MAILING_LIST_ID_WOMEN_SUB);

		// Managers are allowed to see the index, but don't see mailing lists in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'MailingLists', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertResponseContains('/mailing_lists/edit?mailing_list=' . MAILING_LIST_ID_JUNIORS);
		$this->assertResponseContains('/mailing_lists/delete?mailing_list=' . MAILING_LIST_ID_JUNIORS);
		$this->assertResponseNotContains('/mailing_lists/edit?mailing_list=' . MAILING_LIST_ID_WOMEN_SUB);
		$this->assertResponseNotContains('/mailing_lists/delete?mailing_list=' . MAILING_LIST_ID_WOMEN_SUB);

		// Others are not allowed to see the index
		$this->assertGetAsAccessDenied(['controller' => 'MailingLists', 'action' => 'index'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'MailingLists', 'action' => 'index'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'MailingLists', 'action' => 'index'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'MailingLists', 'action' => 'index'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'MailingLists', 'action' => 'index']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testView() {
		// Admins are allowed to view mailing lists
		$this->assertGetAsAccessOk(['controller' => 'MailingLists', 'action' => 'view', 'mailing_list' => MAILING_LIST_ID_JUNIORS], PERSON_ID_ADMIN);
		$this->assertResponseContains('/mailing_lists/edit?mailing_list=' . MAILING_LIST_ID_JUNIORS);
		$this->assertResponseContains('/mailing_lists/delete?mailing_list=' . MAILING_LIST_ID_JUNIORS);

		$this->assertGetAsAccessOk(['controller' => 'MailingLists', 'action' => 'view', 'mailing_list' => MAILING_LIST_ID_WOMEN_SUB], PERSON_ID_ADMIN);
		$this->assertResponseContains('/mailing_lists/edit?mailing_list=' . MAILING_LIST_ID_WOMEN_SUB);
		$this->assertResponseContains('/mailing_lists/delete?mailing_list=' . MAILING_LIST_ID_WOMEN_SUB);

		// Managers are allowed to view mailing lists
		$this->assertGetAsAccessOk(['controller' => 'MailingLists', 'action' => 'view', 'mailing_list' => MAILING_LIST_ID_JUNIORS], PERSON_ID_MANAGER);
		$this->assertResponseContains('/mailing_lists/edit?mailing_list=' . MAILING_LIST_ID_JUNIORS);
		$this->assertResponseContains('/mailing_lists/delete?mailing_list=' . MAILING_LIST_ID_JUNIORS);

		// Others are not allowed to view mailing lists
		$this->assertGetAsAccessDenied(['controller' => 'MailingLists', 'action' => 'view', 'mailing_list' => MAILING_LIST_ID_JUNIORS], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'MailingLists', 'action' => 'view', 'mailing_list' => MAILING_LIST_ID_JUNIORS], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'MailingLists', 'action' => 'view', 'mailing_list' => MAILING_LIST_ID_JUNIORS], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'MailingLists', 'action' => 'view', 'mailing_list' => MAILING_LIST_ID_JUNIORS], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'MailingLists', 'action' => 'view', 'mailing_list' => MAILING_LIST_ID_JUNIORS]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test preview method
	 *
	 * @return void
	 */
	public function testPreview() {
		// Admins are allowed to preview
		$this->assertGetAsAccessOk(['controller' => 'MailingLists', 'action' => 'preview', 'mailing_list' => MAILING_LIST_ID_JUNIORS], PERSON_ID_ADMIN);

		// Managers are allowed to preview
		$this->assertGetAsAccessOk(['controller' => 'MailingLists', 'action' => 'preview', 'mailing_list' => MAILING_LIST_ID_JUNIORS], PERSON_ID_MANAGER);

		// Others are not allowed to preview
		$this->assertGetAsAccessDenied(['controller' => 'MailingLists', 'action' => 'preview', 'mailing_list' => MAILING_LIST_ID_JUNIORS], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'MailingLists', 'action' => 'preview', 'mailing_list' => MAILING_LIST_ID_JUNIORS], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'MailingLists', 'action' => 'preview', 'mailing_list' => MAILING_LIST_ID_JUNIORS], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'MailingLists', 'action' => 'preview', 'mailing_list' => MAILING_LIST_ID_JUNIORS], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'MailingLists', 'action' => 'preview', 'mailing_list' => MAILING_LIST_ID_JUNIORS]);
	}

	/**
	 * Test add method as an admin
	 *
	 * @return void
	 */
	public function testAddAsAdmin() {
		// Admins are allowed to add mailing lists
		$this->assertGetAsAccessOk(['controller' => 'MailingLists', 'action' => 'add'], PERSON_ID_ADMIN);
		$this->assertResponseContains('<option value="1" selected="selected">Club</option>');
		$this->assertResponseContains('<option value="2">Sub</option>');
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers are allowed to add mailing lists
		$this->assertGetAsAccessOk(['controller' => 'MailingLists', 'action' => 'add'], PERSON_ID_MANAGER);
		$this->assertResponseContains('<input type="hidden" name="affiliate_id" value="1"/>');
		$this->assertResponseNotContains('<option value="2">Sub</option>');
	}

	/**
	 * Test add method as others
	 *
	 * @return void
	 */
	public function testAddAsOthers() {
		// Others are not allowed to add mailing lists
		$this->assertGetAsAccessDenied(['controller' => 'MailingLists', 'action' => 'add'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'MailingLists', 'action' => 'add'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'MailingLists', 'action' => 'add'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'MailingLists', 'action' => 'add'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'MailingLists', 'action' => 'add']);
	}

	/**
	 * Test edit method as an admin
	 *
	 * @return void
	 */
	public function testEditAsAdmin() {
		// Admins are allowed to edit mailing lists
		$this->assertGetAsAccessOk(['controller' => 'MailingLists', 'action' => 'edit', 'mailing_list' => MAILING_LIST_ID_JUNIORS], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'MailingLists', 'action' => 'edit', 'mailing_list' => MAILING_LIST_ID_WOMEN_SUB], PERSON_ID_ADMIN);
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers are allowed to edit mailing lists
		$this->assertGetAsAccessOk(['controller' => 'MailingLists', 'action' => 'edit', 'mailing_list' => MAILING_LIST_ID_JUNIORS], PERSON_ID_MANAGER);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'MailingLists', 'action' => 'edit', 'mailing_list' => MAILING_LIST_ID_WOMEN_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test edit method as others
	 *
	 * @return void
	 */
	public function testEditAsOthers() {
		// Others are not allowed to edit mailing lists
		$this->assertGetAsAccessDenied(['controller' => 'MailingLists', 'action' => 'edit', 'mailing_list' => MAILING_LIST_ID_JUNIORS], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'MailingLists', 'action' => 'edit', 'mailing_list' => MAILING_LIST_ID_JUNIORS], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'MailingLists', 'action' => 'edit', 'mailing_list' => MAILING_LIST_ID_JUNIORS], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'MailingLists', 'action' => 'edit', 'mailing_list' => MAILING_LIST_ID_JUNIORS], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'MailingLists', 'action' => 'edit', 'mailing_list' => MAILING_LIST_ID_JUNIORS]);
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
		$this->assertPostAsAccessRedirect(['controller' => 'MailingLists', 'action' => 'delete', 'mailing_list' => MAILING_LIST_ID_ACTIVE],
			PERSON_ID_ADMIN, [], ['controller' => 'MailingLists', 'action' => 'index'],
			'The mailing list has been deleted.');

		// But not ones with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'MailingLists', 'action' => 'delete', 'mailing_list' => MAILING_LIST_ID_JUNIORS],
			PERSON_ID_ADMIN, [], ['controller' => 'MailingLists', 'action' => 'index'],
			'#The following records reference this mailing list, so it cannot be deleted#');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to delete mailing lists in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'MailingLists', 'action' => 'delete', 'mailing_list' => MAILING_LIST_ID_ACTIVE],
			PERSON_ID_MANAGER, [], ['controller' => 'MailingLists', 'action' => 'index'],
			'The mailing list has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'MailingLists', 'action' => 'delete', 'mailing_list' => MAILING_LIST_ID_WOMEN_SUB],
			PERSON_ID_MANAGER);
	}

	/**
	 * Test delete method as others
	 *
	 * @return void
	 */
	public function testDeleteAsOthers() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Others are not allowed to delete mailing lists
		$this->assertPostAsAccessDenied(['controller' => 'MailingLists', 'action' => 'delete', 'mailing_list' => MAILING_LIST_ID_JUNIORS],
			PERSON_ID_COORDINATOR);
		$this->assertPostAsAccessDenied(['controller' => 'MailingLists', 'action' => 'delete', 'mailing_list' => MAILING_LIST_ID_JUNIORS],
			PERSON_ID_CAPTAIN);
		$this->assertPostAsAccessDenied(['controller' => 'MailingLists', 'action' => 'delete', 'mailing_list' => MAILING_LIST_ID_JUNIORS],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'MailingLists', 'action' => 'delete', 'mailing_list' => MAILING_LIST_ID_JUNIORS],
			PERSON_ID_VISITOR);
		$this->assertPostAnonymousAccessDenied(['controller' => 'MailingLists', 'action' => 'delete', 'mailing_list' => MAILING_LIST_ID_JUNIORS]);
	}

	/**
	 * Test unsubscribe method as an admin
	 *
	 * @return void
	 */
	public function testUnsubscribeAsAdmin() {
		// Admins are allowed to unsubscribe
		$this->assertGetAsAccessRedirect(['controller' => 'MailingLists', 'action' => 'unsubscribe', 'list' => MAILING_LIST_ID_JUNIORS],
			PERSON_ID_ADMIN, '/',
			$this->unsubscribeMessage);
		$this->assertGetAsAccessRedirect(['controller' => 'MailingLists', 'action' => 'unsubscribe', 'list' => MAILING_LIST_ID_MASTERS],
			PERSON_ID_ADMIN, '/',
			'You are not subscribed to this mailing list.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unsubscribe method as a manager
	 *
	 * @return void
	 */
	public function testUnsubscribeAsManager() {
		// Managers are allowed to unsubscribe
		$this->assertGetAsAccessRedirect(['controller' => 'MailingLists', 'action' => 'unsubscribe', 'list' => MAILING_LIST_ID_JUNIORS],
			PERSON_ID_MANAGER, '/',
			$this->unsubscribeMessage);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unsubscribe method as a coordinator
	 *
	 * @return void
	 */
	public function testUnsubscribeAsCoordinator() {
		// Coordinators are allowed to unsubscribe
		$this->assertGetAsAccessRedirect(['controller' => 'MailingLists', 'action' => 'unsubscribe', 'list' => MAILING_LIST_ID_JUNIORS],
			PERSON_ID_COORDINATOR, '/',
			$this->unsubscribeMessage);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unsubscribe method as a captain
	 *
	 * @return void
	 */
	public function testUnsubscribeAsCaptain() {
		// Captains are allowed to unsubscribe
		$this->assertGetAsAccessRedirect(['controller' => 'MailingLists', 'action' => 'unsubscribe', 'list' => MAILING_LIST_ID_JUNIORS],
			PERSON_ID_CAPTAIN, '/',
			$this->unsubscribeMessage);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unsubscribe method as a player
	 *
	 * @return void
	 */
	public function testUnsubscribeAsPlayer() {
		// Players are allowed to unsubscribe
		$this->assertGetAsAccessRedirect(['controller' => 'MailingLists', 'action' => 'unsubscribe', 'list' => MAILING_LIST_ID_JUNIORS],
			PERSON_ID_PLAYER, '/',
			$this->unsubscribeMessage);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unsubscribe method as someone else
	 *
	 * @return void
	 */
	public function testUnsubscribeAsVisitor() {
		// Visitors are allowed to unsubscribe
		$this->assertGetAsAccessRedirect(['controller' => 'MailingLists', 'action' => 'unsubscribe', 'list' => MAILING_LIST_ID_JUNIORS],
			PERSON_ID_VISITOR, '/',
			$this->unsubscribeMessage);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unsubscribe method without being logged in
	 *
	 * @return void
	 */
	public function testUnsubscribeAsAnonymous() {
		// Others are allowed to unsubscribe
		$this->assertGetAnonymousAccessRedirect(['controller' => 'MailingLists', 'action' => 'unsubscribe', 'list' => MAILING_LIST_ID_JUNIORS, 'person' => PERSON_ID_PLAYER, 'code' => $this->_makeHash([PERSON_ID_PLAYER, MAILING_LIST_ID_JUNIORS])],
			'/', $this->unsubscribeMessage);
		$this->markTestIncomplete('Not implemented yet.');
	}

}
