<?php
namespace App\Test\TestCase\Controller;

/**
 * App\Controller\HelpController Test Case
 */
class HelpControllerTest extends ControllerTestCase {

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
			'app.Groups',
				'app.GroupsPeople',
			'app.Settings',
		'app.I18n',
		'app.Plugins',
	];

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testView() {
		// Anyone is allowed to view
		$this->assertGetAsAccessOk(['controller' => 'Help', 'action' => 'view'], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Help', 'action' => 'view'], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'Help', 'action' => 'view'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessOk(['controller' => 'Help', 'action' => 'view'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessOk(['controller' => 'Help', 'action' => 'view'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessOk(['controller' => 'Help', 'action' => 'view'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessOk(['controller' => 'Help', 'action' => 'view']);
	}

}
