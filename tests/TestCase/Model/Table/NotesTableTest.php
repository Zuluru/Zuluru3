<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\NotesTable;
use Cake\ORM\TableRegistry;

/**
 * App\Model\Table\NotesTable Test Case
 */
class NotesTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var NotesTable
	 */
	public $Notes;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('Notes') ? [] : ['className' => NotesTable::class];
		$this->Notes = TableRegistry::getTableLocator()->get('Notes', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->Notes);

		parent::tearDown();
	}

	/**
	 * Test initialize method
	 */
	public function testInitialize(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationDefault method
	 */
	public function testValidationDefault(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
