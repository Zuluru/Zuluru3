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
	 * @var FieldsTable
	 */
	public $FieldsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('Fields') ? [] : ['className' => FieldsTable::class];
		$this->FieldsTable = TableRegistry::getTableLocator()->get('Fields', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->FieldsTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 */
	public function testAffiliate(): void {
        $affiliateId = mt_rand();
        $field = FieldFactory::make()
            ->with('Facilities.Regions', ['affiliate_id' => $affiliateId])
            ->persist();
		$this->assertEquals($affiliateId, $this->FieldsTable->affiliate($field->id));
	}

	/**
	 * Test sport method
	 */
	public function testSport(): void {
        $field = FieldFactory::make()->persist();
		$this->assertEquals($field->get('sport'), $this->FieldsTable->sport($field->id));
	}

}
