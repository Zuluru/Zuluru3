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
		'app.Affiliates',
			'app.Users',
				'app.People',
					'app.AffiliatesPeople',
					'app.PeoplePeople',
			'app.Groups',
				'app.GroupsPeople',
			'app.Leagues',
				'app.Divisions',
					'app.Teams',
					'app.DivisionsPeople',
			'app.Contacts',
			'app.Settings',
		'app.I18n',
	];

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex() {
		// Admins are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Contacts', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertResponseContains('/contacts/edit?contact=' . CONTACT_ID_LEAGUES);
		$this->assertResponseContains('/contacts/delete?contact=' . CONTACT_ID_LEAGUES);
		$this->assertResponseContains('/contacts/edit?contact=' . CONTACT_ID_LEAGUES_SUB);
		$this->assertResponseContains('/contacts/delete?contact=' . CONTACT_ID_LEAGUES_SUB);

		// Managers are allowed to see the index, but don't see contacts in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'Contacts', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertResponseContains('/contacts/edit?contact=' . CONTACT_ID_LEAGUES);
		$this->assertResponseContains('/contacts/delete?contact=' . CONTACT_ID_LEAGUES);
		$this->assertResponseNotContains('/contacts/edit?contact=' . CONTACT_ID_LEAGUES_SUB);
		$this->assertResponseNotContains('/contacts/delete?contact=' . CONTACT_ID_LEAGUES_SUB);

		// Others are not allowed to see the index
		$this->assertGetAsAccessDenied(['controller' => 'Contacts', 'action' => 'index'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Contacts', 'action' => 'index'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Contacts', 'action' => 'index'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Contacts', 'action' => 'index'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Contacts', 'action' => 'index']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add method as an admin
	 *
	 * @return void
	 */
	public function testAddAsAdmin() {
		// Admins are allowed to add contacts
		$this->assertGetAsAccessOk(['controller' => 'Contacts', 'action' => 'add'], PERSON_ID_ADMIN);
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers are allowed to add contacts
		$this->assertGetAsAccessOk(['controller' => 'Contacts', 'action' => 'add'], PERSON_ID_MANAGER);
	}

	/**
	 * Test add method as others
	 *
	 * @return void
	 */
	public function testAddAsOthers() {
		// Others are not allowed to add contacts
		$this->assertGetAsAccessDenied(['controller' => 'Contacts', 'action' => 'add'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Contacts', 'action' => 'add'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Contacts', 'action' => 'add'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Contacts', 'action' => 'add'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Contacts', 'action' => 'add']);
	}

	/**
	 * Test edit method as an admin
	 *
	 * @return void
	 */
	public function testEditAsAdmin() {
		// Admins are allowed to edit contacts
		$this->assertGetAsAccessOk(['controller' => 'Contacts', 'action' => 'edit', 'contact' => CONTACT_ID_LEAGUES], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Contacts', 'action' => 'edit', 'contact' => CONTACT_ID_LEAGUES_SUB], PERSON_ID_ADMIN);
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers are allowed to edit contacts
		$this->assertGetAsAccessOk(['controller' => 'Contacts', 'action' => 'edit', 'contact' => CONTACT_ID_LEAGUES], PERSON_ID_MANAGER);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Contacts', 'action' => 'edit', 'contact' => CONTACT_ID_LEAGUES_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test edit method as others
	 *
	 * @return void
	 */
	public function testEditAsOthers() {
		// Others are not allowed to edit contacts
		$this->assertGetAsAccessDenied(['controller' => 'Contacts', 'action' => 'edit', 'contact' => CONTACT_ID_LEAGUES], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Contacts', 'action' => 'edit', 'contact' => CONTACT_ID_LEAGUES], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Contacts', 'action' => 'edit', 'contact' => CONTACT_ID_LEAGUES], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Contacts', 'action' => 'edit', 'contact' => CONTACT_ID_LEAGUES], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Contacts', 'action' => 'edit', 'contact' => CONTACT_ID_LEAGUES]);
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
		$this->assertPostAsAccessRedirect(['controller' => 'Contacts', 'action' => 'delete', 'contact' => CONTACT_ID_LEAGUES],
			PERSON_ID_ADMIN, [], ['controller' => 'Contacts', 'action' => 'index'],
			'The contact has been deleted.');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to delete contacts in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Contacts', 'action' => 'delete', 'contact' => CONTACT_ID_LEAGUES],
			PERSON_ID_MANAGER, [], ['controller' => 'Contacts', 'action' => 'index'],
			'The contact has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Contacts', 'action' => 'delete', 'contact' => CONTACT_ID_LEAGUES_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test delete method as others
	 *
	 * @return void
	 */
	public function testDeleteAsOthers() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Others are not allowed to delete contacts
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Contacts', 'action' => 'delete', 'contact' => CONTACT_ID_LEAGUES], PERSON_ID_COORDINATOR);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Contacts', 'action' => 'delete', 'contact' => CONTACT_ID_LEAGUES], PERSON_ID_CAPTAIN);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Contacts', 'action' => 'delete', 'contact' => CONTACT_ID_LEAGUES], PERSON_ID_PLAYER);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Contacts', 'action' => 'delete', 'contact' => CONTACT_ID_LEAGUES], PERSON_ID_VISITOR);
		$this->assertPostAjaxAnonymousAccessDenied(['controller' => 'Contacts', 'action' => 'delete', 'contact' => CONTACT_ID_LEAGUES]);
	}

	/**
	 * Test message method
	 *
	 * @return void
	 */
	public function testMessage() {
		// Anyone logged in is allowed to see the message page
		$this->assertGetAsAccessOk(['controller' => 'Contacts', 'action' => 'message'], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Contacts', 'action' => 'message'], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'Contacts', 'action' => 'message'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessOk(['controller' => 'Contacts', 'action' => 'message'], PERSON_ID_CAPTAIN);

		$this->assertGetAsAccessOk(['controller' => 'Contacts', 'action' => 'message'], PERSON_ID_PLAYER);
		$this->assertResponseContains('<option value="' . CONTACT_ID_LEAGUES . '">Leagues</option>');
		$this->assertResponseContains('<option value="' . CONTACT_ID_EVENTS . '">Events</option>');

		$this->assertGetAsAccessOk(['controller' => 'Contacts', 'action' => 'message'], PERSON_ID_VISITOR);

		// Anyone not logged in is not allowed to send messages
		$this->assertGetAnonymousAccessDenied(['controller' => 'Contacts', 'action' => 'message']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test message method as a player with one contact
	 *
	 * @return void
	 */
	public function testMessageAsPlayerWithOneContact() {
		// Someone logged in on an affiliate that has only one contact doesn't get a drop-down
		$this->assertGetAsAccessOk(['controller' => 'Contacts', 'action' => 'message'], PERSON_ID_ANDY_SUB);
		$this->assertResponseContains('<input type="hidden" name="contact_id" value="' . CONTACT_ID_LEAGUES_SUB . '"/>');
	}

	/**
	 * Test execute method
	 *
	 * @return void
	 */
	public function testExecute() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		$this->assertPostAsAccessRedirect(['controller' => 'Contacts', 'action' => 'message'],
			PERSON_ID_PLAYER, [
				'contact_id' => CONTACT_ID_LEAGUES,
				'subject' => 'Test',
				'message' => 'Testing',
				'cc' => false,
			], '/', 'Your message has been sent.');
		$messages = Configure::read('test_emails');
		$this->assertEquals(1, count($messages));
		$this->assertContains('From: &quot;Admin&quot; &lt;admin@zuluru.org&gt;', $messages[0]);
		$this->assertContains('Reply-To: &quot;Pam Player&quot; &lt;pam@zuluru.org&gt;', $messages[0]);
		$this->assertContains('To: &quot;Leagues&quot; &lt;leagues@zuluru.net&gt;', $messages[0]);
		$this->assertNotContains('CC: ', $messages[0]);
		$this->assertContains('Subject: Test', $messages[0]);
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

		$this->assertPostAsAccessRedirect(['controller' => 'Contacts', 'action' => 'message'],
			PERSON_ID_PLAYER, [
				'contact_id' => CONTACT_ID_LEAGUES,
				'subject' => 'Test',
				'message' => 'Testing',
				'cc' => true,
			], '/', 'Your message has been sent.');
		$messages = Configure::read('test_emails');
		$this->assertEquals(1, count($messages));
		$this->assertContains('From: &quot;Admin&quot; &lt;admin@zuluru.org&gt;', $messages[0]);
		$this->assertContains('Reply-To: &quot;Pam Player&quot; &lt;pam@zuluru.org&gt;', $messages[0]);
		$this->assertContains('To: &quot;Leagues&quot; &lt;leagues@zuluru.net&gt;', $messages[0]);
		$this->assertContains('CC: &quot;Pam Player&quot; &lt;pam@zuluru.org&gt;', $messages[0]);
		$this->assertContains('Subject: Test', $messages[0]);
		$this->assertRegExp('#<pre>Testing\s*</pre>#ms', $messages[0]);
	}

}
