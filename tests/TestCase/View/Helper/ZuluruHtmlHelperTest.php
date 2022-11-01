<?php
namespace App\Test\TestCase\View\Helper;

use Cake\TestSuite\TestCase;
use Cake\View\View;
use App\View\Helper\ZuluruHtmlHelper;

/**
 * App\Model\Helper\ZuluruHtmlHelper Test Case
 */
class ZuluruHtmlHelperTest extends TestCase {

	/**
	 * Test subject
	 *
	 * @var \App\View\Helper\ZuluruHtmlHelper
	 */
	public $ZuluruHtmlHelper;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$view = new View();
		$this->ZuluruHtmlHelper = new ZuluruHtmlHelper($view);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->ZuluruHtmlHelper);

		parent::tearDown();
	}

	/**
	 * Test link method
	 */
	public function testLink(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test scriptBlock method
	 */
	public function testScriptBlock(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test imageLink method
	 */
	public function testImageLink(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test iconImg method
	 */
	public function testIconImg(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test iconLink method
	 */
	public function testIconLink(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test help method
	 */
	public function testHelp(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test formatMessage method
	 */
	public function testFormatMessage(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
