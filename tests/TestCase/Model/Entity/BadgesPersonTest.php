<?php
namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\BadgesPerson;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Entity\BadgesPerson Test Case
 */
class BadgesPersonTest extends TestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Entity\BadgesPerson
	 */
	public $BadgesPerson;

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
			'app.regions',
				'app.facilities',
					'app.fields',
			'app.leagues',
				'app.divisions',
					'app.teams',
					'app.game_slots',
					'app.pools',
						'app.pools_teams',
					'app.games',
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
		$this->BadgesPerson = new BadgesPerson([
			'game_id' => GAME_ID_LADDER_MATCHED_SCORES,
			'registration_id' => 1,
			'team_id' => TEAM_ID_RED,
		]);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->BadgesPerson);

		parent::tearDown();
	}

	/**
	 * Test __construct method
	 *
	 * @return void
	 */
	public function testConstruct() {
		$this->assertContains('game', $this->BadgesPerson->virtualProperties());
		$this->assertContains('registration', $this->BadgesPerson->virtualProperties());
		$this->assertContains('team', $this->BadgesPerson->virtualProperties());
	}

}
