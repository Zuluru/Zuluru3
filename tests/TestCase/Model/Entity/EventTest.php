<?php
namespace App\Test\TestCase\Model\Entity;

use App\Module\EventTypeIndividual;
use App\Module\EventTypeTeam;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use App\Model\Entity\Event;

/**
 * App\Model\Entity\Event Test Case
 */
class EventTest extends TestCase {

	/**
	 * Test subject 1
	 *
	 * @var \App\Model\Entity\Event
	 */
	public $Event1;

	/**
	 * Test subject 2
	 *
	 * @var \App\Model\Entity\Event
	 */
	public $Event2;

	/**
	 * Test subject 3
	 *
	 * @var \App\Model\Entity\Event
	 */
	public $Event3;

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
			'app.groups',
				'app.groups_people',
			'app.leagues',
				'app.divisions',
			'app.events',
				'app.prices',
					'app.registrations',
			'app.settings',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$events = TableRegistry::get('Events');
		$this->Event1 = $events->get(EVENT_ID_MEMBERSHIP);
		$this->Event2 = $events->get(EVENT_ID_LEAGUE_TEAM);
		$this->Event3 = $events->get(EVENT_ID_LEAGUE_INDIVIDUAL_MONDAY);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->Event1);
		unset($this->Event2);
		unset($this->Event3);

		parent::tearDown();
	}

	/**
	 * Test __construct method
	 *
	 * @return void
	 */
	public function testConstruct() {
		// Check the virtual fields show up from the serialized custom field
		$this->assertEquals($this->Event1->membership_begins, FrozenDate::now()->startOfYear());
		$this->assertEquals($this->Event1->membership_ends, FrozenDate::now()->endOfYear());
		$this->assertEquals($this->Event1->membership_type, 'full');
	}

	/**
	 * Test count method
	 *
	 * @return void
	 */
	public function testCount() {
		$this->assertEquals(2, $this->Event1->count('Woman'));
		$this->assertEquals(0, $this->Event1->count('Open'));
		$this->assertEquals(2, $this->Event1->count('Woman', ['People.addr_city'=>'Toronto']));
		$this->assertEquals(0, $this->Event1->count('Woman', ['People.addr_city'=>'Ottawa']));
		$this->assertEquals(1, $this->Event1->count('Woman', [], ['Paid', 'Not Paid']));
		$this->assertEquals(0, $this->Event1->count('Woman', [], ['Not Paid']));
	}

	/**
	 * Test cap method
	 *
	 * @return void
	 */
	public function testCap() {
		$this->assertEquals(-1, $this->Event1->cap('Open'));
		$this->assertEquals(-1, $this->Event1->cap('Woman'));
		$this->assertEquals(2, $this->Event2->cap('Open'));
		$this->assertEquals(2, $this->Event2->cap('Woman'));
		$this->assertEquals(1, $this->Event3->cap('Open'));
		$this->assertEquals(1, $this->Event3->cap('Woman'));
	}

	/**
	 * Test _getMembershipBegins()
	 */
	public function testGetMembershipBegins() {
		$this->assertEquals(FrozenDate::now()->startOfYear(), $this->Event1->membershipBegins);
	}

	/**
	 * Test _getMembershipEnds()
	 */
	public function testGetMembershipEnds() {
		$this->assertEquals(FrozenDate::now()->endOfYear(), $this->Event1->membershipEnds);
	}

	/**
	 * Test _getMembershipType()
	 */
	public function testGetMembershipType() {
		$this->assertEquals('full', $this->Event1->membershipType);
	}

	/**
	 * Test _getLevelOfPlay()
	 */
	public function testGetLevelOfPlay() {
		$this->assertEquals('Competitive', $this->Event2->levelOfPlay);
	}

	/**
	 * Test _getAskStatus()
	 */
	public function testGetAskStatus() {
		$this->assertTrue($this->Event2->askStatus);
	}

	/**
	 * Test _getAskAttendance()
	 */
	public function testGetAskAttendance() {
		$this->assertTrue($this->Event2->askAttendance);
	}

	/**
	 * test _getPeople()
	 */
	public function testGetPeople() {
		$people1 = $this->Event1->people->toArray();
		// Only one person has paid
		$this->assertEquals(2, count($people1));
		$this->assertEquals(PERSON_ID_PLAYER, $people1[0]->id);
		$this->assertEquals(PERSON_ID_CHILD, $people1[1]->id);

		$this->assertEmpty($this->Event2->people->toArray());
	}

	public function testMergeAutoQuestions() {
		// Make sure we have no questions
		$this->assertNull($this->Event1->questionnaire);

		// Add questions in for different types
		$teamEvent = new EventTypeTeam();
		Configure::write('profile.shirt_size', PROFILE_REGISTRATION);
		$individualEvent = new EventTypeIndividual();
		$this->Event1->mergeAutoQuestions($teamEvent, 5);
		$this->Event1->mergeAutoQuestions($individualEvent, 5);

		// Check that the merge worked:
		$this->assertNotNull($this->Event1->questionnaire);
		$questionsThatShouldBeThere = array_merge($teamEvent->registrationFields($this->Event1, 5), $individualEvent->registrationFields($this->Event1, 5));
		$this->assertEquals(count($this->Event1->questionnaire->questions), count($questionsThatShouldBeThere), 'Not all questions were merged');
		for ($i = 0; $i < count($questionsThatShouldBeThere); $i++) {
			$this->assertEquals($questionsThatShouldBeThere[$i], $this->Event1->questionnaire->questions[$i]);
		}
	}

	/**
	 * Test processWaitingList();
	 */
	public function testProcessWaitingList() {
		$this->markTestIncomplete('Not implemented yet. Need to discuss with Greg, I don\'t think the method does what I think it should be or that could just be some complexity I don\t understand.');
	}

}
