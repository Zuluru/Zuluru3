<?php
namespace App\Test\TestCase\Model\Entity;

use App\Module\EventTypeIndividual;
use App\Module\EventTypeTeam;
use App\Test\Factory\EventFactory;
use App\Test\Factory\GameFactory;
use App\Test\Factory\RegistrationFactory;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\TestSuite\TestCase;
use App\Model\Entity\Event;

/**
 * App\Model\Entity\Event Test Case
 */
class EventTest extends TestCase {
	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		Configure::write('options.gender_binary', []);
	}

	/**
	 * Test __construct method
	 */
	public function testConstruct(): void {
		// Check the virtual fields show up from the serialized custom field
		/** @var Event $event */
		$event = EventFactory::make()->setCustom([
			'membership_begins' => FrozenDate::now()->startOfYear(),
			'membership_ends' => FrozenDate::now()->endOfYear(),
			'membership_type' => 'full',
		])->getEntity();

		$this->assertEquals(FrozenDate::now()->startOfYear(), $event->membership_begins);
		$this->assertEquals(FrozenDate::now()->endOfYear(), $event->membership_ends);
		$this->assertEquals('full', $event->membership_type);
	}

	/**
	 * Test count method
	 */
	public function testCount(): void {
		$nWoman = 3;
		$nOpen = 1;

		/** @var Event $event */
		$event = EventFactory::make()
			->with('Registrations',
				RegistrationFactory::make($nWoman)
					->with('People', [
						'roster_designation' => 'Woman',
						'addr_city' => 'Toronto',
					])
					->paid()
			)
			->with('Registrations',
				RegistrationFactory::make($nOpen)
					->with('People', [
						'roster_designation' => 'Open',
					])
					->paid()
			)
			->persist();

		$this->assertEquals($nWoman, $event->count('Woman'));
		$this->assertEquals($nOpen, $event->count('Open'));
		$this->assertEquals($nWoman, $event->count('Woman', ['People.addr_city' => 'Toronto']));
		$this->assertEquals(0, $event->count('Woman', ['People.addr_city' => 'Ottawa']));
		$this->assertEquals($nWoman, $event->count('Woman', [], ['Paid', 'Unpaid']));

		$this->markTestSkipped(GameFactory::TODO_FACTORIES);
		$this->assertEquals(2, $event->count('Woman', [], ['Unpaid']));
	}

	/**
	 * Test cap method
	 */
	public function testCap(): void {
		$openCap = mt_rand();
		/** @var Event $event */
		$event = EventFactory::make(['women_cap' => CAP_COMBINED, 'open_cap' => $openCap])->getEntity();
		$this->assertEquals($openCap, $event->cap(''));

		$womenCap = mt_rand();
		/** @var Event $event */
		$event = EventFactory::make(['women_cap' => $womenCap, 'open_cap' => $openCap])->getEntity();
		$this->assertEquals($openCap, $event->cap('Open'));
		$this->assertEquals($womenCap, $event->cap(''));
	}

	/**
	 * Test _getMembershipBegins(), _getMembershipEnds(), _getMembershipType(),
	 * _getLevelOfPlay(), _testGetAskStatus(), _getAskAttendance()
	 */
	public function testGetMembershipParameters(): void {
		/** @var Event $event */
		$event = EventFactory::make()->setCustom([
			'membership_begins' => FrozenDate::now()->startOfYear(),
			'membership_ends' => FrozenDate::now()->endOfYear(),
			'membership_type' => 'full',
			'level_of_play' => 'Competitive',
			'ask_status' => true,
			'ask_attendance' => true,
		])->getEntity();
		$this->assertEquals(FrozenDate::now()->startOfYear(), $event->membership_begins);
		$this->assertEquals(FrozenDate::now()->endOfYear(), $event->membership_ends);
		$this->assertEquals('full', $event->membership_type);
		$this->assertEquals('Competitive', $event->level_of_play);
		$this->assertTrue($event->ask_status);
		$this->assertTrue($event->ask_attendance);
	}

	/**
	 * test _getPeople()
	 */
	public function testGetPeople(): void {
		$nPaid = 4;
		/** @var Event $event */
		$event = EventFactory::make()
			->with('Registrations',
				RegistrationFactory::make($nPaid)
					->with('People')
					->paid()
			)->persist();


		$peopleWhoPaid = $event->people->toArray();

		// Only four people have made some payment
		$this->assertCount($nPaid, $peopleWhoPaid);
		$this->assertEquals($event->registrations[0]->person->id, $peopleWhoPaid[0]->id);
		$this->assertEquals($event->registrations[1]->person->id, $peopleWhoPaid[1]->id);
		$this->assertEquals($event->registrations[2]->person->id, $peopleWhoPaid[2]->id);
		$this->assertEquals($event->registrations[3]->person->id, $peopleWhoPaid[3]->id);

		/** @var Event $event */
		$event = EventFactory::make()
			->with('Registrations',
				RegistrationFactory::make(3)
					->with('People')
					->unpaid()
			)->persist();
		$this->assertEmpty($event->people->toArray());
	}

	public function testMergeAutoQuestions(): void {
		/** @var Event $event */
		$event = EventFactory::make()->getEntity();
		// Make sure we have no questions
		$this->assertNull($event->questionnaire);

		// Add questions in for different types
		$teamEvent = new EventTypeTeam();
		Configure::write('profile.shirt_size', PROFILE_REGISTRATION);
		$individualEvent = new EventTypeIndividual();
		$event->mergeAutoQuestions($teamEvent, 5);
		$event->mergeAutoQuestions($individualEvent, 5);

		// Check that the merge worked:
		$this->assertNotNull($event->questionnaire);
		$questionsThatShouldBeThere = array_merge($teamEvent->registrationFields($event, 5), $individualEvent->registrationFields($event, 5));
		$count = count($questionsThatShouldBeThere);
		$this->assertCount($count, $event->questionnaire->questions, 'Not all questions were merged');
		for ($i = 0; $i < $count; $i++) {
			$this->assertEquals($questionsThatShouldBeThere[$i], $event->questionnaire->questions[$i]);
		}
	}

	/**
	 * Test processWaitingList();
	 */
	public function testProcessWaitingList(): void {
		//TODO
		$this->markTestIncomplete('Not implemented yet. Need to discuss with Greg, I don\'t think the method does what I think it should be or that could just be some complexity I don\t understand.');
	}

}
