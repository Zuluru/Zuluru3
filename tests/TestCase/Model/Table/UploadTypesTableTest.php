<?php
namespace App\Test\TestCase\Model\Table;

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
	];

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
		$this->assertEquals(AFFILIATE_ID_CLUB, $this->UploadTypesTable->affiliate(1));
	}

}
