<?php
namespace App\Test\TestCase\Model\Table;

use App\Test\Factory\FieldFactory;
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
	public function testAffiliate(): void {
        $affiliateId = rand();
        $field = FieldFactory::make()
            ->with('Facilities.Regions', ['affiliate_id' => $affiliateId])
            ->persist();
		$this->assertEquals($affiliateId, $this->FieldsTable->affiliate($field->id));
	}

	/**
	 * Test sport method
	 *
	 * @return void
	 */
	public function testSport(): void {
        $field = FieldFactory::make()->persist();
		$this->assertEquals($field->get('sport'), $this->FieldsTable->sport($field->id));
	}

}
