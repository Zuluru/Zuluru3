<?php
namespace App\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\ORM\TableRegistry;

/**
 * App\Controller\PeopleController Test Case
 */
class PeopleControllerTest extends ControllerTestCase {

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
					'app.skills',
					'app.credits',
			'app.groups',
				'app.groups_people',
			'app.upload_types',
				'app.uploads',
			'app.regions',
				'app.facilities',
					'app.fields',
			'app.leagues',
				'app.divisions',
					'app.teams',
						'app.teams_people',
					'app.divisions_days',
					'app.divisions_people',
					'app.game_slots',
					'app.pools',
						'app.pools_teams',
					'app.games',
						'app.games_allstars',
			'app.franchises',
				'app.franchises_people',
				'app.franchises_teams',
			'app.events',
				'app.prices',
					'app.registrations',
						'app.payments',
				'app.preregistrations',
			'app.categories',
				'app.tasks',
					'app.task_slots',
			'app.badges',
				'app.badges_people',
			'app.notes',
			'app.settings',
			'app.waivers',
				'app.waivers_people',
	];

	/**
	 * Test index method as an admin
	 *
	 * @return void
	 */
	public function testIndexAsAdmin() {
		// Admins are allowed to get the index
		$this->assertAccessOk(['controller' => 'People', 'action' => 'index'], PERSON_ID_ADMIN);
	}

	/**
	 * Test index method as a manager
	 *
	 * @return void
	 */
	public function testIndexAsManager() {
		// Managers are allowed to get the index, but don't see people in other affiliates
		$this->assertAccessOk(['controller' => 'People', 'action' => 'index'], PERSON_ID_MANAGER);
	}

	/**
	 * Test index method as a coordinator
	 *
	 * @return void
	 */
	public function testIndexAsCoordinator() {
		// Anyone else is not allowed to get the index
		$this->assertAccessRedirect(['controller' => 'People', 'action' => 'index'], PERSON_ID_COORDINATOR);
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
	 * Test statistics method as an admin
	 *
	 * @return void
	 */
	public function testStatisticsAsAdmin() {
		// Admins are allowed to view the statistics page
		$this->assertAccessOk(['controller' => 'People', 'action' => 'statistics'], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#<h4 class="affiliate">Club</h4>.*<td>Ultimate</td>[\s]*<td>Woman</td>[\s]*<td>4</td>#ms');
	}

	/**
	 * Test statistics method as a manager
	 *
	 * @return void
	 */
	public function testStatisticsAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test statistics method as a coordinator
	 *
	 * @return void
	 */
	public function testStatisticsAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test statistics method as a captain
	 *
	 * @return void
	 */
	public function testStatisticsAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test statistics method as a player
	 *
	 * @return void
	 */
	public function testStatisticsAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test statistics method as someone else
	 *
	 * @return void
	 */
	public function testStatisticsAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test statistics method without being logged in
	 *
	 * @return void
	 */
	public function testStatisticsAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test participation method as an admin
	 *
	 * @return void
	 */
	public function testParticipationAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test participation method as a manager
	 *
	 * @return void
	 */
	public function testParticipationAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test participation method as a coordinator
	 *
	 * @return void
	 */
	public function testParticipationAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test participation method as a captain
	 *
	 * @return void
	 */
	public function testParticipationAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test participation method as a player
	 *
	 * @return void
	 */
	public function testParticipationAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test participation method as someone else
	 *
	 * @return void
	 */
	public function testParticipationAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test participation method without being logged in
	 *
	 * @return void
	 */
	public function testParticipationAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test retention method as an admin
	 *
	 * @return void
	 */
	public function testRetentionAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test retention method as a manager
	 *
	 * @return void
	 */
	public function testRetentionAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test retention method as a coordinator
	 *
	 * @return void
	 */
	public function testRetentionAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test retention method as a captain
	 *
	 * @return void
	 */
	public function testRetentionAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test retention method as a player
	 *
	 * @return void
	 */
	public function testRetentionAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test retention method as someone else
	 *
	 * @return void
	 */
	public function testRetentionAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test retention method without being logged in
	 *
	 * @return void
	 */
	public function testRetentionAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test view method as an admin
	 *
	 * @return void
	 */
	public function testViewAsAdmin() {
		// Admins are allowed to see all data
		$this->assertAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_PLAYER], PERSON_ID_ADMIN);
		$this->assertResponseContains('Phone (home)');
		$this->assertResponseContains('Email Address');
		$this->assertResponseContains('Birthdate');

		// Admins can see and manipulate relatives
		$this->assertAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_CAPTAIN], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/people/remove_relative\?person=' . PERSON_ID_CAPTAIN . '&amp;relative=' . PERSON_ID_CAPTAIN2 . '#ms');
		$this->assertResponseRegExp('#<td>Crystal can control <a[^>]*>Chuck Captain</a></td>\s*<td>Yes</td>#ms');
		$this->assertResponseRegExp('#/people/remove_relative\?person=' . PERSON_ID_CAPTAIN2 . '&amp;relative=' . PERSON_ID_CAPTAIN . '#ms');
		$this->assertResponseRegExp('#<td><a[^>]*>Chuck Captain</a> can control Crystal</td>\s*<td>No</td>#ms');
	}

	/**
	 * Test view method as a manager
	 *
	 * @return void
	 */
	public function testViewAsManager() {
		// Managers are allowed to see all data for people in their affiliate
		$this->assertAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_PLAYER], PERSON_ID_MANAGER);
		$this->assertResponseContains('Phone (home)');
		$this->assertResponseContains('Email Address');
		$this->assertResponseContains('Birthdate');

		// But only regular data for people in their own
		$this->assertAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_ANDY_SUB], PERSON_ID_MANAGER);
		$this->assertResponseNotContains('Phone (home)');
		$this->assertResponseNotContains('Email Address');
		$this->assertResponseNotContains('Birthdate');

		// Managers can see and manipulate relatives
		$this->assertAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_CAPTAIN], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/people/remove_relative\?person=' . PERSON_ID_CAPTAIN . '&amp;relative=' . PERSON_ID_CAPTAIN2 . '#ms');
		$this->assertResponseRegExp('#<td>Crystal can control <a[^>]*>Chuck Captain</a></td>\s*<td>Yes</td>#ms');
		$this->assertResponseRegExp('#/people/remove_relative\?person=' . PERSON_ID_CAPTAIN2 . '&amp;relative=' . PERSON_ID_CAPTAIN . '#ms');
		$this->assertResponseRegExp('#<td><a[^>]*>Chuck Captain</a> can control Crystal</td>\s*<td>No</td>#ms');
	}

	/**
	 * Test view method as a coordinator
	 *
	 * @return void
	 */
	public function testViewAsCoordinator() {
		// Coordinators can see contact info for their captains
		$this->assertAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_CAPTAIN], PERSON_ID_COORDINATOR);
		$this->assertResponseContains('Phone (home)');
		$this->assertResponseContains('Email Address');
		$this->assertResponseNotContains('Birthdate');

		// ...but not regular players
		$this->assertAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_PLAYER], PERSON_ID_COORDINATOR);
		$this->assertResponseNotContains('Phone (home)');
		$this->assertResponseNotContains('Email Address');
		$this->assertResponseNotContains('Birthdate');
	}

	/**
	 * Test view method as a captain
	 *
	 * @return void
	 */
	public function testViewAsCaptain() {
		// Captains can see contact info for their players
		$this->assertAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_PLAYER], PERSON_ID_CAPTAIN);
		$this->assertResponseContains('Phone (home)');
		$this->assertResponseContains('Email Address');
		$this->assertResponseNotContains('Birthdate');

		// ...and their coordinator
		$this->assertAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_COORDINATOR], PERSON_ID_CAPTAIN);
		$this->assertResponseContains('Phone (home)');
		$this->assertResponseContains('Email Address');

		// ...but not others
		$this->assertAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_MANAGER], PERSON_ID_CAPTAIN);
		$this->assertResponseNotContains('Phone (home)');
		$this->assertResponseContains('Email Address'); // Can see this because it's public
	}

	/**
	 * Test view method as a player
	 *
	 * @return void
	 */
	public function testViewAsPlayer() {
		// Players can see contact info for their captains
		$this->assertAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_CAPTAIN], PERSON_ID_PLAYER);
		$this->assertResponseContains('Phone (home)');
		$this->assertResponseContains('Email Address');
		$this->assertResponseNotContains('Birthdate');

		// ...but not others
		$this->assertAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_COORDINATOR], PERSON_ID_PLAYER);
		$this->assertResponseContains('Phone (home)'); // Can see this because it's public
		$this->assertResponseNotContains('Email Address');
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
	 * Test tooltip method as an admin
	 *
	 * @return void
	 */
	public function testTooltipAsAdmin() {
		// Admins are allowed to view person tooltips, and have all information and options
		$this->assertAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => PERSON_ID_PLAYER], PERSON_ID_ADMIN, 'getajax');
		$this->assertResponseRegExp('#mailto:pam@zuluru.org#ms');
		$this->assertResponseRegExp('#\(416\) 678-9012 \(home\)#ms');
		$this->assertResponseRegExp('#\(416\) 789-0123 x456 \(work\)#ms');
		$this->assertResponseRegExp('#/people\\\\/vcf\?person=' . PERSON_ID_PLAYER . '#ms');
		$this->assertResponseRegExp('#/people\\\\/note\?person=' . PERSON_ID_PLAYER . '#ms');
		$this->assertResponseRegExp('#/people\\\\/act_as\?person=' . PERSON_ID_PLAYER . '#ms');

		$this->assertAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => PERSON_ID_ANDY_SUB], PERSON_ID_ADMIN, 'getajax');
		$this->assertResponseRegExp('#mailto:andy@zuluru.org#ms');
		$this->assertResponseRegExp('#\(647\) 555-5555 \(home\)#ms');
		$this->assertResponseRegExp('#\(647\) 555-5556 \(mobile\)#ms');
		$this->assertResponseRegExp('#/people\\\\/vcf\?person=' . PERSON_ID_ANDY_SUB . '#ms');
		$this->assertResponseRegExp('#/people\\\\/note\?person=' . PERSON_ID_ANDY_SUB . '#ms');
		$this->assertResponseRegExp('#/people\\\\/act_as\?person=' . PERSON_ID_ANDY_SUB . '#ms');

		$this->assertAccessRedirect(['controller' => 'People', 'action' => 'tooltip', 'person' => 10000],
			PERSON_ID_ADMIN, 'getajax', [], null,
			'Invalid person.');
	}

	/**
	 * Test tooltip method as a manager
	 *
	 * @return void
	 */
	public function testTooltipAsManager() {
		// Managers are allowed to view person tooltips, and have all information and options
		$this->assertAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => PERSON_ID_PLAYER], PERSON_ID_MANAGER, 'getajax');
		$this->assertResponseRegExp('#mailto:pam@zuluru.org#ms');
		$this->assertResponseRegExp('#\(416\) 678-9012 \(home\)#ms');
		$this->assertResponseRegExp('#\(416\) 789-0123 x456 \(work\)#ms');
		$this->assertResponseRegExp('#/people\\\\/vcf\?person=' . PERSON_ID_PLAYER . '#ms');
		$this->assertResponseRegExp('#/people\\\\/note\?person=' . PERSON_ID_PLAYER . '#ms');
		$this->assertResponseRegExp('#/people\\\\/act_as\?person=' . PERSON_ID_PLAYER . '#ms');

		// But are restricted when viewing tooltip of people not in their affiliate
		$this->assertAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => PERSON_ID_ANDY_SUB], PERSON_ID_MANAGER, 'getajax');
		$this->assertResponseNotRegExp('#mailto#ms');
		$this->assertResponseNotRegExp('#\(home\)#ms');
		$this->assertResponseNotRegExp('#\(work\)#ms');
		$this->assertResponseNotRegExp('#\(mobile\)#ms');
		$this->assertResponseNotRegExp('#/people\\\\/vcf\?person=' . PERSON_ID_ANDY_SUB . '#ms');
		$this->assertResponseRegExp('#/people\\\\/note\?person=' . PERSON_ID_ANDY_SUB . '#ms');
		$this->assertResponseNotRegExp('#/people\\\\/act_as\?person=' . PERSON_ID_ANDY_SUB . '#ms');
	}

	/**
	 * Test tooltip method as a coordinator
	 *
	 * @return void
	 */
	public function testTooltipAsCoordinator() {
		// Coordinator gets to see contact info for their captains
		$this->assertAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => PERSON_ID_CAPTAIN], PERSON_ID_COORDINATOR, 'getajax');
		$this->assertResponseRegExp('#mailto:crystal@zuluru.org#ms');
		$this->assertResponseRegExp('#\(416\) 567-8910 \(home\)#ms');
		$this->assertResponseRegExp('#/people\\\\/vcf\?person=' . PERSON_ID_CAPTAIN . '#ms');
		$this->assertResponseRegExp('#/people\\\\/note\?person=' . PERSON_ID_CAPTAIN . '#ms');
		$this->assertResponseNotRegExp('#/people\\\\/act_as\?person=' . PERSON_ID_CAPTAIN . '#ms');
	}

	/**
	 * Test tooltip method as a captain
	 *
	 * @return void
	 */
	public function testTooltipAsCaptain() {
		// Captain gets to see contact info for their players
		$this->assertAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => PERSON_ID_PLAYER], PERSON_ID_CAPTAIN, 'getajax');
		$this->assertResponseRegExp('#mailto:pam@zuluru.org#ms');
		$this->assertResponseRegExp('#\(416\) 678-9012 \(home\)#ms');
		$this->assertResponseRegExp('#\(416\) 789-0123 x456 \(work\)#ms');
		$this->assertResponseRegExp('#/people\\\\/vcf\?person=' . PERSON_ID_PLAYER . '#ms');
		$this->assertResponseRegExp('#/people\\\\/note\?person=' . PERSON_ID_PLAYER . '#ms');
		$this->assertResponseNotRegExp('#/people\\\\/act_as\?person=' . PERSON_ID_PLAYER . '#ms');

		// And for their coordinator
		$this->assertAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => PERSON_ID_COORDINATOR], PERSON_ID_CAPTAIN, 'getajax');
		$this->assertResponseRegExp('#mailto:cindy@zuluru.org#ms');
		$this->assertResponseRegExp('#\(416\) 456-7890 \(home\)#ms');
		$this->assertResponseRegExp('#/people\\\\/vcf\?person=' . PERSON_ID_COORDINATOR . '#ms');
		$this->assertResponseRegExp('#/people\\\\/note\?person=' . PERSON_ID_COORDINATOR . '#ms');
		$this->assertResponseNotRegExp('#/people\\\\/act_as\?person=' . PERSON_ID_COORDINATOR . '#ms');
	}

	/**
	 * Test tooltip method as a player
	 *
	 * @return void
	 */
	public function testTooltipAsPlayer() {
		// Player gets to see contact info for their own captain
		$this->assertAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => PERSON_ID_CAPTAIN], PERSON_ID_PLAYER, 'getajax');
		$this->assertResponseRegExp('#mailto:crystal@zuluru.org#ms');
		$this->assertResponseRegExp('#\(416\) 567-8910 \(home\)#ms');
		$this->assertResponseRegExp('#/people\\\\/vcf\?person=' . PERSON_ID_CAPTAIN . '#ms');
		$this->assertResponseRegExp('#/people\\\\/note\?person=' . PERSON_ID_CAPTAIN . '#ms');
		$this->assertResponseNotRegExp('#/people\\\\/act_as\?person=' . PERSON_ID_CAPTAIN . '#ms');

		// And can act as their relatives
		$this->assertAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => PERSON_ID_CHILD], PERSON_ID_PLAYER, 'getajax');
		$this->assertResponseRegExp('#/people\\\\/note\?person=' . PERSON_ID_CHILD . '#ms');
		$this->assertResponseRegExp('#/people\\\\/act_as\?person=' . PERSON_ID_CHILD . '#ms');

		// But sees less about other people
		$this->assertAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => PERSON_ID_CAPTAIN2], PERSON_ID_PLAYER, 'getajax');
		$this->assertResponseNotRegExp('#mailto#ms');
		$this->assertResponseNotRegExp('#\(home\)#ms');
		$this->assertResponseNotRegExp('#\(work\)#ms');
		$this->assertResponseNotRegExp('#\(mobile\)#ms');
		$this->assertResponseNotRegExp('#/people\\\\/vcf\?person=' . PERSON_ID_CAPTAIN2 . '#ms');
		$this->assertResponseRegExp('#/people\\\\/note\?person=' . PERSON_ID_CAPTAIN2 . '#ms');
		$this->assertResponseNotRegExp('#/people\\\\/act_as\?person=' . PERSON_ID_CAPTAIN2 . '#ms');
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
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		$this->assertAccessRedirect(['controller' => 'People', 'action' => 'edit'],
			PERSON_ID_PLAYER, 'post', ['shirt_size' => 'Mens Large'], null, 'Your profile has been saved.', 'Flash.flash.0.message');
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
	 * Test deactivate method as an admin
	 *
	 * @return void
	 */
	public function testDeactivateAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test deactivate method as a manager
	 *
	 * @return void
	 */
	public function testDeactivateAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
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
	 * Test reactivate method as an admin
	 *
	 * @return void
	 */
	public function testReactivateAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test reactivate method as a manager
	 *
	 * @return void
	 */
	public function testReactivateAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test reactivate method as a coordinator
	 *
	 * @return void
	 */
	public function testReactivateAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test reactivate method as a captain
	 *
	 * @return void
	 */
	public function testReactivateAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test reactivate method as a player
	 *
	 * @return void
	 */
	public function testReactivateAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test reactivate method as someone else
	 *
	 * @return void
	 */
	public function testReactivateAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test reactivate method without being logged in
	 *
	 * @return void
	 */
	public function testReactivateAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test confirm method as an admin
	 *
	 * @return void
	 */
	public function testConfirmAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test confirm method as a manager
	 *
	 * @return void
	 */
	public function testConfirmAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test confirm method as a coordinator
	 *
	 * @return void
	 */
	public function testConfirmAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test confirm method as a captain
	 *
	 * @return void
	 */
	public function testConfirmAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test confirm method as a player
	 *
	 * @return void
	 */
	public function testConfirmAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test confirm method as someone else
	 *
	 * @return void
	 */
	public function testConfirmAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test confirm method without being logged in
	 *
	 * @return void
	 */
	public function testConfirmAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test note method as an admin
	 *
	 * @return void
	 */
	public function testNoteAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test note method as a manager
	 *
	 * @return void
	 */
	public function testNoteAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test note method as a coordinator
	 *
	 * @return void
	 */
	public function testNoteAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test note method as a captain
	 *
	 * @return void
	 */
	public function testNoteAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test note method as a player
	 *
	 * @return void
	 */
	public function testNoteAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test note method as someone else
	 *
	 * @return void
	 */
	public function testNoteAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test note method without being logged in
	 *
	 * @return void
	 */
	public function testNoteAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_note method as an admin
	 *
	 * @return void
	 */
	public function testDeleteNoteAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_note method as a manager
	 *
	 * @return void
	 */
	public function testDeleteNoteAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_note method as a coordinator
	 *
	 * @return void
	 */
	public function testDeleteNoteAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_note method as a captain
	 *
	 * @return void
	 */
	public function testDeleteNoteAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_note method as a player
	 *
	 * @return void
	 */
	public function testDeleteNoteAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_note method as someone else
	 *
	 * @return void
	 */
	public function testDeleteNoteAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_note method without being logged in
	 *
	 * @return void
	 */
	public function testDeleteNoteAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test preferences method as an admin
	 *
	 * @return void
	 */
	public function testPreferencesAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test preferences method as a manager
	 *
	 * @return void
	 */
	public function testPreferencesAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test preferences method as a coordinator
	 *
	 * @return void
	 */
	public function testPreferencesAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test preferences method as a captain
	 *
	 * @return void
	 */
	public function testPreferencesAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test preferences method as a player
	 *
	 * @return void
	 */
	public function testPreferencesAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test preferences method as someone else
	 *
	 * @return void
	 */
	public function testPreferencesAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test preferences method without being logged in
	 *
	 * @return void
	 */
	public function testPreferencesAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_relative method
	 *
	 * @return void
	 */
	public function testAddRelative() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		$this->assertAccessRedirect(['controller' => 'People', 'action' => 'add_relative'],
			PERSON_ID_PLAYER, 'post', [
				'groups' => ['_ids' => [GROUP_ID_PLAYER]],
				'affiliates' => [['id' => AFFILIATE_ID_CLUB]],
				'first_name' => 'Young',
				'last_name' => 'Test',
				'gender' => 'Woman',
				'gender_description' => null,
				'roster_designation' => 'Woman',
				'birthdate' => ['year' => FrozenDate::now()->year - 10, 'month' => '01', 'day' => '01'],
				'height' => 50,
				'shirt_size' => 'Youth Large',
				'skills' => [
					[
						'enabled' => false,
						'sport' => 'baseball',
					],
					[
						'enabled' => true,
						'sport' => 'ultimate',
						'year_started' => [
							'year' => FrozenDate::now()->year - 1
						],
						'skill_level' => 3,
					],
				],
				'action' => 'create',
			],
			'/', 'The new profile has been saved. It must be approved by an administrator before you will have full access to the site.', 'Flash.flash.0.message'
		);

		$child = TableRegistry::get('People')->get(PERSON_ID_NEW, ['contain' => [
			'Affiliates',
			'Groups',
			'Skills',
		]]);
		$this->assertEquals(PERSON_ID_NEW, $child->id);
		$this->assertEquals('Young', $child->first_name);
		$this->assertEquals('new', $child->status);
		$this->assertEquals(true, $child->complete);
		$this->assertEquals(FrozenDate::now(), $child->modified);
		$this->assertEquals(1, count($child->affiliates));
		$this->assertEquals(AFFILIATE_ID_CLUB, $child->affiliates[0]->id);
		$this->assertEquals(1, count($child->groups));
		$this->assertEquals(GROUP_ID_PLAYER, $child->groups[0]->id);
		$this->assertEquals(2, count($child->skills));
		$this->assertEquals('baseball', $child->skills[0]->sport);
		$this->assertFalse($child->skills[0]->enabled);
		$this->assertEquals('ultimate', $child->skills[1]->sport);
		$this->assertTrue($child->skills[1]->enabled);
		$this->assertEquals(FrozenDate::now()->year - 1, $child->skills[1]->year_started);
		$this->assertEquals(3, $child->skills[1]->skill_level);
	}

	/**
	 * Test link_relative method
	 *
	 * @return void
	 */
	public function testLinkRelative() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Anyone is allowed to link relatives
		$this->assertAccessOk(['controller' => 'People', 'action' => 'link_relative'], PERSON_ID_PLAYER);

		// Try the search page
		$this->assertAccessOk(['controller' => 'People', 'action' => 'link_relative'],
			PERSON_ID_PLAYER, 'post', [
			'affiliate_id' => '1',
			'first_name' => '',
			'last_name' => 'captain',
			'sort' => 'last_name',
			'direction' => 'asc',
		]);
		$this->assertResponseRegExp('#/people/link_relative\?relative=' . PERSON_ID_CAPTAIN . '&amp;person=' . PERSON_ID_PLAYER . '#ms');
		$this->assertResponseRegExp('#/people/link_relative\?relative=' . PERSON_ID_CAPTAIN2 . '&amp;person=' . PERSON_ID_PLAYER . '#ms');

		$this->assertAccessRedirect(['controller' => 'People', 'action' => 'link_relative', 'relative' => PERSON_ID_CAPTAIN, 'person' => PERSON_ID_PLAYER],
			PERSON_ID_PLAYER, 'get', [], null,
			'Linked Crystal Captain as relative; you will not have access to their information until they have approved this.', 'Flash.flash.0.message');

		// Confirm the notification email
		$messages = Configure::read('test_emails');
		$this->assertEquals(1, count($messages));

		$this->assertRegExp('#From: &quot;Admin&quot; &lt;admin@zuluru.org&gt;#ms', $messages[0]);
		$this->assertRegExp('#Reply-To: &quot;Pam Player&quot; &lt;pam@zuluru.org&gt;#ms', $messages[0]);
		$this->assertRegExp('#To: &quot;Crystal Captain&quot; &lt;crystal@zuluru.org&gt;#ms', $messages[0]);
		$this->assertNotRegExp('#CC: #ms', $messages[0]);
		$this->assertRegExp('#Subject: You have been linked as a relative#ms', $messages[0]);
		$this->assertRegExp('#Pam Player has indicated on the Test Zuluru Affiliate web site that you are related to them.#ms', $messages[0]);
		$this->assertRegExp('#If you accept, Pam will be granted access#ms', $messages[0]);
		$this->assertRegExp('#Accept the request here:\s*' . Configure::read('App.fullBaseUrl') . '/people/approve_relative\?person=' . PERSON_ID_CAPTAIN . '&relative=' . PERSON_ID_PLAYER . '#ms', $messages[0]);
		$this->assertRegExp('#Decline the request here:\s*' . Configure::read('App.fullBaseUrl') . '/people/remove_relative\?person=' . PERSON_ID_CAPTAIN . '&relative=' . PERSON_ID_PLAYER . '#ms', $messages[0]);

		// Make sure they were added successfully
		$this->assertAccessOk(['controller' => 'People', 'action' => 'view'], PERSON_ID_PLAYER);
		$this->assertResponseRegExp('#/people/remove_relative\?person=' . PERSON_ID_PLAYER . '&amp;relative=' . PERSON_ID_CAPTAIN . '#ms');
	}

	/**
	 * Test link_relative method without being logged in
	 *
	 * @return void
	 */
	public function testLinkRelativeAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_relative method
	 *
	 * @return void
	 */
	public function testApproveRelative() {
		// The invited relative is allowed to approve the request
		$this->assertAccessRedirect(['controller' => 'People', 'action' => 'approve_relative', 'person' => PERSON_ID_CAPTAIN, 'relative' => PERSON_ID_CAPTAIN2],
			PERSON_ID_CAPTAIN, 'get', [], ['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_CAPTAIN],
			'Approved the relative request.', 'Flash.flash.0.message');

		// Confirm the notification email
		$messages = Configure::read('test_emails');
		$this->assertEquals(1, count($messages));

		$this->assertRegExp('#From: &quot;Admin&quot; &lt;admin@zuluru.org&gt;#ms', $messages[0]);
		$this->assertRegExp('#Reply-To: &quot;Crystal Captain&quot; &lt;crystal@zuluru.org&gt;#ms', $messages[0]);
		$this->assertRegExp('#To: &quot;Chuck Captain&quot; &lt;chuck@zuluru.org&gt;#ms', $messages[0]);
		$this->assertNotRegExp('#CC: #ms', $messages[0]);
		$this->assertRegExp('#Subject: Crystal Captain approved your relative request#ms', $messages[0]);
		$this->assertRegExp('#Your relative request to Crystal Captain on the Test Zuluru Affiliate web site has been approved.#ms', $messages[0]);

		// Make sure they were added successfully
		$this->assertAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_CAPTAIN], PERSON_ID_CAPTAIN);
		$this->assertResponseRegExp('#<td>You can control <a[^>]*>Chuck Captain</a></td>\s*<td>Yes</td>#ms');
		$this->assertResponseRegExp('#<td><a[^>]*>Chuck Captain</a> can control you</td>\s*<td>Yes</td>#ms');

		// TODO: Test with codes
	}

	/**
	 * Test remove_relative method as the person
	 *
	 * @return void
	 */
	public function testRemoveRelativeAsPerson() {
		// A person is allowed to remove their relations
		$this->assertAccessRedirect(['controller' => 'People', 'action' => 'remove_relative', 'person' => PERSON_ID_CAPTAIN, 'relative' => PERSON_ID_CAPTAIN2],
			PERSON_ID_CAPTAIN, 'get', [], ['controller' => 'People', 'action' => 'view'],
			'Removed the relation.', 'Flash.flash.0.message');

		// Confirm the notification email
		$messages = Configure::read('test_emails');
		$this->assertEquals(1, count($messages));

		$this->assertRegExp('#From: &quot;Admin&quot; &lt;admin@zuluru.org&gt;#ms', $messages[0]);
		$this->assertRegExp('#Reply-To: &quot;Crystal Captain&quot; &lt;crystal@zuluru.org&gt;#ms', $messages[0]);
		$this->assertRegExp('#To: &quot;Chuck Captain&quot; &lt;chuck@zuluru.org&gt;#ms', $messages[0]);
		$this->assertNotRegExp('#CC: #ms', $messages[0]);
		$this->assertRegExp('#Subject: Crystal Captain removed your relation#ms', $messages[0]);
		$this->assertRegExp('#Crystal Captain has removed you as a relative on the Test Zuluru Affiliate web site.#ms', $messages[0]);

		// Make sure they were removed successfully
		$this->assertAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_CAPTAIN], PERSON_ID_CAPTAIN);
		$this->assertResponseNotRegExp('#<td>You can control <a[^>]*>Chuck Captain</a></td>#ms');
		$this->assertResponseRegExp('#<a[^>]*>Chuck Captain</a> can control you</td>#ms');
	}

	/**
	 * Test remove_relative method as the relative
	 *
	 * @return void
	 */
	public function testRemoveRelativeAsRelative() {
		// A person is allowed to remove relations in either direction
		$this->assertAccessRedirect(['controller' => 'People', 'action' => 'remove_relative', 'person' => PERSON_ID_CAPTAIN2, 'relative' => PERSON_ID_CAPTAIN],
			PERSON_ID_CAPTAIN, 'get', [], ['controller' => 'People', 'action' => 'view'],
			'Removed the relation.', 'Flash.flash.0.message');

		// Confirm the notification email
		$messages = Configure::read('test_emails');
		$this->assertEquals(1, count($messages));

		$this->assertRegExp('#From: &quot;Admin&quot; &lt;admin@zuluru.org&gt;#ms', $messages[0]);
		$this->assertRegExp('#Reply-To: &quot;Crystal Captain&quot; &lt;crystal@zuluru.org&gt;#ms', $messages[0]);
		$this->assertRegExp('#To: &quot;Chuck Captain&quot; &lt;chuck@zuluru.org&gt;#ms', $messages[0]);
		$this->assertNotRegExp('#CC: #ms', $messages[0]);
		$this->assertRegExp('#Subject: Crystal Captain removed your relation#ms', $messages[0]);
		$this->assertRegExp('#Crystal Captain has removed you as a relative on the Test Zuluru Affiliate web site.#ms', $messages[0]);

		// Make sure they were removed successfully
		$this->assertAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_CAPTAIN], PERSON_ID_CAPTAIN);
		$this->assertResponseRegExp('#<td>You can control <a[^>]*>Chuck Captain</a></td>#ms');
		$this->assertResponseNotRegExp('#<a[^>]*>Chuck Captain</a> can control you</td>#ms');
	}

	/**
	 * Test remove_relative method as someone else
	 *
	 * @return void
	 */
	public function testRemoveRelativeAsOther() {
		// Others are not allowed to remove relatives
		$this->assertAccessRedirect(['controller' => 'People', 'action' => 'remove_relative', 'person' => PERSON_ID_CAPTAIN, 'relative' => PERSON_ID_CAPTAIN2],
			PERSON_ID_PLAYER, 'get', [], ['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_CAPTAIN]);

		// TODO: Test with codes
		// TODO: Test as admin / manager
	}


	/**
	 * Test authorize_twitter method as an admin
	 *
	 * @return void
	 */
	public function testAuthorizeTwitterAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test authorize_twitter method as a manager
	 *
	 * @return void
	 */
	public function testAuthorizeTwitterAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test authorize_twitter method as a coordinator
	 *
	 * @return void
	 */
	public function testAuthorizeTwitterAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test authorize_twitter method as a captain
	 *
	 * @return void
	 */
	public function testAuthorizeTwitterAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test authorize_twitter method as a player
	 *
	 * @return void
	 */
	public function testAuthorizeTwitterAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test authorize_twitter method as someone else
	 *
	 * @return void
	 */
	public function testAuthorizeTwitterAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test authorize_twitter method without being logged in
	 *
	 * @return void
	 */
	public function testAuthorizeTwitterAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test revoke_twitter method as an admin
	 *
	 * @return void
	 */
	public function testRevokeTwitterAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test revoke_twitter method as a manager
	 *
	 * @return void
	 */
	public function testRevokeTwitterAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test revoke_twitter method as a coordinator
	 *
	 * @return void
	 */
	public function testRevokeTwitterAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test revoke_twitter method as a captain
	 *
	 * @return void
	 */
	public function testRevokeTwitterAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test revoke_twitter method as a player
	 *
	 * @return void
	 */
	public function testRevokeTwitterAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test revoke_twitter method as someone else
	 *
	 * @return void
	 */
	public function testRevokeTwitterAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test revoke_twitter method without being logged in
	 *
	 * @return void
	 */
	public function testRevokeTwitterAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test photo method as an admin
	 *
	 * @return void
	 */
	public function testPhotoAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test photo method as a manager
	 *
	 * @return void
	 */
	public function testPhotoAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test photo method as a coordinator
	 *
	 * @return void
	 */
	public function testPhotoAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test photo method as a captain
	 *
	 * @return void
	 */
	public function testPhotoAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test photo method as a player
	 *
	 * @return void
	 */
	public function testPhotoAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test photo method as someone else
	 *
	 * @return void
	 */
	public function testPhotoAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test photo method without being logged in
	 *
	 * @return void
	 */
	public function testPhotoAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test photo_upload method as an admin
	 *
	 * @return void
	 */
	public function testPhotoUploadAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test photo_upload method as a manager
	 *
	 * @return void
	 */
	public function testPhotoUploadAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test photo_upload method as a coordinator
	 *
	 * @return void
	 */
	public function testPhotoUploadAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test photo_upload method as a captain
	 *
	 * @return void
	 */
	public function testPhotoUploadAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test photo_upload method as a player
	 *
	 * @return void
	 */
	public function testPhotoUploadAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test photo_upload method as someone else
	 *
	 * @return void
	 */
	public function testPhotoUploadAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test photo_upload method without being logged in
	 *
	 * @return void
	 */
	public function testPhotoUploadAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test photo_resize method as an admin
	 *
	 * @return void
	 */
	public function testPhotoResizeAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test photo_resize method as a manager
	 *
	 * @return void
	 */
	public function testPhotoResizeAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test photo_resize method as a coordinator
	 *
	 * @return void
	 */
	public function testPhotoResizeAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test photo_resize method as a captain
	 *
	 * @return void
	 */
	public function testPhotoResizeAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test photo_resize method as a player
	 *
	 * @return void
	 */
	public function testPhotoResizeAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test photo_resize method as someone else
	 *
	 * @return void
	 */
	public function testPhotoResizeAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test photo_resize method without being logged in
	 *
	 * @return void
	 */
	public function testPhotoResizeAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_photos method as an admin
	 *
	 * @return void
	 */
	public function testApprovePhotosAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_photos method as a manager
	 *
	 * @return void
	 */
	public function testApprovePhotosAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_photos method as a coordinator
	 *
	 * @return void
	 */
	public function testApprovePhotosAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_photos method as a captain
	 *
	 * @return void
	 */
	public function testApprovePhotosAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_photos method as a player
	 *
	 * @return void
	 */
	public function testApprovePhotosAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_photos method as someone else
	 *
	 * @return void
	 */
	public function testApprovePhotosAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_photos method without being logged in
	 *
	 * @return void
	 */
	public function testApprovePhotosAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_photo method as an admin
	 *
	 * @return void
	 */
	public function testApprovePhotoAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_photo method as a manager
	 *
	 * @return void
	 */
	public function testApprovePhotoAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_photo method as a coordinator
	 *
	 * @return void
	 */
	public function testApprovePhotoAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_photo method as a captain
	 *
	 * @return void
	 */
	public function testApprovePhotoAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_photo method as a player
	 *
	 * @return void
	 */
	public function testApprovePhotoAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_photo method as someone else
	 *
	 * @return void
	 */
	public function testApprovePhotoAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_photo method without being logged in
	 *
	 * @return void
	 */
	public function testApprovePhotoAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_photo method as an admin
	 *
	 * @return void
	 */
	public function testDeletePhotoAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_photo method as a manager
	 *
	 * @return void
	 */
	public function testDeletePhotoAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_photo method as a coordinator
	 *
	 * @return void
	 */
	public function testDeletePhotoAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_photo method as a captain
	 *
	 * @return void
	 */
	public function testDeletePhotoAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_photo method as a player
	 *
	 * @return void
	 */
	public function testDeletePhotoAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_photo method as someone else
	 *
	 * @return void
	 */
	public function testDeletePhotoAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_photo method without being logged in
	 *
	 * @return void
	 */
	public function testDeletePhotoAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test document method as an admin
	 *
	 * @return void
	 */
	public function testDocumentAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test document method as a manager
	 *
	 * @return void
	 */
	public function testDocumentAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test document method as a coordinator
	 *
	 * @return void
	 */
	public function testDocumentAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test document method as a captain
	 *
	 * @return void
	 */
	public function testDocumentAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test document method as a player
	 *
	 * @return void
	 */
	public function testDocumentAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test document method as someone else
	 *
	 * @return void
	 */
	public function testDocumentAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test document method without being logged in
	 *
	 * @return void
	 */
	public function testDocumentAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test document_upload method as an admin
	 *
	 * @return void
	 */
	public function testDocumentUploadAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test document_upload method as a manager
	 *
	 * @return void
	 */
	public function testDocumentUploadAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test document_upload method as a coordinator
	 *
	 * @return void
	 */
	public function testDocumentUploadAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test document_upload method as a captain
	 *
	 * @return void
	 */
	public function testDocumentUploadAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test document_upload method as a player
	 *
	 * @return void
	 */
	public function testDocumentUploadAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test document_upload method as someone else
	 *
	 * @return void
	 */
	public function testDocumentUploadAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test document_upload method without being logged in
	 *
	 * @return void
	 */
	public function testDocumentUploadAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_documents method as an admin
	 *
	 * @return void
	 */
	public function testApproveDocumentsAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_documents method as a manager
	 *
	 * @return void
	 */
	public function testApproveDocumentsAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_documents method as a coordinator
	 *
	 * @return void
	 */
	public function testApproveDocumentsAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_documents method as a captain
	 *
	 * @return void
	 */
	public function testApproveDocumentsAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_documents method as a player
	 *
	 * @return void
	 */
	public function testApproveDocumentsAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_documents method as someone else
	 *
	 * @return void
	 */
	public function testApproveDocumentsAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_documents method without being logged in
	 *
	 * @return void
	 */
	public function testApproveDocumentsAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_document method as an admin
	 *
	 * @return void
	 */
	public function testApproveDocumentAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_document method as a manager
	 *
	 * @return void
	 */
	public function testApproveDocumentAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_document method as a coordinator
	 *
	 * @return void
	 */
	public function testApproveDocumentAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_document method as a captain
	 *
	 * @return void
	 */
	public function testApproveDocumentAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_document method as a player
	 *
	 * @return void
	 */
	public function testApproveDocumentAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_document method as someone else
	 *
	 * @return void
	 */
	public function testApproveDocumentAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_document method without being logged in
	 *
	 * @return void
	 */
	public function testApproveDocumentAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit_document method as an admin
	 *
	 * @return void
	 */
	public function testEditDocumentAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit_document method as a manager
	 *
	 * @return void
	 */
	public function testEditDocumentAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit_document method as a coordinator
	 *
	 * @return void
	 */
	public function testEditDocumentAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit_document method as a captain
	 *
	 * @return void
	 */
	public function testEditDocumentAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit_document method as a player
	 *
	 * @return void
	 */
	public function testEditDocumentAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit_document method as someone else
	 *
	 * @return void
	 */
	public function testEditDocumentAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit_document method without being logged in
	 *
	 * @return void
	 */
	public function testEditDocumentAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_document method as an admin
	 *
	 * @return void
	 */
	public function testDeleteDocumentAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_document method as a manager
	 *
	 * @return void
	 */
	public function testDeleteDocumentAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_document method as a coordinator
	 *
	 * @return void
	 */
	public function testDeleteDocumentAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_document method as a captain
	 *
	 * @return void
	 */
	public function testDeleteDocumentAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_document method as a player
	 *
	 * @return void
	 */
	public function testDeleteDocumentAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_document method as someone else
	 *
	 * @return void
	 */
	public function testDeleteDocumentAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_document method without being logged in
	 *
	 * @return void
	 */
	public function testDeleteDocumentAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test nominate method as an admin
	 *
	 * @return void
	 */
	public function testNominateAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test nominate method as a manager
	 *
	 * @return void
	 */
	public function testNominateAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test nominate method as a coordinator
	 *
	 * @return void
	 */
	public function testNominateAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test nominate method as a captain
	 *
	 * @return void
	 */
	public function testNominateAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test nominate method as a player
	 *
	 * @return void
	 */
	public function testNominateAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test nominate method as someone else
	 *
	 * @return void
	 */
	public function testNominateAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test nominate method without being logged in
	 *
	 * @return void
	 */
	public function testNominateAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test nominate_badge method as an admin
	 *
	 * @return void
	 */
	public function testNominateBadgeAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test nominate_badge method as a manager
	 *
	 * @return void
	 */
	public function testNominateBadgeAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test nominate_badge method as a coordinator
	 *
	 * @return void
	 */
	public function testNominateBadgeAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test nominate_badge method as a captain
	 *
	 * @return void
	 */
	public function testNominateBadgeAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test nominate_badge method as a player
	 *
	 * @return void
	 */
	public function testNominateBadgeAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test nominate_badge method as someone else
	 *
	 * @return void
	 */
	public function testNominateBadgeAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test nominate_badge method without being logged in
	 *
	 * @return void
	 */
	public function testNominateBadgeAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test nominate_badge_reason method as an admin
	 *
	 * @return void
	 */
	public function testNominateBadgeReasonAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test nominate_badge_reason method as a manager
	 *
	 * @return void
	 */
	public function testNominateBadgeReasonAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test nominate_badge_reason method as a coordinator
	 *
	 * @return void
	 */
	public function testNominateBadgeReasonAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test nominate_badge_reason method as a captain
	 *
	 * @return void
	 */
	public function testNominateBadgeReasonAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test nominate_badge_reason method as a player
	 *
	 * @return void
	 */
	public function testNominateBadgeReasonAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test nominate_badge_reason method as someone else
	 *
	 * @return void
	 */
	public function testNominateBadgeReasonAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test nominate_badge_reason method without being logged in
	 *
	 * @return void
	 */
	public function testNominateBadgeReasonAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_badges method as an admin
	 *
	 * @return void
	 */
	public function testApproveBadgesAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_badges method as a manager
	 *
	 * @return void
	 */
	public function testApproveBadgesAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_badges method as a coordinator
	 *
	 * @return void
	 */
	public function testApproveBadgesAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_badges method as a captain
	 *
	 * @return void
	 */
	public function testApproveBadgesAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_badges method as a player
	 *
	 * @return void
	 */
	public function testApproveBadgesAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_badges method as someone else
	 *
	 * @return void
	 */
	public function testApproveBadgesAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_badges method without being logged in
	 *
	 * @return void
	 */
	public function testApproveBadgesAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_badge method as an admin
	 *
	 * @return void
	 */
	public function testApproveBadgeAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_badge method as a manager
	 *
	 * @return void
	 */
	public function testApproveBadgeAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_badge method as a coordinator
	 *
	 * @return void
	 */
	public function testApproveBadgeAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_badge method as a captain
	 *
	 * @return void
	 */
	public function testApproveBadgeAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_badge method as a player
	 *
	 * @return void
	 */
	public function testApproveBadgeAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_badge method as someone else
	 *
	 * @return void
	 */
	public function testApproveBadgeAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_badge method without being logged in
	 *
	 * @return void
	 */
	public function testApproveBadgeAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_badge method as an admin
	 *
	 * @return void
	 */
	public function testDeleteBadgeAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_badge method as a manager
	 *
	 * @return void
	 */
	public function testDeleteBadgeAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_badge method as a coordinator
	 *
	 * @return void
	 */
	public function testDeleteBadgeAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_badge method as a captain
	 *
	 * @return void
	 */
	public function testDeleteBadgeAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_badge method as a player
	 *
	 * @return void
	 */
	public function testDeleteBadgeAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_badge method as someone else
	 *
	 * @return void
	 */
	public function testDeleteBadgeAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_badge method without being logged in
	 *
	 * @return void
	 */
	public function testDeleteBadgeAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete method as an admin
	 *
	 * @return void
	 */
	public function testDeleteAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
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
	 * Test act_as method as an admin
	 *
	 * @return void
	 */
	public function testActAsAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test act_as method as a manager
	 *
	 * @return void
	 */
	public function testActAsAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test act_as method as a coordinator
	 *
	 * @return void
	 */
	public function testActAsAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test act_as method as a captain
	 *
	 * @return void
	 */
	public function testActAsAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test act_as method as a player
	 *
	 * @return void
	 */
	public function testActAsAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test act_as method as someone else
	 *
	 * @return void
	 */
	public function testActAsAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test act_as method without being logged in
	 *
	 * @return void
	 */
	public function testActAsAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test search method as an admin
	 *
	 * @return void
	 */
	public function testSearchAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
		/*
		$data = [
		];
		$this->post(['controller' => 'People', 'action' => 'search', $data);

		$this->assertResponseSuccess();
		$people = TableRegistry::get('People');
		$query = $people->find()->where(['title' => $data['title']]);
		$this->assertEquals(1, $query->count());
		*/
	}

	/**
	 * Test search method as a manager
	 *
	 * @return void
	 */
	public function testSearchAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test search method as a coordinator
	 *
	 * @return void
	 */
	public function testSearchAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test search method as a captain
	 *
	 * @return void
	 */
	public function testSearchAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test search method as a player
	 *
	 * @return void
	 */
	public function testSearchAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test search method as someone else
	 *
	 * @return void
	 */
	public function testSearchAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test search method without being logged in
	 *
	 * @return void
	 */
	public function testSearchAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test rule_search method as an admin
	 *
	 * @return void
	 */
	public function testRuleSearchAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test rule_search method as a manager
	 *
	 * @return void
	 */
	public function testRuleSearchAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test rule_search method as a coordinator
	 *
	 * @return void
	 */
	public function testRuleSearchAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test rule_search method as a captain
	 *
	 * @return void
	 */
	public function testRuleSearchAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test rule_search method as a player
	 *
	 * @return void
	 */
	public function testRuleSearchAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test rule_search method as someone else
	 *
	 * @return void
	 */
	public function testRuleSearchAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test rule_search method without being logged in
	 *
	 * @return void
	 */
	public function testRuleSearchAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test league_search method as an admin
	 *
	 * @return void
	 */
	public function testLeagueSearchAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test league_search method as a manager
	 *
	 * @return void
	 */
	public function testLeagueSearchAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test league_search method as a coordinator
	 *
	 * @return void
	 */
	public function testLeagueSearchAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test league_search method as a captain
	 *
	 * @return void
	 */
	public function testLeagueSearchAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test league_search method as a player
	 *
	 * @return void
	 */
	public function testLeagueSearchAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test league_search method as someone else
	 *
	 * @return void
	 */
	public function testLeagueSearchAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test league_search method without being logged in
	 *
	 * @return void
	 */
	public function testLeagueSearchAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test inactive_search method as an admin
	 *
	 * @return void
	 */
	public function testInactiveSearchAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test inactive_search method as a manager
	 *
	 * @return void
	 */
	public function testInactiveSearchAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test inactive_search method as a coordinator
	 *
	 * @return void
	 */
	public function testInactiveSearchAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test inactive_search method as a captain
	 *
	 * @return void
	 */
	public function testInactiveSearchAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test inactive_search method as a player
	 *
	 * @return void
	 */
	public function testInactiveSearchAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test inactive_search method as someone else
	 *
	 * @return void
	 */
	public function testInactiveSearchAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test inactive_search method without being logged in
	 *
	 * @return void
	 */
	public function testInactiveSearchAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test list_new method as an admin
	 *
	 * @return void
	 */
	public function testListNewAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test list_new method as a manager
	 *
	 * @return void
	 */
	public function testListNewAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test list_new method as a coordinator
	 *
	 * @return void
	 */
	public function testListNewAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test list_new method as a captain
	 *
	 * @return void
	 */
	public function testListNewAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test list_new method as a player
	 *
	 * @return void
	 */
	public function testListNewAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test list_new method as someone else
	 *
	 * @return void
	 */
	public function testListNewAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test list_new method without being logged in
	 *
	 * @return void
	 */
	public function testListNewAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve method as an admin
	 *
	 * @return void
	 */
	public function testApproveAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve method as a manager
	 *
	 * @return void
	 */
	public function testApproveAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve method as a coordinator
	 *
	 * @return void
	 */
	public function testApproveAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve method as a captain
	 *
	 * @return void
	 */
	public function testApproveAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve method as a player
	 *
	 * @return void
	 */
	public function testApproveAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve method as someone else
	 *
	 * @return void
	 */
	public function testApproveAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve method without being logged in
	 *
	 * @return void
	 */
	public function testApproveAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test vcf method as an admin
	 *
	 * @return void
	 */
	public function testVcfAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test vcf method as a manager
	 *
	 * @return void
	 */
	public function testVcfAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test vcf method as a coordinator
	 *
	 * @return void
	 */
	public function testVcfAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test vcf method as a captain
	 *
	 * @return void
	 */
	public function testVcfAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test vcf method as a player
	 *
	 * @return void
	 */
	public function testVcfAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test vcf method as someone else
	 *
	 * @return void
	 */
	public function testVcfAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test vcf method without being logged in
	 *
	 * @return void
	 */
	public function testVcfAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test ical method as an admin
	 *
	 * @return void
	 */
	public function testIcalAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test ical method as a manager
	 *
	 * @return void
	 */
	public function testIcalAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test ical method as a coordinator
	 *
	 * @return void
	 */
	public function testIcalAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test ical method as a captain
	 *
	 * @return void
	 */
	public function testIcalAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test ical method as a player
	 *
	 * @return void
	 */
	public function testIcalAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test ical method as someone else
	 *
	 * @return void
	 */
	public function testIcalAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test ical method without being logged in
	 *
	 * @return void
	 */
	public function testIcalAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test registrations method as an admin
	 *
	 * @return void
	 */
	public function testRegistrationsAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test registrations method as a manager
	 *
	 * @return void
	 */
	public function testRegistrationsAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test registrations method as a coordinator
	 *
	 * @return void
	 */
	public function testRegistrationsAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test registrations method as a captain
	 *
	 * @return void
	 */
	public function testRegistrationsAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test registrations method as a player
	 *
	 * @return void
	 */
	public function testRegistrationsAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test registrations method as someone else
	 *
	 * @return void
	 */
	public function testRegistrationsAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test registrations method without being logged in
	 *
	 * @return void
	 */
	public function testRegistrationsAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test credits method as an admin
	 *
	 * @return void
	 */
	public function testCreditsAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test credits method as a manager
	 *
	 * @return void
	 */
	public function testCreditsAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test credits method as a coordinator
	 *
	 * @return void
	 */
	public function testCreditsAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test credits method as a captain
	 *
	 * @return void
	 */
	public function testCreditsAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test credits method as a player
	 *
	 * @return void
	 */
	public function testCreditsAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test credits method as someone else
	 *
	 * @return void
	 */
	public function testCreditsAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test credits method without being logged in
	 *
	 * @return void
	 */
	public function testCreditsAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test teams method as an admin
	 *
	 * @return void
	 */
	public function testTeamsAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test teams method as a manager
	 *
	 * @return void
	 */
	public function testTeamsAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test teams method as a coordinator
	 *
	 * @return void
	 */
	public function testTeamsAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test teams method as a captain
	 *
	 * @return void
	 */
	public function testTeamsAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test teams method as a player
	 *
	 * @return void
	 */
	public function testTeamsAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test teams method as someone else
	 *
	 * @return void
	 */
	public function testTeamsAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test teams method without being logged in
	 *
	 * @return void
	 */
	public function testTeamsAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test waivers method as an admin
	 *
	 * @return void
	 */
	public function testWaiversAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test waivers method as a manager
	 *
	 * @return void
	 */
	public function testWaiversAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test waivers method as a coordinator
	 *
	 * @return void
	 */
	public function testWaiversAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test waivers method as a captain
	 *
	 * @return void
	 */
	public function testWaiversAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test waivers method as a player
	 *
	 * @return void
	 */
	public function testWaiversAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test waivers method as someone else
	 *
	 * @return void
	 */
	public function testWaiversAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test waivers method without being logged in
	 *
	 * @return void
	 */
	public function testWaiversAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _makeHash method
	 *
	 * @return void
	 */
	public function testMakeHash() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _checkHash method
	 *
	 * @return void
	 */
	public function testCheckHash() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
