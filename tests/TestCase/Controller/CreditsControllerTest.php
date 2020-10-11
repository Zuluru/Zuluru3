<?php
namespace App\Test\TestCase\Controller;

use App\Controller\CreditsController;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

/**
 * App\Controller\CreditsController Test Case
 */
class CreditsControllerTest extends ControllerTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.EventTypes',
		'app.Affiliates',
			'app.Users',
				'app.People',
					'app.AffiliatesPeople',
					'app.PeoplePeople',
					'app.Credits',
			'app.Groups',
				'app.GroupsPeople',
			'app.Leagues',
				'app.Divisions',
					'app.Teams',
						'app.TeamsPeople',
					'app.DivisionsPeople',
			'app.Settings',
		'app.I18n',
		'app.Plugins',
    ];

	/**
	 * Test credits method
	 *
	 * @return void
	 */
	public function testCredits() {
		// Admins are allowed to list credits
		$this->assertGetAsAccessOk(['controller' => 'Credits', 'action' => 'index'], PERSON_ID_ADMIN);

		// Managers are allowed to list credits
		$this->assertGetAsAccessOk(['controller' => 'Credits', 'action' => 'index'], PERSON_ID_MANAGER);

		// Others are not allowed to list credits
		$this->assertGetAsAccessDenied(['controller' => 'Credits', 'action' => 'index'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Credits', 'action' => 'index'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Credits', 'action' => 'index'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Credits', 'action' => 'index'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Credits', 'action' => 'index']);
	}

	/**
	 * Test view method as an admin
	 *
	 * @return void
	 */
	public function testViewAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test view method as a manager
	 *
	 * @return void
	 */
	public function testViewAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
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
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
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
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
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
	 * Test transfer method as an admin
	 *
	 * @return void
	 */
	public function testTransferAsAdmin() {
		// Admins are allowed to transfer credits
		$this->assertGetAsAccessOk(['controller' => 'Credits', 'action' => 'transfer', 'credit' => CREDIT_ID_CAPTAIN], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test transfer method as a manager
	 *
	 * @return void
	 */
	public function testTransferAsManager() {
		// Managers are allowed to transfer credits
		$this->assertGetAsAccessOk(['controller' => 'Credits', 'action' => 'transfer', 'credit' => CREDIT_ID_CAPTAIN], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test transfer method as the credit owner
	 *
	 * @return void
	 */
	public function testTransferAsOwner() {
		// Peopler are allowed to transfer their own credits
		$this->assertGetAsAccessOk(['controller' => 'Credits', 'action' => 'transfer', 'credit' => CREDIT_ID_CAPTAIN], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessRedirect(['controller' => 'Credits', 'action' => 'transfer', 'credit' => CREDIT_ID_CAPTAIN, 'person' => PERSON_ID_CHILD],
			PERSON_ID_CAPTAIN,
			'/', 'The credit has been transferred.'
		);

		$credit = TableRegistry::getTableLocator()->get('Credits')->get(CREDIT_ID_CAPTAIN);
		$this->assertEquals("Credit note.\nTransferred from Crystal Captain.", $credit->notes);
		$this->assertEquals(PERSON_ID_CHILD, $credit->person_id);

		$messages = Configure::consume('test_emails');
		$this->assertEquals(1, count($messages));
		$this->assertTextContains('A credit with a balance of CA$1.00 has been transferred to you.', $messages[0]);
		$this->assertTextContains('This credit can be redeemed towards any future purchase on the Test Zuluru Affiliate site', $messages[0]);
	}

	/**
	 * Test transfer method as a relative of the credit owner
	 *
	 * @return void
	 */
	public function testTransferAsRelative() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test transfer method as others
	 *
	 * @return void
	 */
	public function testTransferAsOthers() {
		// Others are not allowed to transfer credits
		$this->assertGetAsAccessDenied(['controller' => 'Credits', 'action' => 'transfer', 'credit' => CREDIT_ID_CAPTAIN], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Credits', 'action' => 'transfer', 'credit' => CREDIT_ID_CAPTAIN], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Credits', 'action' => 'transfer', 'credit' => CREDIT_ID_CAPTAIN], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Credits', 'action' => 'transfer', 'credit' => CREDIT_ID_CAPTAIN]);
	}

	/**
	 * Test delete method as an admin
	 *
	 * @return void
	 */
	public function testDeleteAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
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

}
