<?php
namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\Holiday;
use Cake\I18n\FrozenDate;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class HolidayTest extends TestCase {

	/**
	 * The Entity we'll be using in the test
	 *
	 * @var \App\Model\Entity\Holiday
	 */
	public $Holiday;

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
		$holidays = TableRegistry::get('Holidays');
		$this->Holiday = $holidays->get(HOLIDAY_ID_CHRISTMAS);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->Holiday);

		parent::tearDown();
	}

	/**
	 * Test _getDate method
	 *
	 * @return void
	 */
	public function testGetDate() {
		$this->assertEquals(new FrozenDate('December 25'), $this->Holiday->date);
	}

}
