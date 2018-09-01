<?php
namespace App\Test\TestCase\Controller;

/**
 * App\Controller\RegistrationsController Test Case
 */
class RegistrationsControllerTest extends ControllerTestCase {

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
					'app.skills',
			'app.groups',
				'app.groups_people',
			'app.upload_types',
				'app.uploads',
			'app.leagues',
				'app.divisions',
					'app.teams',
						'app.teams_people',
					'app.divisions_days',
					'app.divisions_people',
			'app.franchises',
				'app.franchises_people',
				'app.franchises_teams',
			'app.questions',
				'app.answers',
			'app.questionnaires',
				'app.questionnaires_questions',
			'app.events',
				'app.prices',
					'app.registrations',
						'app.payments',
							'app.registration_audits',
						'app.responses',
				'app.preregistrations',
			'app.settings',
			'app.waivers',
				'app.waivers_people',
	];

	/**
	 * Test full_list method as an admin
	 *
	 * @return void
	 */
	public function testFullListAsAdmin() {
		// Admins are allowed to get the full list of registrations
		$this->assertAccessOk(['controller' => 'Registrations', 'action' => 'full_list', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/registrations/view\?registration=' . REGISTRATION_ID_PLAYER_MEMBERSHIP . '#ms');
		$this->assertResponseRegExp('#/registrations/edit\?registration=' . REGISTRATION_ID_PLAYER_MEMBERSHIP . '#ms');

		$this->assertAccessOk(['controller' => 'Registrations', 'action' => 'full_list', 'event' => EVENT_ID_MEMBERSHIP, '_ext' => 'csv'], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#Home Phone#ms');
		$this->assertResponseRegExp('#Total Amount#ms');
	}

	/**
	 * Test full_list method as a manager
	 *
	 * @return void
	 */
	public function testFullListAsManager() {
		// Managers are allowed to get the full list of registrations
		$this->assertAccessOk(['controller' => 'Registrations', 'action' => 'full_list', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/registrations/view\?registration=' . REGISTRATION_ID_PLAYER_MEMBERSHIP . '#ms');
		$this->assertResponseRegExp('#/registrations/edit\?registration=' . REGISTRATION_ID_PLAYER_MEMBERSHIP . '#ms');

		$this->assertAccessOk(['controller' => 'Registrations', 'action' => 'full_list', 'event' => EVENT_ID_MEMBERSHIP, '_ext' => 'csv'], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#Home Phone#ms');
		$this->assertResponseRegExp('#Total Amount#ms');
	}

	/**
	 * Test full_list method as a coordinator
	 *
	 * @return void
	 */
	public function testFullListAsCoordinator() {
		// Coordinators are not allowed to get the full list of registrations
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'full_list', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_COORDINATOR);

		// Except that they can get the list of registrations for divisions they coordinate, but without registration view and edit links
		$this->assertAccessOk(['controller' => 'Registrations', 'action' => 'full_list', 'event' => EVENT_ID_LEAGUE_INDIVIDUAL_THURSDAY], PERSON_ID_COORDINATOR);
		$this->assertResponseRegExp('#/people/view\?person=' . PERSON_ID_PLAYER . '#ms');
		$this->assertResponseNotRegExp('#/registrations/view\?registration=' . REGISTRATION_ID_PLAYER_INDIVIDUAL_THURSDAY . '#ms');
		$this->assertResponseNotRegExp('#/registrations/edit\?registration=' . REGISTRATION_ID_PLAYER_INDIVIDUAL_THURSDAY . '#ms');

		$this->assertAccessOk(['controller' => 'Registrations', 'action' => 'full_list', 'event' => EVENT_ID_LEAGUE_INDIVIDUAL_THURSDAY, '_ext' => 'csv'], PERSON_ID_COORDINATOR);
		$this->assertResponseNotRegExp('#Home Phone#ms');
		$this->assertResponseNotRegExp('#Work Phone#ms');
		$this->assertResponseNotRegExp('#Work Ext#ms');
		$this->assertResponseNotRegExp('#Mobile Phone#ms');
		$this->assertResponseNotRegExp('#Total Amount#ms');
	}

	/**
	 * Test full_list method as a captain
	 *
	 * @return void
	 */
	public function testFullListAsCaptain() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'full_list', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_CAPTAIN);
	}

	/**
	 * Test full_list method as a player
	 *
	 * @return void
	 */
	public function testFullListAsPlayer() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'full_list', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_PLAYER);
	}

	/**
	 * Test full_list method as someone else
	 *
	 * @return void
	 */
	public function testFullListAsVisitor() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'full_list', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_VISITOR);
	}

	/**
	 * Test full_list method without being logged in
	 *
	 * @return void
	 */
	public function testFullListAsAnonymous() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'full_list', 'event' => EVENT_ID_MEMBERSHIP]);
	}

	/**
	 * Test summary method as an admin
	 *
	 * @return void
	 */
	public function testSummaryAsAdmin() {
		// Admins are allowed to get the full list of registrations
		$this->assertAccessOk(['controller' => 'Registrations', 'action' => 'summary', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_ADMIN);
	}

	/**
	 * Test summary method as a manager
	 *
	 * @return void
	 */
	public function testSummaryAsManager() {
		// Managers are allowed to get the full list of registrations
		$this->assertAccessOk(['controller' => 'Registrations', 'action' => 'summary', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_MANAGER);
	}

	/**
	 * Test summary method as a coordinator
	 *
	 * @return void
	 */
	public function testSummaryAsCoordinator() {
		// Coordinators are not allowed to get the summary of registrations
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'summary', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_COORDINATOR);

		// Except that they can get the summary of registrations for divisions they coordinate
		$this->assertAccessOk(['controller' => 'Registrations', 'action' => 'summary', 'event' => EVENT_ID_LEAGUE_INDIVIDUAL_THURSDAY], PERSON_ID_COORDINATOR);
	}

	/**
	 * Test summary method as a captain
	 *
	 * @return void
	 */
	public function testSummaryAsCaptain() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'summary', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_CAPTAIN);
	}

	/**
	 * Test summary method as a player
	 *
	 * @return void
	 */
	public function testSummaryAsPlayer() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'summary', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_PLAYER);
	}

	/**
	 * Test summary method as someone else
	 *
	 * @return void
	 */
	public function testSummaryAsVisitor() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'summary', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_VISITOR);
	}

	/**
	 * Test summary method without being logged in
	 *
	 * @return void
	 */
	public function testSummaryAsAnonymous() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'summary', 'event' => EVENT_ID_MEMBERSHIP]);
	}

	/**
	 * Test statistics method as an admin
	 *
	 * @return void
	 */
	public function testStatisticsAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test statistics method as a manager
	 *
	 * @return void
	 */
	public function testStatisticsAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test statistics method as a coordinator
	 *
	 * @return void
	 */
	public function testStatisticsAsCoordinator() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'statistics'], PERSON_ID_COORDINATOR);
	}

	/**
	 * Test statistics method as a captain
	 *
	 * @return void
	 */
	public function testStatisticsAsCaptain() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'statistics'], PERSON_ID_CAPTAIN);
	}

	/**
	 * Test statistics method as a player
	 *
	 * @return void
	 */
	public function testStatisticsAsPlayer() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'statistics'], PERSON_ID_PLAYER);
	}

	/**
	 * Test statistics method as someone else
	 *
	 * @return void
	 */
	public function testStatisticsAsVisitor() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'statistics'], PERSON_ID_VISITOR);
	}

	/**
	 * Test statistics method without being logged in
	 *
	 * @return void
	 */
	public function testStatisticsAsAnonymous() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'statistics']);
	}

	/**
	 * Test report method as an admin
	 *
	 * @return void
	 */
	public function testReportAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test report method as a manager
	 *
	 * @return void
	 */
	public function testReportAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test report method as a coordinator
	 *
	 * @return void
	 */
	public function testReportAsCoordinator() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'report'], PERSON_ID_COORDINATOR);
	}

	/**
	 * Test report method as a captain
	 *
	 * @return void
	 */
	public function testReportAsCaptain() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'report'], PERSON_ID_CAPTAIN);
	}

	/**
	 * Test report method as a player
	 *
	 * @return void
	 */
	public function testReportAsPlayer() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'report'], PERSON_ID_PLAYER);
	}

	/**
	 * Test report method as someone else
	 *
	 * @return void
	 */
	public function testReportAsVisitor() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'report'], PERSON_ID_VISITOR);
	}

	/**
	 * Test report method without being logged in
	 *
	 * @return void
	 */
	public function testReportAsAnonymous() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'report']);
	}

	/**
	 * Test accounting method as an admin
	 *
	 * @return void
	 */
	public function testAccountingAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test accounting method as a manager
	 *
	 * @return void
	 */
	public function testAccountingAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test accounting method as a coordinator
	 *
	 * @return void
	 */
	public function testAccountingAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test accounting method as a captain
	 *
	 * @return void
	 */
	public function testAccountingAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test accounting method as a player
	 *
	 * @return void
	 */
	public function testAccountingAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test accounting method as someone else
	 *
	 * @return void
	 */
	public function testAccountingAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test accounting method without being logged in
	 *
	 * @return void
	 */
	public function testAccountingAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test view method as an admin
	 *
	 * @return void
	 */
	public function testViewAsAdmin() {
		// Admins are allowed to view registrations, with full edit permissions
		$this->assertAccessOk(['controller' => 'Registrations', 'action' => 'view', 'registration' => REGISTRATION_ID_PLAYER_MEMBERSHIP], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/registrations/edit\?registration=' . REGISTRATION_ID_PLAYER_MEMBERSHIP . '#ms');
		// Paid registrations cannot be removed
		$this->assertResponseNotRegExp('#/registrations/unregister\?registration=' . REGISTRATION_ID_PLAYER_MEMBERSHIP . '#ms');

		$this->assertAccessOk(['controller' => 'Registrations', 'action' => 'view', 'registration' => REGISTRATION_ID_MANAGER_MEMBERSHIP], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/registrations/edit\?registration=' . REGISTRATION_ID_MANAGER_MEMBERSHIP . '#ms');
		$this->assertResponseRegExp('#/registrations/unregister\?registration=' . REGISTRATION_ID_MANAGER_MEMBERSHIP . '#ms');

		$this->assertAccessOk(['controller' => 'Registrations', 'action' => 'view', 'registration' => REGISTRATION_ID_ANDY_SUB_INDIVIDUAL], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/registrations/edit\?registration=' . REGISTRATION_ID_ANDY_SUB_INDIVIDUAL . '#ms');
		$this->assertResponseRegExp('#/registrations/unregister\?registration=' . REGISTRATION_ID_ANDY_SUB_INDIVIDUAL . '#ms');
	}

	/**
	 * Test view method as a manager
	 *
	 * @return void
	 */
	public function testViewAsManager() {
		// Managers are allowed to view registrations
		$this->assertAccessOk(['controller' => 'Registrations', 'action' => 'view', 'registration' => REGISTRATION_ID_CAPTAIN_MEMBERSHIP], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/registrations/edit\?registration=' . REGISTRATION_ID_CAPTAIN_MEMBERSHIP . '#ms');
		$this->assertResponseRegExp('#/registrations/unregister\?registration=' . REGISTRATION_ID_CAPTAIN_MEMBERSHIP . '#ms');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'view', 'registration' => REGISTRATION_ID_ANDY_SUB_INDIVIDUAL], PERSON_ID_MANAGER);
	}

	/**
	 * Test view method as a coordinator
	 *
	 * @return void
	 */
	public function testViewAsCoordinator() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'view', 'registration' => REGISTRATION_ID_PLAYER_INDIVIDUAL_THURSDAY], PERSON_ID_COORDINATOR);
	}

	/**
	 * Test view method as a captain
	 *
	 * @return void
	 */
	public function testViewAsCaptain() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'view', 'registration' => REGISTRATION_ID_CAPTAIN_MEMBERSHIP], PERSON_ID_CAPTAIN);
	}

	/**
	 * Test view method as a player
	 *
	 * @return void
	 */
	public function testViewAsPlayer() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'view', 'registration' => REGISTRATION_ID_PLAYER_MEMBERSHIP], PERSON_ID_PLAYER);
	}

	/**
	 * Test view method as someone else
	 *
	 * @return void
	 */
	public function testViewAsVisitor() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'view', 'registration' => REGISTRATION_ID_PLAYER_MEMBERSHIP], PERSON_ID_VISITOR);
	}

	/**
	 * Test view method without being logged in
	 *
	 * @return void
	 */
	public function testViewAsAnonymous() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'view', 'registration' => REGISTRATION_ID_PLAYER_MEMBERSHIP]);
	}

	/**
	 * Test register method as an admin
	 *
	 * @return void
	 */
	public function testRegisterAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test register method as a manager
	 *
	 * @return void
	 */
	public function testRegisterAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test register method as a coordinator
	 *
	 * @return void
	 */
	public function testRegisterAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test register method as a captain
	 *
	 * @return void
	 */
	public function testRegisterAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test register method as a player
	 *
	 * @return void
	 */
	public function testRegisterAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test register method as someone else
	 *
	 * @return void
	 */
	public function testRegisterAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test register method without being logged in
	 *
	 * @return void
	 */
	public function testRegisterAsAnonymous() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'register', 'event' => EVENT_ID_MEMBERSHIP]);
	}

	/**
	 * Test register_payment_fields method as an admin
	 *
	 * @return void
	 */
	public function testRegisterPaymentFieldsAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test register_payment_fields method as a manager
	 *
	 * @return void
	 */
	public function testRegisterPaymentFieldsAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test register_payment_fields method as a coordinator
	 *
	 * @return void
	 */
	public function testRegisterPaymentFieldsAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test register_payment_fields method as a captain
	 *
	 * @return void
	 */
	public function testRegisterPaymentFieldsAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test register_payment_fields method as a player
	 *
	 * @return void
	 */
	public function testRegisterPaymentFieldsAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test register_payment_fields method as someone else
	 *
	 * @return void
	 */
	public function testRegisterPaymentFieldsAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test register_payment_fields method without being logged in
	 *
	 * @return void
	 */
	public function testRegisterPaymentFieldsAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test redeem method as an admin
	 *
	 * @return void
	 */
	public function testRedeemAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test redeem method as a manager
	 *
	 * @return void
	 */
	public function testRedeemAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test redeem method as a coordinator
	 *
	 * @return void
	 */
	public function testRedeemAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test redeem method as a captain
	 *
	 * @return void
	 */
	public function testRedeemAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test redeem method as a player
	 *
	 * @return void
	 */
	public function testRedeemAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test redeem method as someone else
	 *
	 * @return void
	 */
	public function testRedeemAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test redeem method without being logged in
	 *
	 * @return void
	 */
	public function testRedeemAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test checkout method as an admin
	 *
	 * @return void
	 */
	public function testCheckoutAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test checkout method as a manager
	 *
	 * @return void
	 */
	public function testCheckoutAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test checkout method as a coordinator
	 *
	 * @return void
	 */
	public function testCheckoutAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test checkout method as a captain
	 *
	 * @return void
	 */
	public function testCheckoutAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test checkout method as a player
	 *
	 * @return void
	 */
	public function testCheckoutAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test checkout method as someone else
	 *
	 * @return void
	 */
	public function testCheckoutAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test checkout method without being logged in
	 *
	 * @return void
	 */
	public function testCheckoutAsAnonymous() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'checkout']);
	}

	/**
	 * Test unregister method as an admin
	 *
	 * @return void
	 */
	public function testUnregisterAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unregister method as a manager
	 *
	 * @return void
	 */
	public function testUnregisterAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unregister method as a coordinator
	 *
	 * @return void
	 */
	public function testUnregisterAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unregister method as a captain
	 *
	 * @return void
	 */
	public function testUnregisterAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unregister method as a player
	 *
	 * @return void
	 */
	public function testUnregisterAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unregister method as someone else
	 *
	 * @return void
	 */
	public function testUnregisterAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unregister method without being logged in
	 *
	 * @return void
	 */
	public function testUnregisterAsAnonymous() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'unregister']);
	}

	/**
	 * Test payment method as an admin
	 *
	 * @return void
	 */
	public function testPaymentAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test payment method as a manager
	 *
	 * @return void
	 */
	public function testPaymentAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test payment method as a coordinator
	 *
	 * @return void
	 */
	public function testPaymentAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test payment method as a captain
	 *
	 * @return void
	 */
	public function testPaymentAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test payment method as a player
	 *
	 * @return void
	 */
	public function testPaymentAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test payment method as someone else
	 *
	 * @return void
	 */
	public function testPaymentAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test payment method without being logged in
	 *
	 * @return void
	 */
	public function testPaymentAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_payment method as an admin
	 *
	 * @return void
	 */
	public function testAddPaymentAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_payment method as a manager
	 *
	 * @return void
	 */
	public function testAddPaymentAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_payment method as a coordinator
	 *
	 * @return void
	 */
	public function testAddPaymentAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_payment method as a captain
	 *
	 * @return void
	 */
	public function testAddPaymentAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_payment method as a player
	 *
	 * @return void
	 */
	public function testAddPaymentAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_payment method as someone else
	 *
	 * @return void
	 */
	public function testAddPaymentAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_payment method without being logged in
	 *
	 * @return void
	 */
	public function testAddPaymentAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test refund_payment method as an admin
	 *
	 * @return void
	 */
	public function testRefundPaymentAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test refund_payment method as a manager
	 *
	 * @return void
	 */
	public function testRefundPaymentAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test refund_payment method as a coordinator
	 *
	 * @return void
	 */
	public function testRefundPaymentAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test refund_payment method as a captain
	 *
	 * @return void
	 */
	public function testRefundPaymentAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test refund_payment method as a player
	 *
	 * @return void
	 */
	public function testRefundPaymentAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test refund_payment method as someone else
	 *
	 * @return void
	 */
	public function testRefundPaymentAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test refund_payment method without being logged in
	 *
	 * @return void
	 */
	public function testRefundPaymentAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test credit_payment method as an admin
	 *
	 * @return void
	 */
	public function testCreditPaymentAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test credit_payment method as a manager
	 *
	 * @return void
	 */
	public function testCreditPaymentAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test credit_payment method as a coordinator
	 *
	 * @return void
	 */
	public function testCreditPaymentAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test credit_payment method as a captain
	 *
	 * @return void
	 */
	public function testCreditPaymentAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test credit_payment method as a player
	 *
	 * @return void
	 */
	public function testCreditPaymentAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test credit_payment method as someone else
	 *
	 * @return void
	 */
	public function testCreditPaymentAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test credit_payment method without being logged in
	 *
	 * @return void
	 */
	public function testCreditPaymentAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test transfer_payment method as an admin
	 *
	 * @return void
	 */
	public function testTransferPaymentAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test transfer_payment method as a manager
	 *
	 * @return void
	 */
	public function testTransferPaymentAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test transfer_payment method as a coordinator
	 *
	 * @return void
	 */
	public function testTransferPaymentAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test transfer_payment method as a captain
	 *
	 * @return void
	 */
	public function testTransferPaymentAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test transfer_payment method as a player
	 *
	 * @return void
	 */
	public function testTransferPaymentAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test transfer_payment method as someone else
	 *
	 * @return void
	 */
	public function testTransferPaymentAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test transfer_payment method without being logged in
	 *
	 * @return void
	 */
	public function testTransferPaymentAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as an admin
	 *
	 * @return void
	 */
	public function testEditAsAdmin() {
		// Admins are allowed to edit registrations, with full edit permissions
		$this->assertAccessOk(['controller' => 'Registrations', 'action' => 'edit', 'registration' => REGISTRATION_ID_PLAYER_MEMBERSHIP], PERSON_ID_ADMIN);
		$this->assertAccessOk(['controller' => 'Registrations', 'action' => 'edit', 'registration' => REGISTRATION_ID_MANAGER_MEMBERSHIP], PERSON_ID_ADMIN);
		$this->assertAccessOk(['controller' => 'Registrations', 'action' => 'edit', 'registration' => REGISTRATION_ID_ANDY_SUB_INDIVIDUAL], PERSON_ID_ADMIN);
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers are allowed to edit registrations
		$this->assertAccessOk(['controller' => 'Registrations', 'action' => 'edit', 'registration' => REGISTRATION_ID_CAPTAIN_MEMBERSHIP], PERSON_ID_MANAGER);

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'edit', 'registration' => REGISTRATION_ID_ANDY_SUB_INDIVIDUAL], PERSON_ID_MANAGER);
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
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'edit', 'registration' => REGISTRATION_ID_PLAYER_MEMBERSHIP], PERSON_ID_PLAYER);
	}

	/**
	 * Test edit method as someone else
	 *
	 * @return void
	 */
	public function testEditAsVisitor() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'edit'], PERSON_ID_VISITOR);
	}

	/**
	 * Test edit method without being logged in
	 *
	 * @return void
	 */
	public function testEditAsAnonymous() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'edit']);
	}

	/**
	 * Test unpaid method as an admin
	 *
	 * @return void
	 */
	public function testUnpaidAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unpaid method as a manager
	 *
	 * @return void
	 */
	public function testUnpaidAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unpaid method as a coordinator
	 *
	 * @return void
	 */
	public function testUnpaidAsCoordinator() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'unpaid'], PERSON_ID_COORDINATOR);
	}

	/**
	 * Test unpaid method as a captain
	 *
	 * @return void
	 */
	public function testUnpaidAsCaptain() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'unpaid'], PERSON_ID_CAPTAIN);
	}

	/**
	 * Test unpaid method as a player
	 *
	 * @return void
	 */
	public function testUnpaidAsPlayer() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'unpaid'], PERSON_ID_PLAYER);
	}

	/**
	 * Test unpaid method as someone else
	 *
	 * @return void
	 */
	public function testUnpaidAsVisitor() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'unpaid'], PERSON_ID_VISITOR);
	}

	/**
	 * Test unpaid method without being logged in
	 *
	 * @return void
	 */
	public function testUnpaidAsAnonymous() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'unpaid']);
	}

	/**
	 * Test credits method as an admin
	 *
	 * @return void
	 */
	public function testCreditsAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test credits method as a manager
	 *
	 * @return void
	 */
	public function testCreditsAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test credits method as a coordinator
	 *
	 * @return void
	 */
	public function testCreditsAsCoordinator() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'credits'], PERSON_ID_COORDINATOR);
	}

	/**
	 * Test credits method as a captain
	 *
	 * @return void
	 */
	public function testCreditsAsCaptain() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'credits'], PERSON_ID_CAPTAIN);
	}

	/**
	 * Test credits method as a player
	 *
	 * @return void
	 */
	public function testCreditsAsPlayer() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'credits'], PERSON_ID_PLAYER);
	}

	/**
	 * Test credits method as someone else
	 *
	 * @return void
	 */
	public function testCreditsAsVisitor() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'credits'], PERSON_ID_VISITOR);
	}

	/**
	 * Test credits method without being logged in
	 *
	 * @return void
	 */
	public function testCreditsAsAnonymous() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'credits']);
	}

	/**
	 * Test waiting method as an admin
	 *
	 * @return void
	 */
	public function testWaitingAsAdmin() {
		// Admins are allowed to get the waiting list
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'waiting', 'event' => EVENT_ID_MEMBERSHIP],
			PERSON_ID_ADMIN, 'get', [], [], 'There is nobody on the waiting list for this event.', 'Flash.flash.0.message');
	}

	/**
	 * Test waiting method as a manager
	 *
	 * @return void
	 */
	public function testWaitingAsManager() {
		// Managers are allowed to get the waiting list
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'waiting', 'event' => EVENT_ID_MEMBERSHIP],
			PERSON_ID_MANAGER, 'get', [], [], 'There is nobody on the waiting list for this event.', 'Flash.flash.0.message');
	}

	/**
	 * Test waiting method as a coordinator
	 *
	 * @return void
	 */
	public function testWaitingAsCoordinator() {
		// Coordinators are not allowed to get the waiting list
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'waiting', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_COORDINATOR);

		// Except that they can get the waiting list for divisions they coordinate, but without registration view and edit links
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'waiting', 'event' => EVENT_ID_LEAGUE_INDIVIDUAL_THURSDAY],
			PERSON_ID_COORDINATOR, 'get', [], [], 'There is nobody on the waiting list for this event.', 'Flash.flash.0.message');
	}

	/**
	 * Test waiting method as a captain
	 *
	 * @return void
	 */
	public function testWaitingAsCaptain() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'waiting'], PERSON_ID_CAPTAIN);
	}

	/**
	 * Test waiting method as a player
	 *
	 * @return void
	 */
	public function testWaitingAsPlayer() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'waiting'], PERSON_ID_PLAYER);
	}

	/**
	 * Test waiting method as someone else
	 *
	 * @return void
	 */
	public function testWaitingAsVisitor() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'waiting'], PERSON_ID_VISITOR);
	}

	/**
	 * Test waiting method without being logged in
	 *
	 * @return void
	 */
	public function testWaitingAsAnonymous() {
		$this->assertAccessRedirect(['controller' => 'Registrations', 'action' => 'waiting']);
	}

}
