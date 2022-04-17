<?php
namespace App\Test\TestCase\Model\Table;

use App\Test\Factory\UploadFactory;
use Cake\ORM\TableRegistry;
use App\Model\Table\UploadsTable;

/**
 * App\Model\Table\UploadsTable Test Case
 */
class UploadsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\UploadsTable
	 */
	public $UploadsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::exists('Uploads') ? [] : ['className' => 'App\Model\Table\UploadsTable'];
		$this->UploadsTable = TableRegistry::getTableLocator()->get('Uploads', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->UploadsTable);

		parent::tearDown();
	}

	/**
	 * Test afterSave method
	 */
	public function testAfterSave(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test afterDeleteCommit method
	 */
	public function testAfterDeleteCommit(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test affiliate method
	 */
	public function testAffiliate(): void {
		$affiliateId = rand();
		$entity = UploadFactory::make()->with('UploadTypes', ['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->UploadsTable->affiliate($entity->id));
	}

}
