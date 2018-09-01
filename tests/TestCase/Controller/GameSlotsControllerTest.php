<?php
namespace App\Test\TestCase\Controller;

/**
 * App\Controller\GameSlotsController Test Case
 */
class GameSlotsControllerTest extends ControllerTestCase {

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
			'app.regions',
				'app.facilities',
					'app.fields',
			'app.leagues',
				'app.divisions',
					'app.teams',
					'app.game_slots',
						'app.divisions_gameslots',
					'app.pools',
						'app.pools_teams',
					'app.games',
			'app.settings',
	];

	/**
	 * Test view method as an admin
	 *
	 * @return void
	 */
	public function testViewAsAdmin() {
		// Admins are allowed to view game slots, with full edit permissions
		$this->assertAccessOk(['controller' => 'GameSlots', 'action' => 'view', 'slot' => GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/game_slots/edit\?slot=' . GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1 . '#ms');
		$this->assertResponseRegExp('#/game_slots/delete\?slot=' . GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1 . '#ms');

		$this->assertAccessOk(['controller' => 'GameSlots', 'action' => 'view', 'slot' => GAME_SLOT_ID_SUNDAY_CENTRAL_TECH_WEEK_1], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/game_slots/edit\?slot=' . GAME_SLOT_ID_SUNDAY_CENTRAL_TECH_WEEK_1 . '#ms');
		$this->assertResponseRegExp('#/game_slots/delete\?slot=' . GAME_SLOT_ID_SUNDAY_CENTRAL_TECH_WEEK_1 . '#ms');
	}

	/**
	 * Test view method as a manager
	 *
	 * @return void
	 */
	public function testViewAsManager() {
		// Managers are allowed to view game slots
		$this->assertAccessOk(['controller' => 'GameSlots', 'action' => 'view', 'slot' => GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/game_slots/edit\?slot=' . GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1 . '#ms');
		$this->assertResponseRegExp('#/game_slots/delete\?slot=' . GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1 . '#ms');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'GameSlots', 'action' => 'view', 'slot' => GAME_SLOT_ID_SUNDAY_CENTRAL_TECH_WEEK_1], PERSON_ID_MANAGER);
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
		// Others are not allowed to view game slots
		$this->assertAccessRedirect(['controller' => 'GameSlots', 'action' => 'view', 'slot' => GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1], PERSON_ID_PLAYER);
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
	 * Test delete method as an admin
	 *
	 * @return void
	 */
	public function testDeleteAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to delete game slots
		$this->assertAccessRedirect(['controller' => 'GameSlots', 'action' => 'delete', 'slot' => GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_10],
			PERSON_ID_ADMIN, 'post', [], null,
			'The game slot has been deleted.', 'Flash.flash.0.message');

		// But not ones with dependencies
		$this->assertAccessRedirect(['controller' => 'GameSlots', 'action' => 'delete', 'slot' => GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1],
			PERSON_ID_ADMIN, 'post', [], null,
			'This game slot has a game assigned to it and cannot be deleted.', 'Flash.flash.0.message');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers can delete game slots in their affiliate
		$this->assertAccessRedirect(['controller' => 'GameSlots', 'action' => 'delete', 'slot' => GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_10],
			PERSON_ID_MANAGER, 'post', [], null,
			'The game slot has been deleted.', 'Flash.flash.0.message');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'GameSlots', 'action' => 'delete', 'slot' => GAME_SLOT_ID_SUNDAY_CENTRAL_TECH_WEEK_1],
			PERSON_ID_MANAGER, 'post');
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

	/**
	 * Test submit_score method as an admin
	 *
	 * @return void
	 */
	public function testSubmitScoreAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test submit_score method as a manager
	 *
	 * @return void
	 */
	public function testSubmitScoreAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test submit_score method as a coordinator
	 *
	 * @return void
	 */
	public function testSubmitScoreAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test submit_score method as a captain
	 *
	 * @return void
	 */
	public function testSubmitScoreAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test submit_score method as a player
	 *
	 * @return void
	 */
	public function testSubmitScoreAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test submit_score method as someone else
	 *
	 * @return void
	 */
	public function testSubmitScoreAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test submit_score method without being logged in
	 *
	 * @return void
	 */
	public function testSubmitScoreAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
