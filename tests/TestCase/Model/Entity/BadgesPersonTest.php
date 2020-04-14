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
		'app.EventTypes',
		'app.Affiliates',
			'app.Users',
				'app.People',
					'app.AffiliatesPeople',
			'app.Groups',
				'app.GroupsPeople',
			'app.Regions',
				'app.Facilities',
					'app.Fields',
			'app.Leagues',
				'app.Divisions',
					'app.Teams',
					'app.GameSlots',
					'app.Pools',
						'app.PoolsTeams',
					'app.Games',
			'app.Events',
				'app.Prices',
					'app.Registrations',
			'app.Settings',
		'app.I18n',
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
