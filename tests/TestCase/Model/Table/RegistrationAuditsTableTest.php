<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\RegistrationAuditsTable;
use Cake\ORM\TableRegistry;

/**
 * App\Model\Table\RegistrationAuditsTable Test Case
 */
class RegistrationAuditsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\RegistrationAuditsTable
	 */
	public $RegistrationAudits;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::exists('RegistrationAudits') ? [] : ['className' => 'App\Model\Table\RegistrationAuditsTable'];
		$this->RegistrationAudits = TableRegistry::getTableLocator()->get('RegistrationAudits', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->RegistrationAudits);

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
