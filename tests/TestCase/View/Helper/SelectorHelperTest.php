<?php
namespace App\Test\TestCase\View\Helper;

use App\Model\Entity\Event;
use App\Test\Factory\DivisionsDayFactory;
use App\Test\Factory\EventFactory;
use App\Test\Scenario\LeagueScenario;
use App\View\Helper\SelectorHelper;
use Cake\Chronos\ChronosInterface;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;

class SelectorHelperTest extends TestCase
{

	use ScenarioAwareTrait;
	use TruncateDirtyTables;

	/**
	 * Test subject
	 *
	 * @var \App\View\Helper\SelectorHelper
	 */
	public $SelectorHelper;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.Days',
		'app.EventTypes',
	];

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$view = new View();
		$this->SelectorHelper = new SelectorHelper($view);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->SelectorHelper);

		parent::tearDown();
	}

	/**
	 * Test selector method
	 */
	public function testSelector(): void {
		$this->assertEmpty($this->SelectorHelper->selector('Test', []));
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test extractOptions method
	 */
	public function testExtractOptions(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test extractOptionsUnsorted method
	 */
	public function testExtractOptionsUnsorted(): void {
		/** @var \App\Model\Entity\League $league1 */
		$league1 = $this->loadFixtureScenario(LeagueScenario::class, [
			'day_id' => ChronosInterface::MONDAY,
			'league_details' => ['sport' => 'ultimate'],
		]);
		/** @var \App\Model\Entity\League $league2 */
		$league2 = $this->loadFixtureScenario(LeagueScenario::class, [
			'day_id' => ChronosInterface::TUESDAY,
			'league_details' => ['sport' => 'soccer'],
		]);

		// Add an extra day to each of the divisions
		DivisionsDayFactory::make([
			['division_id' => $league1->divisions[0]->id, 'day_id' => ChronosInterface::FRIDAY],
			['division_id' => $league2->divisions[0]->id, 'day_id' => ChronosInterface::FRIDAY]
		])
			->persist();

		EventFactory::make([
			// Names given to ensure that they are found in the right order
			['name' => '1', 'event_type_id' => EVENT_TYPE_ID_TEAMS_FOR_LEAGUES, 'division_id' => $league1->divisions[0]->id],
			['name' => '2', 'event_type_id' => EVENT_TYPE_ID_TEAMS_FOR_LEAGUES, 'division_id' => $league2->divisions[0]->id],
		])
			->persist();

		$events = EventFactory::find()->contain(['Divisions' => ['Leagues', 'Days']])
			->order('Events.name');

		// Tests for extracting simple strings. Use the query object as list here.
		$options = $this->SelectorHelper->extractOptionsUnsorted($events,
			function (Event $item) { return $item->division ? $item->division->league : null; },
			'sport'
		);

		// Everything from here on will use events as an array, not a query object.
		$events = $events->toArray();

		$this->assertCount(2, $options);

		$this->assertArrayHasKey('ultimate', $options);
		$this->assertTrue(is_array($options['ultimate']));
		$this->assertArrayHasKey('value', $options['ultimate']);
		$this->assertEquals('ultimate', $options['ultimate']['value']);
		$this->assertArrayHasKey('ids', $options['ultimate']);
		$this->assertTrue(is_array($options['ultimate']['ids']));
		$this->assertCount(1, $options['ultimate']['ids']);
		$this->assertEquals($events[0]->id, current($options['ultimate']['ids']));

		$this->assertArrayHasKey('soccer', $options);
		$this->assertTrue(is_array($options['soccer']));
		$this->assertArrayHasKey('value', $options['soccer']);
		$this->assertEquals('soccer', $options['soccer']['value']);
		$this->assertArrayHasKey('ids', $options['soccer']);
		$this->assertTrue(is_array($options['soccer']['ids']));
		$this->assertCount(1, $options['soccer']['ids']);
		$this->assertEquals($events[1]->id, current($options['soccer']['ids']));

		// Tests for extracting arrays.
		$options = $this->SelectorHelper->extractOptionsUnsorted($events,
			function (Event $item) { return $item->division && !empty($item->division->days) ? $item->division->days : null; },
			'name', 'id'
		);

		$this->assertCount(3, $options);

		$this->assertArrayHasKey(ChronosInterface::MONDAY, $options);
		$this->assertTrue(is_array($options[ChronosInterface::MONDAY]));
		$this->assertArrayHasKey('value', $options[ChronosInterface::MONDAY]);
		$this->assertEquals('Monday', $options[ChronosInterface::MONDAY]['value']);
		$this->assertArrayHasKey('ids', $options[ChronosInterface::MONDAY]);
		$this->assertTrue(is_array($options[ChronosInterface::MONDAY]['ids']));
		$this->assertCount(1, $options[ChronosInterface::MONDAY]['ids']);
		$this->assertEquals($events[0]->id, current($options[ChronosInterface::MONDAY]['ids']));

		$this->assertArrayHasKey(ChronosInterface::TUESDAY, $options);
		$this->assertTrue(is_array($options[ChronosInterface::TUESDAY]));
		$this->assertArrayHasKey('value', $options[ChronosInterface::TUESDAY]);
		$this->assertEquals('Tuesday', $options[ChronosInterface::TUESDAY]['value']);
		$this->assertArrayHasKey('ids', $options[ChronosInterface::TUESDAY]);
		$this->assertTrue(is_array($options[ChronosInterface::TUESDAY]['ids']));
		$this->assertCount(1, $options[ChronosInterface::TUESDAY]['ids']);
		$this->assertEquals($events[1]->id, current($options[ChronosInterface::TUESDAY]['ids']));

		$this->assertArrayHasKey(ChronosInterface::FRIDAY, $options);
		$this->assertTrue(is_array($options[ChronosInterface::FRIDAY]));
		$this->assertArrayHasKey('value', $options[ChronosInterface::FRIDAY]);
		$this->assertEquals('Friday', $options[ChronosInterface::FRIDAY]['value']);
		$this->assertArrayHasKey('ids', $options[ChronosInterface::FRIDAY]);
		$this->assertTrue(is_array($options[ChronosInterface::FRIDAY]['ids']));
		$this->assertCount(2, $options[ChronosInterface::FRIDAY]['ids']);
		$this->assertEquals([$events[0]->id, $events[1]->id], array_values($options[ChronosInterface::FRIDAY]['ids']));
	}

}
