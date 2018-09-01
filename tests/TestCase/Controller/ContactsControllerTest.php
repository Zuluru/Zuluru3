<?php
namespace App\Test\TestCase\Controller;

use Cake\Core\Configure;

/**
 * App\Controller\ContactsController Test Case
 */
class ContactsControllerTest extends ControllerTestCase {

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
			'app.contacts',
			'app.settings',
	];

	/**
	 * Test index method as an admin
	 *
	 * @return void
	 */
	public function testIndexAsAdmin() {
		// Admins are allowed to get the index
		$this->assertAccessOk(['controller' => 'Contacts', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/contacts/edit\?contact=' . CONTACT_ID_LEAGUES . '#ms');
		$this->assertResponseRegExp('#/contacts/delete\?contact=' . CONTACT_ID_LEAGUES . '#ms');
		$this->assertResponseRegExp('#/contacts/edit\?contact=' . CONTACT_ID_LEAGUES_SUB . '#ms');
		$this->assertResponseRegExp('#/contacts/delete\?contact=' . CONTACT_ID_LEAGUES_SUB . '#ms');
	}

	/**
	 * Test index method as a manager
	 *
	 * @return void
	 */
	public function testIndexAsManager() {
		// Managers are allowed to get the index, but don't see contacts in other affiliates
		$this->assertAccessOk(['controller' => 'Contacts', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/contacts/edit\?contact=' . CONTACT_ID_LEAGUES . '#ms');
		$this->assertResponseRegExp('#/contacts/delete\?contact=' . CONTACT_ID_LEAGUES . '#ms');
		$this->assertResponseNotRegExp('#/contacts/edit\?contact=' . CONTACT_ID_LEAGUES_SUB . '#ms');
		$this->assertResponseNotRegExp('#/contacts/delete\?contact=' . CONTACT_ID_LEAGUES_SUB . '#ms');
	}

	/**
	 * Test index method as a coordinator
	 *
	 * @return void
	 */
	public function testIndexAsCoordinator() {
		// Others are not allowed to get the index
		$this->assertAccessRedirect(['controller' => 'Contacts', 'action' => 'index'], PERSON_ID_COORDINATOR);
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
		// Admins are allowed to add contacts
		$this->assertAccessOk(['controller' => 'Contacts', 'action' => 'add'], PERSON_ID_ADMIN);
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers are allowed to add contacts
		$this->assertAccessOk(['controller' => 'Contacts', 'action' => 'add'], PERSON_ID_MANAGER);
	}

	/**
	 * Test add method as a coordinator
	 *
	 * @return void
	 */
	public function testAddAsCoordinator() {
		// Others are not allowed to add contacts
		$this->assertAccessRedirect(['controller' => 'Contacts', 'action' => 'add'], PERSON_ID_COORDINATOR);
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
		// Admins are allowed to edit contacts
		$this->assertAccessOk(['controller' => 'Contacts', 'action' => 'edit', 'contact' => CONTACT_ID_LEAGUES], PERSON_ID_ADMIN);
		$this->assertAccessOk(['controller' => 'Contacts', 'action' => 'edit', 'contact' => CONTACT_ID_LEAGUES_SUB], PERSON_ID_ADMIN);
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers are allowed to edit contacts
		$this->assertAccessOk(['controller' => 'Contacts', 'action' => 'edit', 'contact' => CONTACT_ID_LEAGUES], PERSON_ID_MANAGER);

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Contacts', 'action' => 'edit', 'contact' => CONTACT_ID_LEAGUES_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test edit method as a coordinator
	 *
	 * @return void
	 */
	public function testEditAsCoordinator() {
		// Others are not allowed to edit contacts
		$this->assertAccessRedirect(['controller' => 'Contacts', 'action' => 'edit', 'contact' => CONTACT_ID_LEAGUES], PERSON_ID_COORDINATOR);
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

		// Admins are allowed to delete contacts
		$this->assertAccessRedirect(['controller' => 'Contacts', 'action' => 'delete', 'contact' => CONTACT_ID_LEAGUES],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Contacts', 'action' => 'index'],
			'The contact has been deleted.', 'Flash.flash.0.message');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers can delete contacts in their affiliate
		$this->assertAccessRedirect(['controller' => 'Contacts', 'action' => 'delete', 'contact' => CONTACT_ID_LEAGUES],
			PERSON_ID_MANAGER, 'post', [], ['controller' => 'Contacts', 'action' => 'index'],
			'The contact has been deleted.', 'Flash.flash.0.message');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Contacts', 'action' => 'delete', 'contact' => CONTACT_ID_LEAGUES_SUB], PERSON_ID_MANAGER);
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
	 * Test message method as an admin
	 *
	 * @return void
	 */
	public function testMessageAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test message method as a manager
	 *
	 * @return void
	 */
	public function testMessageAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test message method as a coordinator
	 *
	 * @return void
	 */
	public function testMessageAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test message method as a captain
	 *
	 * @return void
	 */
	public function testMessageAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test message method as a player
	 *
	 * @return void
	 */
	public function testMessageAsPlayer() {
		// Any logged-in user can get the message page
		$this->assertAccessOk(['controller' => 'Contacts', 'action' => 'message'], PERSON_ID_PLAYER);
		$this->assertResponseRegExp('#<option value="' . CONTACT_ID_LEAGUES . '">Leagues</option>#ms');
		$this->assertResponseRegExp('#<option value="' . CONTACT_ID_EVENTS . '">Events</option>#ms');
	}

	/**
	 * Test message method as a player with one contact
	 *
	 * @return void
	 */
	public function testMessageAsPlayerWithOneContact() {
		// Someone logged in on an affiliate that has only one contact doesn't get a drop-down
		$this->assertAccessOk(['controller' => 'Contacts', 'action' => 'message'], PERSON_ID_ANDY_SUB);
		$this->assertResponseRegExp('#<input type="hidden" name="contact_id" value="' . CONTACT_ID_LEAGUES_SUB . '"/>#ms');
	}

	/**
	 * Test message method as someone else
	 *
	 * @return void
	 */
	public function testMessageAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test message method without being logged in
	 *
	 * @return void
	 */
	public function testMessageAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test execute method
	 *
	 * @return void
	 */
	public function testExecute() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		$this->assertAccessRedirect(['controller' => 'Contacts', 'action' => 'message'],
			PERSON_ID_PLAYER, 'post', [
				'contact_id' => CONTACT_ID_LEAGUES,
				'subject' => 'Test',
				'message' => 'Testing',
				'cc' => false,
			], null, 'Your message has been sent.', 'Flash.flash.0.message');
		$messages = Configure::read('test_emails');
		$this->assertEquals(1, count($messages));
		$this->assertRegExp('#From: &quot;Admin&quot; &lt;admin@zuluru.org&gt;#ms', $messages[0]);
		$this->assertRegExp('#Reply-To: &quot;Pam Player&quot; &lt;pam@zuluru.org&gt;#ms', $messages[0]);
		$this->assertRegExp('#To: &quot;Leagues&quot; &lt;leagues@zuluru.net&gt;#ms', $messages[0]);
		$this->assertNotRegExp('#CC: #ms', $messages[0]);
		$this->assertRegExp('#Subject: Test#ms', $messages[0]);
		$this->assertRegExp('#<pre>Testing\s*</pre>#ms', $messages[0]);
	}

	/**
	 * Test execute with CC method
	 *
	 * @return void
	 */
	public function testExecuteWithCC() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		$this->assertAccessRedirect(['controller' => 'Contacts', 'action' => 'message'],
			PERSON_ID_PLAYER, 'post', [
				'contact_id' => CONTACT_ID_LEAGUES,
				'subject' => 'Test',
				'message' => 'Testing',
				'cc' => true,
			], null, 'Your message has been sent.', 'Flash.flash.0.message');
		$messages = Configure::read('test_emails');
		$this->assertEquals(1, count($messages));
		$this->assertRegExp('#From: &quot;Admin&quot; &lt;admin@zuluru.org&gt;#ms', $messages[0]);
		$this->assertRegExp('#Reply-To: &quot;Pam Player&quot; &lt;pam@zuluru.org&gt;#ms', $messages[0]);
		$this->assertRegExp('#To: &quot;Leagues&quot; &lt;leagues@zuluru.net&gt;#ms', $messages[0]);
		$this->assertRegExp('#CC: &quot;Pam Player&quot; &lt;pam@zuluru.org&gt;#ms', $messages[0]);
		$this->assertRegExp('#Subject: Test#ms', $messages[0]);
		$this->assertRegExp('#<pre>Testing\s*</pre>#ms', $messages[0]);
	}

}
