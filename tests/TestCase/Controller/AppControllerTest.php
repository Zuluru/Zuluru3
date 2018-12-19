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
	 * Test _addTeamMenuItems method
	 *
	 * @return void
	 */
	public function testAddTeamMenuItems() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _addFranchiseMenuItems method
	 *
	 * @return void
	 */
	public function testAddFranchiseMenuItems() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _addDivisionMenuItems method
	 *
	 * @return void
	 */
	public function testAddDivisionMenuItems() {
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
