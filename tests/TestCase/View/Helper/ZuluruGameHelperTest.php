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
	 */
	public function setUp(): void {
		parent::setUp();
		$view = new View();
		$this->ZuluruGameHelper = new ZuluruGameHelper($view);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->ZuluruGameHelper);

		parent::tearDown();
	}

	/**
	 * Test displayScore method
	 */
	public function testDisplayScore(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
