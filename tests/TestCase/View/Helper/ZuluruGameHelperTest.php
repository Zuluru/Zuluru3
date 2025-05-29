<?php
namespace App\Test\TestCase\View\Helper;

use Cake\TestSuite\TestCase;
use Cake\View\View;
use App\View\Helper\ZuluruGameHelper;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;

/**
 * App\Model\Helper\ZuluruGameHelper Test Case
 */
class ZuluruGameHelperTest extends TestCase {

	use TruncateDirtyTables;

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
	 * Test score method
	 */
	public function testScore(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test score_string method
	 */
	public function testScoreString(): void {
	}

	/**
	 * Test actions method
	 */
	public function testActions(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}
}
