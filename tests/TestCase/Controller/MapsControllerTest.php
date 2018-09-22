<?php
namespace App\Test\TestCase\Controller;

/**
 * App\Controller\MapsController Test Case
 */
class MapsControllerTest extends ControllerTestCase {

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
			'app.regions',
				'app.facilities',
					'app.fields',
			'app.leagues',
				'app.divisions',
			'app.settings',
	];

	/**
	 * Test index method as an admin
	 *
	 * @return void
	 */
	public function testIndexAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test index method as a manager
	 *
	 * @return void
	 */
	public function testIndexAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test index method as a coordinator
	 *
	 * @return void
	 */
	public function testIndexAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test index method as a captain
	 *
	 * @return void
	 */
	public function testIndexAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test index method as a player
	 *
	 * @return void
	 */
	public function testIndexAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test index method as someone else
	 *
	 * @return void
	 */
	public function testIndexAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test index method without being logged in
	 *
	 * @return void
	 */
	public function testIndexAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
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
		// Anyone is allowed to view maps from any affiliate
		$this->assertAccessOk(['controller' => 'Maps', 'action' => 'view', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1], PERSON_ID_PLAYER);
		$this->assertResponseRegExp('#fields\[' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1 . '\] = {#ms');
		$this->assertResponseRegExp('#fields\[' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_2 . '\] = {#ms');
		$this->assertResponseRegExp('#fields\[' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_3 . '\] = {#ms');
		$this->assertResponseNotRegExp('#fields\[' . FIELD_ID_SUNNYBROOK_GREENSPACE . '\] = {#ms');

		$this->assertAccessOk(['controller' => 'Maps', 'action' => 'view', 'field' => FIELD_ID_CENTRAL_TECH], PERSON_ID_PLAYER);

		// But not maps that haven't been created yet
		$this->assertAccessRedirect(['controller' => 'Maps', 'action' => 'view', 'field' => FIELD_ID_MARILYN_BELL],
			PERSON_ID_PLAYER, 'get', [], ['controller' => 'Facilities', 'action' => 'index'],
			'That field has not yet been laid out.', 'Flash.flash.0.message');

		// When viewing closed fields, we get shown all fields at that facility, not just open ones
		$this->assertAccessOk(['controller' => 'Maps', 'action' => 'view', 'field' => FIELD_ID_SUNNYBROOK_GREENSPACE], PERSON_ID_PLAYER);
		$this->assertResponseRegExp('#fields\[' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1 . '\] = {#ms');
		$this->assertResponseRegExp('#fields\[' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_2 . '\] = {#ms');
		$this->assertResponseRegExp('#fields\[' . FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_3 . '\] = {#ms');
		$this->assertResponseRegExp('#fields\[' . FIELD_ID_SUNNYBROOK_GREENSPACE . '\] = {#ms');
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

}
