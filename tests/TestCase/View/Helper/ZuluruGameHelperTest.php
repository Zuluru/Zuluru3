<?php
namespace App\Test\TestCase\View\Helper;

use Cake\TestSuite\TestCase;
use Cake\View\View;
use App\View\Helper\ZuluruGameHelper;

/**
 * App\Model\Helper\ZuluruGameHelper Test Case
 */
class ZuluruGameHelperTest extends TestCase {

	/**
	 * Test subject
	 *
	 * @var \App\View\Helper\ZuluruGameHelper
	 */
	public $ZuluruGameHelper;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$view = new View();
		$this->ZuluruGameHelper = new ZuluruGameHelper($view);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->ZuluruGameHelper);

		parent::tearDown();
	}

	/**
	 * Test displayScore method
	 *
	 * @return void
	 */
	public function testDisplayScore() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
