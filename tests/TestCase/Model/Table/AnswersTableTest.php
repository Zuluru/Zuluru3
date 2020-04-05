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
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.affiliates',
			'app.questions',
				'app.answers',
		'app.i18n',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Answers') ? [] : ['className' => 'App\Model\Table\AnswersTable'];
		$this->AnswersTable = TableRegistry::get('Answers', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->AnswersTable);

		parent::tearDown();
	}

}
