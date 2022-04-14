<?php
namespace App\Test\TestCase\Model\Table;

use App\Test\Factory\EventFactory;
use App\Test\Factory\GameFactory;
use Cake\Event\Event;
use Cake\I18n\FrozenDate;
use Cake\ORM\TableRegistry;
use App\Model\Table\EventsTable;

/**
 * App\Model\Table\EventsTable Test Case
 */
class EventsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\EventsTable
	 */
	public $EventsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::exists('Events') ? [] : ['className' => 'App\Model\Table\EventsTable'];
		$this->EventsTable = TableRegistry::getTableLocator()->get('Events', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->EventsTable);

		parent::tearDown();
	}

	/**
	 * Test validationGeneric method
	 */
	public function testValidationGeneric(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationIndividual method
	 */
	public function testValidationIndividual(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationMembership method
	 */
	public function testValidationMembership(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationTeam method
	 */
	public function testValidationTeam(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test beforeMarshal method
	 */
	public function testBeforeMarshal(): void {
        $this->markTestSkipped(GameFactory::TODO_FACTORIES);
		$date = FrozenDate::now();
		$custom_membership = [
			'membership_begins' => $date,
			'membership_ends' => $date,
			'membership_type' => 'full',
		];
		$custom_team = [
			'ask_status' => true,
			'ask_attendance' => true,
		];

		$data = new \ArrayObject(array_merge($custom_membership, [
			'event_type_id' => EVENT_TYPE_ID_MEMBERSHIP,
		]));
		$expected = serialize($custom_membership);
		$this->EventsTable->beforeMarshal(new Event('testing'), $data, new \ArrayObject());
		$this->assertEquals($expected, $data['custom']);

		$data = new \ArrayObject(array_merge($custom_membership, [
			'event_type_id' => EVENT_TYPE_ID_INDIVIDUALS_FOR_EVENTS,
		]));
		$expected = serialize([]);
		$this->EventsTable->beforeMarshal(new Event('testing'), $data, new \ArrayObject());
		$this->assertEquals($expected, $data['custom']);

		$data = new \ArrayObject(array_merge($custom_team, [
			'event_type_id' => EVENT_TYPE_ID_TEAMS_FOR_EVENTS,
		]));
		$expected = serialize($custom_team);
		$this->EventsTable->beforeMarshal(new Event('testing'), $data, new \ArrayObject());
		$this->assertEquals($expected, $data['custom']);
	}

	/**
	 * Test afterSave method
	 */
	public function testAfterSave(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test affiliate method
	 */
	public function testAffiliate(): void {
        $affiliateId = rand();
        $event = EventFactory::make(['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->EventsTable->affiliate($event->id));
	}

}
