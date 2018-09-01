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
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.affiliates',
			'app.users',
				'app.people',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$table = $this->getMockForModel('People', ['dummy']);
		$this->TrimBehavior = new TrimBehavior($table);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->TrimBehavior);

		parent::tearDown();
	}

	/**
	 * Test trim method
	 *
	 * @return void
	 */
	public function testTrim() {
		$entity = new ArrayObject([
			'first_name' => ' first',
			'last_name' => 'last ',
		]);
		$this->TrimBehavior->trim($entity);
		$this->assertEquals('first', $entity['first_name']);
		$this->assertEquals('last', $entity['last_name']);
	}

}
