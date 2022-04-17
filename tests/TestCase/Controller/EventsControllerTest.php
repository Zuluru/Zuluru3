<?php
namespace App\Test\TestCase\Controller;

use App\Test\Factory\EventFactory;
use App\Test\Factory\EventsConnectionFactory;
use App\Test\Factory\PaymentFactory;
use App\Test\Factory\RegistrationFactory;
use App\Test\Factory\ResponseFactory;
use App\Test\Factory\SettingFactory;
use App\Test\Factory\TeamFactory;
use App\Test\Scenario\DiverseUsersScenario;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\EmailTrait;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\EventsController Test Case
 */
class EventsControllerTest extends ControllerTestCase {

	use EmailTrait;
	use ScenarioAwareTrait;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.EventTypes',
		'app.Groups',
		'app.Settings',
	];

	public function tearDown(): void {
		// Cleanup any emails that were sent
		$this->cleanupEmailTrait();

		parent::tearDown();
	}

	/**
	 * Test index method
	 */
	public function testIndex(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Event $league_team */
		$league_team = EventFactory::make(['affiliate_id' => $affiliates[0]->id, 'event_type_id' => EVENT_TYPE_ID_TEAMS_FOR_LEAGUES])
			->with('Prices')
			->persist();

		/** @var \App\Model\Entity\Event $affiliate_league_team */
		$affiliate_league_team = EventFactory::make(['affiliate_id' => $affiliates[1]->id, 'event_type_id' => EVENT_TYPE_ID_TEAMS_FOR_LEAGUES])
			->with('Prices')
			->persist();

		// Admins are allowed to view the index, with full edit permissions
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'index'], $admin->id);
		$this->assertResponseContains('/events/view?event=' . $league_team->id);
		$this->assertResponseContains('/events/edit?event=' . $league_team->id);
		$this->assertResponseContains('/events/delete?event=' . $league_team->id);
		$this->assertResponseContains('/events/view?event=' . $affiliate_league_team->id);
		$this->assertResponseContains('/events/edit?event=' . $affiliate_league_team->id);
		$this->assertResponseContains('/events/delete?event=' . $affiliate_league_team->id);

		// Managers are allowed to view the index
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'index'], $manager->id);
		$this->assertResponseContains('/events/view?event=' . $league_team->id);
		$this->assertResponseContains('/events/edit?event=' . $league_team->id);
		$this->assertResponseContains('/events/delete?event=' . $league_team->id);

		// But are not allowed to edit ones in other affiliates
		$this->session(['Zuluru.CurrentAffiliate' => $affiliates[1]->id]);
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'index'], $manager->id);
		$this->assertResponseContains('/events/view?event=' . $affiliate_league_team->id);
		$this->assertResponseNotContains('/events/edit?event=' . $affiliate_league_team->id);
		$this->assertResponseNotContains('/events/delete?event=' . $affiliate_league_team->id);
		$this->logout(); // clear that session setting

		// Others are allowed to view the index, but have no edit permissions
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'index'], $volunteer->id);
		$this->assertResponseContains('/events/view?event=' . $league_team->id);
		$this->assertResponseNotContains('/events/edit?event=' . $league_team->id);
		$this->assertResponseNotContains('/events/delete?event=' . $league_team->id);

		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'index'], $player->id);
		$this->assertResponseContains('/events/view?event=' . $league_team->id);
		$this->assertResponseNotContains('/events/edit?event=' . $league_team->id);
		$this->assertResponseNotContains('/events/delete?event=' . $league_team->id);

		// Others are allowed to view the index, but have no edit permissions
		$this->assertGetAnonymousAccessOk(['controller' => 'Events', 'action' => 'index']);
		$this->assertResponseContains('/events/view?event=' . $league_team->id);
		$this->assertResponseNotContains('/events/edit?event=' . $league_team->id);
		$this->assertResponseNotContains('/events/delete?event=' . $league_team->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test wizard method
	 */
	public function testWizard(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$good_date = FrozenDate::now()->startOfYear()->addWeeks(2);
		$bad_date = FrozenDate::now()->startOfYear()->subWeeks(2);

		/** @var \App\Model\Entity\Event $membership */
		$membership = EventFactory::make(['affiliate_id' => $affiliates[0]->id, 'event_type_id' => EVENT_TYPE_ID_MEMBERSHIP])
			->with('Prices')
			->persist();

		/** @var \App\Model\Entity\Event $league_team */
		$league_team = EventFactory::make(['affiliate_id' => $affiliates[0]->id, 'event_type_id' => EVENT_TYPE_ID_TEAMS_FOR_LEAGUES])
			->with('Prices')
			->persist();

		// We don't reference this event in this test, but it's needed to make pages do what we expect
		EventFactory::make(['affiliate_id' => $affiliates[0]->id, 'event_type_id' => EVENT_TYPE_ID_INDIVIDUALS_FOR_LEAGUES])
			->with('Prices')
			->persist();

		// The admin user here is not a player, so doesn't have access to individual or membership events.
		FrozenTime::setTestNow($good_date);
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'wizard'], $admin->id);
		$this->assertResponseContains('/events/view?event=' . $league_team->id);

		// Admins get access to events before they open.
		FrozenTime::setTestNow($bad_date);
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'wizard'], $admin->id);
		$this->assertResponseContains('/events/view?event=' . $league_team->id);

		// The manager user here is not a player, so doesn't have access to individual or membership events.
		FrozenTime::setTestNow($good_date);
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'wizard'], $manager->id);
		$this->assertResponseContains('/events/view?event=' . $league_team->id);

		// Managers get access to events before they open.
		FrozenTime::setTestNow($bad_date);
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'wizard'], $manager->id);
		$this->assertResponseContains('/events/view?event=' . $league_team->id);

		// The coordinator user here is not a player, so doesn't have access to individual or membership events.
		FrozenTime::setTestNow($good_date);
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'wizard'], $volunteer->id);
		$this->assertResponseContains('/events/view?event=' . $league_team->id);

		// Test as player that already has a membership
		RegistrationFactory::make(['event_id' => $membership->id, 'price_id' => $membership->prices[0]->id, 'person_id' => $player->id])->persist();
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'wizard'], $player->id);
		$this->assertResponseNotContains('/events/wizard/membership');
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
	 */
	public function testView(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Event $league_team */
		$league_team = EventFactory::make(['affiliate_id' => $affiliates[0]->id, 'event_type_id' => EVENT_TYPE_ID_TEAMS_FOR_LEAGUES])
			->with('Prices')
			->persist();

		/** @var \App\Model\Entity\Event $affiliate_league_team */
		$affiliate_league_team = EventFactory::make(['affiliate_id' => $affiliates[1]->id, 'event_type_id' => EVENT_TYPE_ID_TEAMS_FOR_LEAGUES])
			->with('Prices')
			->persist();

		// Admins are allowed to view events, with full edit permissions
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'view', 'event' => $league_team->id], $admin->id);
		$this->assertResponseContains('/events/edit?event=' . $league_team->id);
		$this->assertResponseContains('/events/delete?event=' . $league_team->id);

		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'view', 'event' => $affiliate_league_team->id], $admin->id);
		$this->assertResponseContains('/events/edit?event=' . $affiliate_league_team->id);
		$this->assertResponseContains('/events/delete?event=' . $affiliate_league_team->id);

		// Managers are allowed to view events
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'view', 'event' => $league_team->id], $manager->id);
		$this->assertResponseContains('/events/edit?event=' . $league_team->id);
		$this->assertResponseContains('/events/delete?event=' . $league_team->id);

		// But are not allowed to edit ones in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'view', 'event' => $affiliate_league_team->id], $manager->id);
		$this->assertResponseNotContains('/events/edit?event=' . $affiliate_league_team->id);
		$this->assertResponseNotContains('/events/delete?event=' . $affiliate_league_team->id);

		// Coordinators are allowed to view
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'view', 'event' => $league_team->id], $volunteer->id);

		// Others are allowed to view events, but have no edit permissions
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'view', 'event' => $league_team->id], $player->id);
		$this->assertResponseNotContains('/events/edit?event=' . $league_team->id);
		$this->assertResponseNotContains('/events/delete?event=' . $league_team->id);

		// Others are allowed to view
		$this->assertGetAnonymousAccessOk(['controller' => 'Events', 'action' => 'view', 'event' => $league_team->id]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add method as an admin
	 */
	public function testAddAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Event $membership */
		$membership = EventFactory::make(['affiliate_id' => $affiliates[0]->id, 'event_type_id' => EVENT_TYPE_ID_MEMBERSHIP])
			->with('Prices')
			->persist();

		// Admins are allowed to add new events anywhere
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'add'], $admin->id);
		// TODO: Database has default value of "1" for event affiliate_id, which auto-selects the primary affiliate in normal use.
		// Unit tests get some other ID for the affiliates, #1 doesn't exist, so there is no option selected. Either fix the
		// test or fix the default in the template or get rid of the default in the database. All only applies when there are
		// multiple affiliates anyway, otherwise the form makes the affiliate_id a hidden input.
		$this->assertResponseContains('<option value="' . $affiliates[0]->id . '">' . $affiliates[0]->name . '</option>');
		$this->assertResponseContains('<option value="' . $affiliates[1]->id . '">' . $affiliates[1]->name . '</option>');

		// If an event ID is given, we will clone that event
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'add', 'event' => $membership->id], $admin->id);
		$this->assertResponseRegExp('#<input type="text" name="name"[^>]*value="' . $membership->name . '"#ms');
	}

	/**
	 * Test add method as a manager
	 */
	public function testAddAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Event $membership */
		$membership = EventFactory::make(['affiliate_id' => $affiliates[1]->id, 'event_type_id' => EVENT_TYPE_ID_MEMBERSHIP])
			->with('Prices')
			->persist();

		// Managers are allowed to add new events in their own affiliate, but not others
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'add'], $manager->id);
		$this->assertResponseContains('<input type="hidden" name="affiliate_id" value="' . $affiliates[0]->id . '"/>');
		$this->assertResponseNotContains('<option value="' . $affiliates[1]->id . '">' . $affiliates[1]->name . '</option>');

		$this->assertGetAsAccessDenied(['controller' => 'Events', 'action' => 'add', 'event' => $membership->id], $manager->id);
	}

	/**
	 * Test add method as others
	 */
	public function testAddAsOthers(): void {
		[, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Others are not allowed to add events
		$this->assertGetAsAccessDenied(['controller' => 'Events', 'action' => 'add'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Events', 'action' => 'add'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Events', 'action' => 'add']);
	}

	/**
	 * Test edit method as an admin
	 */
	public function testEditAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Event $league_team */
		$league_team = EventFactory::make(['affiliate_id' => $affiliates[0]->id, 'event_type_id' => EVENT_TYPE_ID_TEAMS_FOR_LEAGUES])
			->with('Prices')
			->persist();

		/** @var \App\Model\Entity\Event $affiliate_league_team */
		$affiliate_league_team = EventFactory::make(['affiliate_id' => $affiliates[1]->id, 'event_type_id' => EVENT_TYPE_ID_TEAMS_FOR_LEAGUES])
			->with('Prices')
			->persist();

		// Admins are allowed to edit events anywhere
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'edit', 'event' => $league_team->id], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'edit', 'event' => $affiliate_league_team->id], $admin->id);
	}

	/**
	 * Test edit method as a manager
	 */
	public function testEditAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Event $league_team */
		$league_team = EventFactory::make(['affiliate_id' => $affiliates[0]->id, 'event_type_id' => EVENT_TYPE_ID_TEAMS_FOR_LEAGUES])
			->with('Prices')
			->persist();

		/** @var \App\Model\Entity\Event $affiliate_league_team */
		$affiliate_league_team = EventFactory::make(['affiliate_id' => $affiliates[1]->id, 'event_type_id' => EVENT_TYPE_ID_TEAMS_FOR_LEAGUES])
			->with('Prices')
			->persist();

		// Managers are allowed to edit events in their own affiliate, but not others
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'edit', 'event' => $league_team->id], $manager->id);
		$this->assertGetAsAccessDenied(['controller' => 'Events', 'action' => 'edit', 'event' => $affiliate_league_team->id], $manager->id);
	}

	/**
	 * Test edit method as others
	 */
	public function testEditAsOthers(): void {
		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Event $league_team */
		$league_team = EventFactory::make(['affiliate_id' => $affiliates[0]->id, 'event_type_id' => EVENT_TYPE_ID_TEAMS_FOR_LEAGUES])
			->with('Prices')
			->persist();

		// Others are not allowed to edit events
		$this->assertGetAsAccessDenied(['controller' => 'Events', 'action' => 'edit', 'event' => $league_team->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Events', 'action' => 'edit', 'event' => $league_team->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Events', 'action' => 'edit', 'event' => $league_team->id]);
	}

	/**
	 * Test event_type_fields method
	 */
	public function testEventTypeFields(): void {
		$this->enableCsrfToken();

		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Admins are allowed to see event type fields
		$this->assertPostAjaxAsAccessOk(['controller' => 'Events', 'action' => 'event_type_fields'],
			$admin->id, ['event_type_id' => EVENT_TYPE_ID_MEMBERSHIP]);

		// Managers are allowed to see event type fields
		$this->assertPostAjaxAsAccessOk(['controller' => 'Events', 'action' => 'event_type_fields'],
			$manager->id, ['event_type_id' => EVENT_TYPE_ID_MEMBERSHIP]);

		// Others are not allowed to see event type fields
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Events', 'action' => 'event_type_fields'],
			$volunteer->id, ['event_type_id' => EVENT_TYPE_ID_MEMBERSHIP]);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Events', 'action' => 'event_type_fields'],
			$player->id, ['event_type_id' => EVENT_TYPE_ID_MEMBERSHIP]);
		$this->assertPostAjaxAnonymousAccessDenied(['controller' => 'Events', 'action' => 'event_type_fields'],
			['event_type_id' => EVENT_TYPE_ID_MEMBERSHIP]);
	}

	/**
	 * Test add_price method
	 */
	public function testAddPrice(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Admins are allowed to add a price
		$this->assertGetAjaxAsAccessOk(['controller' => 'Events', 'action' => 'add_price'],
			$admin->id);

		// Managers are allowed to add a price
		$this->assertGetAjaxAsAccessOk(['controller' => 'Events', 'action' => 'add_price'],
			$manager->id);

		// Others are not allowed to add a price
		$this->assertGetAsAccessDenied(['controller' => 'Events', 'action' => 'add_price'],
			$volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Events', 'action' => 'add_price'],
			$player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Events', 'action' => 'add_price']);
	}

	/**
	 * Test delete method as an admin
	 */
	public function testDeleteAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Event[] $league_teams */
		$league_teams = EventFactory::make(['affiliate_id' => $affiliates[0]->id, 'event_type_id' => EVENT_TYPE_ID_TEAMS_FOR_LEAGUES], 2)
			->with('Prices')
			->persist();
		EventsConnectionFactory::make(['event_id' => $league_teams[0]->id, 'connected_event_id' => $league_teams[1]->id])->persist();

		$events = TableRegistry::getTableLocator()->get('Events');
		$connections = TableRegistry::getTableLocator()->get('EventsConnections');
		$query = $events->find();
		$this->assertEquals(2, $query->count());
		$query = $connections->find();
		$this->assertEquals(1, $query->count());

		// Admins are allowed to delete events
		$this->assertPostAsAccessRedirect(['controller' => 'Events', 'action' => 'delete', 'event' => $league_teams[0]->id],
			$admin->id, [], ['controller' => 'Events', 'action' => 'index'],
			'The event has been deleted.');

		// Make sure the connected event wasn't deleted, but the connection was
		$query = $events->find();
		$this->assertEquals(1, $query->count());
		$query = $connections->find();
		$this->assertEquals(0, $query->count());

		// But not ones with dependencies
		RegistrationFactory::make(['event_id' => $league_teams[1]->id, 'price_id' => $league_teams[1]->prices[0]->id, 'person_id' => $admin->id])->persist();
		$this->assertPostAsAccessRedirect(['controller' => 'Events', 'action' => 'delete', 'event' => $league_teams[1]->id],
			$admin->id, [], ['controller' => 'Events', 'action' => 'index'],
			'#The following records reference this event, so it cannot be deleted#');
	}

	/**
	 * Test delete method as a manager
	 */
	public function testDeleteAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Event $league_team */
		$league_team = EventFactory::make(['affiliate_id' => $affiliates[0]->id, 'event_type_id' => EVENT_TYPE_ID_TEAMS_FOR_LEAGUES])
			->with('Prices')
			->persist();

		/** @var \App\Model\Entity\Event $affiliate_league_team */
		$affiliate_league_team = EventFactory::make(['affiliate_id' => $affiliates[1]->id, 'event_type_id' => EVENT_TYPE_ID_TEAMS_FOR_LEAGUES])
			->with('Prices')
			->persist();

		// Managers are allowed to delete events in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Events', 'action' => 'delete', 'event' => $league_team->id],
			$manager->id, [], ['controller' => 'Events', 'action' => 'index'],
			'The event has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Events', 'action' => 'delete', 'event' => $affiliate_league_team->id],
			$manager->id);
	}

	/**
	 * Test delete method as others
	 */
	public function testDeleteAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Event $league_team */
		$league_team = EventFactory::make(['affiliate_id' => $affiliates[0]->id, 'event_type_id' => EVENT_TYPE_ID_TEAMS_FOR_LEAGUES])
			->with('Prices')
			->persist();

		// Others are not allowed to delete events
		$this->assertPostAsAccessDenied(['controller' => 'Events', 'action' => 'delete', 'event' => $league_team->id],
			$volunteer->id);
		$this->assertPostAsAccessDenied(['controller' => 'Events', 'action' => 'delete', 'event' => $league_team->id],
			$player->id);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Events', 'action' => 'delete', 'event' => $league_team->id]);
	}

	/**
	 * Test connections method as an admin
	 */
	public function testConnectionsAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Event[] $league_teams */
		$league_teams = EventFactory::make(['affiliate_id' => $affiliates[0]->id, 'event_type_id' => EVENT_TYPE_ID_TEAMS_FOR_LEAGUES], 2)
			->with('Prices')
			->persist();
		EventsConnectionFactory::make(['event_id' => $league_teams[0]->id, 'connected_event_id' => $league_teams[1]->id])->persist();

		// Admins are allowed to edit event connections
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'connections', 'event' => $league_teams[0]->id], $admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test connections method as a manager
	 */
	public function testConnectionsAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Event[] $league_teams */
		$league_teams = EventFactory::make(['affiliate_id' => $affiliates[0]->id, 'event_type_id' => EVENT_TYPE_ID_TEAMS_FOR_LEAGUES], 2)
			->with('Prices')
			->persist();
		EventsConnectionFactory::make(['event_id' => $league_teams[0]->id, 'connected_event_id' => $league_teams[1]->id])->persist();

		// Managers are allowed to edit event connections
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'connections', 'event' => $league_teams[0]->id], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test connections method as others
	 */
	public function testConnectionsAsOthers(): void {
		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Event[] $league_teams */
		$league_teams = EventFactory::make(['affiliate_id' => $affiliates[0]->id, 'event_type_id' => EVENT_TYPE_ID_TEAMS_FOR_LEAGUES], 2)
			->with('Prices')
			->persist();
		EventsConnectionFactory::make(['event_id' => $league_teams[0]->id, 'connected_event_id' => $league_teams[1]->id])->persist();

		// Others are not allowed to edit event connections
		$this->assertGetAsAccessDenied(['controller' => 'Events', 'action' => 'connections', 'event' => $league_teams[0]->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Events', 'action' => 'connections', 'event' => $league_teams[0]->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Events', 'action' => 'connections', 'event' => $league_teams[0]->id]);
	}

	/**
	 * Test refund method as an admin
	 */
	public function testRefundAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Event $league_team */
		$league_team = EventFactory::make(['affiliate_id' => $affiliates[0]->id, 'event_type_id' => EVENT_TYPE_ID_TEAMS_FOR_LEAGUES])
			->with('Prices')
			->persist();
		$registrations = RegistrationFactory::make([
			['event_id' => $league_team->id, 'price_id' => $league_team->prices[0]->id, 'person_id' => $manager->id, 'total_amount' => 10, 'payment' => 'Paid'],
			['event_id' => $league_team->id, 'price_id' => $league_team->prices[0]->id, 'person_id' => $volunteer->id, 'total_amount' => 10, 'payment' => 'Paid'],
			['event_id' => $league_team->id, 'price_id' => $league_team->prices[0]->id, 'person_id' => $player->id, 'total_amount' => 10, 'payment' => 'Paid'],
		])->persist();
		$payment = PaymentFactory::make(['registration_id' => $registrations[2]->id, 'payment_amount' => 10])->persist();

		// Common data
		$refund_data = [
			'payment_type' => 'Refund',
			'payment_method' => 'Other',
			'amount_type' => 'input',
			'mark_refunded' => false,
			'notes' => 'Test notes',
			'registrations' => [
				$registrations[0]->id => true,
				$registrations[1]->id => true,
				$registrations[2]->id => true,
			],
		];

		// Admins are allowed to refund payments
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'refund', 'event' => $league_team->id], $admin->id);
		$this->assertResponseContains('<input type="checkbox" name="mark_refunded" value="1"');

		// Try to refund more than the paid amount
		$this->assertPostAsAccessOk(['controller' => 'Events', 'action' => 'refund', 'event' => $league_team->id],
			$admin->id, $refund_data + ['payment_amount' => 1000]);
		$this->assertResponseContains('This would refund more than the amount paid.');
		$this->assertResponseContains('This registration has no payments recorded.');
		$this->assertResponseContains('<input type="checkbox" name="mark_refunded" value="1"');

		// Try to refund $0
		$this->assertPostAsAccessOk(['controller' => 'Events', 'action' => 'refund', 'event' => $league_team->id],
			$admin->id, $refund_data + ['payment_amount' => 0]);
		$this->assertResponseContains('Refund amounts must be positive.');
		$this->assertResponseContains('This registration has no payments recorded.');
		$this->assertResponseContains('<input type="checkbox" name="mark_refunded" value="1"');

		// Try to refund just the right amount; player is the only one with all the right payments
		$refund_data['registrations'] = [
			$registrations[2]->id => true,
		];
		$this->assertPostAsAccessOk(['controller' => 'Events', 'action' => 'refund', 'event' => $league_team->id],
			$admin->id, $refund_data + ['payment_amount' => 10]);
		$this->assertResponseContains('The refunds have been saved.');
		/** @var \App\Model\Entity\Registration $registration */
		$registration = TableRegistry::getTableLocator()->get('Registrations')->get($registrations[2]->id, [
			'contain' => ['Payments' => [
				'queryBuilder' => function (Query  $q) {
					return $q->order(['Payments.created']);
				},
			]]
		]);
		$this->assertEquals('Paid', $registration->payment);
		$this->assertCount(2, $registration->payments);
		$this->assertEquals(10, $registration->payments[0]->refunded_amount);
		$this->assertEquals($admin->id, $registration->payments[0]->updated_person_id);
		$refund = $registration->payments[1];
		$this->assertEquals($registrations[2]->id, $refund->registration_id);
		$this->assertEquals('Refund', $refund->payment_type);
		$this->assertEquals(-10, $refund->payment_amount);
		$this->assertEquals(0, $refund->refunded_amount);
		$this->assertEquals('Test notes', $refund->notes);
		$this->assertEquals($admin->id, $refund->created_person_id);
		$this->assertEquals('Other', $refund->payment_method);
		$this->assertEquals($payment->id, $refund->payment_id);

		$this->assertMailCount(1);
		$this->assertMailSentFrom('admin@zuluru.org');
		$this->assertMailSentTo($player->user->email);
		$this->assertMailSentWith('Test Zuluru Affiliate Registration refunded', 'Subject');
		$this->assertMailContains('You have been issued a refund of CA$10.00 for your registration for ' . $league_team->name . '.');

		// Try to refund without selecting any registrations
		unset($refund_data['registrations']);
		$this->assertPostAsAccessOk(['controller' => 'Events', 'action' => 'refund', 'event' => $league_team->id],
			$admin->id, $refund_data);
		$this->assertResponseContains('You didn&#039;t select any registrations to refund.');
	}

	/**
	 * Test refunding of team events
	 */
	public function testRefundTeamEvent(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, , , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Team $team */
		$team = TeamFactory::make()->with('Divisions.Leagues')->persist();
		/** @var \App\Model\Entity\Event $league_team */
		$league_team = EventFactory::make(['affiliate_id' => $affiliates[0]->id, 'event_type_id' => EVENT_TYPE_ID_TEAMS_FOR_LEAGUES, 'division_id' => $team->division_id])
			->with('Prices')
			->persist();
		$registration = RegistrationFactory::make(['event_id' => $league_team->id, 'price_id' => $league_team->prices[0]->id, 'person_id' => $player->id, 'total_amount' => 10, 'payment' => 'Paid'])
			->persist();
		$payment = PaymentFactory::make(['registration_id' => $registration->id, 'payment_amount' => 10])->persist();
		ResponseFactory::make(['event_id' => $league_team->id, 'registration_id' => $registration->id, 'question_id' => TEAM_ID_CREATED, 'answer_text' => $team->id])
			->persist();

		$this->assertPostAsAccessOk(['controller' => 'Events', 'action' => 'refund', 'event' => $league_team->id], $admin->id, [
			'payment_type' => 'Refund',
			'payment_method' => 'Other',
			'payment_amount' => 10,
			'amount_type' => 'input',
			'mark_refunded' => true,
			'notes' => 'Full refund',
			'registrations' => [
				$registration->id => true,
			],
		]);
		$this->assertResponseContains('The refunds have been saved.');
		/** @var \App\Model\Entity\Registration $registration */
		$registration = TableRegistry::getTableLocator()->get('Registrations')->get($registration->id, [
			'contain' => ['Payments' => [
				'queryBuilder' => function (Query  $q) {
					return $q->order(['Payments.created']);
				},
			]]
		]);
		$this->assertEquals('Cancelled', $registration->payment);
		$this->assertCount(2, $registration->payments);
		$this->assertEquals(10, $registration->payments[0]->refunded_amount);
		$this->assertEquals($admin->id, $registration->payments[0]->updated_person_id);
		$refund = $registration->payments[1];
		$this->assertEquals($registration->id, $refund->registration_id);
		$this->assertEquals('Refund', $refund->payment_type);
		$this->assertEquals(-10, $refund->payment_amount);
		$this->assertEquals(0, $refund->refunded_amount);
		$this->assertEquals('Full refund', $refund->notes);
		$this->assertEquals($admin->id, $refund->created_person_id);
		$this->assertEquals('Other', $refund->payment_method);
		$this->assertEquals($payment->id, $refund->payment_id);

		$this->assertMailCount(1);
		$this->assertMailSentFrom('admin@zuluru.org');
		$this->assertMailSentTo($player->user->email);
		$this->assertMailSentWith('Test Zuluru Affiliate Registration refunded', 'Subject');
		$this->assertMailContains('You have been issued a refund of CA$10.00 for your registration for ' . $league_team->name . '.');

		$this->expectException(RecordNotFoundException::class);
		TableRegistry::getTableLocator()->get('Teams')->get($team->id);
	}

	/**
	 * Test issuing bulk credits as an admin
	 */
	public function testCreditAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, , , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Event $league_team */
		$league_team = EventFactory::make(['affiliate_id' => $affiliates[0]->id, 'event_type_id' => EVENT_TYPE_ID_TEAMS_FOR_LEAGUES])
			->with('Prices')
			->persist();
		$registration = RegistrationFactory::make(['event_id' => $league_team->id, 'price_id' => $league_team->prices[0]->id, 'person_id' => $player->id, 'total_amount' => 10, 'payment' => 'Paid'])
			->persist();
		$payment = PaymentFactory::make(['registration_id' => $registration->id, 'payment_amount' => 10])->persist();

		// Common data
		$refund_data = [
			'payment_type' => 'Credit',
			'payment_method' => 'Other',
			'amount_type' => 'input',
			'mark_refunded' => true,
			'notes' => 'Test notes',
			'credit_notes' => 'Test credit notes',
			'registrations' => [
				$registration->id => true,
			],
		];

		// Try to credit just the right amount
		$this->assertPostAsAccessOk(['controller' => 'Events', 'action' => 'refund', 'event' => $league_team->id],
			$admin->id, $refund_data + ['payment_amount' => 10]);
		$this->assertResponseContains('The credits have been saved.');
		/** @var \App\Model\Entity\Registration $registration */
		$registration = TableRegistry::getTableLocator()->get('Registrations')->get($registration->id, [
			'contain' => ['Payments' => [
				'queryBuilder' => function (Query  $q) {
					return $q->order(['Payments.created']);
				},
			]]
		]);
		$this->assertEquals('Cancelled', $registration->payment);
		$this->assertCount(2, $registration->payments);
		$this->assertEquals(10, $registration->payments[0]->refunded_amount);
		$this->assertEquals($admin->id, $registration->payments[0]->updated_person_id);
		$refund = $registration->payments[1];
		$this->assertEquals($registration->id, $refund->registration_id);
		$this->assertEquals('Credit', $refund->payment_type);
		$this->assertEquals(-10, $refund->payment_amount);
		$this->assertEquals(0, $refund->refunded_amount);
		$this->assertEquals('Test notes', $refund->notes);
		$this->assertEquals($admin->id, $refund->created_person_id);
		$this->assertEquals('Other', $refund->payment_method);
		$this->assertEquals($payment->id, $refund->payment_id);

		$credits = TableRegistry::getTableLocator()->get('Credits')->find()
			->where(['person_id' => $player->id])
			->toArray();
		$this->assertCount(1, $credits);
		$this->assertEquals(10, $credits[0]->amount);
		$this->assertEquals(0, $credits[0]->amount_used);
		$this->assertEquals('Test credit notes', $credits[0]->notes);
		$this->assertEquals($admin->id, $credits[0]->created_person_id);
		$this->assertEquals($registration->payments[1]->id, $credits[0]->payment_id);

		$this->assertMailCount(1);
		$this->assertMailSentFrom('admin@zuluru.org');
		$this->assertMailSentTo($player->user->email);
		$this->assertMailSentWith('Test Zuluru Affiliate Registration credited', 'Subject');
		$this->assertMailContains('You have been issued a credit of CA$10.00 for your registration for ' . $league_team->name . '.');
		$this->assertMailContains('This credit can be redeemed towards any future purchase on the Test Zuluru Affiliate site');
	}

	/**
	 * Test translation
	 */
	public function testTranslation(): void {
		$this->markTestIncomplete('Fix this test once translations are changed.');

		[$admin, , , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Event $membership */
		$membership = EventFactory::make(['affiliate_id' => $affiliates[0]->id, 'event_type_id' => EVENT_TYPE_ID_MEMBERSHIP, 'name' => 'Membership'])
			->with('Translations', ['name' => 'Adhésion'])
			->with('Prices')
			->persist();

		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'view', 'event' => $membership->id], $player->id);
		$this->assertResponseContains('<h2>Membership</h2>');

		SettingFactory::make(['person_id' => $player->id, 'category' => 'personal', 'name' => 'language', 'value' => 'fr'])->persist();
		$this->assertGetAsAccessOk(['controller' => 'Events', 'action' => 'view', 'event' => $membership->id], $player->id);
		$this->assertResponseContains('<h2>Adhésion</h2>');
	}

}
