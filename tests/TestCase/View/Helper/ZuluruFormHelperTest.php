<?php
namespace App\Test\TestCase\View\Helper;

use Cake\TestSuite\TestCase;
use Cake\View\View;
use App\View\Helper\ZuluruFormHelper;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;

/**
 * App\Model\Helper\ZuluruFormHelper Test Case
 */
class ZuluruFormHelperTest extends TestCase {

	use TruncateDirtyTables;

	/**
	 * Test subject
	 *
	 * @var \App\View\Helper\ZuluruFormHelper
	 */
	public $ZuluruFormHelper;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$view = new View();
		$this->ZuluruFormHelper = new ZuluruFormHelper($view);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->ZuluruFormHelper);

		parent::tearDown();
	}

	/**
	 * Test create method
	 */
	public function testCreate(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test input method
	 */
	public function testInput(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test iconPostLink method
	 */
	public function testIconPostLink(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
