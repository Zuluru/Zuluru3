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
					'app.divisions_days',
					'app.game_slots',
						'app.divisions_gameslots',
					'app.divisions_people',
					'app.pools',
						'app.pools_teams',
					'app.games',
			'app.settings',
	];

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testView() {
		// Admins are allowed to view game slots, with full edit permissions
		$this->assertGetAsAccessOk(['controller' => 'GameSlots', 'action' => 'view', 'slot' => GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1], PERSON_ID_ADMIN);
		$this->assertResponseContains('/game_slots/edit?slot=' . GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1);
		$this->assertResponseContains('/game_slots/delete?slot=' . GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1);

		$this->assertGetAsAccessOk(['controller' => 'GameSlots', 'action' => 'view', 'slot' => GAME_SLOT_ID_SUNDAY_CENTRAL_TECH_WEEK_1], PERSON_ID_ADMIN);
		$this->assertResponseContains('/game_slots/edit?slot=' . GAME_SLOT_ID_SUNDAY_CENTRAL_TECH_WEEK_1);
		$this->assertResponseContains('/game_slots/delete?slot=' . GAME_SLOT_ID_SUNDAY_CENTRAL_TECH_WEEK_1);

		// Managers are allowed to view game slots
		$this->assertGetAsAccessOk(['controller' => 'GameSlots', 'action' => 'view', 'slot' => GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1], PERSON_ID_MANAGER);
		$this->assertResponseContains('/game_slots/edit?slot=' . GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1);
		$this->assertResponseContains('/game_slots/delete?slot=' . GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'GameSlots', 'action' => 'view', 'slot' => GAME_SLOT_ID_SUNDAY_CENTRAL_TECH_WEEK_1], PERSON_ID_MANAGER);

		// Others are not allowed to view game slots
		$this->assertGetAsAccessDenied(['controller' => 'GameSlots', 'action' => 'view', 'slot' => GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'GameSlots', 'action' => 'view', 'slot' => GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'GameSlots', 'action' => 'view', 'slot' => GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'GameSlots', 'action' => 'view', 'slot' => GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'GameSlots', 'action' => 'view', 'slot' => GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add method as an admin
	 *
	 * @return void
	 */
	public function testAddAsAdmin() {
		// Admins are allowed to add
		$this->assertGetAsAccessOk(['controller' => 'GameSlots', 'action' => 'add', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'GameSlots', 'action' => 'add', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_ADMIN);
		// TODO: Test with affiliates turned off and no affiliate ID in the URL
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers are allowed to add
		$this->assertGetAsAccessOk(['controller' => 'GameSlots', 'action' => 'add', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'GameSlots', 'action' => 'add', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as others
	 *
	 * @return void
	 */
	public function testAddAsOthers() {
		// Others are not allowed to add
		$this->assertGetAsAccessDenied(['controller' => 'GameSlots', 'action' => 'add', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'GameSlots', 'action' => 'add', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_COORDINATOR);

		$this->assertGetAsAccessDenied(['controller' => 'GameSlots', 'action' => 'add', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'GameSlots', 'action' => 'add', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_CAPTAIN);

		$this->assertGetAsAccessDenied(['controller' => 'GameSlots', 'action' => 'add', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'GameSlots', 'action' => 'add', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_PLAYER);

		$this->assertGetAsAccessDenied(['controller' => 'GameSlots', 'action' => 'add', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1], PERSON_ID_VISITOR);
		$this->assertGetAsAccessDenied(['controller' => 'GameSlots', 'action' => 'add', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_VISITOR);

		$this->assertGetAnonymousAccessDenied(['controller' => 'GameSlots', 'action' => 'add', 'field' => FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1]);
		$this->assertGetAnonymousAccessDenied(['controller' => 'GameSlots', 'action' => 'add', 'affiliate' => AFFILIATE_ID_CLUB]);
	}

	/**
	 * Test edit method as an admin
	 *
	 * @return void
	 */
	public function testEditAsAdmin() {
		// Admins are allowed to edit
		$this->assertGetAsAccessOk(['controller' => 'GameSlots', 'action' => 'edit', 'slot' => GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers are allowed to edit
		$this->assertGetAsAccessOk(['controller' => 'GameSlots', 'action' => 'edit', 'slot' => GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as others
	 *
	 * @return void
	 */
	public function testEditAsOthers() {
		// Others are not allowed to edit
		$this->assertGetAsAccessDenied(['controller' => 'GameSlots', 'action' => 'edit', 'slot' => GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'GameSlots', 'action' => 'edit', 'slot' => GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'GameSlots', 'action' => 'edit', 'slot' => GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'GameSlots', 'action' => 'edit', 'slot' => GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'GameSlots', 'action' => 'edit', 'slot' => GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1]);
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
		$this->assertPostAsAccessRedirect(['controller' => 'GameSlots', 'action' => 'delete', 'slot' => GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_10],
			PERSON_ID_ADMIN, [], '/',
			'The game slot has been deleted.');

		// But not ones with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'GameSlots', 'action' => 'delete', 'slot' => GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1],
			PERSON_ID_ADMIN, [], '/',
			'This game slot has a game assigned to it and cannot be deleted.');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to delete game slots in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'GameSlots', 'action' => 'delete', 'slot' => GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_10],
			PERSON_ID_MANAGER, [], '/',
			'The game slot has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'GameSlots', 'action' => 'delete', 'slot' => GAME_SLOT_ID_SUNDAY_CENTRAL_TECH_WEEK_1],
			PERSON_ID_MANAGER);
	}

	/**
	 * Test delete method as others
	 *
	 * @return void
	 */
	public function testDeleteAsOthers() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Others are not allowed to delete game slots
		$this->assertPostAsAccessDenied(['controller' => 'GameSlots', 'action' => 'delete', 'slot' => GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1],
			PERSON_ID_COORDINATOR);
		$this->assertPostAsAccessDenied(['controller' => 'GameSlots', 'action' => 'delete', 'slot' => GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1],
			PERSON_ID_CAPTAIN);
		$this->assertPostAsAccessDenied(['controller' => 'GameSlots', 'action' => 'delete', 'slot' => GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'GameSlots', 'action' => 'delete', 'slot' => GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1],
			PERSON_ID_VISITOR);
		$this->assertPostAnonymousAccessDenied(['controller' => 'GameSlots', 'action' => 'delete', 'slot' => GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1]);
	}

	/**
	 * Test submit_score method as an admin
	 *
	 * @return void
	 */
	public function testSubmitScoreAsAdmin() {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test submit_score method as a manager
	 *
	 * @return void
	 */
	public function testSubmitScoreAsManager() {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test submit_score method as a coordinator
	 *
	 * @return void
	 */
	public function testSubmitScoreAsCoordinator() {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test submit_score method as a captain
	 *
	 * @return void
	 */
	public function testSubmitScoreAsCaptain() {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test submit_score method as others
	 *
	 * @return void
	 */
	public function testSubmitScoreAsOthers() {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

}
