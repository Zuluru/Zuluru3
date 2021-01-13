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
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Questions') ? [] : ['className' => 'App\Model\Table\QuestionsTable'];
		$this->QuestionsTable = TableRegistry::get('Questions', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->QuestionsTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 *
	 * @return void
	 */
	public function testAffiliate() {
        $affiliateId = rand();
        $entity = QuestionFactory::make(['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->QuestionsTable->affiliate($entity->id));
	}

}
