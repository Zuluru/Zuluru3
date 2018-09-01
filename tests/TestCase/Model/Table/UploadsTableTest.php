<?php
namespace App\Test\TestCase\Model\Table;

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
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.affiliates',
			'app.users',
				'app.people',
					'app.affiliates_people',
			'app.upload_types',
				'app.uploads',
	];

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
		$this->assertEquals(AFFILIATE_ID_CLUB, $this->UploadsTable->affiliate(UPLOAD_ID_CHILD_WAIVER));
	}

}
