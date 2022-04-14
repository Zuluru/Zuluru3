<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\QuestionnairesQuestionsTable;

/**
 * App\Model\Table\QuestionnairesQuestionsTable Test Case
 */
class QuestionnairesQuestionsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\QuestionnairesQuestionsTable
	 */
	public $QuestionnairesQuestionsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::exists('QuestionnairesQuestions') ? [] : ['className' => 'App\Model\Table\QuestionnairesQuestionsTable'];
		$this->QuestionnairesQuestionsTable = TableRegistry::getTableLocator()->get('QuestionnairesQuestions', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->QuestionnairesQuestionsTable);

		parent::tearDown();
	}

}
