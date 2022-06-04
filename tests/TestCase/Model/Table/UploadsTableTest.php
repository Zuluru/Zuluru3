<?php
namespace App\Test\TestCase\Model\Table;

use App\Test\Factory\UploadFactory;
use Cake\Core\Configure;
use Cake\Filesystem\Folder;
use Cake\ORM\TableRegistry;
use App\Model\Table\UploadsTable;

/**
 * App\Model\Table\UploadsTable Test Case
 */
class UploadsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var UploadsTable
	 */
	public $UploadsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();

		$config = TableRegistry::getTableLocator()->exists('Uploads') ? [] : ['className' => UploadsTable::class];
		$this->UploadsTable = TableRegistry::getTableLocator()->get('Uploads', $config);

		$folder = new Folder(TESTS . 'test_app' . DS . 'upload', true);
		Configure::write('App.paths.uploads', $folder->path);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->UploadsTable);

		// Delete the temporary uploads
		$upload_path = Configure::read('App.paths.uploads');
		$folder = new Folder($upload_path);
		$folder->delete();

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
		$affiliateId = mt_rand();
		$entity = UploadFactory::make(['person_id' => 1])->with('UploadTypes', ['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->UploadsTable->affiliate($entity->id));
	}

}
