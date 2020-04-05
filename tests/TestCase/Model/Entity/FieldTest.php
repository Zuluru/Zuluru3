<?php
namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\Field;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class FieldTest extends TestCase {

	/**
	 * Test Entity to use
	 *
	 * @var \App\Model\Entity\Field
	 */
	public $Field;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.affiliates',
			'app.regions',
				'app.facilities',
					'app.fields',
		'app.i18n',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		// Read in the Field setup in the fixture and use that for these tests
		$fields = TableRegistry::get('Fields');
		$this->Field = $fields->get(FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->Field);

		parent::tearDown();
	}

	/**
	 * Test _getLongName method
	 *
	 * @return void
	 */
	public function testGetLongName() {
		$this->assertEquals('Sunnybrook Field Hockey 1', $this->Field->long_name);
	}
	/**
	 * Test _getLongCode method
	 *
	 * @return void
	 */
	public function testGetLongCode() {
		$this->assertEquals('SUN Field Hockey 1', $this->Field->long_code);
	}

}
