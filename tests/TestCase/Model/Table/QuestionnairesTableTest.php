<?php
namespace App\Test\TestCase\Model\Table;

use App\Test\Factory\QuestionnaireFactory;
use Cake\ORM\TableRegistry;
use App\Model\Table\QuestionnairesTable;

/**
 * App\Model\Table\QuestionnairesTable Test Case
 */
class QuestionnairesTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\QuestionnairesTable
	 */
	public $QuestionnairesTable;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Questionnaires') ? [] : ['className' => 'App\Model\Table\QuestionnairesTable'];
		$this->QuestionnairesTable = TableRegistry::get('Questionnaires', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->QuestionnairesTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 *
	 * @return void
	 */
	public function testAffiliate() {
        $affiliateId = rand();
        $entity = QuestionnaireFactory::make(['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->QuestionnairesTable->affiliate($entity->id));
	}

}
