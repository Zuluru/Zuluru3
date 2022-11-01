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
	 * @var UploadTypesTable
	 */
	public $UploadTypesTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('UploadTypes') ? [] : ['className' => UploadTypesTable::class];
		$this->UploadTypesTable = TableRegistry::getTableLocator()->get('UploadTypes', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->UploadTypesTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 */
	public function testAffiliate(): void {
        $affiliateId = mt_rand();
        $entity = UploadTypeFactory::make(['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->UploadTypesTable->affiliate($entity->id));
	}

}
