<?php
namespace App\Test\TestCase\Model\Table;

use App\Test\Factory\CategoryFactory;
use Cake\ORM\TableRegistry;
use App\Model\Table\CategoriesTable;

/**
 * App\Model\Table\CategoriesTable Test Case
 */
class CategoriesTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\CategoriesTable
	 */
	public $CategoriesTable;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Categories') ? [] : ['className' => 'App\Model\Table\CategoriesTable'];
		$this->CategoriesTable = TableRegistry::get('Categories', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->CategoriesTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 *
	 * @return void
	 */
	public function testAffiliate() {
        $affiliateId = rand();
        $category = CategoryFactory::make(['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->CategoriesTable->affiliate($category->id));
	}

}
