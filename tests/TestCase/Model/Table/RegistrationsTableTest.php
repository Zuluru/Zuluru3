<?php
namespace App\Test\TestCase\Model\Table;

use App\Core\UserCache;
use App\Middleware\ConfigurationLoader;
use App\Test\Factory\EventFactory;
use App\Test\Factory\RegistrationFactory;
use App\Test\Scenario\DiverseUsersScenario;
use Cake\ORM\TableRegistry;
use App\Model\Table\RegistrationsTable;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Model\Table\RegistrationsTable Test Case
 */
class RegistrationsTableTest extends TableTestCase {

	use ScenarioAwareTrait;

	/**
	 * Test subject
	 *
	 * @var RegistrationsTable
	 */
	public $RegistrationsTable;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.EventTypes',
		'app.UserGroups',
		'app.Settings',
	];

	public $autoFixtures = false;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('Registrations') ? [] : ['className' => RegistrationsTable::class];
		$this->RegistrationsTable = TableRegistry::getTableLocator()->get('Registrations', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->RegistrationsTable);

		parent::tearDown();
	}

	/**
	 * Test beforeSave method
	 */
	public function testBeforeSave(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test afterSave method
	 */
	public function testAfterSave(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test beforeDelete method
	 */
	public function testBeforeDelete(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test afterDelete method
	 */
	public function testAfterDelete(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test expireReservations method
	 */
	public function testExpireReservations(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test affiliate method
	 */
	public function testAffiliate(): void {
		$affiliateId = mt_rand();
		$event = RegistrationFactory::make()->with('Events', ['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->RegistrationsTable->affiliate($event->id));
	}

	/**
	 * Test the waiting list when a refund is issued
	 * @throws \Exception
	 */
	public function testWaitingListWithRefund(): void {
		$this->setupFixtures();
		$this->loadRoutes();
		ConfigurationLoader::loadConfiguration();

		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, [
			'admin',
			'player' => ['roster_designation' => 'Woman'],
		]);
		$this->assertEquals('Woman', $player->roster_designation);

		$event = EventFactory::make(['event_type_id' => EVENT_TYPE_ID_CLINICS, 'open_cap' => 0, 'women_cap' => 1])
			->with('Affiliates')
			->with('Prices', ['cost' => 10, 'tax1' => 1.50, 'tax2' => 0])
			->persist();
		$price = $event->prices[0];

		$registration = RegistrationFactory::make(['event_id' => $event->id, 'price_id' => $price->id, 'total_amount' => 11.50, 'payment' => 'Paid'])
			->with('People', ['roster_designation' => 'Woman'])
			->persist();
		$this->RegistrationsTable->Events->loadInto($event, ['EventTypes']);

		// First, we add a new registration to a full event, it should go on the waiting list
		UserCache::getInstance()->initializeIdForTests($player->id);
		$new_registration = $this->RegistrationsTable->newEntity([
			'person_id' => $player->id,
			'event_id' => $event->id,
			'price_id' => $price->id,
		]);
		$this->assertNotFalse($this->RegistrationsTable->save($new_registration, compact('registration', 'event')));
		$this->assertEquals('Waiting', $new_registration->payment);
		$this->assertEquals(11.50, $new_registration->total_amount);

		// Now, mark the original registration as unpaid, it should bump the new one to reserved
		UserCache::getInstance()->initializeIdForTests($admin->id);
		$registration = $this->RegistrationsTable->get($registration->id, [
			'contain' => ['Events' => ['EventTypes'], 'Prices', 'Payments']
		]);
		$this->assertEquals('Paid', $registration->payment);
		$registration->payments[] = $this->RegistrationsTable->Payments->newEntity([
			'payment_type' => 'Refund',
			'payment_amount' => $registration->total_amount,
			'payment_method' => 'Online',
		]);
		$registration->setDirty('payments', true);
		$registration->mark_refunded = true;
		$this->assertNotFalse($this->RegistrationsTable->save($registration, compact('registration', 'event')));
		$this->assertEquals('Cancelled', $registration->payment);
		// TODO: Figure out how to get Footprint to work with table tests so that we can check created_person_id in the payment

		// Make sure the new registration got reserved
		$this->assertEventFired('Model.Registration.registrationOpened');
		$new_registration = $this->RegistrationsTable->get($new_registration->id);
		$this->assertEquals('Reserved', $new_registration->payment);
	}

	/**
	 * Test the waiting list when the cap is raised
	 * @throws \Exception
	 */
	public function testWaitingListCapRaised(): void {
		$this->setupFixtures();
		$this->loadRoutes();
		ConfigurationLoader::loadConfiguration();

		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, [
			'admin',
			'player' => ['gender' => 'Woman', 'roster_designation' => 'Woman']
		]);
		$this->assertEquals('Woman', $player->roster_designation);

		$event = EventFactory::make(['event_type_id' => EVENT_TYPE_ID_CLINICS, 'open_cap' => 0, 'women_cap' => 1])
			->with('Affiliates')
			->with('Prices', ['cost' => 10, 'tax1' => 1.50, 'tax2' => 0, 'allow_reservations' => true])
			->persist();
		$price = $event->prices[0];

		$registration = RegistrationFactory::make(['event_id' => $event->id, 'price_id' => $price->id, 'total_amount' => 11.50, 'payment' => 'Paid'])
			->with('People', ['roster_designation' => 'Woman'])
			->persist();
		$this->RegistrationsTable->Events->loadInto($event, ['EventTypes']);

		// First, we add a new registration to a full event, it should go on the waiting list
		UserCache::getInstance()->initializeIdForTests($player->id);
		$new_registration = $this->RegistrationsTable->newEntity([
			'person_id' => $player->id,
			'event_id' => $event->id,
			'price_id' => $price->id,
		]);
		$this->assertNotFalse($this->RegistrationsTable->save($new_registration, compact('registration', 'event')));
		$this->assertEquals('Waiting', $new_registration->payment);
		$this->assertEquals(11.50, $new_registration->total_amount);

		// Now, update the cap on the event, it should bump the new one to reserved
		UserCache::getInstance()->initializeIdForTests($admin->id);
		$event->women_cap += 1;
		$this->assertNotFalse($this->RegistrationsTable->Events->save($event));

		// Make sure the new registration got reserved
		$this->assertEventFired('Model.Registration.registrationOpened');
		$new_registration = $this->RegistrationsTable->get($new_registration->id);
		$this->assertEquals('Reserved', $new_registration->payment);
	}

}
