<?php
namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\Group;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class GroupTest extends TestCase {

	/**
	 * The group we'll be using in the test
	 *
	 * @var \App\Model\Entity\Group
	 */
	public $Group;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.affiliates',
			'app.groups',
		'app.i18n',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$groups = TableRegistry::get('Groups');
		$this->Group = $groups->get(GROUP_ID_PLAYER);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->Group);

		parent::tearDown();
	}

	/**
	 * Test _getLongName method
	 *
	 * @return void
	 */
	public function testGetLongName() {
		$this->assertEquals('Player: You will be participating as a player.', $this->Group->long_name);
	}

}
