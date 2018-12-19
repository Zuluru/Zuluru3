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
		'app.affiliates',
			'app.users',
				'app.people',
					'app.affiliates_people',
					'app.people_people',
			'app.groups',
				'app.groups_people',
			'app.leagues',
				'app.divisions',
					'app.teams',
					'app.divisions_people',
			'app.settings',
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

	/**
	 * Test payment_provider_fields method
	 *
	 * @return void
	 */
	public function testPaymentProviderFields() {
		$this->enableCsrfToken();

		// Admins are allowed to get payment provider fields
		$this->assertPostAjaxAsAccessOk(['controller' => 'Settings', 'action' => 'payment_provider_fields'],
			PERSON_ID_ADMIN, ['payment_implementation' => 'paypal']);

		// Managers are allowed to get payment provider fields
		$this->assertPostAjaxAsAccessOk(['controller' => 'Settings', 'action' => 'payment_provider_fields'],
			PERSON_ID_MANAGER, ['payment_implementation' => 'paypal']);

		// Others are not allowed to get payment provider fields
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Settings', 'action' => 'payment_provider_fields'],
			PERSON_ID_COORDINATOR, ['payment_implementation' => 'paypal']);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Settings', 'action' => 'payment_provider_fields'],
			PERSON_ID_CAPTAIN, ['payment_implementation' => 'paypal']);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Settings', 'action' => 'payment_provider_fields'],
			PERSON_ID_PLAYER, ['payment_implementation' => 'paypal']);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Settings', 'action' => 'payment_provider_fields'],
			PERSON_ID_VISITOR, ['payment_implementation' => 'paypal']);
		$this->assertPostAjaxAnonymousAccessDenied(['controller' => 'Settings', 'action' => 'payment_provider_fields'],
			['payment_implementation' => 'paypal']);
	}

}
