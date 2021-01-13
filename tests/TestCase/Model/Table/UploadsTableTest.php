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
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Uploads') ? [] : ['className' => 'App\Model\Table\UploadsTable'];
		$this->UploadsTable = TableRegistry::get('Uploads', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->UploadsTable);

		parent::tearDown();
	}

	/**
	 * Test afterSave method
	 *
	 * @return void
	 */
	public function testAfterSave() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test afterDeleteCommit method
	 *
	 * @return void
	 */
	public function testAfterDeleteCommit() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test affiliate method
	 *
	 * @return void
	 */
	public function testAffiliate() {
        $affiliateId = rand();
        $entity = UploadFactory::make()->with('UploadTypes', ['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->UploadsTable->affiliate($entity->id));
	}

}
