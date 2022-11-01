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
	 * @var CategoriesTable
	 */
	public $CategoriesTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('Categories') ? [] : ['className' => CategoriesTable::class];
		$this->CategoriesTable = TableRegistry::getTableLocator()->get('Categories', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->CategoriesTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 */
	public function testAffiliate(): void {
        $affiliateId = mt_rand();
        $category = CategoryFactory::make(['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->CategoriesTable->affiliate($category->id));
	}

}
