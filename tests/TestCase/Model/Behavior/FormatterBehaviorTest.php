<?php
namespace App\Test\TestCase\Model\Behavior;

use Cake\ORM\Entity;
use Cake\TestSuite\TestCase;
use App\Model\Behavior\FormatterBehavior;

/**
 * App\Model\Behavior\FormatterBehavior Test Case
 */
class FormatterBehaviorTest extends TestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Behavior\FormatterBehavior
	 */
	public $FormatterBehavior;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$table = $this->createMock('Cake\ORM\Table');
		$this->FormatterBehavior = new FormatterBehavior($table, [
			'fields' => [
				'name' => 'proper_case_format',
				'postalcode' => 'postal_format',
				'phone' => 'phone_format',
			],
		]);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->FormatterBehavior);

		parent::tearDown();
	}

	/**
	 * Test format method
	 *
	 * @return void
	 */
	public function testFormat(): void {
		$entity = new Entity([
			'name' => 'amy administrator',
			'postalcode' => 'm1a1a1',
			'country' => 'Canada',
			'phone' => '4162345678',
		]);
		$this->FormatterBehavior->format($entity);
		$this->assertEquals('Amy Administrator', $entity->name);
		$this->assertEquals('M1A 1A1', $entity->postalcode);
		$this->assertEquals('(416) 234-5678', $entity->phone);
	}

	/**
	 * Test postal_format method
	 *
	 * @return void
	 */
	public function testPostalFormat(): void {
		$this->assertEquals('90210-1234', $this->FormatterBehavior->postal_format('902101234', 'US'));
		$this->assertEquals('SW1W 0NY', $this->FormatterBehavior->postal_format('SW1W0NY', 'GB'));
		$this->assertEquals('L1 8JQ', $this->FormatterBehavior->postal_format('L18JQ', 'GB'));
	}

	/**
	 * Test phone_format method
	 *
	 * @return void
	 */
	public function testPhoneFormat(): void {
		$this->assertEquals('(03) 1234 5678', $this->FormatterBehavior->phone_format('312345678', 'AU'));
		$this->assertEquals('0412 345 678', $this->FormatterBehavior->phone_format('0412345678', 'AU'));
	}

	/**
	 * Test proper_case_format method
	 *
	 * @return void
	 */
	public function testProperCaseFormat(): void {
		$this->assertEquals('O\'Reilly', $this->FormatterBehavior->proper_case_format('O\'REILLY', null));
		$this->assertEquals('de Vries', $this->FormatterBehavior->proper_case_format('de Vries', null));
		$this->assertEquals('MacDonald', $this->FormatterBehavior->proper_case_format('macdonald', null));
	}

}
