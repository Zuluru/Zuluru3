<?php
namespace App\Test\TestCase\Controller;

use Cake\I18n\FrozenDate;
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
					'app.divisions_people',
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
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex() {
		FrozenDate::setTestNow(new FrozenDate('first Monday of April'));

		// Admins are allowed to view the index, with full edit permissions
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertResponseContains('/events/view?event=' . EVENT_ID_LEAGUE_TEAM);
		$this->assertResponseContains('/events/edit?event=' . EVENT_ID_LEAGUE_TEAM);
		$this->assertResponseContains('/events/delete?event=' . EVENT_ID_LEAGUE_TEAM);
		$this->assertResponseContains('/events/view?event=' . EVENT_ID_LEAGUE_INDIVIDUAL_SUB);
		$this->assertResponseContains('/events/edit?event=' . EVENT_ID_LEAGUE_INDIVIDUAL_SUB);
		$this->assertResponseContains('/events/delete?event=' . EVENT_ID_LEAGUE_INDIVIDUAL_SUB);

		// Managers are allowed to view the index
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertResponseContains('/events/view?event=' . EVENT_ID_LEAGUE_TEAM);
		$this->assertResponseContains('/events/edit?event=' . EVENT_ID_LEAGUE_TEAM);
		$this->assertResponseContains('/events/delete?event=' . EVENT_ID_LEAGUE_TEAM);

		// But are not allowed to edit ones in other affiliates
		$this->session(['Zuluru.CurrentAffiliate' => AFFILIATE_ID_SUB]);
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertResponseContains('/events/view?event=' . EVENT_ID_LEAGUE_INDIVIDUAL_SUB);
		$this->assertResponseNotContains('/events/edit?event=' . EVENT_ID_LEAGUE_INDIVIDUAL_SUB);
		$this->assertResponseNotContains('/events/delete?event=' . EVENT_ID_LEAGUE_INDIVIDUAL_SUB);
		$this->logout(); // clear that session setting

		// Others are allowed to view the index, but have no edit permissions
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'index'], PERSON_ID_COORDINATOR);
		$this->assertResponseContains('/events/view?event=' . EVENT_ID_LEAGUE_TEAM);
		$this->assertResponseNotContains('/events/edit?event=' . EVENT_ID_LEAGUE_TEAM);
		$this->assertResponseNotContains('/events/delete?event=' . EVENT_ID_LEAGUE_TEAM);

		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'index'], PERSON_ID_CAPTAIN);
		$this->assertResponseContains('/events/view?event=' . EVENT_ID_LEAGUE_TEAM);
		$this->assertResponseNotContains('/events/edit?event=' . EVENT_ID_LEAGUE_TEAM);
		$this->assertResponseNotContains('/events/delete?event=' . EVENT_ID_LEAGUE_TEAM);

		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'index'], PERSON_ID_PLAYER);
		$this->assertResponseContains('/events/view?event=' . EVENT_ID_LEAGUE_TEAM);
		$this->assertResponseNotContains('/events/edit?event=' . EVENT_ID_LEAGUE_TEAM);
		$this->assertResponseNotContains('/events/delete?event=' . EVENT_ID_LEAGUE_TEAM);

		// Others are allowed to view the index, but have no edit permissions
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'index'], PERSON_ID_VISITOR);
		$this->assertResponseContains('/events/view?event=' . EVENT_ID_LEAGUE_TEAM);
		$this->assertResponseNotContains('/events/edit?event=' . EVENT_ID_LEAGUE_TEAM);
		$this->assertResponseNotContains('/events/delete?event=' . EVENT_ID_LEAGUE_TEAM);

		$this->assertGetAnonymousAccessOk(['controller' => 'Events', 'action' => 'index']);
		$this->assertResponseContains('/events/view?event=' . EVENT_ID_LEAGUE_TEAM);
		$this->assertResponseNotContains('/events/edit?event=' . EVENT_ID_LEAGUE_TEAM);
		$this->assertResponseNotContains('/events/delete?event=' . EVENT_ID_LEAGUE_TEAM);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test wizard method
	 *
	 * @return void
	 */
	public function testWizard() {
		// The admin user here is not a player, so doesn't have access to individual or membership events.
		FrozenTime::setTestNow(new FrozenTime('April 30 00:00:00'));
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'wizard'], PERSON_ID_ADMIN);
		$this->assertResponseContains('/events/view?event=' . EVENT_ID_LEAGUE_TEAM);

		// Admins get access to events before they open.
		FrozenTime::setTestNow(new FrozenTime('March 30 00:00:00'));
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'wizard'], PERSON_ID_ADMIN);
		$this->assertResponseContains('/events/view?event=' . EVENT_ID_LEAGUE_TEAM);

		// The manager user here is not a player, so doesn't have access to individual or membership events.
		FrozenTime::setTestNow(new FrozenTime('April 30 00:00:00'));
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'wizard'], PERSON_ID_MANAGER);
		$this->assertResponseContains('/events/view?event=' . EVENT_ID_LEAGUE_TEAM);

		// Managers get access to events before they open.
		FrozenTime::setTestNow(new FrozenTime('March 30 00:00:00'));
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'wizard'], PERSON_ID_MANAGER);
		$this->assertResponseContains('/events/view?event=' . EVENT_ID_LEAGUE_TEAM);

		// The coordinator user here is not a player, so doesn't have access to individual or membership events.
		FrozenTime::setTestNow(new FrozenTime('April 30 00:00:00'));
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'wizard'], PERSON_ID_COORDINATOR);
		$this->assertResponseContains('/events/view?event=' . EVENT_ID_LEAGUE_TEAM);

		// Test as captain
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'wizard'], PERSON_ID_CAPTAIN);
		$this->assertResponseNotContains('/events/wizard/membership');
		$this->assertResponseContains('/events/wizard/league_team');
		$this->assertResponseContains('/events/wizard/league_individual');
		$this->assertResponseNotContains('/events/wizard/event_team');
		$this->assertResponseNotContains('/events/wizard/event_individual');

		// Test as player
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'wizard'], PERSON_ID_PLAYER);
		$this->assertResponseNotContains('/events/wizard/membership');
		$this->assertResponseContains('/events/wizard/league_team');
		$this->assertResponseContains('/events/wizard/league_individual');
		$this->assertResponseNotContains('/events/wizard/event_team');
		$this->assertResponseNotContains('/events/wizard/event_individual');

		// Test as visitor
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'wizard'], PERSON_ID_VISITOR);
		$this->assertResponseContains('/events/wizard/membership');
		$this->assertResponseContains('/events/wizard/league_team');
		$this->assertResponseContains('/events/wizard/league_individual');
		$this->assertResponseNotContains('/events/wizard/event_team');
		$this->assertResponseNotContains('/events/wizard/event_individual');

		// Wizard doesn't make sense for someone not logged in: it sends them to the event list instead
		$this->assertGetAnonymousAccessRedirect(['controller' => 'Events', 'action' => 'wizard'],
			['controller' => 'Events', 'action' => 'index']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testView() {
		// Admins are allowed to view events, with full edit permissions
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'view', 'event' => EVENT_ID_LEAGUE_TEAM], PERSON_ID_ADMIN);
		$this->assertResponseContains('/events/edit?event=' . EVENT_ID_LEAGUE_TEAM);
		$this->assertResponseContains('/events/delete?event=' . EVENT_ID_LEAGUE_TEAM);

		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'view', 'event' => EVENT_ID_LEAGUE_INDIVIDUAL_SUB], PERSON_ID_ADMIN);
		$this->assertResponseContains('/events/edit?event=' . EVENT_ID_LEAGUE_INDIVIDUAL_SUB);
		$this->assertResponseContains('/events/delete?event=' . EVENT_ID_LEAGUE_INDIVIDUAL_SUB);

		// Managers are allowed to view events
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'view', 'event' => EVENT_ID_LEAGUE_TEAM], PERSON_ID_MANAGER);
		$this->assertResponseContains('/events/edit?event=' . EVENT_ID_LEAGUE_TEAM);
		$this->assertResponseContains('/events/delete?event=' . EVENT_ID_LEAGUE_TEAM);

		// But are not allowed to edit ones in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'view', 'event' => EVENT_ID_LEAGUE_INDIVIDUAL_SUB], PERSON_ID_MANAGER);
		$this->assertResponseNotContains('/events/edit?event=' . EVENT_ID_LEAGUE_INDIVIDUAL_SUB);
		$this->assertResponseNotContains('/events/delete?event=' . EVENT_ID_LEAGUE_INDIVIDUAL_SUB);

		// Coordinators are allowed to view
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'view', 'event' => EVENT_ID_LEAGUE_TEAM], PERSON_ID_COORDINATOR);
		$this->markTestIncomplete('Not implemented yet.');
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'view', 'event' => EVENT_ID_LEAGUE_TEAM], PERSON_ID_CAPTAIN);

		// Others are allowed to view events, but have no edit permissions
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'view', 'event' => EVENT_ID_LEAGUE_TEAM], PERSON_ID_PLAYER);
		$this->assertResponseNotContains('/events/edit?event=' . EVENT_ID_LEAGUE_TEAM);
		$this->assertResponseNotContains('/events/delete?event=' . EVENT_ID_LEAGUE_TEAM);

		// Visitors are allowed to view
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'view', 'event' => EVENT_ID_LEAGUE_TEAM], PERSON_ID_VISITOR);

		// Others are allowed to view
		$this->assertGetAnonymousAccessOk(['controller' => 'Events', 'action' => 'view', 'event' => EVENT_ID_LEAGUE_TEAM]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add method as an admin
	 *
	 * @return void
	 */
	public function testAddAsAdmin() {
		// Admins are allowed to add new events anywhere
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'add'], PERSON_ID_ADMIN);
		$this->assertResponseContains('<option value="1" selected="selected">Club</option>');
		$this->assertResponseContains('<option value="2">Sub</option>');

		// If an event ID is given, we will clone that event
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'add', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#<input type="text" name="name"[^>]*value="Membership"#ms');
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers are allowed to add new events in their own affiliate, but not others
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'add'], PERSON_ID_MANAGER);
		$this->assertResponseContains('<input type="hidden" name="affiliate_id" value="1"/>');
		$this->assertResponseNotContains('<option value="2">Sub</option>');

		$this->assertGetAsAccessDenied(['controller' => 'Events', 'action' => 'add', 'event' => EVENT_ID_LEAGUE_INDIVIDUAL_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test add method as others
	 *
	 * @return void
	 */
	public function testAddAsOthers() {
		// Others are not allowed to add events
		$this->assertGetAsAccessDenied(['controller' => 'Events', 'action' => 'add'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Events', 'action' => 'add'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Events', 'action' => 'add'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Events', 'action' => 'add'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Events', 'action' => 'add']);
	}

	/**
	 * Test edit method as an admin
	 *
	 * @return void
	 */
	public function testEditAsAdmin() {
		// Admins are allowed to edit events anywhere
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'edit', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'edit', 'event' => EVENT_ID_LEAGUE_INDIVIDUAL_SUB], PERSON_ID_ADMIN);
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers are allowed to edit events in their own affiliate, but not others
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'edit', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_MANAGER);
		$this->assertGetAsAccessDenied(['controller' => 'Events', 'action' => 'edit', 'event' => EVENT_ID_LEAGUE_INDIVIDUAL_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test edit method as others
	 *
	 * @return void
	 */
	public function testEditAsOthers() {
		// Others are not allowed to edit events
		$this->assertGetAsAccessDenied(['controller' => 'Events', 'action' => 'edit', 'event' => EVENT_ID_LEAGUE_TEAM], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Events', 'action' => 'edit', 'event' => EVENT_ID_LEAGUE_TEAM], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Events', 'action' => 'edit', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Events', 'action' => 'edit', 'event' => EVENT_ID_LEAGUE_TEAM], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Events', 'action' => 'edit', 'event' => EVENT_ID_LEAGUE_TEAM]);
	}

	/**
	 * Test event_type_fields method
	 *
	 * @return void
	 */
	public function testEventTypeFields() {
		$this->enableCsrfToken();

		// Admins are allowed to see event type fields
		$this->assertPostAjaxAsAccessOk(['controller' => 'Events', 'action' => 'event_type_fields'],
			PERSON_ID_ADMIN, ['event_type_id' => EVENT_TYPE_ID_MEMBERSHIP]);

		// Managers are allowed to see event type fields
		$this->assertPostAjaxAsAccessOk(['controller' => 'Events', 'action' => 'event_type_fields'],
			PERSON_ID_MANAGER, ['event_type_id' => EVENT_TYPE_ID_MEMBERSHIP]);

		// Others are not allowed to see event type fields
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Events', 'action' => 'event_type_fields'],
			PERSON_ID_COORDINATOR, ['event_type_id' => EVENT_TYPE_ID_MEMBERSHIP]);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Events', 'action' => 'event_type_fields'],
			PERSON_ID_CAPTAIN, ['event_type_id' => EVENT_TYPE_ID_MEMBERSHIP]);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Events', 'action' => 'event_type_fields'],
			PERSON_ID_PLAYER, ['event_type_id' => EVENT_TYPE_ID_MEMBERSHIP]);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Events', 'action' => 'event_type_fields'],
			PERSON_ID_VISITOR, ['event_type_id' => EVENT_TYPE_ID_MEMBERSHIP]);
		$this->assertPostAjaxAnonymousAccessDenied(['controller' => 'Events', 'action' => 'event_type_fields'],
			['event_type_id' => EVENT_TYPE_ID_MEMBERSHIP]);
	}

	/**
	 * Test add_price method
	 *
	 * @return void
	 */
	public function testAddPrice() {
		// Admins are allowed to add a price
		$this->assertGetAjaxAsAccessOk(['controller' => 'Events', 'action' => 'add_price'],
			PERSON_ID_ADMIN);

		// Managers are allowed to add a price
		$this->assertGetAjaxAsAccessOk(['controller' => 'Events', 'action' => 'add_price'],
			PERSON_ID_MANAGER);

		// Others are not allowed to add a price
		$this->assertGetAsAccessDenied(['controller' => 'Events', 'action' => 'add_price'],
			PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Events', 'action' => 'add_price'],
			PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Events', 'action' => 'add_price'],
			PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Events', 'action' => 'add_price'],
			PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Events', 'action' => 'add_price']);
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
		$this->assertPostAsAccessRedirect(['controller' => 'Events', 'action' => 'delete', 'event' => EVENT_ID_LEAGUE_INDIVIDUAL_MONDAY],
			PERSON_ID_ADMIN, [], ['controller' => 'Events', 'action' => 'index'],
			'The event has been deleted.');

		// Make sure the connected event wasn't deleted, but the connection was
		$events = TableRegistry::get('Events');
		$query = $events->find();
		$this->assertEquals(5, $query->count());
		$query = $events->EventsConnections->find();
		$this->assertEquals(0, $query->count());

		// But not ones with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'Events', 'action' => 'delete', 'event' => EVENT_ID_MEMBERSHIP],
			PERSON_ID_ADMIN, [], ['controller' => 'Events', 'action' => 'index'],
			'#The following records reference this event, so it cannot be deleted#');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to delete events in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Events', 'action' => 'delete', 'event' => EVENT_ID_LEAGUE_INDIVIDUAL_MONDAY],
			PERSON_ID_MANAGER, [], ['controller' => 'Events', 'action' => 'index'],
			'The event has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Events', 'action' => 'delete', 'event' => EVENT_ID_LEAGUE_INDIVIDUAL_SUB],
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

		// Others are not allowed to delete events
		$this->assertPostAsAccessDenied(['controller' => 'Events', 'action' => 'delete', 'event' => EVENT_ID_LEAGUE_INDIVIDUAL_MONDAY],
			PERSON_ID_COORDINATOR);
		$this->assertPostAsAccessDenied(['controller' => 'Events', 'action' => 'delete', 'event' => EVENT_ID_LEAGUE_INDIVIDUAL_MONDAY],
			PERSON_ID_CAPTAIN);
		$this->assertPostAsAccessDenied(['controller' => 'Events', 'action' => 'delete', 'event' => EVENT_ID_LEAGUE_INDIVIDUAL_MONDAY],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'Events', 'action' => 'delete', 'event' => EVENT_ID_LEAGUE_INDIVIDUAL_MONDAY],
			PERSON_ID_VISITOR);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Events', 'action' => 'delete', 'event' => EVENT_ID_LEAGUE_INDIVIDUAL_MONDAY]);
	}

	/**
	 * Test connections method as an admin
	 *
	 * @return void
	 */
	public function testConnectionsAsAdmin() {
		// Admins are allowed to edit event connections
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'connections', 'event' => EVENT_ID_LEAGUE_TEAM], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test connections method as a manager
	 *
	 * @return void
	 */
	public function testConnectionsAsManager() {
		// Managers are allowed to edit event connections
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'connections', 'event' => EVENT_ID_LEAGUE_TEAM], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test connections method as others
	 *
	 * @return void
	 */
	public function testConnectionsAsOthers() {
		// Others are not allowed to edit event connections
		$this->assertGetAsAccessDenied(['controller' => 'Events', 'action' => 'connections', 'event' => EVENT_ID_LEAGUE_TEAM], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Events', 'action' => 'connections', 'event' => EVENT_ID_LEAGUE_TEAM], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Events', 'action' => 'connections', 'event' => EVENT_ID_LEAGUE_TEAM], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Events', 'action' => 'connections', 'event' => EVENT_ID_LEAGUE_TEAM], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Events', 'action' => 'connections', 'event' => EVENT_ID_LEAGUE_TEAM]);
	}

}
