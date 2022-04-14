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
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::exists('Preregistrations') ? [] : ['className' => 'App\Model\Table\PreregistrationsTable'];
		$this->PreregistrationsTable = TableRegistry::getTableLocator()->get('Preregistrations', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->PreregistrationsTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 */
	public function testAffiliate(): void {
        $affiliateId = rand();
        $entity = PreregistrationFactory::make()->with('Events', ['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->PreregistrationsTable->affiliate($entity->id));
	}

}
