<?php
namespace App\Test\TestCase\View\Helper;

use Cake\TestSuite\TestCase;
use Cake\View\View;
use App\View\Helper\ZuluruFormHelper;

/**
 * App\Model\Helper\ZuluruFormHelper Test Case
 */
class ZuluruFormHelperTest extends TestCase {

	/**
	 * Test subject
	 *
	 * @var \App\View\Helper\ZuluruFormHelper
	 */
	public $ZuluruFormHelper;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$view = new View();
		$this->ZuluruFormHelper = new ZuluruFormHelper($view);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->ZuluruFormHelper);

		parent::tearDown();
	}

	/**
	 * Test create method
	 *
	 * @return void
	 */
	public function testCreate(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test input method
	 *
	 * @return void
	 */
	public function testInput(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test iconPostLink method
	 *
	 * @return void
	 */
	public function testIconPostLink(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
