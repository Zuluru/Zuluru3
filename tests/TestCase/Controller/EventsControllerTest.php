<?php
namespace App\Test\TestCase\Controller;

use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;

/**
 * App\Controller\EventsController Test Case
 */
class EventsControllerTest extends ControllerTestCase {

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
					'app.game_slots',
						'app.divisions_gameslots',
			'app.franchises',
				'app.franchises_teams',
			'app.questionnaires',
			'app.events',
				'app.prices',
					'app.registrations',
						'app.payments',
						'app.responses',
				'app.preregistrations',
				'app.events_connections',
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
		// Admins are allowed to view the index, with full edit permissions
		$this->assertAccessOk(['controller' => 'Events', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/events/view\?event=' . EVENT_ID_LEAGUE_TEAM . '#ms');
		$this->assertResponseRegExp('#/events/edit\?event=' . EVENT_ID_LEAGUE_TEAM . '#ms');
		$this->assertResponseRegExp('#/events/delete\?event=' . EVENT_ID_LEAGUE_TEAM . '#ms');
		$this->assertResponseRegExp('#/events/view\?event=' . EVENT_ID_LEAGUE_INDIVIDUAL_SUB . '#ms');
		$this->assertResponseRegExp('#/events/edit\?event=' . EVENT_ID_LEAGUE_INDIVIDUAL_SUB . '#ms');
		$this->assertResponseRegExp('#/events/delete\?event=' . EVENT_ID_LEAGUE_INDIVIDUAL_SUB . '#ms');
	}

	/**
	 * Test index method as a manager
	 *
	 * @return void
	 */
	public function testIndexAsManager() {
		// Managers are allowed to view the index
		$this->assertAccessOk(['controller' => 'Events', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/events/view\?event=' . EVENT_ID_LEAGUE_TEAM . '#ms');
		$this->assertResponseRegExp('#/events/edit\?event=' . EVENT_ID_LEAGUE_TEAM . '#ms');
		$this->assertResponseRegExp('#/events/delete\?event=' . EVENT_ID_LEAGUE_TEAM . '#ms');

		// But cannot edit ones in other affiliates
		$this->session(['Zuluru.CurrentAffiliate' => AFFILIATE_ID_SUB]);
		$this->assertAccessOk(['controller' => 'Events', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/events/view\?event=' . EVENT_ID_LEAGUE_INDIVIDUAL_SUB . '#ms');
		$this->assertResponseNotRegExp('#/events/edit\?event=' . EVENT_ID_LEAGUE_INDIVIDUAL_SUB . '#ms');
		$this->assertResponseNotRegExp('#/events/delete\?event=' . EVENT_ID_LEAGUE_INDIVIDUAL_SUB . '#ms');
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
		// Others are allowed to view the index, but have no edit permissions
		$this->assertAccessOk(['controller' => 'Events', 'action' => 'index'], PERSON_ID_PLAYER);
		$this->assertResponseRegExp('#/events/view\?event=' . EVENT_ID_LEAGUE_TEAM . '#ms');
		$this->assertResponseNotRegExp('#/events/edit\?event=' . EVENT_ID_LEAGUE_TEAM . '#ms');
		$this->assertResponseNotRegExp('#/events/delete\?event=' . EVENT_ID_LEAGUE_TEAM . '#ms');
	}

	/**
	 * Test index method as someone else
	 *
	 * @return void
	 */
	public function testIndexAsVisitor() {
		// Others are allowed to view the index, but have no edit permissions
		$this->assertAccessOk(['controller' => 'Events', 'action' => 'index'], PERSON_ID_VISITOR);
		$this->assertResponseRegExp('#/events/view\?event=' . EVENT_ID_LEAGUE_TEAM . '#ms');
		$this->assertResponseNotRegExp('#/events/edit\?event=' . EVENT_ID_LEAGUE_TEAM . '#ms');
		$this->assertResponseNotRegExp('#/events/delete\?event=' . EVENT_ID_LEAGUE_TEAM . '#ms');
	}

	/**
	 * Test index method without being logged in
	 *
	 * @return void
	 */
	public function testIndexAsAnonymous() {
		// Others are allowed to view the index, but have no edit permissions
		$this->assertAccessOk(['controller' => 'Events', 'action' => 'index']);
		$this->assertResponseRegExp('#/events/view\?event=' . EVENT_ID_LEAGUE_TEAM . '#ms');
		$this->assertResponseNotRegExp('#/events/edit\?event=' . EVENT_ID_LEAGUE_TEAM . '#ms');
		$this->assertResponseNotRegExp('#/events/delete\?event=' . EVENT_ID_LEAGUE_TEAM . '#ms');
	}

	/**
	 * Test wizard method as an admin
	 *
	 * @return void
	 */
	public function testWizardAsAdmin() {
		FrozenTime::setTestNow(new FrozenTime('April 30 00:00:00'));
		$this->assertAccessOk(['controller' => 'Events', 'action' => 'wizard'], PERSON_ID_ADMIN);
		// The admin user here is not a player, so doesn't have access to individual or membership events.
		$this->assertResponseRegExp('#/events/view\?event=' . EVENT_ID_LEAGUE_TEAM . '#ms');

		FrozenTime::setTestNow(new FrozenTime('March 30 00:00:00'));
		$this->assertAccessOk(['controller' => 'Events', 'action' => 'wizard'], PERSON_ID_ADMIN);
		// The admin user here is not a player, so doesn't have access to individual or membership events.
		// Admins get access to events before they open.
		$this->assertResponseRegExp('#/events/view\?event=' . EVENT_ID_LEAGUE_TEAM . '#ms');
	}

	/**
	 * Test wizard method as a manager
	 *
	 * @return void
	 */
	public function testWizardAsManager() {
		FrozenTime::setTestNow(new FrozenTime('April 30 00:00:00'));
		$this->assertAccessOk(['controller' => 'Events', 'action' => 'wizard'], PERSON_ID_MANAGER);
		// The manager user here is not a player, so doesn't have access to individual or membership events.
		$this->assertResponseRegExp('#/events/view\?event=' . EVENT_ID_LEAGUE_TEAM . '#ms');

		FrozenTime::setTestNow(new FrozenTime('March 30 00:00:00'));
		$this->assertAccessOk(['controller' => 'Events', 'action' => 'wizard'], PERSON_ID_MANAGER);
		// The manager user here is not a player, so doesn't have access to individual or membership events.
		// Managers get access to events before they open.
		$this->assertResponseRegExp('#/events/view\?event=' . EVENT_ID_LEAGUE_TEAM . '#ms');
	}

	/**
	 * Test wizard method as a coordinator
	 *
	 * @return void
	 */
	public function testWizardAsCoordinator() {
		FrozenTime::setTestNow(new FrozenTime('April 30 00:00:00'));
		$this->assertAccessOk(['controller' => 'Events', 'action' => 'wizard'], PERSON_ID_COORDINATOR);
		// The coordinator user here is not a player, so doesn't have access to individual or membership events.
		$this->assertResponseRegExp('#/events/view\?event=' . EVENT_ID_LEAGUE_TEAM . '#ms');
	}

	/**
	 * Test wizard method as a captain
	 *
	 * @return void
	 */
	public function testWizardAsCaptain() {
		FrozenTime::setTestNow(new FrozenTime('April 30 00:00:00'));

		$this->assertAccessOk(['controller' => 'Events', 'action' => 'wizard'], PERSON_ID_CAPTAIN);
		$this->assertResponseNotRegExp('#/events/wizard/membership#ms');
		$this->assertResponseRegExp('#/events/wizard/league_team#ms');
		$this->assertResponseRegExp('#/events/wizard/league_individual#ms');
		$this->assertResponseNotRegExp('#/events/wizard/event_team#ms');
		$this->assertResponseNotRegExp('#/events/wizard/event_individual#ms');
	}

	/**
	 * Test wizard method as a player
	 *
	 * @return void
	 */
	public function testWizardAsPlayer() {
		FrozenTime::setTestNow(new FrozenTime('April 30 00:00:00'));

		$this->assertAccessOk(['controller' => 'Events', 'action' => 'wizard'], PERSON_ID_PLAYER);
		$this->assertResponseNotRegExp('#/events/wizard/membership#ms');
		$this->assertResponseRegExp('#/events/wizard/league_team#ms');
		$this->assertResponseRegExp('#/events/wizard/league_individual#ms');
		$this->assertResponseNotRegExp('#/events/wizard/event_team#ms');
		$this->assertResponseNotRegExp('#/events/wizard/event_individual#ms');
	}

	/**
	 * Test wizard method as someone else
	 *
	 * @return void
	 */
	public function testWizardAsVisitor() {
		FrozenTime::setTestNow(new FrozenTime('April 30 00:00:00'));

		$this->assertAccessOk(['controller' => 'Events', 'action' => 'wizard'], PERSON_ID_VISITOR);
		$this->assertResponseRegExp('#/events/wizard/membership#ms');
		$this->assertResponseRegExp('#/events/wizard/league_team#ms');
		$this->assertResponseRegExp('#/events/wizard/league_individual#ms');
		$this->assertResponseNotRegExp('#/events/wizard/event_team#ms');
		$this->assertResponseNotRegExp('#/events/wizard/event_individual#ms');
	}

	/**
	 * Test wizard method without being logged in
	 *
	 * @return void
	 */
	public function testWizardAsAnonymous() {
		FrozenTime::setTestNow(new FrozenTime('April 30 00:00:00'));

		// Wizard doesn't make sense for someone not logged in: it sends them to the event list instead
		$this->assertAccessRedirect(['controller' => 'Events', 'action' => 'wizard'],
			null, 'get', [], ['controller' => 'Events', 'action' => 'index'], false);
	}

	/**
	 * Test view method as an admin
	 *
	 * @return void
	 */
	public function testViewAsAdmin() {
		// Admins are allowed to view events, with full edit permissions
		$this->assertAccessOk(['controller' => 'Events', 'action' => 'view', 'event' => EVENT_ID_LEAGUE_TEAM], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/events/edit\?event=' . EVENT_ID_LEAGUE_TEAM . '#ms');
		$this->assertResponseRegExp('#/events/delete\?event=' . EVENT_ID_LEAGUE_TEAM . '#ms');

		$this->assertAccessOk(['controller' => 'Events', 'action' => 'view', 'event' => EVENT_ID_LEAGUE_INDIVIDUAL_SUB], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/events/edit\?event=' . EVENT_ID_LEAGUE_INDIVIDUAL_SUB . '#ms');
		$this->assertResponseRegExp('#/events/delete\?event=' . EVENT_ID_LEAGUE_INDIVIDUAL_SUB . '#ms');
	}

	/**
	 * Test view method as a manager
	 *
	 * @return void
	 */
	public function testViewAsManager() {
		// Managers are allowed to view events
		$this->assertAccessOk(['controller' => 'Events', 'action' => 'view', 'event' => EVENT_ID_LEAGUE_TEAM], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/events/edit\?event=' . EVENT_ID_LEAGUE_TEAM . '#ms');
		$this->assertResponseRegExp('#/events/delete\?event=' . EVENT_ID_LEAGUE_TEAM . '#ms');

		// But cannot edit ones in other affiliates
		$this->assertAccessOk(['controller' => 'Events', 'action' => 'view', 'event' => EVENT_ID_LEAGUE_INDIVIDUAL_SUB], PERSON_ID_MANAGER);
		$this->assertResponseNotRegExp('#/events/edit\?event=' . EVENT_ID_LEAGUE_INDIVIDUAL_SUB . '#ms');
		$this->assertResponseNotRegExp('#/events/delete\?event=' . EVENT_ID_LEAGUE_INDIVIDUAL_SUB . '#ms');
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
		// Others are allowed to view events, but have no edit permissions
		$this->assertAccessOk(['controller' => 'Events', 'action' => 'view', 'event' => EVENT_ID_LEAGUE_TEAM], PERSON_ID_PLAYER);
		$this->assertResponseNotRegExp('#/events/edit\?event=' . EVENT_ID_LEAGUE_TEAM . '#ms');
		$this->assertResponseNotRegExp('#/events/delete\?event=' . EVENT_ID_LEAGUE_TEAM . '#ms');
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
		// Admins can add new events anywhere
		$this->assertAccessOk(['controller' => 'Events', 'action' => 'add'], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#<option value="1" selected="selected">Club</option>#ms');
		$this->assertResponseRegExp('#<option value="2">Sub</option>#ms');

		// If an event ID is given, we will clone that event
		$this->assertAccessOk(['controller' => 'Events', 'action' => 'add', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#<input type="text" name="name"[^>]*value="Membership"#ms');
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers can add new events in their own affiliate, but not others
		$this->assertAccessOk(['controller' => 'Events', 'action' => 'add'], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#<input type="hidden" name="affiliate_id" value="1"/>#ms');
		$this->assertResponseNotRegExp('#<option value="2">Sub</option>#ms');

		$this->assertAccessRedirect(['controller' => 'Events', 'action' => 'add', 'event' => EVENT_ID_LEAGUE_INDIVIDUAL_SUB], PERSON_ID_MANAGER);
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
		// Others cannot add new events
		$this->assertAccessRedirect(['controller' => 'Events', 'action' => 'add'], PERSON_ID_PLAYER);
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
		// Admins can edit events anywhere
		$this->assertAccessOk(['controller' => 'Events', 'action' => 'edit', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_ADMIN);
		$this->assertAccessOk(['controller' => 'Events', 'action' => 'edit', 'event' => EVENT_ID_LEAGUE_INDIVIDUAL_SUB], PERSON_ID_ADMIN);
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers can edit events in their own affiliate, but not others
		$this->assertAccessOk(['controller' => 'Events', 'action' => 'edit', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_MANAGER);
		$this->assertAccessRedirect(['controller' => 'Events', 'action' => 'edit', 'event' => EVENT_ID_LEAGUE_INDIVIDUAL_SUB], PERSON_ID_MANAGER);
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
		// Others cannot edit events
		$this->assertAccessRedirect(['controller' => 'Events', 'action' => 'edit', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_PLAYER);
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
	 * Test event_type_fields method as an admin
	 *
	 * @return void
	 */
	public function testEventTypeFieldsAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test event_type_fields method as a manager
	 *
	 * @return void
	 */
	public function testEventTypeFieldsAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test event_type_fields method as a coordinator
	 *
	 * @return void
	 */
	public function testEventTypeFieldsAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test event_type_fields method as a captain
	 *
	 * @return void
	 */
	public function testEventTypeFieldsAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test event_type_fields method as a player
	 *
	 * @return void
	 */
	public function testEventTypeFieldsAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test event_type_fields method as someone else
	 *
	 * @return void
	 */
	public function testEventTypeFieldsAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test event_type_fields method without being logged in
	 *
	 * @return void
	 */
	public function testEventTypeFieldsAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_price method as an admin
	 *
	 * @return void
	 */
	public function testAddPriceAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_price method as a manager
	 *
	 * @return void
	 */
	public function testAddPriceAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_price method as a coordinator
	 *
	 * @return void
	 */
	public function testAddPriceAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_price method as a captain
	 *
	 * @return void
	 */
	public function testAddPriceAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_price method as a player
	 *
	 * @return void
	 */
	public function testAddPriceAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_price method as someone else
	 *
	 * @return void
	 */
	public function testAddPriceAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_price method without being logged in
	 *
	 * @return void
	 */
	public function testAddPriceAsAnonymous() {
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

		// Admins are allowed to delete events
		$this->assertAccessRedirect(['controller' => 'Events', 'action' => 'delete', 'event' => EVENT_ID_LEAGUE_INDIVIDUAL_MONDAY],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Events', 'action' => 'index'],
			'The event has been deleted.', 'Flash.flash.0.message');
		$connections = TableRegistry::get('EventsConnections');

		// Make sure the connected event wasn't deleted, but the connection was
		$events = TableRegistry::get('Events');
		$query = $events->find();
		$this->assertEquals(5, $query->count());
		$query = $events->EventsConnections->find();
		$this->assertEquals(0, $query->count());

		// But not ones with dependencies
		$this->assertAccessRedirect(['controller' => 'Events', 'action' => 'delete', 'event' => EVENT_ID_MEMBERSHIP],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Events', 'action' => 'index'],
			'#The following records reference this event, so it cannot be deleted#', 'Flash.flash.0.message');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers can delete events in their affiliate
		$this->assertAccessRedirect(['controller' => 'Events', 'action' => 'delete', 'event' => EVENT_ID_LEAGUE_INDIVIDUAL_MONDAY],
			PERSON_ID_MANAGER, 'post', [], ['controller' => 'Events', 'action' => 'index'],
			'The event has been deleted.', 'Flash.flash.0.message');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Events', 'action' => 'delete', 'event' => EVENT_ID_LEAGUE_INDIVIDUAL_SUB],
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
	 * Test connections method as an admin
	 *
	 * @return void
	 */
	public function testConnectionsAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test connections method as a manager
	 *
	 * @return void
	 */
	public function testConnectionsAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test connections method as a coordinator
	 *
	 * @return void
	 */
	public function testConnectionsAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test connections method as a captain
	 *
	 * @return void
	 */
	public function testConnectionsAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test connections method as a player
	 *
	 * @return void
	 */
	public function testConnectionsAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test connections method as someone else
	 *
	 * @return void
	 */
	public function testConnectionsAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test connections method without being logged in
	 *
	 * @return void
	 */
	public function testConnectionsAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
