<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\BadgesTable;

/**
 * App\Model\Table\BadgesTable Test Case
 */
class BadgesTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\BadgesTable
	 */
	public $BadgesTable;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.affiliates',
			'app.leagues',
				'app.divisions',
					'app.teams',
			'app.badges',
				'app.badges_people',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Badges') ? [] : ['className' => 'App\Model\Table\BadgesTable'];
		$this->BadgesTable = TableRegistry::get('Badges', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->BadgesTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 *
	 * @return void
	 */
	public function testAffiliate() {
		$this->assertEquals(AFFILIATE_ID_CLUB, $this->BadgesTable->affiliate(1));
	}

}
