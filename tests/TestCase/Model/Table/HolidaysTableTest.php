<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\HolidaysTable;

/**
 * App\Model\Table\HolidaysTable Test Case
 */
class HolidaysTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\HolidaysTable
	 */
	public $HolidaysTable;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.affiliates',
			'app.holidays',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Holidays') ? [] : ['className' => 'App\Model\Table\HolidaysTable'];
		$this->HolidaysTable = TableRegistry::get('Holidays', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->HolidaysTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 *
	 * @return void
	 */
	public function testAffiliate() {
		$this->assertEquals(AFFILIATE_ID_CLUB, $this->HolidaysTable->affiliate(1));
	}

}
