<?php
namespace App\Test\TestCase\Controller;

use App\Shell\Task\InitializeBadgeTask;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

/**
 * App\Controller\BadgesController Test Case
 */
class BadgesControllerTest extends ControllerTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.event_types',
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
						'app.teams_people',
					'app.pools',
						'app.pools_teams',
					'app.games',
			'app.events',
				'app.prices',
					'app.registrations',
			'app.badges',
				'app.badges_people',
			'app.settings',
	];

	/**
	 * Test index method as an admin
	 *
	 * @return void
	 */
	public function testIndexAsAdmin() {
		// Admins are allowed to get the index
		$this->assertAccessOk(['controller' => 'Badges', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/badges/view\?badge=' . BADGE_ID_ACTIVE_PLAYER . '#ms');
		$this->assertResponseRegExp('#/badges/edit\?badge=' . BADGE_ID_ACTIVE_PLAYER . '#ms');
		$this->assertResponseRegExp('#/badges/delete\?badge=' . BADGE_ID_ACTIVE_PLAYER . '#ms');
		$this->assertResponseRegExp('#/badges/deactivate\?badge=' . BADGE_ID_ACTIVE_PLAYER . '#ms');
		$this->assertResponseRegExp('#/badges/view\?badge=' . BADGE_ID_ACTIVE_PLAYER_SUB . '#ms');
		$this->assertResponseRegExp('#/badges/edit\?badge=' . BADGE_ID_ACTIVE_PLAYER_SUB . '#ms');
		$this->assertResponseRegExp('#/badges/delete\?badge=' . BADGE_ID_ACTIVE_PLAYER_SUB . '#ms');
		$this->assertResponseRegExp('#/badges/deactivate\?badge=' . BADGE_ID_ACTIVE_PLAYER_SUB . '#ms');
	}

	/**
	 * Test index method as a manager
	 *
	 * @return void
	 */
	public function testIndexAsManager() {
		// Managers are allowed to get the index, but don't have edit options on badges in other affiliates
		$this->assertAccessOk(['controller' => 'Badges', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/badges/view\?badge=' . BADGE_ID_ACTIVE_PLAYER . '#ms');
		$this->assertResponseRegExp('#/badges/edit\?badge=' . BADGE_ID_ACTIVE_PLAYER . '#ms');
		$this->assertResponseRegExp('#/badges/delete\?badge=' . BADGE_ID_ACTIVE_PLAYER . '#ms');
		$this->assertResponseRegExp('#/badges/deactivate\?badge=' . BADGE_ID_ACTIVE_PLAYER . '#ms');
		$this->assertResponseNotRegExp('#/badges/view\?badge=' . BADGE_ID_ACTIVE_PLAYER_SUB . '#ms');
		$this->assertResponseNotRegExp('#/badges/edit\?badge=' . BADGE_ID_ACTIVE_PLAYER_SUB . '#ms');
		$this->assertResponseNotRegExp('#/badges/delete\?badge=' . BADGE_ID_ACTIVE_PLAYER_SUB . '#ms');
		$this->assertResponseNotRegExp('#/badges/deactivate\?badge=' . BADGE_ID_ACTIVE_PLAYER_SUB . '#ms');
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
		// Others are allowed to get the index, but don't have edit options
		$this->assertAccessOk(['controller' => 'Badges', 'action' => 'index'], PERSON_ID_PLAYER);
		$this->assertResponseRegExp('#/badges/view\?badge=' . BADGE_ID_ACTIVE_PLAYER . '#ms');
		$this->assertResponseNotRegExp('#/badges/edit\?badge=' . BADGE_ID_ACTIVE_PLAYER . '#ms');
		$this->assertResponseNotRegExp('#/badges/delete\?badge=' . BADGE_ID_ACTIVE_PLAYER . '#ms');
		$this->assertResponseNotRegExp('#/badges/deactivate\?badge=' . BADGE_ID_ACTIVE_PLAYER . '#ms');
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
	 * Test deactivated method as an admin
	 *
	 * @return void
	 */
	public function testDeactivatedAsAdmin() {
		// Admins are allowed to view the list of deactivated badges
		$this->assertAccessOk(['controller' => 'Badges', 'action' => 'deactivated'], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/badges/edit\?badge=' . BADGE_ID_BOARD_OF_DIRECTORS . '#ms');
		$this->assertResponseRegExp('#/badges/delete\?badge=' . BADGE_ID_BOARD_OF_DIRECTORS . '#ms');
		$this->assertResponseRegExp('#/badges/activate\?badge=' . BADGE_ID_BOARD_OF_DIRECTORS . '#ms');
	}

	/**
	 * Test deactivated method as a manager
	 *
	 * @return void
	 */
	public function testDeactivatedAsManager() {
		// Managers are allowed to view the list of deactivated badges
		$this->assertAccessOk(['controller' => 'Badges', 'action' => 'deactivated'], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/badges/edit\?badge=' . BADGE_ID_BOARD_OF_DIRECTORS . '#ms');
		$this->assertResponseRegExp('#/badges/delete\?badge=' . BADGE_ID_BOARD_OF_DIRECTORS . '#ms');
		$this->assertResponseRegExp('#/badges/activate\?badge=' . BADGE_ID_BOARD_OF_DIRECTORS . '#ms');
	}

	/**
	 * Test deactivated method as a coordinator
	 *
	 * @return void
	 */
	public function testDeactivatedAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test deactivated method as a captain
	 *
	 * @return void
	 */
	public function testDeactivatedAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test deactivated method as a player
	 *
	 * @return void
	 */
	public function testDeactivatedAsPlayer() {
		// Others are not allowed to view the list of deactivated badges
		$this->assertAccessRedirect(['controller' => 'Badges', 'action' => 'deactivated'], PERSON_ID_PLAYER);
	}

	/**
	 * Test deactivated method as someone else
	 *
	 * @return void
	 */
	public function testDeactivatedAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test deactivated method without being logged in
	 *
	 * @return void
	 */
	public function testDeactivatedAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test view method as an admin
	 *
	 * @return void
	 */
	public function testViewAsAdmin() {
		// Admins are allowed to view and edit badges
		$this->assertAccessOk(['controller' => 'Badges', 'action' => 'view', 'badge' => BADGE_ID_ACTIVE_PLAYER], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/badges/edit\?badge=' . BADGE_ID_ACTIVE_PLAYER . '#ms');
		$this->assertResponseRegExp('#/badges/delete\?badge=' . BADGE_ID_ACTIVE_PLAYER . '#ms');

		// Admins can also view deactivated badges
		$this->assertAccessOk(['controller' => 'Badges', 'action' => 'view', 'badge' => BADGE_ID_MEMBER], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/badges/edit\?badge=' . BADGE_ID_MEMBER . '#ms');
		$this->assertResponseRegExp('#/badges/delete\?badge=' . BADGE_ID_MEMBER . '#ms');

		// Or anything with admin-only access
		$this->assertAccessOk(['controller' => 'Badges', 'action' => 'view', 'badge' => BADGE_ID_RED_FLAG], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/badges/edit\?badge=' . BADGE_ID_RED_FLAG . '#ms');
		$this->assertResponseRegExp('#/badges/delete\?badge=' . BADGE_ID_RED_FLAG . '#ms');

		// And badges from all affiliates
		$this->assertAccessOk(['controller' => 'Badges', 'action' => 'view', 'badge' => BADGE_ID_ACTIVE_PLAYER_SUB], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/badges/edit\?badge=' . BADGE_ID_ACTIVE_PLAYER_SUB . '#ms');
		$this->assertResponseRegExp('#/badges/delete\?badge=' . BADGE_ID_ACTIVE_PLAYER_SUB . '#ms');
	}

	/**
	 * Test view method as a manager
	 *
	 * @return void
	 */
	public function testViewAsManager() {
		// Managers are allowed to view and edit badges
		$this->assertAccessOk(['controller' => 'Badges', 'action' => 'view', 'badge' => BADGE_ID_ACTIVE_PLAYER], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/badges/edit\?badge=' . BADGE_ID_ACTIVE_PLAYER . '#ms');
		$this->assertResponseRegExp('#/badges/delete\?badge=' . BADGE_ID_ACTIVE_PLAYER . '#ms');

		// Managers can also view deactivated badges
		$this->assertAccessOk(['controller' => 'Badges', 'action' => 'view', 'badge' => BADGE_ID_MEMBER], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/badges/edit\?badge=' . BADGE_ID_MEMBER . '#ms');
		$this->assertResponseRegExp('#/badges/delete\?badge=' . BADGE_ID_MEMBER . '#ms');

		// Or anything with admin-only access
		$this->assertAccessOk(['controller' => 'Badges', 'action' => 'view', 'badge' => BADGE_ID_RED_FLAG], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/badges/edit\?badge=' . BADGE_ID_RED_FLAG . '#ms');
		$this->assertResponseRegExp('#/badges/delete\?badge=' . BADGE_ID_RED_FLAG . '#ms');

		// Managers have no edit options on ones in other affiliates
		$this->assertAccessOk(['controller' => 'Badges', 'action' => 'view', 'badge' => BADGE_ID_ACTIVE_PLAYER_SUB], PERSON_ID_MANAGER);
		$this->assertResponseNotRegExp('#/badges/edit\?badge=' . BADGE_ID_ACTIVE_PLAYER_SUB . '#ms');
		$this->assertResponseNotRegExp('#/badges/delete\?badge=' . BADGE_ID_ACTIVE_PLAYER_SUB . '#ms');
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
		// Others can only view badges
		$this->assertAccessOk(['controller' => 'Badges', 'action' => 'view', 'badge' => BADGE_ID_ACTIVE_PLAYER], PERSON_ID_PLAYER);
		$this->assertResponseNotRegExp('#/badges/edit\?badge=' . BADGE_ID_ACTIVE_PLAYER . '#ms');
		$this->assertResponseNotRegExp('#/badges/delete\?badge=' . BADGE_ID_ACTIVE_PLAYER . '#ms');

		// But they can't view deactivated badges
		$this->assertAccessRedirect(['controller' => 'Badges', 'action' => 'view', 'badge' => BADGE_ID_MEMBER],
			PERSON_ID_PLAYER, 'get', [], ['controller' => 'Badges', 'action' => 'index'],
			'Invalid badge.', 'Flash.flash.0.message');

		// Or anything with admin-only access
		$this->assertAccessRedirect(['controller' => 'Badges', 'action' => 'view', 'badge' => BADGE_ID_RED_FLAG],
			PERSON_ID_PLAYER, 'get', [], ['controller' => 'Badges', 'action' => 'index'],
			'Invalid badge.', 'Flash.flash.0.message');
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
	 * Test initialize_awards method as an admin
	 *
	 * @return void
	 */
	public function testInitializeAwardsAsAdmin() {
		$badges_table = TableRegistry::get('Badges');
		$badge = $badges_table->get(BADGE_ID_ACTIVE_PLAYER);
		$this->assertEquals(0, $badge->refresh_from);

		// Admins can initialize the awarding of badges
		$this->assertAccessRedirect(['controller' => 'Badges', 'action' => 'initialize_awards', 'badge' => BADGE_ID_ACTIVE_PLAYER],
			PERSON_ID_ADMIN, 'get', [], ['controller' => 'Badges', 'action' => 'view', 'badge' => BADGE_ID_ACTIVE_PLAYER],
			'This badge has been scheduled for re-initialization.', 'Flash.flash.0.message');

		$badge = $badges_table->get(BADGE_ID_ACTIVE_PLAYER);
		$this->assertEquals(1, $badge->refresh_from);

		// Run the badge initialization task
		$task = new InitializeBadgeTask();
		$task->main();
		$this->assertEmpty(Configure::read('test_emails'));

		// At this point, the refresh_from will be set to one past the last team in the database
		$badge = $badges_table->get(BADGE_ID_ACTIVE_PLAYER);
		$this->assertEquals(TEAM_ID_NEW, $badge->refresh_from);

		// Run the task again
		$task->main();
		$this->assertEmpty(Configure::read('test_emails'));

		// Now, the refresh_from will be back to 0
		$badge = $badges_table->get(BADGE_ID_ACTIVE_PLAYER);
		$this->assertEquals(0, $badge->refresh_from);

		$this->assertEquals(6, TableRegistry::get('BadgesPeople')->find()
			->where(['badge_id' => BADGE_ID_ACTIVE_PLAYER])
			->count()
		);
	}

	/**
	 * Test initialize_awards method as a manager
	 *
	 * @return void
	 */
	public function testInitializeAwardsAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test initialize_awards method as a coordinator
	 *
	 * @return void
	 */
	public function testInitializeAwardsAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test initialize_awards method as a captain
	 *
	 * @return void
	 */
	public function testInitializeAwardsAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test initialize_awards method as a player
	 *
	 * @return void
	 */
	public function testInitializeAwardsAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test initialize_awards method as someone else
	 *
	 * @return void
	 */
	public function testInitializeAwardsAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test initialize_awards method without being logged in
	 *
	 * @return void
	 */
	public function testInitializeAwardsAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test tooltip method as an admin
	 *
	 * @return void
	 */
	public function testTooltipAsAdmin() {
		$this->assertAccessOk(['controller' => 'Badges', 'action' => 'tooltip', 'badge' => BADGE_ID_ACTIVE_PLAYER], PERSON_ID_ADMIN, 'getajax');
		$this->assertAccessOk(['controller' => 'Badges', 'action' => 'tooltip', 'badge' => BADGE_ID_RED_FLAG], PERSON_ID_ADMIN, 'getajax');
	}

	/**
	 * Test tooltip method as a manager
	 *
	 * @return void
	 */
	public function testTooltipAsManager() {
		$this->assertAccessOk(['controller' => 'Badges', 'action' => 'tooltip', 'badge' => BADGE_ID_ACTIVE_PLAYER], PERSON_ID_MANAGER, 'getajax');
		$this->assertAccessOk(['controller' => 'Badges', 'action' => 'tooltip', 'badge' => BADGE_ID_RED_FLAG], PERSON_ID_MANAGER, 'getajax');
	}

	/**
	 * Test tooltip method as a coordinator
	 *
	 * @return void
	 */
	public function testTooltipAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test tooltip method as a captain
	 *
	 * @return void
	 */
	public function testTooltipAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test tooltip method as a player
	 *
	 * @return void
	 */
	public function testTooltipAsPlayer() {
		$this->assertAccessOk(['controller' => 'Badges', 'action' => 'tooltip', 'badge' => BADGE_ID_ACTIVE_PLAYER], PERSON_ID_PLAYER, 'getajax');

		$this->assertAccessRedirect(['controller' => 'Badges', 'action' => 'tooltip', 'badge' => BADGE_ID_RED_FLAG],
			PERSON_ID_PLAYER, 'getajax', [], ['controller' => 'Badges', 'action' => 'index'],
			'Invalid badge.');
	}

	/**
	 * Test tooltip method as someone else
	 *
	 * @return void
	 */
	public function testTooltipAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test tooltip method without being logged in
	 *
	 * @return void
	 */
	public function testTooltipAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as an admin
	 *
	 * @return void
	 */
	public function testAddAsAdmin() {
		// Admins are allowed to add badges
		$this->assertAccessOk(['controller' => 'Badges', 'action' => 'add'], PERSON_ID_ADMIN);
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers are allowed to add badges
		$this->assertAccessOk(['controller' => 'Badges', 'action' => 'add'], PERSON_ID_MANAGER);
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
		// Others are not allowed to add badges
		$this->assertAccessRedirect(['controller' => 'Badges', 'action' => 'add'], PERSON_ID_PLAYER);
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
		// Admins are allowed to edit badges
		$this->assertAccessOk(['controller' => 'Badges', 'action' => 'edit', 'badge' => BADGE_ID_ACTIVE_PLAYER], PERSON_ID_ADMIN);
		$this->assertAccessOk(['controller' => 'Badges', 'action' => 'edit', 'badge' => BADGE_ID_ACTIVE_PLAYER_SUB], PERSON_ID_ADMIN);
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers are allowed to edit badges
		$this->assertAccessOk(['controller' => 'Badges', 'action' => 'edit', 'badge' => BADGE_ID_ACTIVE_PLAYER], PERSON_ID_MANAGER);

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Badges', 'action' => 'edit', 'badge' => BADGE_ID_ACTIVE_PLAYER_SUB], PERSON_ID_MANAGER);
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
		// Others are not allowed to edit badges
		$this->assertAccessRedirect(['controller' => 'Badges', 'action' => 'edit', 'badge' => BADGE_ID_ACTIVE_PLAYER], PERSON_ID_PLAYER);
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
	 * Test deactivate method as an admin
	 *
	 * @return void
	 */
	public function testDeactivateAsAdmin() {
		// Admins are allowed to deactivate badges
		$this->assertAccessOk(['controller' => 'Badges', 'action' => 'deactivate', 'badge' => BADGE_ID_CHAMPION], PERSON_ID_ADMIN, 'getajax');
		$this->assertResponseRegExp('#/badges\\\\/activate\?badge=' . BADGE_ID_CHAMPION . '#ms');
	}

	/**
	 * Test deactivate method as a manager
	 *
	 * @return void
	 */
	public function testDeactivateAsManager() {
		// Managers are allowed to deactivate badges
		$this->assertAccessOk(['controller' => 'Badges', 'action' => 'deactivate', 'badge' => BADGE_ID_CHAMPION], PERSON_ID_MANAGER, 'getajax');
		$this->assertResponseRegExp('#/badges\\\\/activate\?badge=' . BADGE_ID_CHAMPION . '#ms');
	}

	/**
	 * Test deactivate method as a coordinator
	 *
	 * @return void
	 */
	public function testDeactivateAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test deactivate method as a captain
	 *
	 * @return void
	 */
	public function testDeactivateAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test deactivate method as a player
	 *
	 * @return void
	 */
	public function testDeactivateAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test deactivate method as someone else
	 *
	 * @return void
	 */
	public function testDeactivateAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test deactivate method without being logged in
	 *
	 * @return void
	 */
	public function testDeactivateAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test activate method as an admin
	 *
	 * @return void
	 */
	public function testActivateAsAdmin() {
		// Admins are allowed to activate badges
		$this->assertAccessOk(['controller' => 'Badges', 'action' => 'activate', 'badge' => BADGE_ID_CHAMPION], PERSON_ID_ADMIN, 'getajax');
		$this->assertResponseRegExp('#/badges\\\\/deactivate\?badge=' . BADGE_ID_CHAMPION . '#ms');
	}

	/**
	 * Test activate method as a manager
	 *
	 * @return void
	 */
	public function testActivateAsManager() {
		// Managers are allowed to activate badges
		$this->assertAccessOk(['controller' => 'Badges', 'action' => 'activate', 'badge' => BADGE_ID_CHAMPION], PERSON_ID_MANAGER, 'getajax');
		$this->assertResponseRegExp('#/badges\\\\/deactivate\?badge=' . BADGE_ID_CHAMPION . '#ms');
	}

	/**
	 * Test activate method as a coordinator
	 *
	 * @return void
	 */
	public function testActivateAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test activate method as a captain
	 *
	 * @return void
	 */
	public function testActivateAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test activate method as a player
	 *
	 * @return void
	 */
	public function testActivateAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test activate method as someone else
	 *
	 * @return void
	 */
	public function testActivateAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test activate method without being logged in
	 *
	 * @return void
	 */
	public function testActivateAsAnonymous() {
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

		// Admins are allowed to delete badges
		$this->assertAccessRedirect(['controller' => 'Badges', 'action' => 'delete', 'badge' => BADGE_ID_MEMBER],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Badges', 'action' => 'index'],
			'The badge has been deleted.', 'Flash.flash.0.message');

		// But not ones with dependencies
		$this->assertAccessRedirect(['controller' => 'Badges', 'action' => 'delete', 'badge' => BADGE_ID_ACTIVE_PLAYER],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Badges', 'action' => 'index'],
			'#The following records reference this badge, so it cannot be deleted#', 'Flash.flash.0.message');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers cannot delete badges
		$this->assertAccessRedirect(['controller' => 'Badges', 'action' => 'delete', 'badge' => BADGE_ID_ACTIVE_PLAYER],
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

}
