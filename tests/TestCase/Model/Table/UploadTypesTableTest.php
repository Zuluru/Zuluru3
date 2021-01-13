<?php
namespace App\Test\TestCase\Model\Table;

use App\Test\Factory\UploadTypeFactory;
use Cake\ORM\TableRegistry;
use App\Model\Table\UploadTypesTable;

/**
 * App\Model\Table\UploadTypesTable Test Case
 */
class UploadTypesTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\UploadTypesTable
	 */
	public $UploadTypesTable;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('UploadTypes') ? [] : ['className' => 'App\Model\Table\UploadTypesTable'];
		$this->UploadTypesTable = TableRegistry::get('UploadTypes', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->UploadTypesTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 *
	 * @return void
	 */
	public function testAffiliate() {
        $affiliateId = rand();
        $entity = UploadTypeFactory::make(['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->UploadTypesTable->affiliate($entity->id));
	}

}
