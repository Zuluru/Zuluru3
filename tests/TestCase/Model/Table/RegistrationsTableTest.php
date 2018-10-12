<?php
namespace App\Test\TestCase\Model\Table;

use App\Core\UserCache;
use Cake\Event\Event as CakeEvent;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;
use App\Model\Table\RegistrationsTable;

/**
 * App\Model\Table\RegistrationsTable Test Case
 */
class RegistrationsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\RegistrationsTable
	 */
	public $RegistrationsTable;

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
					'app.credits',
			'app.groups',
				'app.groups_people',
			'app.leagues',
				'app.divisions',
			'app.questions',
				'app.answers',
			'app.questionnaires',
				'app.questionnaires_questions',
			'app.events',
				'app.prices',
					'app.registrations',
						'app.payments',
							'app.registration_audits',
						'app.responses',
				'app.preregistrations',
			'app.badges',
			'app.settings',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Registrations') ? [] : ['className' => 'App\Model\Table\RegistrationsTable'];
		$this->RegistrationsTable = TableRegistry::get('Registrations', $config);

		$event = new CakeEvent('Configuration.initialize', $this);
		EventManager::instance()->dispatch($event);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->RegistrationsTable);

		parent::tearDown();
	}

	/**
	 * Test beforeSave method
	 *
	 * @return void
	 */
	public function testBeforeSave() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test afterSave method
	 *
	 * @return void
	 */
	public function testAfterSave() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test beforeDelete method
	 *
	 * @return void
	 */
	public function testBeforeDelete() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test afterDelete method
	 *
	 * @return void
	 */
	public function testAfterDelete() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test expireReservations method
	 *
	 * @return void
	 */
	public function testExpireReservations() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test affiliate method
	 *
	 * @return void
	 */
	public function testAffiliate() {
		$this->assertEquals(AFFILIATE_ID_CLUB, $this->RegistrationsTable->affiliate(1));
	}

	/**
	 * Test the waiting list when a refund is issued
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function testWaitingListWithRefund() {
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
		$this->assertEquals(9, $registration->total_amount);

		// Now, mark the original registration as unpaid, it should bump the new one to reserved
		UserCache::getInstance()->initializeIdForTests(PERSON_ID_ADMIN);
		$registration = $this->RegistrationsTable->get(REGISTRATION_ID_PLAYER_INDIVIDUAL_THURSDAY, [
			'contain' => ['Events' => ['EventTypes'], 'Prices', 'Payments']
		]);
		$this->assertEquals('Paid', $registration->payment);
		$registration->payments[] = $this->RegistrationsTable->Payments->newEntity([
			'payment_type' => 'Refund',
			'payment_amount' => - $registration->total_amount,
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
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function testWaitingListCapRaised() {
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
		$this->assertEquals(9, $registration->total_amount);

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
