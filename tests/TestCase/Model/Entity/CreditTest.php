<?php
namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\Credit;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Entity\BadgesPerson Test Case
 */
class CreditTest extends TestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Entity\Credit
	 */
	public $Credit;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->Credit = new Credit([
			'affiliate_id' => 1,
			'person_id' => 1,
			'amount' => 15.092,
			'amount_used' => 7,
			'notes' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'created' => '2016-04-26 16:08:01',
			'created_person_id' => 1
		]);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->Credit);

		parent::tearDown();
	}

	/**
	 * Test _getBalance method
	 *
	 * @return void
	 */
	public function testGetBalance(): void {
		$result = $this->Credit->balance;
		$this->assertEquals(8.09, $result, 'Wrong credit balance remaining');
	}

}
