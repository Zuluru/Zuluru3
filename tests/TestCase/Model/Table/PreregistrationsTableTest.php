<?php
namespace App\Test\TestCase\Model\Table;

use App\Test\Factory\PreregistrationFactory;
use Cake\ORM\TableRegistry;
use App\Model\Table\PreregistrationsTable;

/**
 * App\Model\Table\PreregistrationsTable Test Case
 */
class PreregistrationsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\PreregistrationsTable
	 */
	public $PreregistrationsTable;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Preregistrations') ? [] : ['className' => 'App\Model\Table\PreregistrationsTable'];
		$this->PreregistrationsTable = TableRegistry::get('Preregistrations', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->PreregistrationsTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 *
	 * @return void
	 */
	public function testAffiliate() {
        $affiliateId = rand();
        $entity = PreregistrationFactory::make()->with('Events', ['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->PreregistrationsTable->affiliate($entity->id));
	}

}
