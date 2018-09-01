<?php
namespace App\Test\TestCase\Model\Table;

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
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.affiliates',
			'app.questions',
				'app.answers',
			'app.questionnaires',
				'app.questionnaires_questions',
	];

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
		$this->assertEquals(AFFILIATE_ID_CLUB, $this->QuestionnairesTable->affiliate(1));
	}

}
