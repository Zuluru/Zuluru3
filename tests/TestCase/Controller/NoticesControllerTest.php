<?php
namespace App\Test\TestCase\Controller;

/**
 * App\Controller\NoticesController Test Case
 */
class NoticesControllerTest extends ControllerTestCase {

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
			//'app.notices',
				'app.notices_people',
			'app.settings',
	];

	/**
	 * Test viewed method as an admin
	 *
	 * @return void
	 */
	public function testViewedAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test viewed method as a manager
	 *
	 * @return void
	 */
	public function testViewedAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test viewed method as a coordinator
	 *
	 * @return void
	 */
	public function testViewedAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test viewed method as a captain
	 *
	 * @return void
	 */
	public function testViewedAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test viewed method as a player
	 *
	 * @return void
	 */
	public function testViewedAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test viewed method as someone else
	 *
	 * @return void
	 */
	public function testViewedAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test viewed method without being logged in
	 *
	 * @return void
	 */
	public function testViewedAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
