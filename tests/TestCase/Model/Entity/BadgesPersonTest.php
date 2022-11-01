<?php
namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\BadgesPerson;
use App\Test\Factory\BadgesPersonFactory;
use App\Test\Factory\GameFactory;
use Cake\Core\Configure;
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
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
        Configure::write('options.sport', ['ultimate']);

		// Create a BadgesPerson entity associated to a game, a registration
        // and a team
		$badgePerson = BadgesPersonFactory::make()
            ->with('Games',
                GameFactory::make()
                    ->with('Divisions.Leagues')
                    ->with('GameSlots')
            )
            ->with('Registrations.Events')
            ->with('Teams.Divisions.Leagues')
            ->persist()
            ->toArray();

        $this->BadgesPerson = new BadgesPerson($badgePerson);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->BadgesPerson);

		parent::tearDown();
	}

	/**
	 * Test __construct method
	 */
	public function testConstruct(): void {
		$this->assertContains('game', $this->BadgesPerson->virtualProperties());
		$this->assertContains('registration', $this->BadgesPerson->virtualProperties());
		$this->assertContains('team', $this->BadgesPerson->virtualProperties());
	}

}
