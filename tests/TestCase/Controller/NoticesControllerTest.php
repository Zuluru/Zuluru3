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
					'app.people_people',
			'app.groups',
				'app.groups_people',
			'app.leagues',
				'app.divisions',
					'app.teams',
					'app.divisions_people',
			//'app.notices',
				'app.notices_people',
			'app.settings',
		'app.i18n',
	];

	/**
	 * Test viewed method as an admin
	 *
	 * @return void
	 */
	public function testViewedAsAdmin() {
		// Admins are allowed to mark a notice as viewed
		$this->assertGetAsAccessOk(['controller' => 'Notices', 'action' => 'viewed', 1], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test viewed method as a manager
	 *
	 * @return void
	 */
	public function testViewedAsManager() {
		// Managers are allowed to mark a notice as viewed
		$this->assertGetAsAccessOk(['controller' => 'Notices', 'action' => 'viewed', 1], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test viewed method as a coordinator
	 *
	 * @return void
	 */
	public function testViewedAsCoordinator() {
		// Coordinators are allowed to mark a notice as viewed
		$this->assertGetAsAccessOk(['controller' => 'Notices', 'action' => 'viewed', 1], PERSON_ID_COORDINATOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test viewed method as a captain
	 *
	 * @return void
	 */
	public function testViewedAsCaptain() {
		// Captains are allowed to mark a notice as viewed
		$this->assertGetAsAccessOk(['controller' => 'Notices', 'action' => 'viewed', 1], PERSON_ID_CAPTAIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test viewed method as a player
	 *
	 * @return void
	 */
	public function testViewedAsPlayer() {
		// Players are allowed to mark a notice as viewed
		$this->assertGetAsAccessOk(['controller' => 'Notices', 'action' => 'viewed', 1], PERSON_ID_PLAYER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test viewed method as someone else
	 *
	 * @return void
	 */
	public function testViewedAsVisitor() {
		// Visitors are allowed to mark a notice as viewed
		$this->assertGetAsAccessOk(['controller' => 'Notices', 'action' => 'viewed', 1], PERSON_ID_VISITOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test viewed method without being logged in
	 *
	 * @return void
	 */
	public function testViewedAsAnonymous() {
		// Others are allowed to mark a notice as viewed
		$this->assertGetAnonymousAccessOk(['controller' => 'Notices', 'action' => 'viewed', 1]);
		$this->markTestIncomplete('Not implemented yet.');
	}

}
