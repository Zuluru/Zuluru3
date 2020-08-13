<?php
namespace App\Test\TestCase\Controller;

/**
 * App\Controller\SettingsController Test Case
 */
class SettingsControllerTest extends ControllerTestCase {

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
		'app.I18n',
		'app.Plugins',
	];

	/**
	 * Test edit method as an admin
	 *
	 * @return void
	 */
	public function testEditAsAdmin() {
		// Admins are allowed to edit settings
		$this->assertGetAsAccessOk(['controller' => 'Settings', 'action' => 'edit', 'organization'], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers are allowed to edit settings
		$this->assertGetAsAccessOk(['controller' => 'Settings', 'action' => 'edit', 'organization'], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as others
	 *
	 * @return void
	 */
	public function testEditAsOthers() {
		// Others are not allowed to edit
		$this->assertGetAsAccessDenied(['controller' => 'Settings', 'action' => 'edit', 'organization'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Settings', 'action' => 'edit', 'organization'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Settings', 'action' => 'edit', 'organization'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Settings', 'action' => 'edit', 'organization'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Settings', 'action' => 'edit', 'organization']);
	}

}
