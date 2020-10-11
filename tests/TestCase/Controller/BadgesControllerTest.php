<?php
namespace App\Test\TestCase\Controller;

use App\Shell\Task\InitializeBadgeTask;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

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
		'app.EventTypes',
		'app.Affiliates',
			'app.Users',
				'app.People',
					'app.AffiliatesPeople',
					'app.PeoplePeople',
			'app.Groups',
				'app.GroupsPeople',
			'app.Regions',
				'app.Facilities',
					'app.Fields',
			'app.Leagues',
				'app.Divisions',
					'app.Teams',
						'app.TeamsPeople',
					'app.DivisionsPeople',
					'app.Pools',
						'app.PoolsTeams',
					'app.Games',
			'app.Events',
				'app.Prices',
					'app.Registrations',
			'app.Badges',
				'app.BadgesPeople',
			'app.Settings',
		'app.I18n',
		'app.Plugins',
	];

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex() {
		// Admins are allowed to see the index, with full edit controls
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertResponseContains('/badges/view?badge=' . BADGE_ID_ACTIVE_PLAYER);
		$this->assertResponseContains('/badges/edit?badge=' . BADGE_ID_ACTIVE_PLAYER);
		$this->assertResponseContains('/badges/delete?badge=' . BADGE_ID_ACTIVE_PLAYER);
		$this->assertResponseContains('/badges/deactivate?badge=' . BADGE_ID_ACTIVE_PLAYER);
		$this->assertResponseContains('/badges/view?badge=' . BADGE_ID_ACTIVE_PLAYER_SUB);
		$this->assertResponseContains('/badges/edit?badge=' . BADGE_ID_ACTIVE_PLAYER_SUB);
		$this->assertResponseContains('/badges/delete?badge=' . BADGE_ID_ACTIVE_PLAYER_SUB);
		$this->assertResponseContains('/badges/deactivate?badge=' . BADGE_ID_ACTIVE_PLAYER_SUB);

		// Managers are allowed to see the index, but don't have edit options on badges in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertResponseContains('/badges/view?badge=' . BADGE_ID_ACTIVE_PLAYER);
		$this->assertResponseContains('/badges/edit?badge=' . BADGE_ID_ACTIVE_PLAYER);
		$this->assertResponseContains('/badges/delete?badge=' . BADGE_ID_ACTIVE_PLAYER);
		$this->assertResponseContains('/badges/deactivate?badge=' . BADGE_ID_ACTIVE_PLAYER);
		$this->assertResponseNotContains('/badges/view?badge=' . BADGE_ID_ACTIVE_PLAYER_SUB);
		$this->assertResponseNotContains('/badges/edit?badge=' . BADGE_ID_ACTIVE_PLAYER_SUB);
		$this->assertResponseNotContains('/badges/delete?badge=' . BADGE_ID_ACTIVE_PLAYER_SUB);
		$this->assertResponseNotContains('/badges/deactivate?badge=' . BADGE_ID_ACTIVE_PLAYER_SUB);

		// Coordinators are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'index'], PERSON_ID_COORDINATOR);

		// Captains are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'index'], PERSON_ID_CAPTAIN);

		// Others are allowed to see the index, but don't have edit options
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'index'], PERSON_ID_PLAYER);
		$this->assertResponseContains('/badges/view?badge=' . BADGE_ID_ACTIVE_PLAYER);
		$this->assertResponseNotContains('/badges/edit?badge=' . BADGE_ID_ACTIVE_PLAYER);
		$this->assertResponseNotContains('/badges/delete?badge=' . BADGE_ID_ACTIVE_PLAYER);
		$this->assertResponseNotContains('/badges/deactivate?badge=' . BADGE_ID_ACTIVE_PLAYER);

		// Visitors are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'index'], PERSON_ID_VISITOR);

		// Others are not allowed to see the index
		$this->assertGetAnonymousAccessDenied(['controller' => 'Badges', 'action' => 'index']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test deactivated method
	 *
	 * @return void
	 */
	public function testDeactivated() {
		// Admins are allowed to view the list of deactivated badges
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'deactivated'], PERSON_ID_ADMIN);
		$this->assertResponseContains('/badges/edit?badge=' . BADGE_ID_BOARD_OF_DIRECTORS);
		$this->assertResponseContains('/badges/delete?badge=' . BADGE_ID_BOARD_OF_DIRECTORS);
		$this->assertResponseContains('/badges/activate?badge=' . BADGE_ID_BOARD_OF_DIRECTORS);

		// Managers are allowed to view the list of deactivated badges
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'deactivated'], PERSON_ID_MANAGER);
		$this->assertResponseContains('/badges/edit?badge=' . BADGE_ID_BOARD_OF_DIRECTORS);
		$this->assertResponseContains('/badges/delete?badge=' . BADGE_ID_BOARD_OF_DIRECTORS);
		$this->assertResponseContains('/badges/activate?badge=' . BADGE_ID_BOARD_OF_DIRECTORS);

		// Others are not allowed to view the list of deactivated badges
		$this->assertGetAsAccessDenied(['controller' => 'Badges', 'action' => 'deactivated'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Badges', 'action' => 'deactivated'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Badges', 'action' => 'deactivated'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Badges', 'action' => 'deactivated'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Badges', 'action' => 'deactivated']);
	}

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testView() {
		// Admins are allowed to view and edit badges
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'view', 'badge' => BADGE_ID_ACTIVE_PLAYER], PERSON_ID_ADMIN);
		$this->assertResponseContains('/badges/edit?badge=' . BADGE_ID_ACTIVE_PLAYER);
		$this->assertResponseContains('/badges/delete?badge=' . BADGE_ID_ACTIVE_PLAYER);

		// Admins are also allowed to view deactivated badges
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'view', 'badge' => BADGE_ID_MEMBER], PERSON_ID_ADMIN);
		$this->assertResponseContains('/badges/edit?badge=' . BADGE_ID_MEMBER);
		$this->assertResponseContains('/badges/delete?badge=' . BADGE_ID_MEMBER);

		// Or anything with admin-only access
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'view', 'badge' => BADGE_ID_RED_FLAG], PERSON_ID_ADMIN);
		$this->assertResponseContains('/badges/edit?badge=' . BADGE_ID_RED_FLAG);
		$this->assertResponseContains('/badges/delete?badge=' . BADGE_ID_RED_FLAG);

		// And badges from all affiliates
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'view', 'badge' => BADGE_ID_ACTIVE_PLAYER_SUB], PERSON_ID_ADMIN);
		$this->assertResponseContains('/badges/edit?badge=' . BADGE_ID_ACTIVE_PLAYER_SUB);
		$this->assertResponseContains('/badges/delete?badge=' . BADGE_ID_ACTIVE_PLAYER_SUB);

		// Managers are allowed to view and edit badges
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'view', 'badge' => BADGE_ID_ACTIVE_PLAYER], PERSON_ID_MANAGER);
		$this->assertResponseContains('/badges/edit?badge=' . BADGE_ID_ACTIVE_PLAYER);
		$this->assertResponseContains('/badges/delete?badge=' . BADGE_ID_ACTIVE_PLAYER);

		// Managers are also allowed to view deactivated badges
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'view', 'badge' => BADGE_ID_MEMBER], PERSON_ID_MANAGER);
		$this->assertResponseContains('/badges/edit?badge=' . BADGE_ID_MEMBER);
		$this->assertResponseContains('/badges/delete?badge=' . BADGE_ID_MEMBER);

		// Or anything with admin-only access
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'view', 'badge' => BADGE_ID_RED_FLAG], PERSON_ID_MANAGER);
		$this->assertResponseContains('/badges/edit?badge=' . BADGE_ID_RED_FLAG);
		$this->assertResponseContains('/badges/delete?badge=' . BADGE_ID_RED_FLAG);

		// Managers have no edit options on ones in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'view', 'badge' => BADGE_ID_ACTIVE_PLAYER_SUB], PERSON_ID_MANAGER);
		$this->assertResponseNotContains('/badges/edit?badge=' . BADGE_ID_ACTIVE_PLAYER_SUB);
		$this->assertResponseNotContains('/badges/delete?badge=' . BADGE_ID_ACTIVE_PLAYER_SUB);

		// Coordinators are allowed to view badges
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'view', 'badge' => BADGE_ID_ACTIVE_PLAYER], PERSON_ID_COORDINATOR);

		// Captains are allowed to view badges
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'view', 'badge' => BADGE_ID_ACTIVE_PLAYER], PERSON_ID_CAPTAIN);

		// Others are allowed to view badges
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'view', 'badge' => BADGE_ID_ACTIVE_PLAYER], PERSON_ID_PLAYER);
		$this->assertResponseNotContains('/badges/edit?badge=' . BADGE_ID_ACTIVE_PLAYER);
		$this->assertResponseNotContains('/badges/delete?badge=' . BADGE_ID_ACTIVE_PLAYER);

		// But they are not allowed to view deactivated badges
		$this->assertGetAsAccessRedirect(['controller' => 'Badges', 'action' => 'view', 'badge' => BADGE_ID_MEMBER],
			PERSON_ID_PLAYER, ['controller' => 'Badges', 'action' => 'index'],
			'Invalid badge.');

		// Or anything with admin-only access
		$this->assertGetAsAccessRedirect(['controller' => 'Badges', 'action' => 'view', 'badge' => BADGE_ID_RED_FLAG],
			PERSON_ID_PLAYER, ['controller' => 'Badges', 'action' => 'index'],
			'Invalid badge.');

		// Visitors are allowed to view badges
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'view', 'badge' => BADGE_ID_ACTIVE_PLAYER], PERSON_ID_VISITOR);

		// Others are not allowed to view badges
		$this->assertGetAnonymousAccessDenied(['controller' => 'Badges', 'action' => 'view', 'badge' => BADGE_ID_ACTIVE_PLAYER]);

		$this->markTestIncomplete('More scenarios to test above.');
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

		// Admins are allowed to initialize the awarding of badges
		$this->assertGetAsAccessRedirect(['controller' => 'Badges', 'action' => 'initialize_awards', 'badge' => BADGE_ID_ACTIVE_PLAYER],
			PERSON_ID_ADMIN, ['controller' => 'Badges', 'action' => 'view', 'badge' => BADGE_ID_ACTIVE_PLAYER],
			'This badge has been scheduled for re-initialization.');

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

		$this->assertEquals(9, TableRegistry::get('BadgesPeople')->find()
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
		// Managers are allowed to initialize awards
		$this->assertGetAsAccessRedirect(['controller' => 'Badges', 'action' => 'initialize_awards', 'badge' => BADGE_ID_ACTIVE_PLAYER],
			PERSON_ID_MANAGER, ['controller' => 'Badges', 'action' => 'view', 'badge' => BADGE_ID_ACTIVE_PLAYER],
			'This badge has been scheduled for re-initialization.');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test initialize_awards method as others
	 *
	 * @return void
	 */
	public function testInitializeAwardsAsOthers() {
		// Others are not allowed to initialize awards
		$this->assertGetAsAccessDenied(['controller' => 'Badges', 'action' => 'initialize_awards', 'badge' => BADGE_ID_ACTIVE_PLAYER], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Badges', 'action' => 'initialize_awards', 'badge' => BADGE_ID_ACTIVE_PLAYER], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Badges', 'action' => 'initialize_awards', 'badge' => BADGE_ID_ACTIVE_PLAYER], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Badges', 'action' => 'initialize_awards', 'badge' => BADGE_ID_ACTIVE_PLAYER], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Badges', 'action' => 'initialize_awards', 'badge' => BADGE_ID_ACTIVE_PLAYER]);
	}

	/**
	 * Test tooltip method
	 *
	 * @return void
	 */
	public function testTooltip() {
		$this->assertGetAjaxAsAccessOk(['controller' => 'Badges', 'action' => 'tooltip', 'badge' => BADGE_ID_ACTIVE_PLAYER],
			PERSON_ID_ADMIN);
		$this->assertGetAjaxAsAccessOk(['controller' => 'Badges', 'action' => 'tooltip', 'badge' => BADGE_ID_RED_FLAG],
			PERSON_ID_ADMIN);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Badges', 'action' => 'tooltip', 'badge' => BADGE_ID_ACTIVE_PLAYER],
			PERSON_ID_MANAGER);
		$this->assertGetAjaxAsAccessOk(['controller' => 'Badges', 'action' => 'tooltip', 'badge' => BADGE_ID_RED_FLAG],
			PERSON_ID_MANAGER);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Badges', 'action' => 'tooltip', 'badge' => BADGE_ID_ACTIVE_PLAYER],
			PERSON_ID_COORDINATOR);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Badges', 'action' => 'tooltip', 'badge' => BADGE_ID_ACTIVE_PLAYER],
			PERSON_ID_CAPTAIN);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Badges', 'action' => 'tooltip', 'badge' => BADGE_ID_ACTIVE_PLAYER],
			PERSON_ID_PLAYER);
		$this->assertGetAjaxAsAccessRedirect(['controller' => 'Badges', 'action' => 'tooltip', 'badge' => BADGE_ID_RED_FLAG],
			PERSON_ID_PLAYER, ['controller' => 'Badges', 'action' => 'index'],
			'Invalid badge.');

		$this->assertGetAjaxAsAccessOk(['controller' => 'Badges', 'action' => 'tooltip', 'badge' => BADGE_ID_ACTIVE_PLAYER],
			PERSON_ID_VISITOR);

		$this->assertGetAnonymousAccessDenied(['controller' => 'Badges', 'action' => 'tooltip', 'badge' => BADGE_ID_ACTIVE_PLAYER]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add method as an admin
	 *
	 * @return void
	 */
	public function testAddAsAdmin() {
		// Admins are allowed to add badges
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'add'], PERSON_ID_ADMIN);
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers are allowed to add badges
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'add'], PERSON_ID_MANAGER);
	}

	/**
	 * Test add method as others
	 *
	 * @return void
	 */
	public function testAddAsOthers() {
		// Others are not allowed to add badges
		$this->assertGetAsAccessDenied(['controller' => 'Badges', 'action' => 'add'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Badges', 'action' => 'add'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Badges', 'action' => 'add'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Badges', 'action' => 'add'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Badges', 'action' => 'add']);
	}

	/**
	 * Test edit method as an admin
	 *
	 * @return void
	 */
	public function testEditAsAdmin() {
		// Admins are allowed to edit badges
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'edit', 'badge' => BADGE_ID_ACTIVE_PLAYER], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'edit', 'badge' => BADGE_ID_ACTIVE_PLAYER_SUB], PERSON_ID_ADMIN);
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers are allowed to edit badges
		$this->assertGetAsAccessOk(['controller' => 'Badges', 'action' => 'edit', 'badge' => BADGE_ID_ACTIVE_PLAYER], PERSON_ID_MANAGER);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Badges', 'action' => 'edit', 'badge' => BADGE_ID_ACTIVE_PLAYER_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test edit method as others
	 *
	 * @return void
	 */
	public function testEditAsOthers() {
		// Others are not allowed to edit badges
		$this->assertGetAsAccessDenied(['controller' => 'Badges', 'action' => 'edit', 'badge' => BADGE_ID_ACTIVE_PLAYER], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Badges', 'action' => 'edit', 'badge' => BADGE_ID_ACTIVE_PLAYER], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Badges', 'action' => 'edit', 'badge' => BADGE_ID_ACTIVE_PLAYER], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Badges', 'action' => 'edit', 'badge' => BADGE_ID_ACTIVE_PLAYER], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Badges', 'action' => 'edit', 'badge' => BADGE_ID_ACTIVE_PLAYER]);
	}

	/**
	 * Test deactivate method as an admin
	 *
	 * @return void
	 */
	public function testDeactivateAsAdmin() {
		// Admins are allowed to deactivate badges
		$this->assertGetAjaxAsAccessOk(['controller' => 'Badges', 'action' => 'deactivate', 'badge' => BADGE_ID_CHAMPION],
			PERSON_ID_ADMIN);
		$this->assertResponseContains('/badges\\/activate?badge=' . BADGE_ID_CHAMPION);
	}

	/**
	 * Test deactivate method as a manager
	 *
	 * @return void
	 */
	public function testDeactivateAsManager() {
		// Managers are allowed to deactivate badges
		$this->assertGetAjaxAsAccessOk(['controller' => 'Badges', 'action' => 'deactivate', 'badge' => BADGE_ID_CHAMPION],
			PERSON_ID_MANAGER);
		$this->assertResponseContains('/badges\\/activate?badge=' . BADGE_ID_CHAMPION);
	}

	/**
	 * Test deactivate method as others
	 *
	 * @return void
	 */
	public function testDeactivateAsOthers() {
		// Others are not allowed to deactivate badges
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Badges', 'action' => 'deactivate', 'badge' => BADGE_ID_CHAMPION],
			PERSON_ID_COORDINATOR);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Badges', 'action' => 'deactivate', 'badge' => BADGE_ID_CHAMPION],
			PERSON_ID_CAPTAIN);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Badges', 'action' => 'deactivate', 'badge' => BADGE_ID_CHAMPION],
			PERSON_ID_PLAYER);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Badges', 'action' => 'deactivate', 'badge' => BADGE_ID_CHAMPION],
			PERSON_ID_VISITOR);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Badges', 'action' => 'deactivate', 'badge' => BADGE_ID_CHAMPION]);
	}

	/**
	 * Test activate method as an admin
	 *
	 * @return void
	 */
	public function testActivateAsAdmin() {
		// Admins are allowed to activate badges
		$this->assertGetAjaxAsAccessOk(['controller' => 'Badges', 'action' => 'activate', 'badge' => BADGE_ID_CHAMPION],
			PERSON_ID_ADMIN);
		$this->assertResponseContains('/badges\\/deactivate?badge=' . BADGE_ID_CHAMPION);
	}

	/**
	 * Test activate method as a manager
	 *
	 * @return void
	 */
	public function testActivateAsManager() {
		// Managers are allowed to activate badges
		$this->assertGetAjaxAsAccessOk(['controller' => 'Badges', 'action' => 'activate', 'badge' => BADGE_ID_CHAMPION],
			PERSON_ID_MANAGER);
		$this->assertResponseContains('/badges\\/deactivate?badge=' . BADGE_ID_CHAMPION);
	}

	/**
	 * Test activate method as others
	 *
	 * @return void
	 */
	public function testActivateAsOthers() {
		// Others are not allowed to activate badges
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Badges', 'action' => 'activate', 'badge' => BADGE_ID_CHAMPION],
			PERSON_ID_COORDINATOR);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Badges', 'action' => 'activate', 'badge' => BADGE_ID_CHAMPION],
			PERSON_ID_CAPTAIN);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Badges', 'action' => 'activate', 'badge' => BADGE_ID_CHAMPION],
			PERSON_ID_PLAYER);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Badges', 'action' => 'activate', 'badge' => BADGE_ID_CHAMPION],
			PERSON_ID_VISITOR);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Badges', 'action' => 'activate', 'badge' => BADGE_ID_CHAMPION]);
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
		$this->assertPostAsAccessRedirect(['controller' => 'Badges', 'action' => 'delete', 'badge' => BADGE_ID_MEMBER],
			PERSON_ID_ADMIN, [], ['controller' => 'Badges', 'action' => 'index'],
			'The badge has been deleted.');

		// But not ones with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'Badges', 'action' => 'delete', 'badge' => BADGE_ID_ACTIVE_PLAYER],
			PERSON_ID_ADMIN, [], ['controller' => 'Badges', 'action' => 'index'],
			'#The following records reference this badge, so it cannot be deleted#');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to delete badges
		$this->assertPostAsAccessRedirect(['controller' => 'Badges', 'action' => 'delete', 'badge' => BADGE_ID_MEMBER],
			PERSON_ID_MANAGER, [], ['controller' => 'Badges', 'action' => 'index'],
			'The badge has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Badges', 'action' => 'delete', 'badge' => BADGE_ID_ACTIVE_PLAYER_SUB],
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

		// Others are not allowed to delete badges
		$this->assertPostAsAccessDenied(['controller' => 'Badges', 'action' => 'delete', 'badge' => BADGE_ID_ACTIVE_PLAYER],
			PERSON_ID_COORDINATOR);
		$this->assertPostAsAccessDenied(['controller' => 'Badges', 'action' => 'delete', 'badge' => BADGE_ID_ACTIVE_PLAYER],
			PERSON_ID_CAPTAIN);
		$this->assertPostAsAccessDenied(['controller' => 'Badges', 'action' => 'delete', 'badge' => BADGE_ID_ACTIVE_PLAYER],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'Badges', 'action' => 'delete', 'badge' => BADGE_ID_ACTIVE_PLAYER],
			PERSON_ID_VISITOR);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Badges', 'action' => 'delete', 'badge' => BADGE_ID_ACTIVE_PLAYER]);
	}

}
