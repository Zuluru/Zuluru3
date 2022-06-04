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
	 * @var QuestionnairesQuestionsTable
	 */
	public $QuestionnairesQuestionsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('QuestionnairesQuestions') ? [] : ['className' => QuestionnairesQuestionsTable::class];
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
