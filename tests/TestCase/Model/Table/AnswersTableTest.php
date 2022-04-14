<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\AnswersTable;

/**
 * App\Model\Table\AnswersTable Test Case
 */
class AnswersTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\AnswersTable
	 */
	public $AnswersTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::exists('Answers') ? [] : ['className' => 'App\Model\Table\AnswersTable'];
		$this->AnswersTable = TableRegistry::getTableLocator()->get('Answers', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->AnswersTable);

		parent::tearDown();
	}

}
