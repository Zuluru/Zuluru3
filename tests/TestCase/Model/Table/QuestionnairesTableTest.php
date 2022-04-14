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
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::exists('Questionnaires') ? [] : ['className' => 'App\Model\Table\QuestionnairesTable'];
		$this->QuestionnairesTable = TableRegistry::getTableLocator()->get('Questionnaires', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->QuestionnairesTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 */
	public function testAffiliate(): void {
        $affiliateId = rand();
        $entity = QuestionnaireFactory::make(['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->QuestionnairesTable->affiliate($entity->id));
	}

}
