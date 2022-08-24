<?php
namespace App\Test\TestCase\Controller;

use App\Model\Entity\Registration;
use App\Test\Factory\CreditFactory;
use App\Test\Factory\EventFactory;
use App\Test\Scenario\DiverseRegistrationsScenario;
use App\Test\Scenario\DiverseUsersScenario;
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\EmailTrait;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\RegistrationsController Test Case
 */
class RegistrationsControllerTest extends ControllerTestCase {

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
	 * Set up mock API objects to avoid talking to various servers
	 */
	public function controllerSpy($event, $controller = null) {
		parent::controllerSpy($event, $controller);

		$globalListeners = Configure::read('App.globalListeners');

		if (array_key_exists('PayPal', $globalListeners)) {
			$globalListeners['PayPal']->api = \PayPalPayment\Test\Mock::setup($this);
		}
		if (array_key_exists('Stripe', $globalListeners)) {
			$globalListeners['Stripe']->api = \StripePayment\Test\Mock::setup($this);
		}
	}

	/**
	 * Test full_list method
	 */
	public function testFullList(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$registrations = $this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'coordinator' => $volunteer,
			'member' => $player,
			'player' => $player,
		]);

		$membership = $registrations[DiverseRegistrationsScenario::$MEMBERSHIP];
		$individual = $registrations[DiverseRegistrationsScenario::$INDIVIDUAL];

		// Admins are allowed to see the full list of registrations
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'full_list', 'event' => $membership->event_id], $admin->id);
		$this->assertResponseContains('/registrations/view?registration=' . $membership->id);
		$this->assertResponseContains('/registrations/edit?registration=' . $membership->id);

		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'full_list', 'event' => $membership->event_id, '_ext' => 'csv'], $admin->id);
		$this->assertResponseContains('Home Phone');
		$this->assertResponseContains('Total Amount');

		// Managers are allowed to see the full list of registrations
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'full_list', 'event' => $membership->event_id], $manager->id);
		$this->assertResponseContains('/registrations/view?registration=' . $membership->id);
		$this->assertResponseContains('/registrations/edit?registration=' . $membership->id);

		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'full_list', 'event' => $membership->event_id, '_ext' => 'csv'], $manager->id);
		$this->assertResponseContains('Home Phone');
		$this->assertResponseContains('Total Amount');

		// Coordinators are not allowed to see the full list of registrations
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'full_list', 'event' => $membership->event_id], $volunteer->id);

		// Except that they are allowed to see the list of registrations for divisions they coordinate, but without registration view and edit links
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'full_list', 'event' => $individual->event_id], $volunteer->id);
		$this->assertResponseContains('/people/view?person=' . $player->id);
		$this->assertResponseNotContains('/registrations/view?registration=' . $individual->id);
		$this->assertResponseNotContains('/registrations/edit?registration=' . $individual->id);

		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'full_list', 'event' => $individual->event_id, '_ext' => 'csv'], $volunteer->id);
		$this->assertResponseNotContains('Home Phone');
		$this->assertResponseNotContains('Work Phone');
		$this->assertResponseNotContains('Work Ext');
		$this->assertResponseNotContains('Mobile Phone');
		$this->assertResponseContains('Total Amount');

		// Others are not allowed to see the list of registrations
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'full_list', 'event' => $membership->event_id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'full_list', 'event' => $membership->event_id]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test summary method
	 */
	public function testSummary(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$registrations = $this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'coordinator' => $volunteer,
			'member' => $player,
			'player' => $player,
		]);

		$membership = $registrations[DiverseRegistrationsScenario::$MEMBERSHIP];
		$individual = $registrations[DiverseRegistrationsScenario::$INDIVIDUAL];

		// Admins are allowed to see the summary of registrations
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'summary', 'event' => $membership->event_id], $admin->id);

		// Managers are allowed to see the summary of registrations
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'summary', 'event' => $membership->event_id], $manager->id);

		// Coordinators are not allowed to see the summary of registrations
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'summary', 'event' => $membership->event_id], $volunteer->id);

		// Except that they are allowed to see the summary of registrations for divisions they coordinate
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'summary', 'event' => $individual->event_id], $volunteer->id);

		// Others are not allowed to see the summary of registrations
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'summary', 'event' => $membership->event_id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'summary', 'event' => $membership->event_id]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test statistics method
	 */
	public function testStatistics(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'coordinator' => $volunteer,
			'member' => $player,
			'captain' => $player,
			'player' => $player,
		]);

		// Admins are allowed to see statistics
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'statistics'], $admin->id);

		// Managers are allowed to see statistics
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'statistics'], $manager->id);

		// Others are not allowed to see statistics
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'statistics'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'statistics'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'statistics']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test report method
	 */
	public function testReport(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'coordinator' => $volunteer,
			'member' => $player,
			'captain' => $player,
			'player' => $player,
		]);

		// Admins are allowed to report
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'report'], $admin->id);

		// Managers are allowed to report
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'report'], $manager->id);

		// Others are not allowed to report
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'report'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'report'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'report']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test accounting method
	 */
	public function testAccounting(): void {
		$this->markTestIncomplete('Operation not implemented yet.');

		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'coordinator' => $volunteer,
			'member' => $player,
			'captain' => $player,
			'player' => $player,
		]);

		// Admins are allowed to see accounting
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'accounting'], $admin->id);

		// Managers are allowed to see accounting
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'accounting'], $manager->id);

		// Others are not allowed to see accounting
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'accounting'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'accounting'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'accounting']);
	}

	/**
	 * Test view method
	 */
	public function testView(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$registrations = $this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'coordinator' => $volunteer,
			'member' => $player,
			'player' => $player,
			'membershipPayment' => 'Paid',
		]);

		$membership = $registrations[DiverseRegistrationsScenario::$MEMBERSHIP];
		$individual = $registrations[DiverseRegistrationsScenario::$INDIVIDUAL];

		$affiliate_registrations = $this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[1],
			'captain' => $player,
		]);
		$affiliate_team = $affiliate_registrations[DiverseRegistrationsScenario::$TEAM];

		// Admins are allowed to view registrations, with full edit permissions
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'view', 'registration' => $membership->id], $admin->id);
		$this->assertResponseContains('/registrations/edit?registration=' . $membership->id);
		// Paid registrations cannot be removed
		$this->assertResponseNotContains('/registrations/unregister?registration=' . $membership->id);

		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'view', 'registration' => $individual->id], $admin->id);
		$this->assertResponseContains('/registrations/edit?registration=' . $individual->id);
		$this->assertResponseContains('/registrations/unregister?registration=' . $individual->id);

		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'view', 'registration' => $affiliate_team->id], $admin->id);
		$this->assertResponseContains('/registrations/edit?registration=' . $affiliate_team->id);
		$this->assertResponseContains('/registrations/unregister?registration=' . $affiliate_team->id);

		// Managers are allowed to view registrations
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'view', 'registration' => $membership->id], $manager->id);
		$this->assertResponseContains('/registrations/edit?registration=' . $membership->id);
		$this->assertResponseNotContains('/registrations/unregister?registration=' . $membership->id);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'view', 'registration' => $affiliate_team->id], $manager->id);

		// Others are not allowed to view registrations
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'view', 'registration' => $individual->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'view', 'registration' => $membership->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'view', 'registration' => $membership->id]);
	}

	/**
	 * Test register method as an admin
	 */
	public function testRegisterAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$now = FrozenDate::now();

		/** @var \App\Model\Entity\Event $event */
		$event = EventFactory::make([
			'affiliate_id' => $admin->affiliates[0]->id,
			'event_type_id' => EVENT_TYPE_ID_TEAMS_FOR_LEAGUES,
			'open' => $now,
			'close' => $now->addWeeks(4),
		])
			->with('Prices', [
				'open' => $now,
				'close' => $now->addWeeks(4),
			])
			->persist();

		// Admins are allowed to register, within or somewhat before the date range
		FrozenDate::setTestNow($now->subDays(30));
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'register', 'event' => $event->id], $admin->id);

		FrozenDate::setTestNow($now->subDays(45));
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'register', 'event' => $event->id], $admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test register method as a manager
	 */
	public function testRegisterAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$now = FrozenDate::now();

		/** @var \App\Model\Entity\Event $event */
		$event = EventFactory::make([
			'affiliate_id' => $admin->affiliates[0]->id,
			'event_type_id' => EVENT_TYPE_ID_TEAMS_FOR_LEAGUES,
			'open' => $now,
			'close' => $now->addWeeks(4),
		])
			->with('Prices', [
				'open' => $now,
				'close' => $now->addWeeks(4),
			])
			->persist();

		// Managers are allowed to register, within or somewhat before the date range
		FrozenDate::setTestNow($now->subDays(60));
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'register', 'event' => $event->id], $manager->id);

		FrozenDate::setTestNow($now->subDays(90));
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'register', 'event' => $event->id], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test register method as a coordinator
	 */
	public function testRegisterAsCoordinator(): void {
		[$admin, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer']);
		$now = FrozenDate::now();

		/** @var \App\Model\Entity\Event $event */
		$event = EventFactory::make([
			'affiliate_id' => $admin->affiliates[0]->id,
			'event_type_id' => EVENT_TYPE_ID_TEAMS_FOR_LEAGUES,
			'open' => $now,
			'close' => $now->addWeeks(4),
		])
			->with('Prices', [
				'open' => $now,
				'close' => $now->addWeeks(4),
			])
			->persist();

		// Coordinators are allowed to register, within the date range
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'register', 'event' => $event->id], $volunteer->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test register method as a player
	 */
	public function testRegisterAsPlayer(): void {
		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);
		$now = FrozenDate::now();

		/** @var \App\Model\Entity\Event $event */
		$event = EventFactory::make([
			'affiliate_id' => $admin->affiliates[0]->id,
			'event_type_id' => EVENT_TYPE_ID_TEAMS_FOR_LEAGUES,
			'open' => $now,
			'close' => $now->addWeeks(4),
		])
			->with('Prices', [
				'open' => $now,
				'close' => $now->addWeeks(4),
			])
			->persist();

		// Players are allowed to register, within the date range
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'register', 'event' => $event->id], $player->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test register method without being logged in
	 */
	public function testRegisterAsAnonymous(): void {
		$now = FrozenDate::now();

		/** @var \App\Model\Entity\Event $event */
		$event = EventFactory::make([
			'event_type_id' => EVENT_TYPE_ID_TEAMS_FOR_LEAGUES,
			'open' => $now,
			'close' => $now->addWeeks(4),
		])
			->with('Prices', [
				'open' => $now,
				'close' => $now->addWeeks(4),
			])
			->persist();
		$this->assertGetAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'register', 'event' => $event->id]);
	}

	/**
	 * Test register_payment_fields method
	 */
	public function testRegisterPaymentFields(): void {
		$this->enableCsrfToken();

		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$now = FrozenDate::now();

		/** @var \App\Model\Entity\Event $event */
		$event = EventFactory::make([
			'affiliate_id' => $admin->affiliates[0]->id,
			'event_type_id' => EVENT_TYPE_ID_TEAMS_FOR_LEAGUES,
			'open' => $now,
			'close' => $now->addWeeks(4),
		])
			->with('Prices', [
				'open' => $now,
				'close' => $now->addWeeks(4),
			])
			->persist();
		$price = $event->prices[0];

		// Anyone logged in is allowed to get register payment fields, within the date range
		$this->assertPostAjaxAsAccessOk(['controller' => 'Registrations', 'action' => 'register_payment_fields'],
			$admin->id, ['price_id' => $price->id]);
		$this->assertPostAjaxAsAccessOk(['controller' => 'Registrations', 'action' => 'register_payment_fields'],
			$manager->id, ['price_id' => $price->id]);
		$this->assertPostAjaxAsAccessOk(['controller' => 'Registrations', 'action' => 'register_payment_fields'],
			$volunteer->id, ['price_id' => $price->id]);
		$this->assertPostAjaxAsAccessOk(['controller' => 'Registrations', 'action' => 'register_payment_fields'],
			$player->id, ['price_id' => $price->id]);

		// Others are not allowed to register payment fields
		$this->assertPostAjaxAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'register_payment_fields'],
			['price_id' => $price->id]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test redeem method for success
	 */
	public function testRedeemSuccess(): void {
		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);
		$registrations = $this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'member' => $player,
		]);

		CreditFactory::make(['person_id' => $player->id, 'affiliate_id' => $admin->affiliates[0]->id])->persist();

		// People are allowed to redeem their own credits
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'redeem', 'registration' => $registrations[DiverseRegistrationsScenario::$MEMBERSHIP]->id], $player->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test redeem method for various failure scenarios
	 */
	public function testRedeemFailure(): void {
		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);
		$registrations = $this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'member' => $player,
			'captain' => $player,
			'teamPayment' => 'Paid',
		]);

		$this->assertGetAsAccessRedirect(['controller' => 'Registrations', 'action' => 'redeem', 'registration' => $registrations[DiverseRegistrationsScenario::$MEMBERSHIP]->id],
			$player->id, ['controller' => 'Registrations', 'action' => 'checkout'],
			'You have no available credits.');

		CreditFactory::make(['person_id' => $player->id, 'affiliate_id' => $admin->affiliates[0]->id])->persist();

		$this->assertGetAsAccessRedirect(['controller' => 'Registrations', 'action' => 'redeem', 'registration' => $registrations[DiverseRegistrationsScenario::$TEAM]->id],
			$player->id, ['controller' => 'Registrations', 'action' => 'checkout'],
			'This registration is marked as Paid.');
	}

	/**
	 * Test redeem method as others
	 */
	public function testRedeemAsOthers(): void {
		[$admin, $manager, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager', 'player']);
		$registrations = $this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'member' => $player,
		]);

		$membership = $registrations[DiverseRegistrationsScenario::$MEMBERSHIP];

		CreditFactory::make(['person_id' => $player->id, 'affiliate_id' => $admin->affiliates[0]->id])->persist();

		// Even admins are not allowed to redeem credits on behalf of players
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'redeem', 'registration' => $membership->id], $admin->id);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'redeem', 'registration' => $membership->id], $manager->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'redeem', 'registration' => $membership->id]);
	}

	/**
	 * Test checkout method for success
	 */
	public function testCheckoutSuccess(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'member' => $manager,
			'captain' => $volunteer,
			'player' => $player,
		]);

		// Anyone with outstanding registrations is allowed to check out
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'checkout'], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'checkout'], $volunteer->id);
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'checkout'], $player->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test checkout method for various failure scenarios
	 */
	public function testCheckoutFailure(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'member' => $manager,
			'captain' => $volunteer,
			'player' => $player,
			'membershipPayment' => 'Paid',
			'teamPayment' => 'Paid',
			'individualPayment' => 'Paid',
		]);

		// Anyone without an outstanding registration has no reason to check out
		$this->assertGetAsAccessRedirect(['controller' => 'Registrations', 'action' => 'checkout'],
			$admin->id, ['controller' => 'Events', 'action' => 'wizard']);
		$this->assertGetAsAccessRedirect(['controller' => 'Registrations', 'action' => 'checkout'],
			$manager->id, ['controller' => 'Events', 'action' => 'wizard']);
		$this->assertGetAsAccessRedirect(['controller' => 'Registrations', 'action' => 'checkout'],
			$volunteer->id, ['controller' => 'Events', 'action' => 'wizard']);
		$this->assertGetAsAccessRedirect(['controller' => 'Registrations', 'action' => 'checkout'],
			$player->id, ['controller' => 'Events', 'action' => 'wizard']);

		$this->assertGetAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'checkout']);
	}

	/**
	 * Test unregister method as an admin
	 */
	public function testUnregisterAsAdmin(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$registrations = $this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'member' => $manager,
			'captain' => $volunteer,
			'player' => $player,
		]);

		// Admins are allowed to unregister anyone
		$this->assertGetAsAccessRedirect(['controller' => 'Registrations', 'action' => 'unregister', 'registration' => $registrations[DiverseRegistrationsScenario::$MEMBERSHIP]->id],
			$admin->id, '/',
			'Successfully unregistered from this event.');
		$this->assertGetAsAccessRedirect(['controller' => 'Registrations', 'action' => 'unregister', 'registration' => $registrations[DiverseRegistrationsScenario::$TEAM]->id],
			$admin->id, '/',
			'Successfully unregistered from this event.');
		$this->assertGetAsAccessRedirect(['controller' => 'Registrations', 'action' => 'unregister', 'registration' => $registrations[DiverseRegistrationsScenario::$INDIVIDUAL]->id],
			$admin->id, '/',
			'Successfully unregistered from this event.');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test unregister method as a manager
	 */
	public function testUnregisterAsManager(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$registrations = $this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'member' => $manager,
			'captain' => $volunteer,
			'player' => $player,
		]);

		// Managers are allowed to unregister anyone
		$this->assertGetAsAccessRedirect(['controller' => 'Registrations', 'action' => 'unregister', 'registration' => $registrations[DiverseRegistrationsScenario::$MEMBERSHIP]->id],
			$manager->id, '/registrations/checkout',
			'Successfully unregistered from this event.');
		$this->assertGetAsAccessRedirect(['controller' => 'Registrations', 'action' => 'unregister', 'registration' => $registrations[DiverseRegistrationsScenario::$TEAM]->id],
			$manager->id, '/',
			'Successfully unregistered from this event.');
		$this->assertGetAsAccessRedirect(['controller' => 'Registrations', 'action' => 'unregister', 'registration' => $registrations[DiverseRegistrationsScenario::$INDIVIDUAL]->id],
			$manager->id, '/',
			'Successfully unregistered from this event.');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test unregister method as a coordinator
	 */
	public function testUnregisterAsCoordinator(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$registrations = $this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'member' => $manager,
			'captain' => $volunteer,
			'player' => $player,
		]);

		// Coordinators are allowed to unregister themselves only
		$this->assertGetAsAccessRedirect(['controller' => 'Registrations', 'action' => 'unregister', 'registration' => $registrations[DiverseRegistrationsScenario::$TEAM]->id],
			$volunteer->id, ['controller' => 'Registrations', 'action' => 'checkout'],
			'Successfully unregistered from this event.');
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'unregister', 'registration' => $registrations[DiverseRegistrationsScenario::$MEMBERSHIP]->id],
			$volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'unregister', 'registration' => $registrations[DiverseRegistrationsScenario::$INDIVIDUAL]->id],
			$volunteer->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test unregister method as a player
	 */
	public function testUnregisterAsPlayer(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$registrations = $this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'member' => $manager,
			'captain' => $volunteer,
			'player' => $player,
		]);

		// Players are allowed to unregister themselves only
		$this->assertGetAsAccessRedirect(['controller' => 'Registrations', 'action' => 'unregister', 'registration' => $registrations[DiverseRegistrationsScenario::$INDIVIDUAL]->id],
			$player->id, ['controller' => 'Registrations', 'action' => 'checkout'],
			'Successfully unregistered from this event.');
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'unregister', 'registration' => $registrations[DiverseRegistrationsScenario::$MEMBERSHIP]->id],
			$player->id);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'unregister', 'registration' => $registrations[DiverseRegistrationsScenario::$TEAM]->id],
			$player->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add_payment method as an admin
	 */
	public function testAddPaymentAsAdmin(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$registrations = $this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'member' => $manager,
			'captain' => $volunteer,
			'player' => $player,
		]);

		// Admins are allowed to add payments
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'add_payment', 'registration' => $registrations[DiverseRegistrationsScenario::$MEMBERSHIP]->id], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'add_payment', 'registration' => $registrations[DiverseRegistrationsScenario::$TEAM]->id], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'add_payment', 'registration' => $registrations[DiverseRegistrationsScenario::$INDIVIDUAL]->id], $admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add_payment method as a manager
	 */
	public function testAddPaymentAsManager(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$registrations = $this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'member' => $manager,
			'captain' => $volunteer,
			'player' => $player,
		]);

		// Managers are allowed to add payments
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'add_payment', 'registration' => $registrations[DiverseRegistrationsScenario::$MEMBERSHIP]->id], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'add_payment', 'registration' => $registrations[DiverseRegistrationsScenario::$TEAM]->id], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'add_payment', 'registration' => $registrations[DiverseRegistrationsScenario::$INDIVIDUAL]->id], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add_payment method as others
	 */
	public function testAddPaymentAsOthers(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$registrations = $this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'member' => $manager,
			'captain' => $volunteer,
			'player' => $player,
		]);

		// Others are not allowed to add payments
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'add_payment', 'registration' => $registrations[DiverseRegistrationsScenario::$MEMBERSHIP]->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'add_payment', 'registration' => $registrations[DiverseRegistrationsScenario::$MEMBERSHIP]->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'add_payment', 'registration' => $registrations[DiverseRegistrationsScenario::$MEMBERSHIP]->id]);
	}

	/**
	 * Test refund_payment method as an admin
	 */
	public function testRefundPaymentAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);
		$registrations = $this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'member' => $player,
			'membershipPayment' => 'Paid',
		]);

		$membership = $registrations[DiverseRegistrationsScenario::$MEMBERSHIP];
		$payment = $membership->payments[0];

		// Common data
		$refund_data = [
			'registration_id' => $membership->id,
			'payment_type' => 'Refund',
			'payment_method' => 'Other',
			'mark_refunded' => false,
			'notes' => 'Test notes',
		];

		// Admins are allowed to refund payments
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'refund_payment', 'payment' => $payment->id], $admin->id);
		$this->assertResponseContains('<input type="checkbox" name="mark_refunded" value="1"');
		$this->assertResponseContains('<input type="number" name="payment_amount" required="required" step="any" id="payment-amount" class="form-control" value="11.5"/>');

		// Try to refund more than the paid amount
		$this->assertPostAsAccessOk(['controller' => 'Registrations', 'action' => 'refund_payment', 'payment' => $payment->id],
			$admin->id, $refund_data + ['payment_amount' => 1000]);
		$this->assertResponseContains('The refund could not be saved. Please correct the errors below and try again.');
		$this->assertResponseContains('This would refund more than the amount paid.');
		$this->assertResponseContains('<input type="number" name="payment_amount" required="required" step="any" id="payment-amount" class="form-control" value="1000"/>');
		$this->assertResponseContains('<input type="checkbox" name="mark_refunded" value="1"');

		// Try to refund $0
		$this->assertPostAsAccessOk(['controller' => 'Registrations', 'action' => 'refund_payment', 'payment' => $payment->id],
			$admin->id, $refund_data + ['payment_amount' => 0]);
		$this->assertResponseContains('The refund could not be saved. Please correct the errors below and try again.');
		$this->assertResponseContains('Refund amounts must be positive.');
		$this->assertResponseContains('<input type="number" name="payment_amount" required="required" step="any" id="payment-amount" class="form-control" value="0"/>');
		$this->assertResponseContains('<input type="checkbox" name="mark_refunded" value="1"');

		// Try to refund just the right amount
		$this->assertPostAsAccessRedirect(['controller' => 'Registrations', 'action' => 'refund_payment', 'payment' => $payment->id],
			$admin->id, $refund_data + ['payment_amount' => 11.50],
			['controller' => 'Registrations', 'action' => 'view', 'registration' => $membership->id],
			'The refund has been saved.');

		/** @var Registration $registration */
		$registration = TableRegistry::getTableLocator()->get('Registrations')->get($membership->id, [
			'contain' => ['Payments' => [
				'queryBuilder' => function (Query  $q) {
					return $q->order(['Payments.created']);
				},
			]]
		]);
		$this->assertEquals('Paid', $registration->payment);
		$this->assertCount(2, $registration->payments);
		$this->assertEquals(11.5, $registration->payments[0]->refunded_amount);
		$this->assertEquals($admin->id, $registration->payments[0]->updated_person_id);
		$refund = $registration->payments[1];
		$this->assertEquals($membership->id, $refund->registration_id);
		$this->assertEquals('Refund', $refund->payment_type);
		$this->assertEquals(-11.5, $refund->payment_amount);
		$this->assertEquals(0, $refund->refunded_amount);
		$this->assertEquals('Test notes', $refund->notes);
		$this->assertEquals($admin->id, $refund->created_person_id);
		$this->assertEquals('Other', $refund->payment_method);
		$this->assertEquals($payment->id, $refund->payment_id);

		$this->assertMailCount(1);
		$this->assertMailContains('You have been issued a refund of CA$11.50 for your registration for Membership.');
	}

	/**
	 * Test refunding of team events
	 */
	public function testRefundTeamEvent(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);
		$registrations = $this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'captain' => $player,
			'teamPayment' => 'Paid',
		]);

		$team = $registrations[DiverseRegistrationsScenario::$TEAM];
		$payment = $team->payments[0];
		$team_id = $team->responses[0]->answer_text;

		$this->assertPostAsAccessRedirect(['controller' => 'Registrations', 'action' => 'refund_payment', 'payment' => $payment->id],
			$admin->id,
			[
				'registration_id' => $team->id,
				'payment_type' => 'Refund',
				'payment_method' => 'Other',
				'payment_amount' => $team->total_amount,
				'mark_refunded' => true,
				'notes' => 'Full refund',
			],
			['controller' => 'Registrations', 'action' => 'view', 'registration' => $team->id],
			'The refund has been saved.');

		/** @var Registration $registration */
		$registration = TableRegistry::getTableLocator()->get('Registrations')->get($team->id, [
			'contain' => ['Payments' => [
				'queryBuilder' => function (Query  $q) {
					return $q->order(['Payments.created']);
				},
			]]
		]);
		$this->assertEquals('Cancelled', $registration->payment);
		$this->assertCount(2, $registration->payments);
		$this->assertEquals(1150, $registration->payments[0]->refunded_amount);
		$this->assertEquals($admin->id, $registration->payments[0]->updated_person_id);
		$refund = $registration->payments[1];
		$this->assertEquals($team->id, $refund->registration_id);
		$this->assertEquals('Refund', $refund->payment_type);
		$this->assertEquals(-1150, $refund->payment_amount);
		$this->assertEquals(0, $refund->refunded_amount);
		$this->assertEquals('Full refund', $refund->notes);
		$this->assertEquals($admin->id, $refund->created_person_id);
		$this->assertEquals('Other', $refund->payment_method);
		$this->assertEquals($payment->id, $refund->payment_id);

		$this->assertMailCount(1);
		$this->assertMailContains('You have been issued a refund of CA$1,150.00 for your registration for Team.');

		try {
			$team = TableRegistry::getTableLocator()->get('Teams')->get($team_id);
			$this->assertNull($team, 'The team was not successfully deleted.');
		} catch (RecordNotFoundException $ex) {
			// Expected result; the team should be gone
		}
	}

	/**
	 * Test refund_payment method as a manager
	 */
	public function testRefundPaymentAsManager(): void {
		[$admin, $manager, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager', 'player']);
		$registrations = $this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'member' => $player,
			'membershipPayment' => 'Paid',
		]);

		$membership = $registrations[DiverseRegistrationsScenario::$MEMBERSHIP];
		$payment = $membership->payments[0];

		// Managers are allowed to refund payments
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'refund_payment', 'payment' => $payment->id], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test refund_payment method as others
	 */
	public function testRefundPaymentAsOthers(): void {
		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);
		$registrations = $this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'member' => $player,
			'membershipPayment' => 'Paid',
		]);

		$membership = $registrations[DiverseRegistrationsScenario::$MEMBERSHIP];
		$payment = $membership->payments[0];

		// Others are not allowed to refund payments
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'refund_payment', 'payment' => $payment->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'refund_payment', 'payment' => $payment->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'refund_payment', 'payment' => $payment->id]);
	}

	/**
	 * Test credit_payment method as an admin
	 */
	public function testCreditPaymentAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);
		$registrations = $this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'member' => $player,
			'membershipPayment' => 'Paid',
		]);

		$membership = $registrations[DiverseRegistrationsScenario::$MEMBERSHIP];
		$payment = $membership->payments[0];

		// Common data
		$refund_data = [
			'registration_id' => $membership->id,
			'payment_type' => 'Credit',
			'payment_method' => 'Other',
			'mark_refunded' => true,
			'notes' => 'Test notes',
			'credit_notes' => 'Test credit notes',
		];

		// Admins are allowed to credit payments
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'credit_payment', 'payment' => $payment->id], $admin->id);
		$this->assertResponseContains('<input type="checkbox" name="mark_refunded" value="1"');
		$this->assertResponseContains('<input type="number" name="payment_amount" required="required" step="any" id="payment-amount" class="form-control" value="11.5"/>');

		// Try to credit more than the paid amount
		$this->assertPostAsAccessOk(['controller' => 'Registrations', 'action' => 'credit_payment', 'payment' => $payment->id],
			$admin->id, $refund_data + ['payment_amount' => 1000]);
		$this->assertResponseContains('The refund could not be saved. Please correct the errors below and try again.');
		$this->assertResponseContains('This would refund more than the amount paid.');
		$this->assertResponseContains('<input type="number" name="payment_amount" required="required" step="any" id="payment-amount" class="form-control" value="1000"/>');
		$this->assertResponseContains('<input type="checkbox" name="mark_refunded" value="1"');

		// Try to credit $0
		$this->assertPostAsAccessOk(['controller' => 'Registrations', 'action' => 'credit_payment', 'payment' => $payment->id],
			$admin->id, $refund_data + ['payment_amount' => 0]);
		$this->assertResponseContains('The refund could not be saved. Please correct the errors below and try again.');
		$this->assertResponseContains('Credit amounts must be positive.');
		$this->assertResponseContains('<input type="number" name="payment_amount" required="required" step="any" id="payment-amount" class="form-control" value="0"/>');
		$this->assertResponseContains('<input type="checkbox" name="mark_refunded" value="1"');

		// Try to credit just the right amount
		$this->assertPostAsAccessRedirect(['controller' => 'Registrations', 'action' => 'credit_payment', 'payment' => $payment->id],
			$admin->id, $refund_data + ['payment_amount' => 11.50],
			['controller' => 'Registrations', 'action' => 'view', 'registration' => $membership->id],
			'The credit has been saved.');

		/** @var Registration $registration */
		$registration = TableRegistry::getTableLocator()->get('Registrations')->get($membership->id, [
			'contain' => ['Payments' => [
				'queryBuilder' => function (Query  $q) {
					return $q->order(['Payments.created']);
				},
			]]
		]);
		$this->assertEquals('Cancelled', $registration->payment);
		$this->assertCount(2, $registration->payments);
		$this->assertEquals(11.5, $registration->payments[0]->refunded_amount);
		$this->assertEquals($admin->id, $registration->payments[0]->updated_person_id);
		$refund = $registration->payments[1];
		$this->assertEquals($membership->id, $refund->registration_id);
		$this->assertEquals('Credit', $refund->payment_type);
		$this->assertEquals(-11.5, $refund->payment_amount);
		$this->assertEquals(0, $refund->refunded_amount);
		$this->assertEquals('Test notes', $refund->notes);
		$this->assertEquals($admin->id, $refund->created_person_id);
		$this->assertEquals('Other', $refund->payment_method);
		$this->assertEquals($payment->id, $refund->payment_id);

		$credits = TableRegistry::getTableLocator()->get('Credits')->find()
			->where(['person_id' => $player->id])
			->toArray();
		$this->assertCount(1, $credits);
		$this->assertEquals(11.5, $credits[0]->amount);
		$this->assertEquals(0, $credits[0]->amount_used);
		$this->assertEquals('Test credit notes', $credits[0]->notes);
		$this->assertEquals($admin->id, $credits[0]->created_person_id);
		$this->assertEquals($refund->id, $credits[0]->payment_id);

		$this->assertMailCount(1);
		$this->assertMailContains('You have been issued a credit of CA$11.50 for your registration for Membership.');
		$this->assertMailContains('This credit can be redeemed towards any future purchase on the Test Zuluru Affiliate site');
	}

	/**
	 * Test credit_payment method as a manager
	 */
	public function testCreditPaymentAsManager(): void {
		[$admin, $manager, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager', 'player']);
		$registrations = $this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'member' => $player,
			'membershipPayment' => 'Paid',
		]);

		$membership = $registrations[DiverseRegistrationsScenario::$MEMBERSHIP];
		$payment = $membership->payments[0];

		// Managers are allowed to credit payments
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'credit_payment', 'payment' => $payment->id], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test credit_payment method as others
	 */
	public function testCreditPaymentAsOthers(): void {
		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);
		$registrations = $this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'member' => $player,
			'membershipPayment' => 'Paid',
		]);

		$membership = $registrations[DiverseRegistrationsScenario::$MEMBERSHIP];
		$payment = $membership->payments[0];

		// Others are not allowed to credit payments
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'credit_payment', 'payment' => $payment->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'credit_payment', 'payment' => $payment->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'credit_payment', 'payment' => $payment->id]);
	}

	/**
	 * Test edit method as an admin
	 */
	public function testEditAsAdmin(): void {
		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);
		$registrations = $this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'member' => $player,
			'captain' => $player,
		]);

		$affiliate_registrations = $this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[1],
			'player' => $player,
		]);

		// Admins are allowed to edit registrations, with full edit permissions
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'edit', 'registration' => $registrations[DiverseRegistrationsScenario::$MEMBERSHIP]->id], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'edit', 'registration' => $registrations[DiverseRegistrationsScenario::$TEAM]->id], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'edit', 'registration' => $affiliate_registrations[DiverseRegistrationsScenario::$INDIVIDUAL]->id], $admin->id);
	}

	/**
	 * Test edit method as a manager
	 */
	public function testEditAsManager(): void {
		[$admin, $manager, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager', 'player']);
		$registrations = $this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'member' => $player,
			'captain' => $player,
		]);

		$affiliate_registrations = $this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[1],
			'player' => $player,
		]);

		// Managers are allowed to edit registrations
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'edit', 'registration' => $registrations[DiverseRegistrationsScenario::$MEMBERSHIP]->id], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'edit', 'registration' => $registrations[DiverseRegistrationsScenario::$TEAM]->id], $manager->id);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'edit', 'registration' => $affiliate_registrations[DiverseRegistrationsScenario::$INDIVIDUAL]->id], $manager->id);
	}

	/**
	 * Test edit method as others
	 */
	public function testEditAsOthers(): void {
		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);
		$registrations = $this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'member' => $player,
			'player' => $player,
			'individualPayment' => 'Partial',
		]);

		// Registrants are allowed to edit, if payments have not been made
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'edit', 'registration' => $registrations[DiverseRegistrationsScenario::$MEMBERSHIP]->id], $player->id);

		$this->assertGetAsAccessRedirect(['controller' => 'Registrations', 'action' => 'edit', 'registration' => $registrations[DiverseRegistrationsScenario::$INDIVIDUAL]->id],
			$player->id, '/',
			'You cannot edit a registration once a payment has been made.');

		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'edit', 'registration' => $registrations[DiverseRegistrationsScenario::$MEMBERSHIP]->id],
			$volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'edit', 'registration' => $registrations[DiverseRegistrationsScenario::$INDIVIDUAL]->id],
			$volunteer->id);

		$this->assertGetAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'edit', 'registration' => $registrations[DiverseRegistrationsScenario::$MEMBERSHIP]->id]);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'edit', 'registration' => $registrations[DiverseRegistrationsScenario::$INDIVIDUAL]->id]);
	}

	/**
	 * Test unpaid method
	 */
	public function testUnpaid(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'member' => $player,
			'player' => $player,
		]);

		// Admins are allowed to list unpaid registrations
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'unpaid'], $admin->id);

		// Managers are allowed to list unpaid registrations
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'unpaid'], $manager->id);

		// Others are not allowed to list unpaid registrations
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'unpaid'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'unpaid'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'unpaid']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test waiting list method
	 */
	public function testWaiting(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$registrations = $this->loadFixtureScenario(DiverseRegistrationsScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'coordinator' => $volunteer,
			'member' => $player,
			'player' => $player,
			'individualPayment' => 'Waiting',
		]);

		$membership = $registrations[DiverseRegistrationsScenario::$MEMBERSHIP];
		$individual = $registrations[DiverseRegistrationsScenario::$INDIVIDUAL];

		// Admins are allowed to see the waiting list
		$this->assertGetAsAccessRedirect(['controller' => 'Registrations', 'action' => 'waiting', 'event' => $membership->event_id],
			$admin->id, '/',
			'There is nobody on the waiting list for this event.');
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'waiting', 'event' => $individual->event_id], $admin->id);
		$this->assertResponseContains('/registrations/view');
		$this->assertResponseContains('/registrations/edit');

		// Managers are allowed to see the waiting list
		$this->assertGetAsAccessRedirect(['controller' => 'Registrations', 'action' => 'waiting', 'event' => $membership->event_id],
			$manager->id, '/',
			'There is nobody on the waiting list for this event.');
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'waiting', 'event' => $individual->event_id], $manager->id);
		$this->assertResponseContains('/registrations/view');
		$this->assertResponseContains('/registrations/edit');

		// Coordinators are not allowed to see the waiting list
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'waiting', 'event' => $membership->event_id], $volunteer->id);

		// Except that they are allowed to see the waiting list for divisions they coordinate, but without registration view and edit links
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'waiting', 'event' => $individual->event_id], $volunteer->id);
		$this->assertResponseNotContains('/registrations/view');
		$this->assertResponseNotContains('/registrations/edit');

		// Others are not allowed to see the waiting list
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'waiting', 'event' => $membership->event_id], $player->id);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'waiting', 'event' => $individual->event_id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'waiting', 'event' => $membership->event_id]);
	}

}
