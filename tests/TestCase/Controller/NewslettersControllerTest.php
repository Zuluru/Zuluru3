<?php
namespace App\Test\TestCase\Controller;

/**
 * App\Controller\NewslettersController Test Case
 */
class NewslettersControllerTest extends ControllerTestCase {

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex(): void {
		// Admins are allowed to see the index, and all future newsletters will be on it
		$this->assertGetAsAccessOk(['controller' => 'Newsletters', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertResponseNotContains('/newsletters/edit?newsletter=' . NEWSLETTER_ID_JUNIOR_CLINICS);
		$this->assertResponseNotContains('/newsletters/delete?newsletter=' . NEWSLETTER_ID_JUNIOR_CLINICS);
		$this->assertResponseContains('/newsletters/edit?newsletter=' . NEWSLETTER_ID_MASTER_MEETUPS);
		$this->assertResponseContains('/newsletters/delete?newsletter=' . NEWSLETTER_ID_MASTER_MEETUPS);
		$this->assertResponseContains('/newsletters/edit?newsletter=' . NEWSLETTER_ID_WOMENS_CLINICS_SUB);
		$this->assertResponseContains('/newsletters/delete?newsletter=' . NEWSLETTER_ID_WOMENS_CLINICS_SUB);

		// Managers are allowed to see the index, but don't see newsletters in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'Newsletters', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertResponseNotContains('/newsletters/edit?newsletter=' . NEWSLETTER_ID_JUNIOR_CLINICS);
		$this->assertResponseNotContains('/newsletters/delete?newsletter=' . NEWSLETTER_ID_JUNIOR_CLINICS);
		$this->assertResponseContains('/newsletters/edit?newsletter=' . NEWSLETTER_ID_MASTER_MEETUPS);
		$this->assertResponseContains('/newsletters/delete?newsletter=' . NEWSLETTER_ID_MASTER_MEETUPS);
		$this->assertResponseNotContains('/newsletters/edit?newsletter=' . NEWSLETTER_ID_WOMENS_CLINICS_SUB);
		$this->assertResponseNotContains('/newsletters/delete?newsletter=' . NEWSLETTER_ID_WOMENS_CLINICS_SUB);

		// Others are not allowed to see the index
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'index'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'index'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'index'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'index'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Newsletters', 'action' => 'index']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test past method
	 *
	 * @return void
	 */
	public function testPast(): void {
		// Admins are allowed to see the past index
		$this->assertGetAsAccessOk(['controller' => 'Newsletters', 'action' => 'past'], PERSON_ID_ADMIN);
		$this->assertResponseContains('/newsletters/edit?newsletter=' . NEWSLETTER_ID_JUNIOR_CLINICS);
		$this->assertResponseContains('/newsletters/delete?newsletter=' . NEWSLETTER_ID_JUNIOR_CLINICS);
		$this->assertResponseContains('/newsletters/edit?newsletter=' . NEWSLETTER_ID_MASTER_MEETUPS);
		$this->assertResponseContains('/newsletters/delete?newsletter=' . NEWSLETTER_ID_MASTER_MEETUPS);
		$this->assertResponseContains('/newsletters/edit?newsletter=' . NEWSLETTER_ID_WOMENS_CLINICS_SUB);
		$this->assertResponseContains('/newsletters/delete?newsletter=' . NEWSLETTER_ID_WOMENS_CLINICS_SUB);

		// Managers are allowed to see the past index
		$this->assertGetAsAccessOk(['controller' => 'Newsletters', 'action' => 'past'], PERSON_ID_MANAGER);
		$this->assertResponseContains('/newsletters/edit?newsletter=' . NEWSLETTER_ID_JUNIOR_CLINICS);
		$this->assertResponseContains('/newsletters/delete?newsletter=' . NEWSLETTER_ID_JUNIOR_CLINICS);
		$this->assertResponseContains('/newsletters/edit?newsletter=' . NEWSLETTER_ID_MASTER_MEETUPS);
		$this->assertResponseContains('/newsletters/delete?newsletter=' . NEWSLETTER_ID_MASTER_MEETUPS);
		$this->assertResponseNotContains('/newsletters/edit?newsletter=' . NEWSLETTER_ID_WOMENS_CLINICS_SUB);
		$this->assertResponseNotContains('/newsletters/delete?newsletter=' . NEWSLETTER_ID_WOMENS_CLINICS_SUB);

		// Others are not allowed to see the past index
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'past'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'past'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'past'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'past'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Newsletters', 'action' => 'past']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testView(): void {
		// Admins are allowed to view newsletters
		$this->assertGetAsAccessOk(['controller' => 'Newsletters', 'action' => 'view', 'newsletter' => NEWSLETTER_ID_JUNIOR_CLINICS], PERSON_ID_ADMIN);
		$this->assertResponseContains('/newsletters/edit?newsletter=' . NEWSLETTER_ID_JUNIOR_CLINICS);
		$this->assertResponseContains('/newsletters/delete?newsletter=' . NEWSLETTER_ID_JUNIOR_CLINICS);

		$this->assertGetAsAccessOk(['controller' => 'Newsletters', 'action' => 'view', 'newsletter' => NEWSLETTER_ID_WOMENS_CLINICS_SUB], PERSON_ID_ADMIN);
		$this->assertResponseContains('/newsletters/edit?newsletter=' . NEWSLETTER_ID_WOMENS_CLINICS_SUB);
		$this->assertResponseContains('/newsletters/delete?newsletter=' . NEWSLETTER_ID_WOMENS_CLINICS_SUB);

		// Managers are allowed to view newsletters
		$this->assertGetAsAccessOk(['controller' => 'Newsletters', 'action' => 'view', 'newsletter' => NEWSLETTER_ID_JUNIOR_CLINICS], PERSON_ID_MANAGER);
		$this->assertResponseContains('/newsletters/edit?newsletter=' . NEWSLETTER_ID_JUNIOR_CLINICS);
		$this->assertResponseContains('/newsletters/delete?newsletter=' . NEWSLETTER_ID_JUNIOR_CLINICS);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'view', 'newsletter' => NEWSLETTER_ID_WOMENS_CLINICS_SUB], PERSON_ID_MANAGER);

		// Others are not allowed to view newsletters
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'view', 'newsletter' => NEWSLETTER_ID_JUNIOR_CLINICS], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'view', 'newsletter' => NEWSLETTER_ID_JUNIOR_CLINICS], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'view', 'newsletter' => NEWSLETTER_ID_JUNIOR_CLINICS], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'view', 'newsletter' => NEWSLETTER_ID_JUNIOR_CLINICS], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Newsletters', 'action' => 'view', 'newsletter' => NEWSLETTER_ID_JUNIOR_CLINICS]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add method as an admin
	 *
	 * @return void
	 */
	public function testAddAsAdmin(): void {
		// Admins are allowed to add newsletters
		$this->assertGetAsAccessOk(['controller' => 'Newsletters', 'action' => 'add'], PERSON_ID_ADMIN);
		$this->assertResponseContains('<option value="' . MAILING_LIST_ID_JUNIORS . '">Juniors</option>');
		$this->assertResponseContains('<option value="' . MAILING_LIST_ID_WOMEN_SUB . '">Women</option>');
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager(): void {
		// Managers are allowed to add newsletters
		$this->assertGetAsAccessOk(['controller' => 'Newsletters', 'action' => 'add'], PERSON_ID_MANAGER);
		$this->assertResponseContains('<option value="' . MAILING_LIST_ID_JUNIORS . '">Juniors</option>');
		$this->assertResponseNotContains('<option value="' . MAILING_LIST_ID_WOMEN_SUB . '">Women</option>');
	}

	/**
	 * Test add method as others
	 *
	 * @return void
	 */
	public function testAddAsOthers(): void {
		// Others are not allowed to add newsletters
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'add'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'add'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'add'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'add'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Newsletters', 'action' => 'add']);
	}

	/**
	 * Test edit method as an admin
	 *
	 * @return void
	 */
	public function testEditAsAdmin(): void {
		// Admins are allowed to edit newsletters
		$this->assertGetAsAccessOk(['controller' => 'Newsletters', 'action' => 'edit', 'newsletter' => NEWSLETTER_ID_JUNIOR_CLINICS], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Newsletters', 'action' => 'edit', 'newsletter' => NEWSLETTER_ID_WOMENS_CLINICS_SUB], PERSON_ID_ADMIN);
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager(): void {
		// Managers are allowed to edit newsletters
		$this->assertGetAsAccessOk(['controller' => 'Newsletters', 'action' => 'edit', 'newsletter' => NEWSLETTER_ID_JUNIOR_CLINICS], PERSON_ID_MANAGER);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'edit', 'newsletter' => NEWSLETTER_ID_WOMENS_CLINICS_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test edit method as others
	 *
	 * @return void
	 */
	public function testEditAsOthers(): void {
		// Others are not allowed to edit newsletters
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'edit', 'newsletter' => NEWSLETTER_ID_JUNIOR_CLINICS], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'edit', 'newsletter' => NEWSLETTER_ID_JUNIOR_CLINICS], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'edit', 'newsletter' => NEWSLETTER_ID_JUNIOR_CLINICS], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'edit', 'newsletter' => NEWSLETTER_ID_JUNIOR_CLINICS], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Newsletters', 'action' => 'edit', 'newsletter' => NEWSLETTER_ID_JUNIOR_CLINICS]);
	}

	/**
	 * Test delete method as an admin
	 *
	 * @return void
	 */
	public function testDeleteAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to delete newsletters
		$this->assertPostAsAccessRedirect(['controller' => 'Newsletters', 'action' => 'delete', 'newsletter' => NEWSLETTER_ID_MASTER_MEETUPS],
			PERSON_ID_ADMIN, [], ['controller' => 'Newsletters', 'action' => 'index'],
			'The newsletter has been deleted.');

		// But not ones with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'Newsletters', 'action' => 'delete', 'newsletter' => NEWSLETTER_ID_JUNIOR_CLINICS],
			PERSON_ID_ADMIN, [], ['controller' => 'Newsletters', 'action' => 'index'],
			'#The following records reference this newsletter, so it cannot be deleted#');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to delete newsletters in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Newsletters', 'action' => 'delete', 'newsletter' => NEWSLETTER_ID_MASTER_MEETUPS],
			PERSON_ID_MANAGER, [], ['controller' => 'Newsletters', 'action' => 'index'],
			'The newsletter has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Newsletters', 'action' => 'delete', 'newsletter' => NEWSLETTER_ID_WOMENS_CLINICS_SUB],
			PERSON_ID_MANAGER);
	}

	/**
	 * Test delete method as others
	 *
	 * @return void
	 */
	public function testDeleteAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Others are not allowed to delete newsletters
		$this->assertPostAsAccessDenied(['controller' => 'Newsletters', 'action' => 'delete', 'newsletter' => NEWSLETTER_ID_MASTER_MEETUPS],
			PERSON_ID_COORDINATOR);
		$this->assertPostAsAccessDenied(['controller' => 'Newsletters', 'action' => 'delete', 'newsletter' => NEWSLETTER_ID_MASTER_MEETUPS],
			PERSON_ID_CAPTAIN);
		$this->assertPostAsAccessDenied(['controller' => 'Newsletters', 'action' => 'delete', 'newsletter' => NEWSLETTER_ID_MASTER_MEETUPS],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'Newsletters', 'action' => 'delete', 'newsletter' => NEWSLETTER_ID_MASTER_MEETUPS],
			PERSON_ID_VISITOR);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Newsletters', 'action' => 'delete', 'newsletter' => NEWSLETTER_ID_MASTER_MEETUPS]);
	}

	/**
	 * Test delivery method
	 *
	 * @return void
	 */
	public function testDelivery(): void {
		// Admins are allowed to see the delivery report
		$this->assertGetAsAccessOk(['controller' => 'Newsletters', 'action' => 'delivery', 'newsletter' => NEWSLETTER_ID_MASTER_MEETUPS], PERSON_ID_ADMIN);

		// Managers are allowed to see the delivery report
		$this->assertGetAsAccessOk(['controller' => 'Newsletters', 'action' => 'delivery', 'newsletter' => NEWSLETTER_ID_MASTER_MEETUPS], PERSON_ID_MANAGER);

		// Others are not allowed to see the delivery report
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'delivery', 'newsletter' => NEWSLETTER_ID_MASTER_MEETUPS], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'delivery', 'newsletter' => NEWSLETTER_ID_MASTER_MEETUPS], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'delivery', 'newsletter' => NEWSLETTER_ID_MASTER_MEETUPS], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'delivery', 'newsletter' => NEWSLETTER_ID_MASTER_MEETUPS], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Newsletters', 'action' => 'delivery', 'newsletter' => NEWSLETTER_ID_MASTER_MEETUPS]);
	}

	/**
	 * Test send method as an admin
	 *
	 * @return void
	 */
	public function testSendAsAdmin(): void {
		// Admins are allowed to send
		$this->assertGetAsAccessOk(['controller' => 'Newsletters', 'action' => 'send', 'newsletter' => NEWSLETTER_ID_MASTER_MEETUPS], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Newsletters', 'action' => 'send', 'newsletter' => NEWSLETTER_ID_MASTER_MEETUPS, 'execute' => true, 'test' => true], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test send method as a manager
	 *
	 * @return void
	 */
	public function testSendAsManager(): void {
		// Managers are allowed to send
		$this->assertGetAsAccessOk(['controller' => 'Newsletters', 'action' => 'send', 'newsletter' => NEWSLETTER_ID_MASTER_MEETUPS], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test send method as others
	 *
	 * @return void
	 */
	public function testSendAsOthers(): void {
		// Others are not allowed to send
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'send', 'newsletter' => NEWSLETTER_ID_MASTER_MEETUPS], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'send', 'newsletter' => NEWSLETTER_ID_MASTER_MEETUPS], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'send', 'newsletter' => NEWSLETTER_ID_MASTER_MEETUPS], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'send', 'newsletter' => NEWSLETTER_ID_MASTER_MEETUPS], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Newsletters', 'action' => 'send', 'newsletter' => NEWSLETTER_ID_MASTER_MEETUPS]);
	}

}
