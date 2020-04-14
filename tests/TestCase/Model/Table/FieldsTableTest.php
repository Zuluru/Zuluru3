<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\FieldsTable;

/**
 * App\Model\Table\FieldsTable Test Case
 */
class FieldsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\FieldsTable
	 */
	public $FieldsTable;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.Affiliates',
			'app.Regions',
				'app.Facilities',
					'app.Fields',
		'app.I18n',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Fields') ? [] : ['className' => 'App\Model\Table\FieldsTable'];
		$this->FieldsTable = TableRegistry::get('Fields', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->FieldsTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 *
	 * @return void
	 */
	public function testAffiliate() {
		$this->assertEquals(AFFILIATE_ID_CLUB, $this->FieldsTable->affiliate(FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1));
	}

	/**
	 * Test sport method
	 *
	 * @return void
	 */
	public function testSport() {
		$this->assertEquals('ultimate', $this->FieldsTable->sport(FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1));
	}

}
