<?php
namespace App\Test\TestCase\Controller;

use Cake\ORM\TableRegistry;
use App\Controller\AppController;

/**
 * App\Controller\AppController Test Case
 */
class AppControllerTest extends ControllerTestCase {

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
			'app.groups',
				'app.groups_people',
			'app.settings',
	];

	/**
	 * Test initialize method
	 *
	 * @return void
	 */
	public function testInitialize() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test afterIdentify method
	 *
	 * @return void
	 */
	public function testAfterIdentify() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test beforeFilter method
	 *
	 * @return void
	 */
	public function testBeforeFilter() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test flashEmail method
	 *
	 * @return void
	 */
	public function testFlashEmail() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test flash method
	 *
	 * @return void
	 */
	public function testFlash() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test beforeRender method
	 *
	 * @return void
	 */
	public function testBeforeRender() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test redirect method
	 *
	 * @return void
	 */
	public function testRedirect() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test forceRedirect method
	 *
	 * @return void
	 */
	public function testForceRedirect() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _applicableAffiliateIDs method as an admin
	 *
	 * @return void
	 */
	public function testApplicableAffiliateIDsAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _applicableAffiliateIDs method as a manager
	 *
	 * @return void
	 */
	public function testApplicableAffiliateIDsAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _applicableAffiliateIDs method as a coordinator
	 *
	 * @return void
	 */
	public function testApplicableAffiliateIDsAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _applicableAffiliateIDs method as a captain
	 *
	 * @return void
	 */
	public function testApplicableAffiliateIDsAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _applicableAffiliateIDs method as a player
	 *
	 * @return void
	 */
	public function testApplicableAffiliateIDsAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _applicableAffiliateIDs method as someone else
	 *
	 * @return void
	 */
	public function testApplicableAffiliateIDsAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _applicableAffiliateIDs method without being logged in
	 *
	 * @return void
	 */
	public function testApplicableAffiliateIDsAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _addTeamMenuItems method as an admin
	 *
	 * @return void
	 */
	public function testAddTeamMenuItemsAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _addTeamMenuItems method as a manager
	 *
	 * @return void
	 */
	public function testAddTeamMenuItemsAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _addTeamMenuItems method as a coordinator
	 *
	 * @return void
	 */
	public function testAddTeamMenuItemsAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _addTeamMenuItems method as a captain
	 *
	 * @return void
	 */
	public function testAddTeamMenuItemsAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _addTeamMenuItems method as a player
	 *
	 * @return void
	 */
	public function testAddTeamMenuItemsAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _addTeamMenuItems method as someone else
	 *
	 * @return void
	 */
	public function testAddTeamMenuItemsAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _addTeamMenuItems method without being logged in
	 *
	 * @return void
	 */
	public function testAddTeamMenuItemsAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _addFranchiseMenuItems method as an admin
	 *
	 * @return void
	 */
	public function testAddFranchiseMenuItemsAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _addFranchiseMenuItems method as a manager
	 *
	 * @return void
	 */
	public function testAddFranchiseMenuItemsAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _addFranchiseMenuItems method as a coordinator
	 *
	 * @return void
	 */
	public function testAddFranchiseMenuItemsAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _addFranchiseMenuItems method as a captain
	 *
	 * @return void
	 */
	public function testAddFranchiseMenuItemsAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _addFranchiseMenuItems method as a player
	 *
	 * @return void
	 */
	public function testAddFranchiseMenuItemsAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _addFranchiseMenuItems method as someone else
	 *
	 * @return void
	 */
	public function testAddFranchiseMenuItemsAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _addFranchiseMenuItems method without being logged in
	 *
	 * @return void
	 */
	public function testAddFranchiseMenuItemsAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _addDivisionMenuItems method as an admin
	 *
	 * @return void
	 */
	public function testAddDivisionMenuItemsAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _addDivisionMenuItems method as a manager
	 *
	 * @return void
	 */
	public function testAddDivisionMenuItemsAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _addDivisionMenuItems method as a coordinator
	 *
	 * @return void
	 */
	public function testAddDivisionMenuItemsAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _addDivisionMenuItems method as a captain
	 *
	 * @return void
	 */
	public function testAddDivisionMenuItemsAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _addDivisionMenuItems method as a player
	 *
	 * @return void
	 */
	public function testAddDivisionMenuItemsAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _addDivisionMenuItems method as someone else
	 *
	 * @return void
	 */
	public function testAddDivisionMenuItemsAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _addDivisionMenuItems method without being logged in
	 *
	 * @return void
	 */
	public function testAddDivisionMenuItemsAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _showRegistration method as an admin
	 *
	 * @return void
	 */
	public function testShowRegistrationAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _showRegistration method as a manager
	 *
	 * @return void
	 */
	public function testShowRegistrationAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _showRegistration method as a coordinator
	 *
	 * @return void
	 */
	public function testShowRegistrationAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _showRegistration method as a captain
	 *
	 * @return void
	 */
	public function testShowRegistrationAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _showRegistration method as a player
	 *
	 * @return void
	 */
	public function testShowRegistrationAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _showRegistration method as someone else
	 *
	 * @return void
	 */
	public function testShowRegistrationAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _showRegistration method without being logged in
	 *
	 * @return void
	 */
	public function testShowRegistrationAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _addMenuItem method
	 *
	 * @return void
	 */
	public function testAddMenuItem() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _sendMail method
	 *
	 * @return void
	 */
	public function testSendMail() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _extractEmails method
	 *
	 * @return void
	 */
	public function testExtractEmails() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _isChild method
	 *
	 * @return void
	 */
	public function testIsChild() {
		$person = TableRegistry::get('People')->get(PERSON_ID_PLAYER, [
			'contain' => ['Groups']
		]);
		$this->assertFalse(AppController::_isChild($person));
		$person = TableRegistry::get('People')->get(PERSON_ID_CHILD, [
			'contain' => ['Groups']
		]);
		$this->assertTrue(AppController::_isChild($person));
	}

}
