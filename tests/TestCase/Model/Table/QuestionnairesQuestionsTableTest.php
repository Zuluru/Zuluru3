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
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('QuestionnairesQuestions') ? [] : ['className' => 'App\Model\Table\QuestionnairesQuestionsTable'];
		$this->QuestionnairesQuestionsTable = TableRegistry::get('QuestionnairesQuestions', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->QuestionnairesQuestionsTable);

		parent::tearDown();
	}

}
