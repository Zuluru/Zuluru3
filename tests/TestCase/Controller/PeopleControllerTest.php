<?php
namespace App\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\Filesystem\Folder;
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
						'app.team_events',
					'app.divisions_days',
					'app.divisions_people',
					'app.game_slots',
					'app.pools',
						'app.pools_teams',
					'app.games',
						'app.games_allstars',
						'app.score_entries',
						'app.stats',
			'app.attendances',
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
			'app.mailing_lists',
				'app.subscriptions',
			'app.notes',
			'app.settings',
			'app.waivers',
				'app.waivers_people',
	];

	public function setUp() {
		parent::setUp();

		// TODO: Handle in the bootstrap?
		Configure::write('App.paths.uploads', TESTS . 'test_app' . DS . 'upload');

		// Copy the test uploads to the destination
		// TODO: Handle this somehow via the uploads fixture, so changes are only required there when new ones are added
		$dummy = TESTS . 'test_app' . DS . 'dummy.png';
		$upload_path = Configure::read('App.paths.uploads') . DS;
		$folder = new Folder($upload_path);
		$folder->create($upload_path);
		copy($dummy, $upload_path . PERSON_ID_CAPTAIN . '.png');
		copy($dummy, $upload_path . PERSON_ID_PLAYER . '.png');
		copy($dummy, $upload_path . PERSON_ID_CHILD . '_' . UPLOAD_ID_CHILD_WAIVER . '.png');
		copy($dummy, $upload_path . PERSON_ID_CAPTAIN2 . '_' . UPLOAD_ID_DOG_WAIVER . '.png');
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		// Delete the temporary uploads
		$upload_path = Configure::read('App.paths.uploads');
		$folder = new Folder($upload_path);
		$folder->delete();

		parent::tearDown();
	}

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex() {
		// Admins are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'index'], PERSON_ID_ADMIN);

		// Managers are allowed to see the index, but don't see people in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'index'], PERSON_ID_MANAGER);

		// Anyone else is not allowed to get the index
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'index'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'index'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'index'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'index'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'index']);
	}

	/**
	 * Test statistics method
	 *
	 * @return void
	 */
	public function testStatistics() {
		// Admins are allowed to view the statistics page
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'statistics'], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#<h4 class="affiliate">Club</h4>.*<td>Ultimate</td>[\s]*<td>Woman</td>[\s]*<td>6</td>#ms');

		// Managers are allowed to view the statistics page
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'statistics'], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test statistics method as others
	 *
	 * @return void
	 */
	public function testStatisticsAsOthers() {
		// Others are not allowed to statistics
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'statistics'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'statistics'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'statistics'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'statistics'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'statistics']);
	}

	/**
	 * Test participation method
	 *
	 * @return void
	 */
	public function testParticipation() {
		// Admins are allowed to view the participation report
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'participation'], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');

		// Managers are allowed to view the participation report
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'participation'], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');

		// Others are not allowed to view the participation report
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'participation'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'participation'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'participation'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'participation'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'participation']);
	}

	/**
	 * Test retention method
	 *
	 * @return void
	 */
	public function testRetention() {
		// Admins are allowed to view the retention report
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'retention'], PERSON_ID_ADMIN);

		// Managers are allowed to view the retention report
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'retention'], PERSON_ID_MANAGER);

		// Others are not allowed to view the retention report
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'retention'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'retention'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'retention'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'retention'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'retention']);
	}

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testView() {
		// Admins are allowed to see all data
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_PLAYER], PERSON_ID_ADMIN);
		$this->assertResponseContains('Phone (home)');
		$this->assertResponseContains('Email Address');
		$this->assertResponseContains('Birthdate');

		// Admins are allowed to see and manipulate relatives
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_CAPTAIN], PERSON_ID_ADMIN);
		$this->assertResponseContains('/people/remove_relative?person=' . PERSON_ID_CAPTAIN . '&amp;relative=' . PERSON_ID_CAPTAIN2);
		$this->assertResponseRegExp('#<td>Crystal can control <a[^>]*>Chuck Captain</a></td>\s*<td>Yes</td>#ms');
		$this->assertResponseContains('/people/remove_relative?person=' . PERSON_ID_CAPTAIN2 . '&amp;relative=' . PERSON_ID_CAPTAIN);
		$this->assertResponseRegExp('#<td><a[^>]*>Chuck Captain</a> can control Crystal</td>\s*<td>No</td>#ms');

		// Managers are allowed to see all data for people in their affiliate
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_PLAYER], PERSON_ID_MANAGER);
		$this->assertResponseContains('Phone (home)');
		$this->assertResponseContains('Email Address');
		$this->assertResponseContains('Birthdate');

		// But only regular data for people in their own
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_ANDY_SUB], PERSON_ID_MANAGER);
		$this->assertResponseNotContains('Phone (home)');
		$this->assertResponseNotContains('Email Address');
		$this->assertResponseNotContains('Birthdate');

		// Managers are allowed to see and manipulate relatives
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_CAPTAIN], PERSON_ID_MANAGER);
		$this->assertResponseContains('/people/remove_relative?person=' . PERSON_ID_CAPTAIN . '&amp;relative=' . PERSON_ID_CAPTAIN2);
		$this->assertResponseRegExp('#<td>Crystal can control <a[^>]*>Chuck Captain</a></td>\s*<td>Yes</td>#ms');
		$this->assertResponseContains('/people/remove_relative?person=' . PERSON_ID_CAPTAIN2 . '&amp;relative=' . PERSON_ID_CAPTAIN);
		$this->assertResponseRegExp('#<td><a[^>]*>Chuck Captain</a> can control Crystal</td>\s*<td>No</td>#ms');

		// Coordinators are allowed to see contact info for their captains
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_CAPTAIN], PERSON_ID_COORDINATOR);
		$this->assertResponseContains('Phone (home)');
		$this->assertResponseContains('Email Address');
		$this->assertResponseNotContains('Birthdate');

		// ...but not regular players
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_PLAYER], PERSON_ID_COORDINATOR);
		$this->assertResponseNotContains('Phone (home)');
		$this->assertResponseNotContains('Email Address');
		$this->assertResponseNotContains('Birthdate');

		// Captains are allowed to see contact info for their players
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_PLAYER], PERSON_ID_CAPTAIN);
		$this->assertResponseContains('Phone (home)');
		$this->assertResponseContains('Email Address');
		$this->assertResponseNotContains('Birthdate');

		// ...and their coordinator
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_COORDINATOR], PERSON_ID_CAPTAIN);
		$this->assertResponseContains('Phone (home)');
		$this->assertResponseContains('Email Address');

		// ...but not others
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_MANAGER], PERSON_ID_CAPTAIN);
		$this->assertResponseNotContains('Phone (home)');
		$this->assertResponseContains('Email Address'); // Can see this because it's public

		// Players are allowed to see contact info for their captains
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_CAPTAIN], PERSON_ID_PLAYER);
		$this->assertResponseContains('Phone (home)');
		$this->assertResponseContains('Email Address');
		$this->assertResponseNotContains('Birthdate');

		// ...but not others
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_COORDINATOR], PERSON_ID_PLAYER);
		$this->assertResponseContains('Phone (home)'); // Can see this because it's public
		$this->assertResponseNotContains('Email Address');
		$this->assertResponseNotContains('Birthdate');

		// ...and this is still true if it's an admin acting as the player
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_COORDINATOR], [PERSON_ID_ADMIN, PERSON_ID_PLAYER]);
		$this->assertResponseContains('Phone (home)'); // Can see this because it's public
		$this->assertResponseNotContains('Email Address');
		$this->assertResponseNotContains('Birthdate');

		// ...or if it's a player acting as a (presumably related, though that's not checked here) admin
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_COORDINATOR], [PERSON_ID_PLAYER, PERSON_ID_ADMIN]);
		$this->assertResponseContains('Phone (home)'); // Can see this because it's public
		$this->assertResponseNotContains('Email Address');
		$this->assertResponseNotContains('Birthdate');

		// Visitors are allowed to view
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_PLAYER], PERSON_ID_VISITOR);
		$this->assertResponseNotContains('Phone');
		$this->assertResponseNotContains('Email Address');
		$this->assertResponseNotContains('Birthdate');
		$this->markTestIncomplete('Not implemented yet.');

		// Others are allowed to view
		$this->assertGetAnonymousAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_PLAYER]);
		$this->assertResponseNotContains('Phone');
		$this->assertResponseNotContains('Email Address');
		$this->assertResponseNotContains('Birthdate');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test tooltip method
	 *
	 * @return void
	 */
	public function testTooltip() {
		// Admins are allowed to view person tooltips, and have all information and options
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => PERSON_ID_PLAYER], PERSON_ID_ADMIN);
		$this->assertResponseContains('mailto:pam@zuluru.org');
		$this->assertResponseContains('(416) 678-9012 (home)');
		$this->assertResponseContains('(416) 789-0123 x456 (work)');
		$this->assertResponseContains('/people\\/vcf?person=' . PERSON_ID_PLAYER);
		$this->assertResponseContains('/people\\/note?person=' . PERSON_ID_PLAYER);
		$this->assertResponseContains('/people\\/act_as?person=' . PERSON_ID_PLAYER);

		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => PERSON_ID_ANDY_SUB], PERSON_ID_ADMIN);
		$this->assertResponseContains('mailto:andy@zuluru.org');
		$this->assertResponseContains('(647) 555-5555 (home)');
		$this->assertResponseContains('(647) 555-5556 (mobile)');
		$this->assertResponseContains('/people\\/vcf?person=' . PERSON_ID_ANDY_SUB);
		$this->assertResponseContains('/people\\/note?person=' . PERSON_ID_ANDY_SUB);
		$this->assertResponseContains('/people\\/act_as?person=' . PERSON_ID_ANDY_SUB);

		$this->assertGetAjaxAsAccessRedirect(['controller' => 'People', 'action' => 'tooltip', 'person' => 10000],
			PERSON_ID_ADMIN, '/',
			'Invalid person.');

		// Managers are allowed to view person tooltips, and have all information and options
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => PERSON_ID_PLAYER],
			PERSON_ID_MANAGER);
		$this->assertResponseContains('mailto:pam@zuluru.org');
		$this->assertResponseContains('(416) 678-9012 (home)');
		$this->assertResponseContains('(416) 789-0123 x456 (work)');
		$this->assertResponseContains('/people\\/vcf?person=' . PERSON_ID_PLAYER);
		$this->assertResponseContains('/people\\/note?person=' . PERSON_ID_PLAYER);
		$this->assertResponseContains('/people\\/act_as?person=' . PERSON_ID_PLAYER);

		// But are restricted when viewing tooltip of people not in their affiliate
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => PERSON_ID_ANDY_SUB],
			PERSON_ID_MANAGER);
		$this->assertResponseNotContains('mailto');
		$this->assertResponseNotContains('(home)');
		$this->assertResponseNotContains('(work)');
		$this->assertResponseNotContains('(mobile)');
		$this->assertResponseNotContains('/people\\/vcf?person=' . PERSON_ID_ANDY_SUB);
		$this->assertResponseContains('/people\\/note?person=' . PERSON_ID_ANDY_SUB);
		$this->assertResponseNotContains('/people\\/act_as?person=' . PERSON_ID_ANDY_SUB);

		// Coordinator gets to see contact info for their captains
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => PERSON_ID_CAPTAIN],
			PERSON_ID_COORDINATOR);
		$this->assertResponseContains('mailto:crystal@zuluru.org');
		$this->assertResponseContains('(416) 567-8910 (home)');
		$this->assertResponseContains('/people\\/vcf?person=' . PERSON_ID_CAPTAIN);
		$this->assertResponseContains('/people\\/note?person=' . PERSON_ID_CAPTAIN);
		$this->assertResponseNotContains('/people\\/act_as?person=' . PERSON_ID_CAPTAIN);

		// Captain gets to see contact info for their players
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => PERSON_ID_PLAYER],
			PERSON_ID_CAPTAIN);
		$this->assertResponseContains('mailto:pam@zuluru.org');
		$this->assertResponseContains('(416) 678-9012 (home)');
		$this->assertResponseContains('(416) 789-0123 x456 (work)');
		$this->assertResponseContains('/people\\/vcf?person=' . PERSON_ID_PLAYER);
		$this->assertResponseContains('/people\\/note?person=' . PERSON_ID_PLAYER);
		$this->assertResponseNotContains('/people\\/act_as?person=' . PERSON_ID_PLAYER);

		// And for their coordinator
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => PERSON_ID_COORDINATOR],
			PERSON_ID_CAPTAIN);
		$this->assertResponseContains('mailto:cindy@zuluru.org');
		$this->assertResponseContains('(416) 456-7890 (home)');
		$this->assertResponseContains('/people\\/vcf?person=' . PERSON_ID_COORDINATOR);
		$this->assertResponseContains('/people\\/note?person=' . PERSON_ID_COORDINATOR);
		$this->assertResponseNotContains('/people\\/act_as?person=' . PERSON_ID_COORDINATOR);

		// Player gets to see contact info for their own captain
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => PERSON_ID_CAPTAIN],
			PERSON_ID_PLAYER);
		$this->assertResponseContains('mailto:crystal@zuluru.org');
		$this->assertResponseContains('(416) 567-8910 (home)');
		$this->assertResponseContains('/people\\/vcf?person=' . PERSON_ID_CAPTAIN);
		$this->assertResponseContains('/people\\/note?person=' . PERSON_ID_CAPTAIN);
		$this->assertResponseNotContains('/people\\/act_as?person=' . PERSON_ID_CAPTAIN);

		// And are allowed to act as their relatives
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => PERSON_ID_CHILD],
			PERSON_ID_PLAYER);
		$this->assertResponseContains('/people\\/note?person=' . PERSON_ID_CHILD);
		$this->assertResponseContains('/people\\/act_as?person=' . PERSON_ID_CHILD);

		// But sees less about other people
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => PERSON_ID_CAPTAIN2],
			PERSON_ID_PLAYER);
		$this->assertResponseNotContains('mailto');
		$this->assertResponseNotContains('(home)');
		$this->assertResponseNotContains('(work)');
		$this->assertResponseNotContains('(mobile)');
		$this->assertResponseNotContains('/people\\/vcf?person=' . PERSON_ID_CAPTAIN2);
		$this->assertResponseContains('/people\\/note?person=' . PERSON_ID_CAPTAIN2);
		$this->assertResponseNotContains('/people\\/act_as?person=' . PERSON_ID_CAPTAIN2);

		// Including inactive people, even if they've published info
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => PERSON_ID_INACTIVE],
			PERSON_ID_PLAYER);
		$this->assertResponseNotContains('mailto');
		$this->assertResponseNotContains('(home)');
		$this->assertResponseNotContains('(work)');
		$this->assertResponseNotContains('(mobile)');
		$this->assertResponseNotContains('/people\\/vcf?person=' . PERSON_ID_INACTIVE);
		$this->assertResponseContains('/people\\/note?person=' . PERSON_ID_INACTIVE);
		$this->assertResponseNotContains('/people\\/act_as?person=' . PERSON_ID_INACTIVE);

		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => PERSON_ID_PLAYER],
			PERSON_ID_VISITOR);

		$this->assertGetAjaxAnonymousAccessOk(['controller' => 'People', 'action' => 'tooltip', 'person' => PERSON_ID_PLAYER]);
	}

	/**
	 * Test edit method as an admin
	 *
	 * @return void
	 */
	public function testEditAsAdmin() {
		// Admins are allowed to edit
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'edit'], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'edit', 'person' => PERSON_ID_PLAYER], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers are allowed to edit
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'edit'], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'edit', 'person' => PERSON_ID_PLAYER], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as a coordinator
	 *
	 * @return void
	 */
	public function testEditAsCoordinator() {
		// Coordinators are allowed to edit themselves only
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'edit'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'edit', 'person' => PERSON_ID_PLAYER], PERSON_ID_COORDINATOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as a captain
	 *
	 * @return void
	 */
	public function testEditAsCaptain() {
		// Captains are allowed to edit themselves and their relatives only
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'edit'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'edit', 'person' => PERSON_ID_CAPTAIN2], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'edit', 'person' => PERSON_ID_PLAYER], PERSON_ID_CAPTAIN);
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

		// Players are allowed to edit themselves and their relatives only
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'edit'], PERSON_ID_PLAYER);
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'edit'],
			PERSON_ID_PLAYER, ['shirt_size' => 'Mens Large'], '/', 'Your profile has been saved.');
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'edit', 'person' => PERSON_ID_CHILD], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'edit', 'person' => PERSON_ID_COORDINATOR], PERSON_ID_PLAYER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as someone else
	 *
	 * @return void
	 */
	public function testEditAsVisitor() {
		// Visitors are allowed to edit themselves only
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'edit'], PERSON_ID_VISITOR);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'edit', 'person' => PERSON_ID_PLAYER], PERSON_ID_VISITOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method without being logged in
	 *
	 * @return void
	 */
	public function testEditAsAnonymous() {
		// Others are not allowed to edit people
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'edit']);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'edit', 'person' => PERSON_ID_PLAYER]);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test deactivate method as an admin
	 *
	 * @return void
	 */
	public function testDeactivateAsAdmin() {
		// Admins are allowed to deactivate people
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'deactivate', 'person' => PERSON_ID_PLAYER], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'deactivate'], PERSON_ID_ADMIN);

		$this->enableCsrfToken();
		$this->enableSecurityToken();
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'deactivate'],
			PERSON_ID_ADMIN, [], '/', 'Your profile has been deactivated; sorry to see you go. If you ever change your mind, you can just return to the site and reactivate your profile; we\'ll be happy to have you back!');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test deactivate method as a manager
	 *
	 * @return void
	 */
	public function testDeactivateAsManager() {
		// Managers are allowed to deactivate people
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'deactivate', 'person' => PERSON_ID_PLAYER], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'deactivate'], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test deactivate method as a coordinator
	 *
	 * @return void
	 */
	public function testDeactivateAsCoordinator() {
		// Coordinators are not allowed to deactivate others, or themselves while they're actively running a league
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'deactivate', 'person' => PERSON_ID_PLAYER], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'deactivate'],
			PERSON_ID_COORDINATOR, '/',
			'You cannot deactivate your account while you are coordinating an active division.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test deactivate method as a captain
	 *
	 * @return void
	 */
	public function testDeactivateAsCaptain() {
		// Captains are not allowed to deactivate others, or themselves while they're actively running a team
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'deactivate', 'person' => PERSON_ID_PLAYER], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'deactivate'],
			PERSON_ID_CAPTAIN, '/',
			'You cannot deactivate your account while you are on an active team.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test deactivate method as a player
	 *
	 * @return void
	 */
	public function testDeactivateAsPlayer() {
		// Players are not allowed to deactivate others, or themselves while they're on an active team
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'deactivate', 'person' => PERSON_ID_VISITOR], PERSON_ID_PLAYER);
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'deactivate'],
			PERSON_ID_PLAYER, '/',
			'You cannot deactivate your account while you are on an active team.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test deactivate method as someone else
	 *
	 * @return void
	 */
	public function testDeactivateAsVisitor() {
		// Visitors are allowed to deactivate themselves only
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'deactivate', 'person' => PERSON_ID_PLAYER], PERSON_ID_VISITOR);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'deactivate'], PERSON_ID_VISITOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test deactivate method without being logged in
	 *
	 * @return void
	 */
	public function testDeactivateAsAnonymous() {
		// Others are not allowed to deactivate people
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'deactivate']);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'deactivate', 'person' => PERSON_ID_PLAYER]);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test reactivate method as an admin
	 *
	 * @return void
	 */
	public function testReactivateAsAdmin() {
		// Admins are allowed to reactivate
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'reactivate', 'person' => PERSON_ID_INACTIVE], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test reactivate method as a manager
	 *
	 * @return void
	 */
	public function testReactivateAsManager() {
		// Managers are allowed to reactivate
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'reactivate', 'person' => PERSON_ID_INACTIVE], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test reactivate method as a coordinator
	 *
	 * @return void
	 */
	public function testReactivateAsCoordinator() {
		// Coordinators are allowed to reactivate
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'reactivate', 'person' => PERSON_ID_INACTIVE], PERSON_ID_COORDINATOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test reactivate method as a captain
	 *
	 * @return void
	 */
	public function testReactivateAsCaptain() {
		// Captains are allowed to reactivate
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'reactivate', 'person' => PERSON_ID_INACTIVE], PERSON_ID_CAPTAIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test reactivate method as a player
	 *
	 * @return void
	 */
	public function testReactivateAsPlayer() {
		// Players are allowed to reactivate
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'reactivate', 'person' => PERSON_ID_INACTIVE], PERSON_ID_PLAYER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test reactivate method as someone else
	 *
	 * @return void
	 */
	public function testReactivateAsVisitor() {
		// Visitors are allowed to reactivate
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'reactivate', 'person' => PERSON_ID_INACTIVE], PERSON_ID_VISITOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test reactivate method without being logged in
	 *
	 * @return void
	 */
	public function testReactivateAsAnonymous() {
		// Others are not allowed to reactivate people
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'reactivate', 'person' => PERSON_ID_INACTIVE]);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test confirm method as an admin
	 *
	 * @return void
	 */
	public function testConfirmAsAdmin() {
		// Admins are allowed to confirm
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'confirm'],
			PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test confirm method as a manager
	 *
	 * @return void
	 */
	public function testConfirmAsManager() {
		// Managers are allowed to confirm
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'confirm'],
			PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test confirm method as a coordinator
	 *
	 * @return void
	 */
	public function testConfirmAsCoordinator() {
		// Coordinators are allowed to confirm
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'confirm'],
			PERSON_ID_COORDINATOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test confirm method as a captain
	 *
	 * @return void
	 */
	public function testConfirmAsCaptain() {
		// Captains are allowed to confirm
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'confirm'],
			PERSON_ID_CAPTAIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test confirm method as a player
	 *
	 * @return void
	 */
	public function testConfirmAsPlayer() {
		// Players are allowed to confirm
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'confirm'],
			PERSON_ID_PLAYER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test confirm method as someone else
	 *
	 * @return void
	 */
	public function testConfirmAsVisitor() {
		// Visitors are allowed to confirm
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'confirm'],
			PERSON_ID_VISITOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test confirm method without being logged in
	 *
	 * @return void
	 */
	public function testConfirmAsAnonymous() {
		// Others are not allowed to confirm
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'confirm']);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test note method as an admin
	 *
	 * @return void
	 */
	public function testNoteAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to add notes
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'note', 'person' => PERSON_ID_PLAYER], PERSON_ID_ADMIN);

		// Empty notes don't get added
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'note', 'person' => PERSON_ID_PLAYER],
			PERSON_ID_ADMIN, [
				'person_id' => PERSON_ID_PLAYER,
				'visibility' => VISIBILITY_PRIVATE,
				'note' => '',
			], ['action' => 'view', 'person' => PERSON_ID_PLAYER], 'You entered no text, so no note was added.');

		// Add a private note
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'note', 'person' => PERSON_ID_PLAYER],
			PERSON_ID_ADMIN, [
				'person_id' => PERSON_ID_PLAYER,
				'visibility' => VISIBILITY_PRIVATE,
				'note' => 'This is a private note.',
			], ['action' => 'view', 'person' => PERSON_ID_PLAYER], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertEmpty(Configure::read('test_emails'));

		// Add a note for all admins to see
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'note', 'person' => PERSON_ID_PLAYER],
			PERSON_ID_ADMIN, [
				'person_id' => PERSON_ID_PLAYER,
				'visibility' => VISIBILITY_ADMIN,
				'note' => 'This is an admin note.',
			], ['action' => 'view', 'person' => PERSON_ID_PLAYER], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertEmpty(Configure::read('test_emails'));

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_PLAYER], PERSON_ID_ADMIN);
		$this->assertResponseContains('This is a private note.');
		$this->assertResponseContains('This is an admin note.');

		// Check the manager can also see the admin one
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_PLAYER], PERSON_ID_MANAGER);
		$this->assertResponseNotContains('This is a private note.');
		$this->assertResponseContains('This is an admin note.');

		// Empty notes don't get added
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'note', 'person' => PERSON_ID_PLAYER],
			PERSON_ID_ADMIN, [
				'person_id' => PERSON_ID_PLAYER,
				'visibility' => VISIBILITY_PRIVATE,
				'note' => '',
			], ['action' => 'view', 'person' => PERSON_ID_PLAYER], 'You entered no text, so no note was added.');

		// Add a private note
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'note', 'person' => PERSON_ID_PLAYER],
			PERSON_ID_ADMIN, [
				'person_id' => PERSON_ID_PLAYER,
				'visibility' => VISIBILITY_PRIVATE,
				'note' => 'This is a private note.',
			], ['action' => 'view', 'person' => PERSON_ID_PLAYER], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertEmpty(Configure::read('test_emails'));

		// Add a note for all admins to see
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'note', 'person' => PERSON_ID_PLAYER],
			PERSON_ID_ADMIN, [
				'person_id' => PERSON_ID_PLAYER,
				'visibility' => VISIBILITY_ADMIN,
				'note' => 'This is an admin note.',
			], ['action' => 'view', 'person' => PERSON_ID_PLAYER], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertEmpty(Configure::read('test_emails'));

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_PLAYER], PERSON_ID_ADMIN);
		$this->assertResponseContains('This is a private note.');
		$this->assertResponseContains('This is an admin note.');

		// Check the manager can also see the admin one
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_PLAYER], PERSON_ID_MANAGER);
		$this->assertResponseNotContains('This is a private note.');
		$this->assertResponseContains('This is an admin note.');
	}

	/**
	 * Test note method as a manager
	 *
	 * @return void
	 */
	public function testNoteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to add notes
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'note', 'person' => PERSON_ID_PLAYER], PERSON_ID_MANAGER);
		// Add a private note
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'note', 'person' => PERSON_ID_PLAYER],
			PERSON_ID_MANAGER, [
				'person_id' => PERSON_ID_PLAYER,
				'visibility' => VISIBILITY_PRIVATE,
				'note' => 'This is a private note.',
			], ['action' => 'view', 'person' => PERSON_ID_PLAYER], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertEmpty(Configure::read('test_emails'));

		// Add a note for all admins to see
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'note', 'person' => PERSON_ID_PLAYER],
			PERSON_ID_MANAGER, [
				'person_id' => PERSON_ID_PLAYER,
				'visibility' => VISIBILITY_ADMIN,
				'note' => 'This is an admin note.',
			], ['action' => 'view', 'person' => PERSON_ID_PLAYER], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertEmpty(Configure::read('test_emails'));

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_PLAYER], PERSON_ID_MANAGER);
		$this->assertResponseContains('This is a private note.');
		$this->assertResponseContains('This is an admin note.');

		// Check the admin can also see the admin one
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_PLAYER], PERSON_ID_ADMIN);
		$this->assertResponseNotContains('This is a private note.');
		$this->assertResponseContains('This is an admin note.');
	}

	/**
	 * Test note method as a coordinator
	 *
	 * @return void
	 */
	public function testNoteAsCoordinator() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Coordinators are allowed to add notes
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'note', 'person' => PERSON_ID_PLAYER], PERSON_ID_COORDINATOR);

		// Add a private note
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'note', 'person' => PERSON_ID_PLAYER],
			PERSON_ID_COORDINATOR, [
				'person_id' => PERSON_ID_PLAYER,
				'visibility' => VISIBILITY_PRIVATE,
				'note' => 'This is a private note.',
			], ['action' => 'view', 'person' => PERSON_ID_PLAYER], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertEmpty(Configure::read('test_emails'));

		// Make sure it was added successfully
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_PLAYER], PERSON_ID_COORDINATOR);
		$this->assertResponseContains('This is a private note.');
	}

	/**
	 * Test note method as a captain
	 *
	 * @return void
	 */
	public function testNoteAsCaptain() {
		// Captains are allowed to add notes
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'note', 'person' => PERSON_ID_PLAYER], PERSON_ID_CAPTAIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test note method as a player
	 *
	 * @return void
	 */
	public function testNoteAsPlayer() {
		// Players are allowed to add notes
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'note', 'person' => PERSON_ID_CAPTAIN], PERSON_ID_PLAYER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test note method as someone else
	 *
	 * @return void
	 */
	public function testNoteAsVisitor() {
		// Visitors are allowed to add notes
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'note', 'person' => PERSON_ID_PLAYER], PERSON_ID_VISITOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test note method without being logged in
	 *
	 * @return void
	 */
	public function testNoteAsAnonymous() {
		// Others are not allowed to add notes
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'note', 'person' => PERSON_ID_PLAYER]);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_note method as an admin
	 *
	 * @return void
	 */
	public function testDeleteNoteAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to delete admin notes
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'delete_note', 'note' => NOTE_ID_PERSON_PLAYER_ADMIN],
			PERSON_ID_ADMIN, [], ['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_PLAYER],
			'The note has been deleted.');

		// But not other notes
		$this->assertPostAsAccessDenied(['controller' => 'People', 'action' => 'delete_note', 'note' => NOTE_ID_PERSON_PLAYER_CAPTAIN],
			PERSON_ID_ADMIN);
		$this->assertPostAsAccessDenied(['controller' => 'People', 'action' => 'delete_note', 'note' => NOTE_ID_PERSON_PLAYER_PLAYER],
			PERSON_ID_ADMIN);
		$this->assertPostAsAccessDenied(['controller' => 'People', 'action' => 'delete_note', 'note' => NOTE_ID_PERSON_PLAYER_VISITOR],
			PERSON_ID_ADMIN);

		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_note method as a manager
	 *
	 * @return void
	 */
	public function testDeleteNoteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to delete admin notes
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'delete_note', 'note' => NOTE_ID_PERSON_PLAYER_ADMIN],
			PERSON_ID_MANAGER, [], ['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_PLAYER],
			'The note has been deleted.');

		// But not other notes
		$this->assertPostAsAccessDenied(['controller' => 'People', 'action' => 'delete_note', 'note' => NOTE_ID_PERSON_PLAYER_CAPTAIN],
			PERSON_ID_MANAGER);
		$this->assertPostAsAccessDenied(['controller' => 'People', 'action' => 'delete_note', 'note' => NOTE_ID_PERSON_PLAYER_PLAYER],
			PERSON_ID_MANAGER);
		$this->assertPostAsAccessDenied(['controller' => 'People', 'action' => 'delete_note', 'note' => NOTE_ID_PERSON_PLAYER_VISITOR],
			PERSON_ID_MANAGER);

		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_note method as a captain
	 *
	 * @return void
	 */
	public function testDeleteNoteAsCaptain() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Captains are only allowed to delete notes they created
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'delete_note', 'note' => NOTE_ID_PERSON_PLAYER_CAPTAIN],
			PERSON_ID_CAPTAIN, [], ['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_PLAYER],
			'The note has been deleted.');

		// But not other notes
		$this->assertPostAsAccessDenied(['controller' => 'People', 'action' => 'delete_note', 'note' => NOTE_ID_PERSON_PLAYER_ADMIN],
			PERSON_ID_CAPTAIN);
		$this->assertPostAsAccessDenied(['controller' => 'People', 'action' => 'delete_note', 'note' => NOTE_ID_PERSON_PLAYER_PLAYER],
			PERSON_ID_CAPTAIN);
		$this->assertPostAsAccessDenied(['controller' => 'People', 'action' => 'delete_note', 'note' => NOTE_ID_PERSON_PLAYER_VISITOR],
			PERSON_ID_CAPTAIN);

		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_note method as a player
	 *
	 * @return void
	 */
	public function testDeleteNoteAsPlayer() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Players are only allowed to delete notes they created
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'delete_note', 'note' => NOTE_ID_PERSON_PLAYER_PLAYER],
			PERSON_ID_PLAYER, [], ['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_PLAYER],
			'The note has been deleted.');

		// But not other notes
		$this->assertPostAsAccessDenied(['controller' => 'People', 'action' => 'delete_note', 'note' => NOTE_ID_PERSON_PLAYER_ADMIN],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'People', 'action' => 'delete_note', 'note' => NOTE_ID_PERSON_PLAYER_CAPTAIN],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'People', 'action' => 'delete_note', 'note' => NOTE_ID_PERSON_PLAYER_VISITOR],
			PERSON_ID_PLAYER);

		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_note method as someone else
	 *
	 * @return void
	 */
	public function testDeleteNoteAsVisitor() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Visitors are only allowed to delete notes they created
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'delete_note', 'note' => NOTE_ID_PERSON_PLAYER_VISITOR],
			PERSON_ID_VISITOR, [], ['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_PLAYER],
			'The note has been deleted.');

		// But not other notes
		$this->assertPostAsAccessDenied(['controller' => 'People', 'action' => 'delete_note', 'note' => NOTE_ID_PERSON_PLAYER_ADMIN],
			PERSON_ID_VISITOR);
		$this->assertPostAsAccessDenied(['controller' => 'People', 'action' => 'delete_note', 'note' => NOTE_ID_PERSON_PLAYER_CAPTAIN],
			PERSON_ID_VISITOR);
		$this->assertPostAsAccessDenied(['controller' => 'People', 'action' => 'delete_note', 'note' => NOTE_ID_PERSON_PLAYER_PLAYER],
			PERSON_ID_VISITOR);

		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_note method without being logged in
	 *
	 * @return void
	 */
	public function testDeleteNoteAsAnonymous() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Others are not allowed to delete notes
		$this->assertPostAjaxAnonymousAccessDenied(['controller' => 'People', 'action' => 'delete_note', 'note' => NOTE_ID_PERSON_PLAYER_PLAYER]);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test preferences method as an admin
	 *
	 * @return void
	 */
	public function testPreferencesAsAdmin() {
		// Admins are allowed to edit their preferences
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'preferences'], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test preferences method as a manager
	 *
	 * @return void
	 */
	public function testPreferencesAsManager() {
		// Managers are allowed to edit their preferences
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'preferences'], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test preferences method as a coordinator
	 *
	 * @return void
	 */
	public function testPreferencesAsCoordinator() {
		// Coordinators are allowed to edit their preferences
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'preferences'], PERSON_ID_COORDINATOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test preferences method as a captain
	 *
	 * @return void
	 */
	public function testPreferencesAsCaptain() {
		// Captains are allowed to edit their preferences
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'preferences'], PERSON_ID_CAPTAIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test preferences method as a player
	 *
	 * @return void
	 */
	public function testPreferencesAsPlayer() {
		// Players are allowed to edit their preferences
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'preferences'], PERSON_ID_PLAYER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test preferences method as someone else
	 *
	 * @return void
	 */
	public function testPreferencesAsVisitor() {
		// Visitors are allowed to edit their preferences
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'preferences'], PERSON_ID_VISITOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test preferences method without being logged in
	 *
	 * @return void
	 */
	public function testPreferencesAsAnonymous() {
		// Others are allowed to edit their preferences
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'preferences']);
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

		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'add_relative'],
			PERSON_ID_PLAYER, [
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
			'/', 'The new profile has been saved. It must be approved by an administrator before you will have full access to the site.'
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
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'link_relative'], PERSON_ID_PLAYER);

		// Try the search page
		$this->assertPostAsAccessOk(['controller' => 'People', 'action' => 'link_relative'],
			PERSON_ID_PLAYER, [
				'affiliate_id' => '1',
				'first_name' => '',
				'last_name' => 'captain',
				'sort' => 'last_name',
				'direction' => 'asc',
			]
		);
		$this->assertResponseContains('/people/link_relative?relative=' . PERSON_ID_CAPTAIN . '&amp;person=' . PERSON_ID_PLAYER);
		$this->assertResponseContains('/people/link_relative?relative=' . PERSON_ID_CAPTAIN2 . '&amp;person=' . PERSON_ID_PLAYER);

		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'link_relative', 'relative' => PERSON_ID_CAPTAIN, 'person' => PERSON_ID_PLAYER],
			PERSON_ID_PLAYER, '/',
			'Linked Crystal Captain as relative; you will not have access to their information until they have approved this.');

		// Confirm the notification email
		$messages = Configure::read('test_emails');
		$this->assertEquals(1, count($messages));

		$this->assertContains('From: &quot;Admin&quot; &lt;admin@zuluru.org&gt;', $messages[0]);
		$this->assertContains('Reply-To: &quot;Pam Player&quot; &lt;pam@zuluru.org&gt;', $messages[0]);
		$this->assertContains('To: &quot;Crystal Captain&quot; &lt;crystal@zuluru.org&gt;', $messages[0]);
		$this->assertNotContains('CC: ', $messages[0]);
		$this->assertContains('Subject: You have been linked as a relative', $messages[0]);
		$this->assertContains('Pam Player has indicated on the Test Zuluru Affiliate web site that you are related to them.', $messages[0]);
		$this->assertContains('If you accept, Pam will be granted access', $messages[0]);
		$this->assertRegExp('#Accept the request here:\s*' . Configure::read('App.fullBaseUrl') . Configure::read('App.base') . '/people/approve_relative\?person=' . PERSON_ID_CAPTAIN . '&relative=' . PERSON_ID_PLAYER . '#ms', $messages[0]);
		$this->assertRegExp('#Decline the request here:\s*' . Configure::read('App.fullBaseUrl') . Configure::read('App.base') . '/people/remove_relative\?person=' . PERSON_ID_CAPTAIN . '&relative=' . PERSON_ID_PLAYER . '#ms', $messages[0]);

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view'], PERSON_ID_PLAYER);
		$this->assertResponseContains('/people/remove_relative?person=' . PERSON_ID_PLAYER . '&amp;relative=' . PERSON_ID_CAPTAIN);
	}

	/**
	 * Test link_relative method without being logged in
	 *
	 * @return void
	 */
	public function testLinkRelativeAsAnonymous() {
		// Others are not allowed to link relatives
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'link_relative']);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_relative method
	 *
	 * @return void
	 */
	public function testApproveRelative() {
		// The invited relative is allowed to approve the request
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'approve_relative', 'person' => PERSON_ID_CAPTAIN, 'relative' => PERSON_ID_CAPTAIN2],
			PERSON_ID_CAPTAIN, ['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_CAPTAIN],
			'Approved the relative request.');

		// Confirm the notification email
		$messages = Configure::read('test_emails');
		$this->assertEquals(1, count($messages));

		$this->assertContains('From: &quot;Admin&quot; &lt;admin@zuluru.org&gt;', $messages[0]);
		$this->assertContains('Reply-To: &quot;Crystal Captain&quot; &lt;crystal@zuluru.org&gt;', $messages[0]);
		$this->assertContains('To: &quot;Chuck Captain&quot; &lt;chuck@zuluru.org&gt;', $messages[0]);
		$this->assertNotContains('CC: ', $messages[0]);
		$this->assertContains('Subject: Crystal Captain approved your relative request', $messages[0]);
		$this->assertContains('Your relative request to Crystal Captain on the Test Zuluru Affiliate web site has been approved.', $messages[0]);

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_CAPTAIN], PERSON_ID_CAPTAIN);
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
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'remove_relative', 'person' => PERSON_ID_CAPTAIN, 'relative' => PERSON_ID_CAPTAIN2],
			PERSON_ID_CAPTAIN, ['controller' => 'People', 'action' => 'view'],
			'Removed the relation.');

		// Confirm the notification email
		$messages = Configure::read('test_emails');
		$this->assertEquals(1, count($messages));

		$this->assertContains('From: &quot;Admin&quot; &lt;admin@zuluru.org&gt;', $messages[0]);
		$this->assertContains('Reply-To: &quot;Crystal Captain&quot; &lt;crystal@zuluru.org&gt;', $messages[0]);
		$this->assertContains('To: &quot;Chuck Captain&quot; &lt;chuck@zuluru.org&gt;', $messages[0]);
		$this->assertNotContains('CC: ', $messages[0]);
		$this->assertContains('Subject: Crystal Captain removed your relation', $messages[0]);
		$this->assertContains('Crystal Captain has removed you as a relative on the Test Zuluru Affiliate web site.', $messages[0]);

		// Make sure they were removed successfully
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_CAPTAIN], PERSON_ID_CAPTAIN);
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
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'remove_relative', 'person' => PERSON_ID_CAPTAIN2, 'relative' => PERSON_ID_CAPTAIN],
			PERSON_ID_CAPTAIN, ['controller' => 'People', 'action' => 'view'],
			'Removed the relation.');

		// Confirm the notification email
		$messages = Configure::read('test_emails');
		$this->assertEquals(1, count($messages));

		$this->assertContains('From: &quot;Admin&quot; &lt;admin@zuluru.org&gt;', $messages[0]);
		$this->assertContains('Reply-To: &quot;Crystal Captain&quot; &lt;crystal@zuluru.org&gt;', $messages[0]);
		$this->assertContains('To: &quot;Chuck Captain&quot; &lt;chuck@zuluru.org&gt;', $messages[0]);
		$this->assertNotContains('CC: ', $messages[0]);
		$this->assertContains('Subject: Crystal Captain removed your relation', $messages[0]);
		$this->assertContains('Crystal Captain has removed you as a relative on the Test Zuluru Affiliate web site.', $messages[0]);

		// Make sure they were removed successfully
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_CAPTAIN], PERSON_ID_CAPTAIN);
		$this->assertResponseRegExp('#<td>You can control <a[^>]*>Chuck Captain</a></td>#ms');
		$this->assertResponseNotRegExp('#<a[^>]*>Chuck Captain</a> can control you</td>#ms');
	}

	/**
	 * Test remove_relative method as someone else
	 *
	 * @return void
	 */
	public function testRemoveRelativeAsOthers() {
		// Others are not allowed to remove relatives
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'remove_relative', 'person' => PERSON_ID_PLAYER, 'relative' => PERSON_ID_CAPTAIN2],
			PERSON_ID_PLAYER, ['controller' => 'People', 'action' => 'view', 'person' => PERSON_ID_PLAYER],
			'The authorization code is invalid.');

		// TODO: Test with codes
		// TODO: Test as admin / manager
	}

	/**
	 * Test authorize_twitter method as an admin
	 *
	 * @return void
	 */
	public function testAuthorizeTwitterAsAdmin() {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test authorize_twitter method as a manager
	 *
	 * @return void
	 */
	public function testAuthorizeTwitterAsManager() {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test authorize_twitter method as a coordinator
	 *
	 * @return void
	 */
	public function testAuthorizeTwitterAsCoordinator() {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test authorize_twitter method as a captain
	 *
	 * @return void
	 */
	public function testAuthorizeTwitterAsCaptain() {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test authorize_twitter method as a player
	 *
	 * @return void
	 */
	public function testAuthorizeTwitterAsPlayer() {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test authorize_twitter method as someone else
	 *
	 * @return void
	 */
	public function testAuthorizeTwitterAsVisitor() {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test authorize_twitter method without being logged in
	 *
	 * @return void
	 */
	public function testAuthorizeTwitterAsAnonymous() {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test revoke_twitter method as an admin
	 *
	 * @return void
	 */
	public function testRevokeTwitterAsAdmin() {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test revoke_twitter method as a manager
	 *
	 * @return void
	 */
	public function testRevokeTwitterAsManager() {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test revoke_twitter method as a coordinator
	 *
	 * @return void
	 */
	public function testRevokeTwitterAsCoordinator() {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test revoke_twitter method as a captain
	 *
	 * @return void
	 */
	public function testRevokeTwitterAsCaptain() {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test revoke_twitter method as a player
	 *
	 * @return void
	 */
	public function testRevokeTwitterAsPlayer() {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test revoke_twitter method as someone else
	 *
	 * @return void
	 */
	public function testRevokeTwitterAsVisitor() {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test revoke_twitter method without being logged in
	 *
	 * @return void
	 */
	public function testRevokeTwitterAsAnonymous() {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test photo method
	 *
	 * @return void
	 */
	public function testPhoto() {
		// Anyone logged in is allowed to view photos
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'photo', 'person' => PERSON_ID_CAPTAIN], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'photo', 'person' => PERSON_ID_CAPTAIN], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'photo', 'person' => PERSON_ID_CAPTAIN], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'photo', 'person' => PERSON_ID_CAPTAIN], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'photo', 'person' => PERSON_ID_CAPTAIN], PERSON_ID_PLAYER);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'photo', 'person' => PERSON_ID_CAPTAIN], PERSON_ID_VISITOR);

		// Others are not allowed to view photos
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'photo', 'person' => PERSON_ID_CAPTAIN]);
	}

	/**
	 * Test photo_upload method as an admin
	 *
	 * @return void
	 */
	public function testPhotoUploadAsAdmin() {
		// Admins are allowed to upload photos
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'photo_upload'], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test photo_upload method as a manager
	 *
	 * @return void
	 */
	public function testPhotoUploadAsManager() {
		// Managers are allowed to upload photos
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'photo_upload'], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test photo_upload method as a coordinator
	 *
	 * @return void
	 */
	public function testPhotoUploadAsCoordinator() {
		// Coordinators are allowed to upload photos
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'photo_upload'], PERSON_ID_COORDINATOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test photo_upload method as a captain
	 *
	 * @return void
	 */
	public function testPhotoUploadAsCaptain() {
		// Captains are allowed to upload photos
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'photo_upload'], PERSON_ID_CAPTAIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test photo_upload method as a player
	 *
	 * @return void
	 */
	public function testPhotoUploadAsPlayer() {
		// Players are allowed to upload photos
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'photo_upload'], PERSON_ID_PLAYER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test photo_upload method as someone else
	 *
	 * @return void
	 */
	public function testPhotoUploadAsVisitor() {
		// Visitors are allowed to upload photos
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'photo_upload'], PERSON_ID_VISITOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test photo_upload method without being logged in
	 *
	 * @return void
	 */
	public function testPhotoUploadAsAnonymous() {
		// Others are not allowed to upload photos
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'photo_upload']);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_photos method
	 *
	 * @return void
	 */
	public function testApprovePhotos() {
		// Admins are allowed to approve photos
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'approve_photos'], PERSON_ID_ADMIN);

		// Managers are allowed to approve photos
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'approve_photos'], PERSON_ID_MANAGER);

		// Others are not allowed to approve photos
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'approve_photos'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'approve_photos'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'approve_photos'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'approve_photos'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'approve_photos']);
	}

	/**
	 * Test approve_photo method as an admin
	 *
	 * @return void
	 */
	public function testApprovePhotoAsAdmin() {
		// Admins are allowed to approve photo
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'approve_photo', 'person' => PERSON_ID_PLAYER],
			PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_photo method as a manager
	 *
	 * @return void
	 */
	public function testApprovePhotoAsManager() {
		// Managers are allowed to approve photos
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'approve_photo', 'person' => PERSON_ID_PLAYER],
			PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_photo method as others
	 *
	 * @return void
	 */
	public function testApprovePhotoAsOthers() {
		// Others are not allowed to approve photos
		$this->assertGetAjaxAsAccessDenied(['controller' => 'People', 'action' => 'approve_photo', 'person' => PERSON_ID_PLAYER],
			PERSON_ID_COORDINATOR);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'People', 'action' => 'approve_photo', 'person' => PERSON_ID_PLAYER],
			PERSON_ID_CAPTAIN);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'People', 'action' => 'approve_photo', 'person' => PERSON_ID_PLAYER],
			PERSON_ID_PLAYER);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'People', 'action' => 'approve_photo', 'person' => PERSON_ID_PLAYER],
			PERSON_ID_VISITOR);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'People', 'action' => 'approve_photo', 'person' => PERSON_ID_PLAYER]);
	}

	/**
	 * Test delete_photo method as an admin
	 *
	 * @return void
	 */
	public function testDeletePhotoAsAdmin() {
		// Admins are allowed to delete photos
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'delete_photo', 'person' => PERSON_ID_PLAYER],
			PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_photo method as a manager
	 *
	 * @return void
	 */
	public function testDeletePhotoAsManager() {
		// Managers are allowed to delete photos
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'delete_photo', 'person' => PERSON_ID_PLAYER],
			PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_photo method as others
	 *
	 * @return void
	 */
	public function testDeletePhotoAsOthers() {
		// Others are not allowed to delete photos
		$this->assertGetAjaxAsAccessDenied(['controller' => 'People', 'action' => 'delete_photo', 'person' => PERSON_ID_PLAYER],
			PERSON_ID_COORDINATOR);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'People', 'action' => 'delete_photo', 'person' => PERSON_ID_PLAYER],
			PERSON_ID_CAPTAIN);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'People', 'action' => 'delete_photo', 'person' => PERSON_ID_PLAYER],
			PERSON_ID_PLAYER);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'People', 'action' => 'delete_photo', 'person' => PERSON_ID_PLAYER],
			PERSON_ID_VISITOR);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'People', 'action' => 'delete_photo', 'person' => PERSON_ID_PLAYER]);
	}

	/**
	 * Test document method
	 *
	 * @return void
	 */
	public function testDocument() {
		// Admins are allowed to view documents
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'document', 'document' => UPLOAD_ID_CHILD_WAIVER], PERSON_ID_ADMIN);

		// Managers are allowed to view documents
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'document', 'document' => UPLOAD_ID_CHILD_WAIVER], PERSON_ID_MANAGER);

		// Others are not allowed to view documents
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'document', 'document' => UPLOAD_ID_CHILD_WAIVER], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'document', 'document' => UPLOAD_ID_CHILD_WAIVER], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'document', 'document' => UPLOAD_ID_CHILD_WAIVER], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'document', 'document' => UPLOAD_ID_CHILD_WAIVER], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'document', 'document' => UPLOAD_ID_CHILD_WAIVER]);
	}

	/**
	 * Test document_upload method as an admin
	 *
	 * @return void
	 */
	public function testDocumentUploadAsAdmin() {
		// Admins are allowed to upload documents
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'document_upload'], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test document_upload method as a manager
	 *
	 * @return void
	 */
	public function testDocumentUploadAsManager() {
		// Managers are allowed to upload documents
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'document_upload'], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test document_upload method as a coordinator
	 *
	 * @return void
	 */
	public function testDocumentUploadAsCoordinator() {
		// Coordinators are allowed to upload documents
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'document_upload'], PERSON_ID_COORDINATOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test document_upload method as a captain
	 *
	 * @return void
	 */
	public function testDocumentUploadAsCaptain() {
		// Captains are allowed to upload documents
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'document_upload'], PERSON_ID_CAPTAIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test document_upload method as a player
	 *
	 * @return void
	 */
	public function testDocumentUploadAsPlayer() {
		// Players are allowed to upload documents
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'document_upload'], PERSON_ID_PLAYER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test document_upload method as someone else
	 *
	 * @return void
	 */
	public function testDocumentUploadAsVisitor() {
		// Visitors are allowed to upload documents
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'document_upload'], PERSON_ID_VISITOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test document_upload method without being logged in
	 *
	 * @return void
	 */
	public function testDocumentUploadAsAnonymous() {
		// Others are not allowed to upload documents
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'document_upload']);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_documents method
	 *
	 * @return void
	 */
	public function testApproveDocuments() {
		// Admins are allowed to approve documents
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'approve_documents'], PERSON_ID_ADMIN);

		// Managers are allowed to approve documents
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'approve_documents'], PERSON_ID_MANAGER);

		// Others are not allowed to approve documents
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'approve_documents'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'approve_documents'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'approve_documents'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'approve_documents'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'approve_documents']);
	}

	/**
	 * Test approve_document method as an admin
	 *
	 * @return void
	 */
	public function testApproveDocumentAsAdmin() {
		// Admins are allowed to approve documents
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'approve_document', 'document' => UPLOAD_ID_DOG_WAIVER], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_document method as a manager
	 *
	 * @return void
	 */
	public function testApproveDocumentAsManager() {
		// Managers are allowed to approve documents
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'approve_document', 'document' => UPLOAD_ID_DOG_WAIVER], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_document method as others
	 *
	 * @return void
	 */
	public function testApproveDocumentAsOthers() {
		// Others are not allowed to approve documents
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'approve_document', 'document' => UPLOAD_ID_DOG_WAIVER], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'approve_document', 'document' => UPLOAD_ID_DOG_WAIVER], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'approve_document', 'document' => UPLOAD_ID_DOG_WAIVER], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'approve_document', 'document' => UPLOAD_ID_DOG_WAIVER], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'approve_document', 'document' => UPLOAD_ID_DOG_WAIVER]);
	}

	/**
	 * Test edit_document method as an admin
	 *
	 * @return void
	 */
	public function testEditDocumentAsAdmin() {
		// Admins are allowed to edit documents
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'edit_document', 'document' => UPLOAD_ID_CHILD_WAIVER], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit_document method as a manager
	 *
	 * @return void
	 */
	public function testEditDocumentAsManager() {
		// Managers are allowed to edit documents
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'edit_document', 'document' => UPLOAD_ID_CHILD_WAIVER], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit_document method as others
	 *
	 * @return void
	 */
	public function testEditDocumentAsOthers() {
		// Others are not allowed to edit documents
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'edit_document', 'document' => UPLOAD_ID_CHILD_WAIVER], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'edit_document', 'document' => UPLOAD_ID_CHILD_WAIVER], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'edit_document', 'document' => UPLOAD_ID_CHILD_WAIVER], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'edit_document', 'document' => UPLOAD_ID_CHILD_WAIVER], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'edit_document', 'document' => UPLOAD_ID_CHILD_WAIVER]);
	}

	/**
	 * Test delete_document method as an admin
	 *
	 * @return void
	 */
	public function testDeleteDocumentAsAdmin() {
		// Admins are allowed to delete documents
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'delete_document', 'document' => UPLOAD_ID_CHILD_WAIVER],
			PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_document method as a manager
	 *
	 * @return void
	 */
	public function testDeleteDocumentAsManager() {
		// Managers are allowed to delete documents
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'delete_document', 'document' => UPLOAD_ID_CHILD_WAIVER],
			PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_document method as others
	 *
	 * @return void
	 */
	public function testDeleteDocumentAsOthers() {
		// Others are not allowed to delete documents
		$this->assertGetAjaxAsAccessDenied(['controller' => 'People', 'action' => 'delete_document', 'document' => UPLOAD_ID_CHILD_WAIVER],
			PERSON_ID_COORDINATOR);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'People', 'action' => 'delete_document', 'document' => UPLOAD_ID_CHILD_WAIVER],
			PERSON_ID_CAPTAIN);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'People', 'action' => 'delete_document', 'document' => UPLOAD_ID_CHILD_WAIVER],
			PERSON_ID_PLAYER);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'People', 'action' => 'delete_document', 'document' => UPLOAD_ID_CHILD_WAIVER],
			PERSON_ID_VISITOR);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'People', 'action' => 'delete_document', 'document' => UPLOAD_ID_CHILD_WAIVER]);
	}

	/**
	 * Test nominate method as an admin
	 *
	 * @return void
	 */
	public function testNominateAsAdmin() {
		// Admins are allowed to nominate
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'nominate'], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'nominate_badge', 'badge' => BADGE_ID_HALL_OF_FAME], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'nominate_badge_reason', 'badge' => BADGE_ID_HALL_OF_FAME, 'person' => PERSON_ID_INACTIVE], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test nominate method as a manager
	 *
	 * @return void
	 */
	public function testNominateAsManager() {
		// Managers are allowed to nominate
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'nominate'], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'nominate_badge', 'badge' => BADGE_ID_HALL_OF_FAME], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'nominate_badge_reason', 'badge' => BADGE_ID_HALL_OF_FAME, 'person' => PERSON_ID_INACTIVE], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test nominate method as a coordinator
	 *
	 * @return void
	 */
	public function testNominateAsCoordinator() {
		// Coordinators are allowed to nominate
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'nominate'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'nominate_badge', 'badge' => BADGE_ID_HALL_OF_FAME], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'nominate_badge_reason', 'badge' => BADGE_ID_HALL_OF_FAME, 'person' => PERSON_ID_INACTIVE], PERSON_ID_COORDINATOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test nominate method as a captain
	 *
	 * @return void
	 */
	public function testNominateAsCaptain() {
		// Captains are allowed to nominate
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'nominate'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'nominate_badge', 'badge' => BADGE_ID_HALL_OF_FAME], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'nominate_badge_reason', 'badge' => BADGE_ID_HALL_OF_FAME, 'person' => PERSON_ID_INACTIVE], PERSON_ID_CAPTAIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test nominate method as a player
	 *
	 * @return void
	 */
	public function testNominateAsPlayer() {
		// Players are allowed to nominate
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'nominate'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'nominate_badge', 'badge' => BADGE_ID_HALL_OF_FAME], PERSON_ID_PLAYER);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'nominate_badge_reason', 'badge' => BADGE_ID_HALL_OF_FAME, 'person' => PERSON_ID_INACTIVE], PERSON_ID_PLAYER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test nominate method as someone else
	 *
	 * @return void
	 */
	public function testNominateAsVisitor() {
		// Visitors are allowed to nominate
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'nominate'], PERSON_ID_VISITOR);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'nominate_badge', 'badge' => BADGE_ID_HALL_OF_FAME], PERSON_ID_VISITOR);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'nominate_badge_reason', 'badge' => BADGE_ID_HALL_OF_FAME, 'person' => PERSON_ID_INACTIVE], PERSON_ID_VISITOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test nominate method without being logged in
	 *
	 * @return void
	 */
	public function testNominateAsAnonymous() {
		// Others are not allowed to nominate
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'nominate']);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'nominate_badge', 'badge' => BADGE_ID_HALL_OF_FAME]);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'nominate_badge_reason', 'badge' => BADGE_ID_HALL_OF_FAME, 'person' => PERSON_ID_INACTIVE]);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_badges method
	 *
	 * @return void
	 */
	public function testApproveBadges() {
		// Admins are allowed to approve badges
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'approve_badges'], PERSON_ID_ADMIN);

		// Managers are allowed to approve badges
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'approve_badges'], PERSON_ID_MANAGER);

		// Others are not allowed to approve badges
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'approve_badges'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'approve_badges'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'approve_badges'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'approve_badges'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'approve_badges']);
	}

	/**
	 * Test approve_badge method as an admin
	 *
	 * @return void
	 */
	public function testApproveBadgeAsAdmin() {
		// Admins are allowed to approve badges
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'approve_badge', 'badge' => BADGE_ID_FOR_HALL_OF_FAME_CAPTAIN],
			PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_badge method as a manager
	 *
	 * @return void
	 */
	public function testApproveBadgeAsManager() {
		// Managers are allowed to approve badges
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'approve_badge', 'badge' => BADGE_ID_FOR_HALL_OF_FAME_CAPTAIN],
			PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_badge method as others
	 *
	 * @return void
	 */
	public function testApproveBadgeAsOthers() {
		// Others are not allowed to approve badges
		$this->assertGetAjaxAsAccessDenied(['controller' => 'People', 'action' => 'approve_badge', 'badge' => BADGE_ID_FOR_HALL_OF_FAME_CAPTAIN],
			PERSON_ID_COORDINATOR);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'People', 'action' => 'approve_badge', 'badge' => BADGE_ID_FOR_HALL_OF_FAME_CAPTAIN],
			PERSON_ID_CAPTAIN);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'People', 'action' => 'approve_badge', 'badge' => BADGE_ID_FOR_HALL_OF_FAME_CAPTAIN],
			PERSON_ID_PLAYER);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'People', 'action' => 'approve_badge', 'badge' => BADGE_ID_FOR_HALL_OF_FAME_CAPTAIN],
			PERSON_ID_VISITOR);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'People', 'action' => 'approve_badge', 'badge' => BADGE_ID_FOR_HALL_OF_FAME_CAPTAIN]);
	}

	/**
	 * Test delete_badge method as an admin
	 *
	 * @return void
	 */
	public function testDeleteBadgeAsAdmin() {
		$this->enableCsrfToken();

		// Admins are allowed to delete badges
		$this->assertPostAjaxAsAccessOk(['controller' => 'People', 'action' => 'delete_badge', 'badge' => BADGE_ID_FOR_HALL_OF_FAME_CAPTAIN],
			PERSON_ID_ADMIN, ['comment' => 'No badge for you.']);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_badge method as a manager
	 *
	 * @return void
	 */
	public function testDeleteBadgeAsManager() {
		$this->enableCsrfToken();

		// Managers are allowed to delete badges
		$this->assertPostAjaxAsAccessOk(['controller' => 'People', 'action' => 'delete_badge', 'badge' => BADGE_ID_FOR_HALL_OF_FAME_CAPTAIN],
			PERSON_ID_MANAGER, ['comment' => 'No badge for you.']);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_badge method as others
	 *
	 * @return void
	 */
	public function testDeleteBadgeAsOthers() {
		$this->enableCsrfToken();

		// Others are not allowed to delete badges
		$this->assertPostAjaxAsAccessDenied(['controller' => 'People', 'action' => 'delete_badge', 'badge' => BADGE_ID_FOR_HALL_OF_FAME_CAPTAIN],
			PERSON_ID_COORDINATOR, ['comment' => 'No badge for you.']);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'People', 'action' => 'delete_badge', 'badge' => BADGE_ID_FOR_HALL_OF_FAME_CAPTAIN],
			PERSON_ID_CAPTAIN, ['comment' => 'No badge for you.']);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'People', 'action' => 'delete_badge', 'badge' => BADGE_ID_FOR_HALL_OF_FAME_CAPTAIN],
			PERSON_ID_PLAYER, ['comment' => 'No badge for you.']);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'People', 'action' => 'delete_badge', 'badge' => BADGE_ID_FOR_HALL_OF_FAME_CAPTAIN],
			PERSON_ID_VISITOR, ['comment' => 'No badge for you.']);
		$this->assertPostAjaxAnonymousAccessDenied(['controller' => 'People', 'action' => 'delete_badge', 'badge' => BADGE_ID_FOR_HALL_OF_FAME_CAPTAIN],
			['comment' => 'No badge for you.']);
	}

	/**
	 * Test delete method as an admin
	 *
	 * @return void
	 */
	public function testDeleteAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to delete people
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'delete', 'person' => PERSON_ID_VISITOR],
			PERSON_ID_ADMIN, [], '/',
			'The person has been deleted.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to delete people
		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'delete', 'person' => PERSON_ID_VISITOR],
			PERSON_ID_MANAGER, [], '/',
			'The person has been deleted.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete method as others
	 *
	 * @return void
	 */
	public function testDeleteAsOthers() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Others are not allowed to delete people
		$this->assertPostAsAccessDenied(['controller' => 'People', 'action' => 'delete', 'person' => PERSON_ID_VISITOR],
			PERSON_ID_COORDINATOR);
		$this->assertPostAsAccessDenied(['controller' => 'People', 'action' => 'delete', 'person' => PERSON_ID_VISITOR],
			PERSON_ID_CAPTAIN);
		$this->assertPostAsAccessDenied(['controller' => 'People', 'action' => 'delete', 'person' => PERSON_ID_VISITOR],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'People', 'action' => 'delete', 'person' => PERSON_ID_VISITOR],
			PERSON_ID_VISITOR);
		$this->assertPostAnonymousAccessDenied(['controller' => 'People', 'action' => 'delete', 'person' => PERSON_ID_VISITOR]);
	}

	/**
	 * Test splash method
	 *
	 * @return void
	 */
	public function testSplash() {
		// Include all menu building in these tests
		Configure::write('feature.minimal_menus', false);

		// Anyone is allowed to get the splash page, different roles have different sets of messages
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'splash'], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#The following affiliates do not yet have managers assigned to them:.*/affiliates/edit\?affiliate=2.*/affiliates/delete\?affiliate=2#ms');
		$this->assertResponseContains('There are 1 new <a href="' . Configure::read('App.base') . '/people/list_new">accounts to approve</a>');
		$this->assertResponseNotContains('Recent and Upcoming Schedule');

		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'splash'], PERSON_ID_MANAGER);
		$this->assertResponseNotRegExp('#The following affiliates do not yet have managers assigned to them:.*/affiliates/edit\?affiliate=2.*/affiliates/delete\?affiliate=2#ms');
		$this->assertResponseContains('There are 1 new <a href="' . Configure::read('App.base') . '/people/list_new">accounts to approve</a>');
		$this->assertResponseNotContains('Recent and Upcoming Schedule');

		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'splash'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'splash'], PERSON_ID_CAPTAIN);

		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'splash'], PERSON_ID_PLAYER);
		$this->assertResponseNotRegExp('#The following affiliates do not yet have managers assigned to them:.*/affiliates/edit\?affiliate=2.*/affiliates/delete\?affiliate=2#ms');
		$this->assertResponseNotContains('There are 1 new <a href="' . Configure::read('App.base') . '/people/list_new">accounts to approve</a>');
		$this->assertResponseContains('Recent and Upcoming Schedule');

		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'splash'], PERSON_ID_CAPTAIN);
		$this->assertResponseContains('Recent and Upcoming Schedule');
		$this->assertResponseRegExp('#<div id="ui-tabs-1">.*My Teams.*<div id="ui-tabs-2">.*Chuck\'s Teams.*<div id="ui-tabs-3">.*One moment\.\.\.#ms');

		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'splash'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'splash']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test schedule method
	 *
	 * @return void
	 */
	public function testSchedule() {
		// Anyone logged in is allowed to see their own schedule, and that of confirmed relatives
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'schedule'],
			PERSON_ID_ADMIN);
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'schedule'],
			PERSON_ID_MANAGER);
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'schedule'],
			PERSON_ID_COORDINATOR);
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'schedule'],
			PERSON_ID_CAPTAIN);
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'schedule', 'person' => PERSON_ID_CAPTAIN2],
			PERSON_ID_CAPTAIN);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'People', 'action' => 'schedule', 'person' => PERSON_ID_PLAYER],
			PERSON_ID_CAPTAIN);

		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'schedule'],
			PERSON_ID_PLAYER);
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'schedule'],
			[PERSON_ID_PLAYER, PERSON_ID_CHILD]);

		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'schedule'],
			PERSON_ID_VISITOR);

		// Others are not allowed to get schedules
		$this->assertGetAjaxAsAccessDenied(['controller' => 'People', 'action' => 'schedule', 'person' => PERSON_ID_CAPTAIN],
			PERSON_ID_ADMIN);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'People', 'action' => 'schedule', 'person' => PERSON_ID_PLAYER],
			PERSON_ID_ADMIN);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'People', 'action' => 'schedule', 'person' => PERSON_ID_ANDY_SUB],
			PERSON_ID_COORDINATOR);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'People', 'action' => 'schedule']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test consolidated_schedule method
	 *
	 * @return void
	 */
	public function testConsolidatedSchedule() {
		// Anyone logged in is allowed to see their consolidated schedule
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'consolidated_schedule'],
			PERSON_ID_ADMIN);
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'consolidated_schedule'],
			PERSON_ID_MANAGER);
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'consolidated_schedule'],
			PERSON_ID_COORDINATOR);
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'consolidated_schedule'],
			PERSON_ID_CAPTAIN);
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'consolidated_schedule'],
			PERSON_ID_PLAYER);
		$this->assertGetAjaxAsAccessOk(['controller' => 'People', 'action' => 'consolidated_schedule'],
			PERSON_ID_VISITOR);

		// Others are not allowed to see consolidated schedules
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'People', 'action' => 'consolidated_schedule']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test act_as method
	 *
	 * @return void
	 */
	public function testActAs() {
		// Admins are allowed to act as anyone
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'act_as'],
			PERSON_ID_ADMIN, '/',
			'There is nobody else you can act as.');
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'act_as', 'person' => PERSON_ID_MANAGER],
			PERSON_ID_ADMIN, '/',
			'You are now acting as Mary Manager.');
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'act_as', 'person' => PERSON_ID_COORDINATOR],
			PERSON_ID_ADMIN, '/',
			'You are now acting as Cindy Coordinator.');
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'act_as', 'person' => PERSON_ID_CAPTAIN],
			PERSON_ID_ADMIN, '/',
			'You are now acting as Crystal Captain.');
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'act_as', 'person' => PERSON_ID_PLAYER],
			PERSON_ID_ADMIN, '/',
			'You are now acting as Pam Player.');
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'act_as', 'person' => PERSON_ID_CHILD],
			PERSON_ID_ADMIN, '/',
			'You are now acting as Carla Child.');
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'act_as', 'person' => PERSON_ID_VISITOR],
			PERSON_ID_ADMIN, '/',
			'You are now acting as Veronica Visitor.');
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'act_as', 'person' => PERSON_ID_DUPLICATE],
			PERSON_ID_ADMIN, '/',
			'You are now acting as Mary Duplicate.');

		// Managers are allowed to act as anyone in their affiliate
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'act_as', 'person' => PERSON_ID_PLAYER],
			PERSON_ID_MANAGER, '/',
			'You are now acting as Pam Player.');
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'act_as', 'person' => PERSON_ID_ADMIN],
			PERSON_ID_MANAGER, '/',
			'Managers cannot act as other managers.');
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'act_as', 'person' => PERSON_ID_COORDINATOR],
			PERSON_ID_MANAGER, '/',
			'You are now acting as Cindy Coordinator.');
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'act_as', 'person' => PERSON_ID_CAPTAIN],
			PERSON_ID_MANAGER, '/',
			'You are now acting as Crystal Captain.');
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'act_as', 'person' => PERSON_ID_PLAYER],
			PERSON_ID_MANAGER, '/',
			'You are now acting as Pam Player.');
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'act_as', 'person' => PERSON_ID_CHILD],
			PERSON_ID_MANAGER, '/',
			'You are now acting as Carla Child.');
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'act_as', 'person' => PERSON_ID_VISITOR],
			PERSON_ID_MANAGER, '/',
			'You are now acting as Veronica Visitor.');
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'act_as', 'person' => PERSON_ID_DUPLICATE],
			PERSON_ID_MANAGER, '/',
			'You are now acting as Mary Duplicate.');
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'act_as', 'person' => PERSON_ID_ANDY_SUB], PERSON_ID_MANAGER);

		// Others are allowed to act as themselves or their relatives only
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'act_as', 'person' => PERSON_ID_PLAYER], PERSON_ID_COORDINATOR);

		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'act_as'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'act_as', 'person' => PERSON_ID_CAPTAIN2],
			PERSON_ID_CAPTAIN, '/',
			'You are now acting as Chuck Captain.');
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'act_as', 'person' => PERSON_ID_CAPTAIN], PERSON_ID_CAPTAIN2);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'act_as', 'person' => PERSON_ID_PLAYER], PERSON_ID_CAPTAIN);

		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'act_as'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessRedirect(['controller' => 'People', 'action' => 'act_as', 'person' => PERSON_ID_CHILD],
			PERSON_ID_PLAYER, '/',
			'You are now acting as Carla Child.');
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'act_as', 'person' => PERSON_ID_CAPTAIN], PERSON_ID_PLAYER);

		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'act_as', 'person' => PERSON_ID_PLAYER], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'act_as', 'person' => PERSON_ID_PLAYER]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test search method
	 *
	 * @return void
	 */
	public function testSearch() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Anyone logged in is allowed to search
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'search'], PERSON_ID_ADMIN);

		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'search'], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'search'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'search'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'search'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'search'], PERSON_ID_VISITOR);

		// Others are not allowed to search
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'search']);

		$this->markTestIncomplete('More scenarios to test below.');

		$data = [
		];
		$this->post(['controller' => 'People', 'action' => 'search'], $data);

		$this->assertResponseSuccess();
		$people = TableRegistry::get('People');
		$query = $people->find()->where(['title' => $data['title']]);
		$this->assertEquals(1, $query->count());
	}

	/**
	 * Test rule_search method
	 *
	 * @return void
	 */
	public function testRuleSearch() {
		// Admins are allowed to rule search
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'rule_search'], PERSON_ID_ADMIN);

		// Managers are allowed to rule search
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'rule_search'], PERSON_ID_MANAGER);

		// Others are not allowed to rule search
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'rule_search'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'rule_search'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'rule_search'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'rule_search'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'rule_search']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test league_search method
	 *
	 * @return void
	 */
	public function testLeagueSearch() {
		// Admins are allowed to league search
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'league_search'], PERSON_ID_ADMIN);

		// Managers are allowed to league search
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'league_search'], PERSON_ID_MANAGER);

		// Others are not allowed to league search
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'league_search'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'league_search'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'league_search'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'league_search'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'league_search']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test inactive_search method
	 *
	 * @return void
	 */
	public function testInactiveSearch() {
		// Admins are allowed to inactive search
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'inactive_search'], PERSON_ID_ADMIN);

		// Managers are allowed to inactive search
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'inactive_search'], PERSON_ID_MANAGER);

		// Others are not allowed to inactive search
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'inactive_search'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'inactive_search'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'inactive_search'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'inactive_search'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'inactive_search']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test list_new method
	 *
	 * @return void
	 */
	public function testListNew() {
		// Admins are allowed to list new users
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'list_new'], PERSON_ID_ADMIN);

		// Managers are allowed to list new users
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'list_new'], PERSON_ID_MANAGER);

		// Others are not allowed to list new users
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'list_new'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'list_new'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'list_new'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'list_new'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'list_new']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test approve method as an admin
	 *
	 * @return void
	 */
	public function testApproveAsAdmin() {
		// Admins are allowed to approve
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'approve', 'person' => PERSON_ID_DUPLICATE], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve method as an admin, approving the duplicate
	 *
	 * @return void
	 */
	public function testApproveAsAdminApprove() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'approve', 'person' => PERSON_ID_DUPLICATE],
			PERSON_ID_ADMIN, ['disposition' => 'approved'], ['controller' => 'People', 'action' => 'list_new']);

		// Confirm the notification email
		$messages = Configure::read('test_emails');
		$this->assertEquals(1, count($messages));

		$this->assertContains('From: &quot;Admin&quot; &lt;admin@zuluru.org&gt;', $messages[0]);
		$this->assertNotContains('Reply-To: ', $messages[0]);
		$this->assertContains('To: &quot;Mary Duplicate&quot; &lt;mary@zuluru.org&gt;', $messages[0]);
		$this->assertNotContains('CC: ', $messages[0]);
		$this->assertContains('Subject: Test Zuluru Affiliate Account Activation for mary2', $messages[0]);
		$this->assertContains('Your TZA account has been approved.', $messages[0]);

		// Make sure that everything is still there
		$person = TableRegistry::get('People')->get(PERSON_ID_DUPLICATE, [
			'contain' => [Configure::read('Security.authModel'), 'Affiliates', 'Groups', 'Settings', 'Skills', 'Preregistrations', 'Franchises']
		]);
		$this->assertEquals('active', $person->status);
		$this->assertEquals(USER_ID_DUPLICATE, $person->user_id);
		$this->assertNotNull($person->user);
		$this->assertEquals(USER_ID_DUPLICATE, $person->user->id);
		$this->assertEquals(2, count($person->affiliates));
		$this->assertEquals(1, count($person->groups));
		$this->assertEquals(2, count($person->settings));
		$this->assertEquals(2, count($person->skills));
		$this->assertEquals(1, count($person->preregistrations));
		$this->assertEquals(1, count($person->franchises));
	}

	/**
	 * Test approve method as an admin, silently deleting the duplicate
	 *
	 * @return void
	 */
	public function testApproveAsAdminDeleteSilently() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'approve', 'person' => PERSON_ID_DUPLICATE],
			PERSON_ID_ADMIN, ['disposition' => 'delete'], ['controller' => 'People', 'action' => 'list_new']);

		// Confirm there was no notification email
		$this->assertEmpty(Configure::read('test_emails'));

		// Make sure that everything is gone
		$table = TableRegistry::get('People');
		$authModel = Configure::read('Security.authModel');
		$this->assertEquals(0, $table->find()->where(['id' => PERSON_ID_DUPLICATE])->count());
		$this->assertEquals(0, $table->$authModel->find()->where(['id' => USER_ID_DUPLICATE])->count());
		$this->assertEquals(0, $table->AffiliatesPeople->find()->where(['person_id' => PERSON_ID_DUPLICATE])->count());
		$this->assertEquals(0, $table->GroupsPeople->find()->where(['person_id' => PERSON_ID_DUPLICATE])->count());
		$this->assertEquals(0, $table->Settings->find()->where(['person_id' => PERSON_ID_DUPLICATE])->count());
		$this->assertEquals(0, $table->Skills->find()->where(['person_id' => PERSON_ID_DUPLICATE])->count());
		$this->assertEquals(0, $table->Preregistrations->find()->where(['person_id' => PERSON_ID_DUPLICATE])->count());
		$this->assertEquals(0, $table->FranchisesPeople->find()->where(['person_id' => PERSON_ID_DUPLICATE])->count());
	}

	/**
	 * Test approve method as an admin, deleting the duplicate with notice
	 *
	 * @return void
	 */
	public function testApproveAsAdminDeleteNotice() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'approve', 'person' => PERSON_ID_DUPLICATE],
			PERSON_ID_ADMIN, ['disposition' => 'delete_duplicate:' . PERSON_ID_MANAGER], ['controller' => 'People', 'action' => 'list_new']);

		// Confirm the notification email
		$messages = Configure::read('test_emails');
		$this->assertEquals(1, count($messages));

		$this->assertContains('From: &quot;Admin&quot; &lt;admin@zuluru.org&gt;', $messages[0]);
		$this->assertNotContains('Reply-To: ', $messages[0]);
		$this->assertContains('To: &quot;Mary Manager&quot; &lt;mary@zuluru.org&gt;', $messages[0]);
		$this->assertNotContains('CC: ', $messages[0]);
		$this->assertContains('Subject: Test Zuluru Affiliate Account Update', $messages[0]);
		$this->assertContains('You seem to have created a duplicate TZA account. You already have an account.', $messages[0]);
		$this->assertContains('Your second account has been deleted.', $messages[0]);

		// Make sure that everything is gone
		$table = TableRegistry::get('People');
		$authModel = Configure::read('Security.authModel');
		$this->assertEquals(0, $table->find()->where(['id' => PERSON_ID_DUPLICATE])->count());
		$this->assertEquals(0, $table->$authModel->find()->where(['id' => USER_ID_DUPLICATE])->count());
		$this->assertEquals(0, $table->AffiliatesPeople->find()->where(['person_id' => PERSON_ID_DUPLICATE])->count());
		$this->assertEquals(0, $table->GroupsPeople->find()->where(['person_id' => PERSON_ID_DUPLICATE])->count());
		$this->assertEquals(0, $table->Settings->find()->where(['person_id' => PERSON_ID_DUPLICATE])->count());
		$this->assertEquals(0, $table->Skills->find()->where(['person_id' => PERSON_ID_DUPLICATE])->count());
		$this->assertEquals(0, $table->Preregistrations->find()->where(['person_id' => PERSON_ID_DUPLICATE])->count());
		$this->assertEquals(0, $table->FranchisesPeople->find()->where(['person_id' => PERSON_ID_DUPLICATE])->count());
	}

	/**
	 * Test approve method as an admin, merging the duplicate with another
	 *
	 * @return void
	 */
	public function testApproveAsAdminMerge() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		$this->assertPostAsAccessRedirect(['controller' => 'People', 'action' => 'approve', 'person' => PERSON_ID_DUPLICATE],
			PERSON_ID_ADMIN, ['disposition' => 'merge_duplicate:' . PERSON_ID_MANAGER], ['controller' => 'People', 'action' => 'list_new']);

		// Confirm the notification email
		$messages = Configure::read('test_emails');
		$this->assertEquals(1, count($messages));

		$this->assertContains('From: &quot;Admin&quot; &lt;admin@zuluru.org&gt;', $messages[0]);
		$this->assertNotContains('Reply-To: ', $messages[0]);
		$this->assertContains('To: &quot;Mary Duplicate&quot; &lt;mary@zuluru.org&gt;', $messages[0]);
		$this->assertNotContains('CC: ', $messages[0]);
		$this->assertContains('Subject: Test Zuluru Affiliate Account Update', $messages[0]);
		$this->assertContains('You seem to have created a duplicate TZA account. You already have an account.', $messages[0]);
		$this->assertContains('this old account has been merged with your new information', $messages[0]);

		// Make sure that everything was correctly merged
		$table = TableRegistry::get('People');
		$authModel = Configure::read('Security.authModel');
		$person = $table->get(PERSON_ID_MANAGER, [
			'contain' => [$authModel, 'Affiliates', 'Groups', 'Settings', 'Skills', 'Preregistrations', 'Franchises']
		]);
		$this->assertEquals('Duplicate', $person->last_name);
		$this->assertEquals('active', $person->status);
		$this->assertFalse($person->publish_email);
		$this->assertEmpty($person->work_phone);
		$this->assertEquals('(416) 345-6790', $person->mobile_phone);
		$this->assertEquals('236 Main St.', $person->addr_street);
		$this->assertEquals('Womens Large', $person->shirt_size);
		$this->assertEquals(USER_ID_MANAGER, $person->user_id);
		$this->assertNotNull($person->user);
		$this->assertEquals(USER_ID_MANAGER, $person->user->id);
		$this->assertEquals(2, count($person->affiliates));
		$this->assertEquals(2, count($person->groups));
		$this->assertEquals(3, count($person->settings));
		$this->assertEquals('enable_ical', $person->settings[0]->name);
		$this->assertEquals(1, $person->settings[0]->value);
		$this->assertEquals(2, count($person->skills));
		$this->assertEquals(1, count($person->preregistrations));
		$this->assertEquals(1, count($person->franchises));

		// And all the old stuff is gone
		$this->assertEquals(0, $table->find()->where(['id' => PERSON_ID_DUPLICATE])->count());
		$this->assertEquals(0, $table->$authModel->find()->where(['id' => USER_ID_DUPLICATE])->count());
		$this->assertEquals(0, $table->AffiliatesPeople->find()->where(['person_id' => PERSON_ID_DUPLICATE])->count());
		$this->assertEquals(0, $table->GroupsPeople->find()->where(['person_id' => PERSON_ID_DUPLICATE])->count());
		$this->assertEquals(0, $table->Settings->find()->where(['person_id' => PERSON_ID_DUPLICATE])->count());
		$this->assertEquals(0, $table->Skills->find()->where(['person_id' => PERSON_ID_DUPLICATE])->count());
		$this->assertEquals(0, $table->Preregistrations->find()->where(['person_id' => PERSON_ID_DUPLICATE])->count());
		$this->assertEquals(0, $table->FranchisesPeople->find()->where(['person_id' => PERSON_ID_DUPLICATE])->count());
	}

	// TODO: Test some more merging options above: child with adult, adult with child, parent with player, etc.

	/**
	 * Test approve method as a manager
	 *
	 * @return void
	 */
	public function testApproveAsManager() {
		// Managers are allowed to approve
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'approve', 'person' => PERSON_ID_DUPLICATE], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve method as others
	 *
	 * @return void
	 */
	public function testApproveAsOthers() {
		// Others are not allowed to approve
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'approve', 'person' => PERSON_ID_DUPLICATE], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'approve', 'person' => PERSON_ID_DUPLICATE], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'approve', 'person' => PERSON_ID_DUPLICATE], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'People', 'action' => 'approve', 'person' => PERSON_ID_DUPLICATE], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'approve', 'person' => PERSON_ID_DUPLICATE]);
	}

	/**
	 * Test vcf method
	 *
	 * @return void
	 */
	public function testVcf() {
		// Anyone is allowed to download the VCF. Different people have different info available.
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'vcf', 'person' => PERSON_ID_PLAYER], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'vcf', 'person' => PERSON_ID_INACTIVE], PERSON_ID_ADMIN);

		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'vcf', 'person' => PERSON_ID_PLAYER], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'vcf', 'person' => PERSON_ID_INACTIVE], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'vcf', 'person' => PERSON_ID_INACTIVE], PERSON_ID_MANAGER);

		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'vcf', 'person' => PERSON_ID_CAPTAIN], PERSON_ID_COORDINATOR);
		$this->assertResponseContains('EMAIL;PREF;INTERNET:crystal@zuluru.org');
		$this->assertResponseContains('TEL;HOME;VOICE:(416) 567-8910');
		$this->assertResponseNotContains('TEL;WORK');
		$this->assertResponseNotContains('TEL;CELL');
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'vcf', 'person' => PERSON_ID_PLAYER], PERSON_ID_COORDINATOR);
		$this->assertResponseNotContains('EMAIL');
		$this->assertResponseNotContains('TEL;HOME');
		$this->assertResponseNotContains('TEL;WORK');
		$this->assertResponseNotContains('TEL;CELL');
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'vcf', 'person' => PERSON_ID_INACTIVE], PERSON_ID_COORDINATOR);
		$this->assertResponseNotContains('EMAIL');
		$this->assertResponseNotContains('TEL;HOME');
		$this->assertResponseNotContains('TEL;WORK');
		$this->assertResponseNotContains('TEL;CELL');

		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'vcf', 'person' => PERSON_ID_PLAYER], PERSON_ID_CAPTAIN);
		$this->assertResponseContains('EMAIL;PREF;INTERNET:pam@zuluru.org');
		$this->assertResponseContains('TEL;HOME;VOICE:(416) 678-9012');
		$this->assertResponseContains('TEL;WORK;VOICE:(416) 789-0123;ext=456');
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'vcf', 'person' => PERSON_ID_INACTIVE], PERSON_ID_CAPTAIN);
		$this->assertResponseNotContains('EMAIL');
		$this->assertResponseNotContains('TEL;HOME');
		$this->assertResponseNotContains('TEL;WORK');
		$this->assertResponseNotContains('TEL;CELL');

		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'vcf', 'person' => PERSON_ID_PLAYER], PERSON_ID_PLAYER);
		$this->assertResponseContains('EMAIL;PREF;INTERNET:pam@zuluru.org');
		$this->assertResponseContains('TEL;HOME;VOICE:(416) 678-9012');
		$this->assertResponseContains('TEL;WORK;VOICE:(416) 789-0123;ext=456');
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'vcf', 'person' => PERSON_ID_INACTIVE], PERSON_ID_PLAYER);
		$this->assertResponseNotContains('EMAIL');
		$this->assertResponseNotContains('TEL;HOME');
		$this->assertResponseNotContains('TEL;WORK');
		$this->assertResponseNotContains('TEL;CELL');

		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'vcf', 'person' => PERSON_ID_PLAYER], PERSON_ID_VISITOR);
		$this->assertResponseNotContains('EMAIL');
		$this->assertResponseNotContains('TEL;HOME');
		$this->assertResponseNotContains('TEL;WORK');
		$this->assertResponseNotContains('TEL;CELL');
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'vcf', 'person' => PERSON_ID_INACTIVE], PERSON_ID_VISITOR);
		$this->assertResponseNotContains('EMAIL');
		$this->assertResponseNotContains('TEL;HOME');
		$this->assertResponseNotContains('TEL;WORK');
		$this->assertResponseNotContains('TEL;CELL');

		$this->assertGetAnonymousAccessOk(['controller' => 'People', 'action' => 'vcf', 'person' => PERSON_ID_PLAYER]);
		$this->assertResponseNotContains('EMAIL');
		$this->assertResponseNotContains('TEL;HOME');
		$this->assertResponseNotContains('TEL;WORK');
		$this->assertResponseNotContains('TEL;CELL');
	}

	/**
	 * Test ical method
	 *
	 * @return void
	 */
	public function testIcal() {
		// Can get the ical feed for anyone with the option enabled
		$this->get(['controller' => 'People', 'action' => 'ical', PERSON_ID_ADMIN]);
		$this->assertResponseCode(410);
		$this->assertGetAnonymousAccessOk(['controller' => 'People', 'action' => 'ical', PERSON_ID_CAPTAIN]);
		$this->assertGetAnonymousAccessOk(['controller' => 'People', 'action' => 'ical', PERSON_ID_PLAYER]);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test registrations method
	 *
	 * @return void
	 */
	public function testRegistrations() {
		// Anyone logged in is allowed to see the list of their personal registrations
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'registrations'], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'registrations'], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'registrations'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'registrations'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'registrations'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'registrations'], PERSON_ID_VISITOR);

		// Others are not allowed to see the list of their personal registrations
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'registrations']);
	}

	/**
	 * Test credits method
	 *
	 * @return void
	 */
	public function testCredits() {
		// Anyone logged in is allowed to see their list of credits
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'credits'], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'credits'], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'credits'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'credits'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'credits'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'credits'], PERSON_ID_VISITOR);

		// Others are not allowed to see their list of credits
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'credits']);
	}

	/**
	 * Test teams method
	 *
	 * @return void
	 */
	public function testTeams() {
		// Anyone logged in is allowed to see their team history
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'teams'], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'teams'], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'teams'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'teams'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'teams'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'teams'], PERSON_ID_VISITOR);

		// Others are not allowed to see team history
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'teams']);
	}

	/**
	 * Test waivers method
	 *
	 * @return void
	 */
	public function testWaivers() {
		// Anyone logged in is allowed to see their waiver history
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'waivers'], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'waivers'], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'waivers'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'waivers'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'waivers'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessOk(['controller' => 'People', 'action' => 'waivers'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'People', 'action' => 'waivers']);
	}

}
