<?php
namespace App\Test\TestCase\Controller;

use Cake\I18n\FrozenDate;

/**
 * App\Controller\WaiversController Test Case
 */
class WaiversControllerTest extends ControllerTestCase {

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
			'app.Settings',
			'app.Waivers',
				'app.WaiversPeople',
		'app.I18n',
		'app.Plugins',
	];

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex() {
		// Admins are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertResponseContains('/waivers/edit?waiver=' . WAIVER_ID_ANNUAL);
		$this->assertResponseContains('/waivers/delete?waiver=' . WAIVER_ID_ANNUAL);
		$this->assertResponseContains('/waivers/edit?waiver=' . WAIVER_ID_PERPETUAL);
		$this->assertResponseContains('/waivers/delete?waiver=' . WAIVER_ID_PERPETUAL);

		// Managers are allowed to see the index, but don't see waivers in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertResponseContains('/waivers/edit?waiver=' . WAIVER_ID_ANNUAL);
		$this->assertResponseContains('/waivers/delete?waiver=' . WAIVER_ID_ANNUAL);
		$this->assertResponseNotContains('/waivers/edit?waiver=' . WAIVER_ID_PERPETUAL);
		$this->assertResponseNotContains('/waivers/delete?waiver=' . WAIVER_ID_PERPETUAL);

		// Others are not allowed to see the index
		$this->assertGetAsAccessDenied(['controller' => 'Waivers', 'action' => 'index'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Waivers', 'action' => 'index'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Waivers', 'action' => 'index'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Waivers', 'action' => 'index'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Waivers', 'action' => 'index']);
	}

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testView() {
		// Admins are allowed to view waivers
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'view', 'waiver' => WAIVER_ID_ANNUAL], PERSON_ID_ADMIN);
		$this->assertResponseContains('/waivers/edit?waiver=' . WAIVER_ID_ANNUAL);
		$this->assertResponseContains('/waivers/delete?waiver=' . WAIVER_ID_ANNUAL);

		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'view', 'waiver' => WAIVER_ID_PERPETUAL], PERSON_ID_ADMIN);
		$this->assertResponseContains('/waivers/edit?waiver=' . WAIVER_ID_PERPETUAL);
		$this->assertResponseContains('/waivers/delete?waiver=' . WAIVER_ID_PERPETUAL);

		// Managers are allowed to view waivers
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'view', 'waiver' => WAIVER_ID_ANNUAL], PERSON_ID_MANAGER);
		$this->assertResponseContains('/waivers/edit?waiver=' . WAIVER_ID_ANNUAL);
		$this->assertResponseContains('/waivers/delete?waiver=' . WAIVER_ID_ANNUAL);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Waivers', 'action' => 'view', 'waiver' => WAIVER_ID_PERPETUAL], PERSON_ID_MANAGER);

		// Others are not allowed to view waivers
		$this->assertGetAsAccessDenied(['controller' => 'Waivers', 'action' => 'view', 'waiver' => WAIVER_ID_ANNUAL], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Waivers', 'action' => 'view', 'waiver' => WAIVER_ID_ANNUAL], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Waivers', 'action' => 'view', 'waiver' => WAIVER_ID_ANNUAL], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Waivers', 'action' => 'view', 'waiver' => WAIVER_ID_ANNUAL], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Waivers', 'action' => 'view', 'waiver' => WAIVER_ID_ANNUAL]);
	}

	/**
	 * Test add method as an admin
	 *
	 * @return void
	 */
	public function testAddAsAdmin() {
		// Admins are allowed to add waivers
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'add'], PERSON_ID_ADMIN);
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers are allowed to add waivers
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'add'], PERSON_ID_MANAGER);
	}

	/**
	 * Test add method as others
	 *
	 * @return void
	 */
	public function testAddAsOthers() {
		// Others are not allowed to add waivers
		$this->assertGetAsAccessDenied(['controller' => 'Waivers', 'action' => 'add'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Waivers', 'action' => 'add'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Waivers', 'action' => 'add'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Waivers', 'action' => 'add'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Waivers', 'action' => 'add']);
	}

	/**
	 * Test edit method as an admin
	 *
	 * @return void
	 */
	public function testEditAsAdmin() {
		// Admins are allowed to edit waivers
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'edit', 'waiver' => WAIVER_ID_ANNUAL], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'edit', 'waiver' => WAIVER_ID_PERPETUAL], PERSON_ID_ADMIN);
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers are allowed to edit waivers
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'edit', 'waiver' => WAIVER_ID_ANNUAL], PERSON_ID_MANAGER);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Waivers', 'action' => 'edit', 'waiver' => WAIVER_ID_PERPETUAL], PERSON_ID_MANAGER);
	}

	/**
	 * Test edit method as others
	 *
	 * @return void
	 */
	public function testEditAsOthers() {
		// Others are not allowed to edit waivers
		$this->assertGetAsAccessDenied(['controller' => 'Waivers', 'action' => 'edit', 'waiver' => WAIVER_ID_ANNUAL], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Waivers', 'action' => 'edit', 'waiver' => WAIVER_ID_ANNUAL], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Waivers', 'action' => 'edit', 'waiver' => WAIVER_ID_ANNUAL], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Waivers', 'action' => 'edit', 'waiver' => WAIVER_ID_ANNUAL], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Waivers', 'action' => 'edit', 'waiver' => WAIVER_ID_ANNUAL]);
	}

	/**
	 * Test delete method as an admin
	 *
	 * @return void
	 */
	public function testDeleteAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to delete waivers
		$this->assertPostAsAccessRedirect(['controller' => 'Waivers', 'action' => 'delete', 'waiver' => WAIVER_ID_EVENT],
			PERSON_ID_ADMIN, [], ['controller' => 'Waivers', 'action' => 'index'],
			'The waiver has been deleted.');

		// But not ones with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'Waivers', 'action' => 'delete', 'waiver' => WAIVER_ID_ANNUAL],
			PERSON_ID_ADMIN, [], ['controller' => 'Waivers', 'action' => 'index'],
			'#The following records reference this waiver, so it cannot be deleted#');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to delete waivers in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Waivers', 'action' => 'delete', 'waiver' => WAIVER_ID_EVENT],
			PERSON_ID_MANAGER, [], ['controller' => 'Waivers', 'action' => 'index'],
			'The waiver has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Waivers', 'action' => 'delete', 'waiver' => WAIVER_ID_PERPETUAL],
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

		// Others are not allowed to delete waivers
		$this->assertPostAsAccessDenied(['controller' => 'Waivers', 'action' => 'delete', 'waiver' => WAIVER_ID_EVENT],
			PERSON_ID_COORDINATOR);
		$this->assertPostAsAccessDenied(['controller' => 'Waivers', 'action' => 'delete', 'waiver' => WAIVER_ID_EVENT],
			PERSON_ID_CAPTAIN);
		$this->assertPostAsAccessDenied(['controller' => 'Waivers', 'action' => 'delete', 'waiver' => WAIVER_ID_EVENT],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'Waivers', 'action' => 'delete', 'waiver' => WAIVER_ID_EVENT],
			PERSON_ID_VISITOR);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Waivers', 'action' => 'delete', 'waiver' => WAIVER_ID_EVENT]);
	}

	/**
	 * Test sign method as an admin
	 *
	 * @return void
	 */
	public function testSignAsAdmin() {
		// Admins are allowed to sign
		FrozenDate::setTestNow(new FrozenDate('July 1'));
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'sign', 'waiver' => WAIVER_ID_EVENT, 'date' => FrozenDate::now()->toDateString()], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test sign method as a manager
	 *
	 * @return void
	 */
	public function testSignAsManager() {
		// Managers are allowed to sign
		FrozenDate::setTestNow(new FrozenDate('July 1'));
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'sign', 'waiver' => WAIVER_ID_EVENT, 'date' => FrozenDate::now()->toDateString()], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test sign method as a coordinator
	 *
	 * @return void
	 */
	public function testSignAsCoordinator() {
		// Coordinators are allowed to sign
		FrozenDate::setTestNow(new FrozenDate('July 1'));
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'sign', 'waiver' => WAIVER_ID_EVENT, 'date' => FrozenDate::now()->toDateString()], PERSON_ID_COORDINATOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test sign method as a captain
	 *
	 * @return void
	 */
	public function testSignAsCaptain() {
		// Captains are allowed to sign
		FrozenDate::setTestNow(new FrozenDate('July 1'));
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'sign', 'waiver' => WAIVER_ID_EVENT, 'date' => FrozenDate::now()->toDateString()], PERSON_ID_CAPTAIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test sign method as a player
	 *
	 * @return void
	 */
	public function testSignAsPlayer() {
		// Players are allowed to sign
		FrozenDate::setTestNow(new FrozenDate('July 1'));
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'sign', 'waiver' => WAIVER_ID_EVENT, 'date' => FrozenDate::now()->toDateString()], PERSON_ID_PLAYER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test sign method as someone else
	 *
	 * @return void
	 */
	public function testSignAsVisitor() {
		// Visitors are allowed to sign
		FrozenDate::setTestNow(new FrozenDate('July 1'));
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'sign', 'waiver' => WAIVER_ID_EVENT, 'date' => FrozenDate::now()->toDateString()], PERSON_ID_VISITOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test sign method without being logged in
	 *
	 * @return void
	 */
	public function testSignAsAnonymous() {
		// Others are not allowed to sign
		FrozenDate::setTestNow(new FrozenDate('July 1'));
		$this->assertGetAnonymousAccessDenied(['controller' => 'Waivers', 'action' => 'sign', 'waiver' => WAIVER_ID_EVENT, 'date' => FrozenDate::now()->toDateString()]);
	}

	/**
	 * Test review method
	 *
	 * @return void
	 */
	public function testReview() {
		// Admins are allowed to review
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'review', 'waiver' => WAIVER_ID_EVENT], PERSON_ID_ADMIN);

		// Managers are allowed to review
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'review', 'waiver' => WAIVER_ID_EVENT], PERSON_ID_MANAGER);

		// Coordinators are allowed to review
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'review', 'waiver' => WAIVER_ID_EVENT], PERSON_ID_COORDINATOR);

		// Captains are allowed to review
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'review', 'waiver' => WAIVER_ID_EVENT], PERSON_ID_CAPTAIN);

		// Players are allowed to review
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'review', 'waiver' => WAIVER_ID_EVENT], PERSON_ID_PLAYER);

		// Visitors are allowed to review
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'review', 'waiver' => WAIVER_ID_EVENT], PERSON_ID_VISITOR);

		// Others are not allowed to review
		$this->assertGetAnonymousAccessDenied(['controller' => 'Waivers', 'action' => 'review', 'waiver' => WAIVER_ID_EVENT]);
	}

}
