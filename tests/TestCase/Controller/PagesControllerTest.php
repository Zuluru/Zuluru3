<?php
namespace App\Test\TestCase\Controller;

/**
 * App\Controller\PagesController Test Case
 */
class PagesControllerTest extends ControllerTestCase {

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
	 * Test display method as an admin
	 *
	 * @return void
	 */
	public function testDisplayAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test display method as a manager
	 *
	 * @return void
	 */
	public function testDisplayAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test display method as a coordinator
	 *
	 * @return void
	 */
	public function testDisplayAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test display method as a captain
	 *
	 * @return void
	 */
	public function testDisplayAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test display method as a player
	 *
	 * @return void
	 */
	public function testDisplayAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test display method as someone else
	 *
	 * @return void
	 */
	public function testDisplayAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test display method without being logged in
	 *
	 * @return void
	 */
	public function testDisplayAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
