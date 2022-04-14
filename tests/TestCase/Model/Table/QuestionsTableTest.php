<?php
namespace App\Test\TestCase\Model\Table;

use App\Test\Factory\QuestionFactory;
use Cake\ORM\TableRegistry;
use App\Model\Table\QuestionsTable;

/**
 * App\Model\Table\QuestionsTable Test Case
 */
class QuestionsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\QuestionsTable
	 */
	public $QuestionsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::exists('Questions') ? [] : ['className' => 'App\Model\Table\QuestionsTable'];
		$this->QuestionsTable = TableRegistry::getTableLocator()->get('Questions', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->QuestionsTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 */
	public function testAffiliate(): void {
        $affiliateId = rand();
        $entity = QuestionFactory::make(['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->QuestionsTable->affiliate($entity->id));
	}

}
