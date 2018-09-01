<?php
namespace App\Test\TestCase\Controller;

/**
 * App\Controller\NewslettersController Test Case
 */
class NewslettersControllerTest extends ControllerTestCase {

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
					'app.teams',
					'app.pools',
						'app.pools_teams',
					'app.games',
			'app.mailing_lists',
				'app.newsletters',
			'app.activity_logs',
			'app.settings',
	];

	/**
	 * Test index method as an admin
	 *
	 * @return void
	 */
	public function testIndexAsAdmin() {
		// Admins are allowed to get the index, and all future newsletters will be on it
		$this->assertAccessOk(['controller' => 'Newsletters', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertResponseNotRegExp('#/newsletters/edit\?newsletter=' . NEWSLETTER_ID_JUNIOR_CLINICS . '#ms');
		$this->assertResponseNotRegExp('#/newsletters/delete\?newsletter=' . NEWSLETTER_ID_JUNIOR_CLINICS . '#ms');
		$this->assertResponseRegExp('#/newsletters/edit\?newsletter=' . NEWSLETTER_ID_MASTER_MEETUPS . '#ms');
		$this->assertResponseRegExp('#/newsletters/delete\?newsletter=' . NEWSLETTER_ID_MASTER_MEETUPS . '#ms');
		$this->assertResponseRegExp('#/newsletters/edit\?newsletter=' . NEWSLETTER_ID_WOMENS_CLINICS_SUB . '#ms');
		$this->assertResponseRegExp('#/newsletters/delete\?newsletter=' . NEWSLETTER_ID_WOMENS_CLINICS_SUB . '#ms');
	}

	/**
	 * Test index method as a manager
	 *
	 * @return void
	 */
	public function testIndexAsManager() {
		// Managers are allowed to get the index, but don't see newsletters in other affiliates
		$this->assertAccessOk(['controller' => 'Newsletters', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertResponseNotRegExp('#/newsletters/edit\?newsletter=' . NEWSLETTER_ID_JUNIOR_CLINICS . '#ms');
		$this->assertResponseNotRegExp('#/newsletters/delete\?newsletter=' . NEWSLETTER_ID_JUNIOR_CLINICS . '#ms');
		$this->assertResponseRegExp('#/newsletters/edit\?newsletter=' . NEWSLETTER_ID_MASTER_MEETUPS . '#ms');
		$this->assertResponseRegExp('#/newsletters/delete\?newsletter=' . NEWSLETTER_ID_MASTER_MEETUPS . '#ms');
		$this->assertResponseNotRegExp('#/newsletters/edit\?newsletter=' . NEWSLETTER_ID_WOMENS_CLINICS_SUB . '#ms');
		$this->assertResponseNotRegExp('#/newsletters/delete\?newsletter=' . NEWSLETTER_ID_WOMENS_CLINICS_SUB . '#ms');
	}

	/**
	 * Test index method as a coordinator
	 *
	 * @return void
	 */
	public function testIndexAsCoordinator() {
		// Others are not allowed to get the index
		$this->assertAccessRedirect(['controller' => 'Newsletters', 'action' => 'index'], PERSON_ID_COORDINATOR);
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
	 * Test past method as an admin
	 *
	 * @return void
	 */
	public function testPastAsAdmin() {
		// Admins are allowed to get the past index
		$this->assertAccessOk(['controller' => 'Newsletters', 'action' => 'past'], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/newsletters/edit\?newsletter=' . NEWSLETTER_ID_JUNIOR_CLINICS . '#ms');
		$this->assertResponseRegExp('#/newsletters/delete\?newsletter=' . NEWSLETTER_ID_JUNIOR_CLINICS . '#ms');
		$this->assertResponseRegExp('#/newsletters/edit\?newsletter=' . NEWSLETTER_ID_MASTER_MEETUPS . '#ms');
		$this->assertResponseRegExp('#/newsletters/delete\?newsletter=' . NEWSLETTER_ID_MASTER_MEETUPS . '#ms');
		$this->assertResponseRegExp('#/newsletters/edit\?newsletter=' . NEWSLETTER_ID_WOMENS_CLINICS_SUB . '#ms');
		$this->assertResponseRegExp('#/newsletters/delete\?newsletter=' . NEWSLETTER_ID_WOMENS_CLINICS_SUB . '#ms');
	}

	/**
	 * Test past method as a manager
	 *
	 * @return void
	 */
	public function testPastAsManager() {
		// Managers are allowed to get the past index
		$this->assertAccessOk(['controller' => 'Newsletters', 'action' => 'past'], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/newsletters/edit\?newsletter=' . NEWSLETTER_ID_JUNIOR_CLINICS . '#ms');
		$this->assertResponseRegExp('#/newsletters/delete\?newsletter=' . NEWSLETTER_ID_JUNIOR_CLINICS . '#ms');
		$this->assertResponseRegExp('#/newsletters/edit\?newsletter=' . NEWSLETTER_ID_MASTER_MEETUPS . '#ms');
		$this->assertResponseRegExp('#/newsletters/delete\?newsletter=' . NEWSLETTER_ID_MASTER_MEETUPS . '#ms');
		$this->assertResponseNotRegExp('#/newsletters/edit\?newsletter=' . NEWSLETTER_ID_WOMENS_CLINICS_SUB . '#ms');
		$this->assertResponseNotRegExp('#/newsletters/delete\?newsletter=' . NEWSLETTER_ID_WOMENS_CLINICS_SUB . '#ms');
	}

	/**
	 * Test past method as a coordinator
	 *
	 * @return void
	 */
	public function testPastAsCoordinator() {
		// Others are not allowed to get the past index
		$this->assertAccessRedirect(['controller' => 'Newsletters', 'action' => 'past'], PERSON_ID_COORDINATOR);
	}

	/**
	 * Test past method as a captain
	 *
	 * @return void
	 */
	public function testPastAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test past method as a player
	 *
	 * @return void
	 */
	public function testPastAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test past method as someone else
	 *
	 * @return void
	 */
	public function testPastAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test past method without being logged in
	 *
	 * @return void
	 */
	public function testPastAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test view method as an admin
	 *
	 * @return void
	 */
	public function testViewAsAdmin() {
		// Admins are allowed to view newsletters
		$this->assertAccessOk(['controller' => 'Newsletters', 'action' => 'view', 'newsletter' => NEWSLETTER_ID_JUNIOR_CLINICS], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/newsletters/edit\?newsletter=' . NEWSLETTER_ID_JUNIOR_CLINICS . '#ms');
		$this->assertResponseRegExp('#/newsletters/delete\?newsletter=' . NEWSLETTER_ID_JUNIOR_CLINICS . '#ms');

		$this->assertAccessOk(['controller' => 'Newsletters', 'action' => 'view', 'newsletter' => NEWSLETTER_ID_WOMENS_CLINICS_SUB], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/newsletters/edit\?newsletter=' . NEWSLETTER_ID_WOMENS_CLINICS_SUB . '#ms');
		$this->assertResponseRegExp('#/newsletters/delete\?newsletter=' . NEWSLETTER_ID_WOMENS_CLINICS_SUB . '#ms');
	}

	/**
	 * Test view method as a manager
	 *
	 * @return void
	 */
	public function testViewAsManager() {
		// Managers are allowed to view newsletters
		$this->assertAccessOk(['controller' => 'Newsletters', 'action' => 'view', 'newsletter' => NEWSLETTER_ID_JUNIOR_CLINICS], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/newsletters/edit\?newsletter=' . NEWSLETTER_ID_JUNIOR_CLINICS . '#ms');
		$this->assertResponseRegExp('#/newsletters/delete\?newsletter=' . NEWSLETTER_ID_JUNIOR_CLINICS . '#ms');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Newsletters', 'action' => 'view', 'newsletter' => NEWSLETTER_ID_WOMENS_CLINICS_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test view method as a coordinator
	 *
	 * @return void
	 */
	public function testViewAsCoordinator() {
		// Others are not allowed to view newsletters
		$this->assertAccessRedirect(['controller' => 'Newsletters', 'action' => 'view', 'newsletter' => NEWSLETTER_ID_JUNIOR_CLINICS], PERSON_ID_COORDINATOR);
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
		// Admins are allowed to add newsletters
		$this->assertAccessOk(['controller' => 'Newsletters', 'action' => 'add'], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#<option value="' . MAILING_LIST_ID_JUNIORS . '">Juniors</option>#ms');
		$this->assertResponseRegExp('#<option value="' . MAILING_LIST_ID_WOMEN_SUB . '">Women</option>#ms');
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers are allowed to add newsletters
		$this->assertAccessOk(['controller' => 'Newsletters', 'action' => 'add'], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#<option value="' . MAILING_LIST_ID_JUNIORS . '">Juniors</option>#ms');
		$this->assertResponseNotRegExp('#<option value="' . MAILING_LIST_ID_WOMEN_SUB . '">Women</option>#ms');
	}

	/**
	 * Test add method as a coordinator
	 *
	 * @return void
	 */
	public function testAddAsCoordinator() {
		// Others are not allowed to add newsletters
		$this->assertAccessRedirect(['controller' => 'Newsletters', 'action' => 'add'], PERSON_ID_COORDINATOR);
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
		// Admins are allowed to edit newsletters
		$this->assertAccessOk(['controller' => 'Newsletters', 'action' => 'edit', 'newsletter' => NEWSLETTER_ID_JUNIOR_CLINICS], PERSON_ID_ADMIN);
		$this->assertAccessOk(['controller' => 'Newsletters', 'action' => 'edit', 'newsletter' => NEWSLETTER_ID_WOMENS_CLINICS_SUB], PERSON_ID_ADMIN);
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers are allowed to edit newsletters
		$this->assertAccessOk(['controller' => 'Newsletters', 'action' => 'edit', 'newsletter' => NEWSLETTER_ID_JUNIOR_CLINICS], PERSON_ID_MANAGER);

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Newsletters', 'action' => 'edit', 'newsletter' => NEWSLETTER_ID_WOMENS_CLINICS_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test edit method as a coordinator
	 *
	 * @return void
	 */
	public function testEditAsCoordinator() {
		// Others are not allowed to edit newsletters
		$this->assertAccessRedirect(['controller' => 'Newsletters', 'action' => 'edit', 'newsletter' => NEWSLETTER_ID_JUNIOR_CLINICS], PERSON_ID_COORDINATOR);
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

		// Admins are allowed to delete newsletters
		$this->assertAccessRedirect(['controller' => 'Newsletters', 'action' => 'delete', 'newsletter' => NEWSLETTER_ID_MASTER_MEETUPS],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Newsletters', 'action' => 'index'],
			'The newsletter has been deleted.', 'Flash.flash.0.message');

		// But not ones with dependencies
		$this->assertAccessRedirect(['controller' => 'Newsletters', 'action' => 'delete', 'newsletter' => NEWSLETTER_ID_JUNIOR_CLINICS],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Newsletters', 'action' => 'index'],
			'#The following records reference this newsletter, so it cannot be deleted#', 'Flash.flash.0.message');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers can delete newsletters in their affiliate
		$this->assertAccessRedirect(['controller' => 'Newsletters', 'action' => 'delete', 'newsletter' => NEWSLETTER_ID_MASTER_MEETUPS],
			PERSON_ID_MANAGER, 'post', [], ['controller' => 'Newsletters', 'action' => 'index'],
			'The newsletter has been deleted.', 'Flash.flash.0.message');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Newsletters', 'action' => 'delete', 'newsletter' => NEWSLETTER_ID_WOMENS_CLINICS_SUB],
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
	 * Test delivery method as an admin
	 *
	 * @return void
	 */
	public function testDeliveryAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delivery method as a manager
	 *
	 * @return void
	 */
	public function testDeliveryAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delivery method as a coordinator
	 *
	 * @return void
	 */
	public function testDeliveryAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delivery method as a captain
	 *
	 * @return void
	 */
	public function testDeliveryAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delivery method as a player
	 *
	 * @return void
	 */
	public function testDeliveryAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delivery method as someone else
	 *
	 * @return void
	 */
	public function testDeliveryAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delivery method without being logged in
	 *
	 * @return void
	 */
	public function testDeliveryAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test send method as an admin
	 *
	 * @return void
	 */
	public function testSendAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test send method as a manager
	 *
	 * @return void
	 */
	public function testSendAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test send method as a coordinator
	 *
	 * @return void
	 */
	public function testSendAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test send method as a captain
	 *
	 * @return void
	 */
	public function testSendAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test send method as a player
	 *
	 * @return void
	 */
	public function testSendAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test send method as someone else
	 *
	 * @return void
	 */
	public function testSendAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test send method without being logged in
	 *
	 * @return void
	 */
	public function testSendAsAnonymous() {
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
