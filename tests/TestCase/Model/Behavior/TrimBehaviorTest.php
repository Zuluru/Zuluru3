<?php
namespace App\Test\TestCase\Model\Behavior;

use ArrayObject;
use Cake\TestSuite\TestCase;
use App\Model\Behavior\TrimBehavior;

/**
 * App\Model\Behavior\TrimBehavior Test Case
 */
class TrimBehaviorTest extends TestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Behavior\TrimBehavior
	 */
	public $TrimBehavior;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$table = $this->getMockForModel('People', ['dummy']);
		$this->TrimBehavior = new TrimBehavior($table);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->TrimBehavior);

		parent::tearDown();
	}

	/**
	 * Test trim method
	 */
	public function testTrim(): void {
		$entity = new ArrayObject([
			'first_name' => ' first',
			'last_name' => 'last ',
		]);
		$this->TrimBehavior->trim($entity);
		$this->assertEquals('first', $entity['first_name']);
		$this->assertEquals('last', $entity['last_name']);
	}

}
