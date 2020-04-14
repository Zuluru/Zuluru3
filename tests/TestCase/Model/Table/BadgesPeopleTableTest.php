<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\BadgesPeopleTable;

/**
 * App\Model\Table\BadgesPeopleTable Test Case
 */
class BadgesPeopleTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\BadgesPeopleTable
	 */
	public $BadgesPeopleTable;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.Affiliates',
			'app.Users',
				'app.People',
			'app.Leagues',
				'app.Divisions',
					'app.Teams',
			'app.Badges',
				'app.BadgesPeople',
		'app.I18n',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('BadgesPeople') ? [] : ['className' => 'App\Model\Table\BadgesPeopleTable'];
		$this->BadgesPeopleTable = TableRegistry::get('BadgesPeople', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->BadgesPeopleTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 *
	 * @return void
	 */
	public function testAffiliate() {
		$this->assertEquals(AFFILIATE_ID_CLUB, $this->BadgesPeopleTable->affiliate(1));
	}

}
