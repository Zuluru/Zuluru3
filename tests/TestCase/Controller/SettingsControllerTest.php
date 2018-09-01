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
			'app.groups',
				'app.groups_people',
			'app.settings',
	];

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
	 * Test payment_provider_fields method as an admin
	 *
	 * @return void
	 */
	public function testPaymentProviderFieldsAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test payment_provider_fields method as a manager
	 *
	 * @return void
	 */
	public function testPaymentProviderFieldsAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test payment_provider_fields method as a coordinator
	 *
	 * @return void
	 */
	public function testPaymentProviderFieldsAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test payment_provider_fields method as a captain
	 *
	 * @return void
	 */
	public function testPaymentProviderFieldsAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test payment_provider_fields method as a player
	 *
	 * @return void
	 */
	public function testPaymentProviderFieldsAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test payment_provider_fields method as someone else
	 *
	 * @return void
	 */
	public function testPaymentProviderFieldsAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test payment_provider_fields method without being logged in
	 *
	 * @return void
	 */
	public function testPaymentProviderFieldsAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
