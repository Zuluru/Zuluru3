<?php
namespace App\Test\TestCase\Model\Table;

use App\Core\UserCache;
use App\Middleware\ConfigurationLoader;
use App\Test\Factory\RegistrationFactory;
use Cake\ORM\TableRegistry;
use App\Model\Table\RegistrationsTable;

/**
 * App\Model\Table\RegistrationsTable Test Case
 */
class RegistrationsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var RegistrationsTable
	 */
	public $RegistrationsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('Registrations') ? [] : ['className' => RegistrationsTable::class];
		$this->RegistrationsTable = TableRegistry::getTableLocator()->get('Registrations', $config);

		ConfigurationLoader::loadConfiguration();
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
		// First, we add a new registration to a full event, it should go on the waiting list
		UserCache::getInstance()->initializeIdForTests(PERSON_ID_CAPTAIN);
		$registration = $this->RegistrationsTable->newEntity([
			'person_id' => PERSON_ID_CAPTAIN,
			'event_id' => EVENT_ID_LEAGUE_INDIVIDUAL_THURSDAY,
			'price_id' => PRICE_ID_MEMBERSHIP,
		]);
		$event = $this->RegistrationsTable->Events->get(EVENT_ID_LEAGUE_INDIVIDUAL_THURSDAY, [
			'contain' => ['EventTypes']
		]);
		$this->assertNotFalse($this->RegistrationsTable->save($registration, compact('registration', 'event')));
		$this->assertEquals('Waiting', $registration->payment);
		$this->assertEquals(11.50, $registration->total_amount);

		// Now, mark the original registration as unpaid, it should bump the new one to reserved
		UserCache::getInstance()->initializeIdForTests(PERSON_ID_ADMIN);
		$registration = $this->RegistrationsTable->get(REGISTRATION_ID_PLAYER_INDIVIDUAL_THURSDAY, [
			'contain' => ['Events' => ['EventTypes'], 'Prices', 'Payments']
		]);
		$this->assertEquals('Paid', $registration->payment);
		$registration->payments[] = $this->RegistrationsTable->Payments->newEntity([
			'payment_type' => 'Refund',
			'payment_amount' => $registration->total_amount,
			'payment_method' => 'Online',
		]);
		$registration->dirty('payments', true);
		$registration->mark_refunded = true;
		$this->assertNotFalse($this->RegistrationsTable->save($registration, compact('registration', 'event')));
		$this->assertEquals('Cancelled', $registration->payment);
		// TODO: Figure out how to get Footprint to work with table tests so that we can check created_person_id in the payment

		// Make sure the new registration got reserved
		$this->assertEventFired('Model.Registration.registrationOpened');
		$registration = $this->RegistrationsTable->get(REGISTRATION_ID_NEW);
		$this->assertEquals('Reserved', $registration->payment);
	}

	/**
	 * Test the waiting list when the cap is raised
	 * @throws \Exception
	 */
	public function testWaitingListCapRaised(): void {
		// First, we add a new registration to a full event, it should go on the waiting list
		UserCache::getInstance()->initializeIdForTests(PERSON_ID_CAPTAIN);
		$registration = $this->RegistrationsTable->newEntity([
			'person_id' => PERSON_ID_CAPTAIN,
			'event_id' => EVENT_ID_LEAGUE_INDIVIDUAL_THURSDAY,
			'price_id' => PRICE_ID_MEMBERSHIP,
		]);
		$event = $this->RegistrationsTable->Events->get(EVENT_ID_LEAGUE_INDIVIDUAL_THURSDAY, [
			'contain' => ['EventTypes']
		]);
		$this->assertNotFalse($this->RegistrationsTable->save($registration, compact('registration', 'event')));
		$this->assertEquals('Waiting', $registration->payment);
		$this->assertEquals(11.50, $registration->total_amount);

		// Now, update the cap on the event, it should bump the new one to reserved
		UserCache::getInstance()->initializeIdForTests(PERSON_ID_ADMIN);
		$event->women_cap += 1;
		$this->assertNotFalse($this->RegistrationsTable->Events->save($event));

		// Make sure the new registration got reserved
		$this->assertEventFired('Model.Registration.registrationOpened');
		$registration = $this->RegistrationsTable->get(REGISTRATION_ID_NEW);
		$this->assertEquals('Reserved', $registration->payment);
	}

}
