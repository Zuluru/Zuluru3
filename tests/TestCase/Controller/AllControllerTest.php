<?php
namespace App\Test\TestCase\Controller;

use Cake\Core\Configure;

/**
 * App\Controller\AllController Test Case
 */
class AllControllerTest extends ControllerTestCase {

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
			'app.franchises',
				'app.franchises_people',
				'app.franchises_teams',
			'app.events',
				'app.prices',
					'app.registrations',
						'app.payments',
			'app.categories',
				'app.tasks',
					'app.task_slots',
			'app.badges',
				'app.badges_people',
			'app.settings',
			'app.waivers',
				'app.waivers_people',
	];

	/**
	 * Test splash method as an admin
	 *
	 * @return void
	 */
	public function testSplashAsAdmin() {
		// Include all menu building in these tests
		Configure::write('feature.minimal_menus', false);

		// Everyone is allowed to get the splash page, different roles have different sets of messages
		$this->assertAccessOk(['controller' => 'All', 'action' => 'splash'], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#The following affiliates do not yet have managers assigned to them:.*/affiliates/edit\?affiliate=2.*/affiliates/delete\?affiliate=2#ms');
		$this->assertResponseRegExp('#There are 1 new <a href="/people/list_new">accounts to approve</a>#ms');
		$this->assertResponseNotRegExp('#Recent and Upcoming Schedule#ms');
	}

	/**
	 * Test splash method as a manager
	 *
	 * @return void
	 */
	public function testSplashAsManager() {
		// Include all menu building in these tests
		Configure::write('feature.minimal_menus', false);

		$this->assertAccessOk(['controller' => 'All', 'action' => 'splash'], PERSON_ID_MANAGER);
		$this->assertResponseNotRegExp('#The following affiliates do not yet have managers assigned to them:.*/affiliates/edit\?affiliate=2.*/affiliates/delete\?affiliate=2#ms');
		$this->assertResponseRegExp('#There are 1 new <a href="/people/list_new">accounts to approve</a>#ms');
		$this->assertResponseNotRegExp('#Recent and Upcoming Schedule#ms');
	}

	/**
	 * Test splash method as a coordinator
	 *
	 * @return void
	 */
	public function testSplashAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test splash method as a captain
	 *
	 * @return void
	 */
	public function testSplashAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');

	}

	/**
	 * Test splash method as a player
	 *
	 * @return void
	 */
	public function testSplashAsPlayer() {
		// Include all menu building in these tests
		Configure::write('feature.minimal_menus', false);

		$this->assertAccessOk(['controller' => 'All', 'action' => 'splash'], PERSON_ID_PLAYER);
		$this->assertResponseNotRegExp('#The following affiliates do not yet have managers assigned to them:.*/affiliates/edit\?affiliate=2.*/affiliates/delete\?affiliate=2#ms');
		$this->assertResponseNotRegExp('#There are 1 new <a href="/people/list_new">accounts to approve</a>#ms');
		$this->assertResponseRegExp('#Recent and Upcoming Schedule#ms');
	}

	/**
	 * Test splash method as a relative
	 *
	 * @return void
	 */
	public function testSplashAsRelative() {
		// Include all menu building in these tests
		Configure::write('feature.minimal_menus', false);

		// Related players have multiple tabs
		$this->assertAccessOk(['controller' => 'All', 'action' => 'splash'], PERSON_ID_CAPTAIN);
		$this->assertResponseRegExp('#Recent and Upcoming Schedule#ms');
		$this->assertResponseRegExp('#<div id="ui-tabs-1">.*My Teams.*<div id="ui-tabs-2">.*Chuck\'s Teams.*<div id="ui-tabs-3">.*One moment\.\.\.#ms');
	}

	/**
	 * Test splash method as someone else
	 *
	 * @return void
	 */
	public function testSplashAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test splash method without being logged in
	 *
	 * @return void
	 */
	public function testSplashAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test schedule method as an admin
	 *
	 * @return void
	 */
	public function testScheduleAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test schedule method as a manager
	 *
	 * @return void
	 */
	public function testScheduleAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test schedule method as a coordinator
	 *
	 * @return void
	 */
	public function testScheduleAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test schedule method as a captain
	 *
	 * @return void
	 */
	public function testScheduleAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test schedule method as a player
	 *
	 * @return void
	 */
	public function testScheduleAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test schedule method as someone else
	 *
	 * @return void
	 */
	public function testScheduleAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test schedule method without being logged in
	 *
	 * @return void
	 */
	public function testScheduleAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test consolidated_schedule method as an admin
	 *
	 * @return void
	 */
	public function testConsolidatedScheduleAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test consolidated_schedule method as a manager
	 *
	 * @return void
	 */
	public function testConsolidatedScheduleAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test consolidated_schedule method as a coordinator
	 *
	 * @return void
	 */
	public function testConsolidatedScheduleAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test consolidated_schedule method as a captain
	 *
	 * @return void
	 */
	public function testConsolidatedScheduleAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test consolidated_schedule method as a player
	 *
	 * @return void
	 */
	public function testConsolidatedScheduleAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test consolidated_schedule method as someone else
	 *
	 * @return void
	 */
	public function testConsolidatedScheduleAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test consolidated_schedule method without being logged in
	 *
	 * @return void
	 */
	public function testConsolidatedScheduleAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test clear_cache method as an admin
	 *
	 * @return void
	 */
	public function testClearCacheAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test clear_cache method as a manager
	 *
	 * @return void
	 */
	public function testClearCacheAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test clear_cache method as a coordinator
	 *
	 * @return void
	 */
	public function testClearCacheAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test clear_cache method as a captain
	 *
	 * @return void
	 */
	public function testClearCacheAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test clear_cache method as a player
	 *
	 * @return void
	 */
	public function testClearCacheAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test clear_cache method as someone else
	 *
	 * @return void
	 */
	public function testClearCacheAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test clear_cache method without being logged in
	 *
	 * @return void
	 */
	public function testClearCacheAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test language method as an admin
	 *
	 * @return void
	 */
	public function testLanguageAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test language method as a manager
	 *
	 * @return void
	 */
	public function testLanguageAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test language method as a coordinator
	 *
	 * @return void
	 */
	public function testLanguageAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test language method as a captain
	 *
	 * @return void
	 */
	public function testLanguageAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test language method as a player
	 *
	 * @return void
	 */
	public function testLanguageAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test language method as someone else
	 *
	 * @return void
	 */
	public function testLanguageAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test language method without being logged in
	 *
	 * @return void
	 */
	public function testLanguageAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
