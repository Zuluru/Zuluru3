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
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		// TODO
    ];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('RegistrationAudits') ? [] : ['className' => 'App\Model\Table\RegistrationAuditsTable'];
		$this->RegistrationAudits = TableRegistry::get('RegistrationAudits', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->RegistrationAudits);

		parent::tearDown();
	}

	/**
	 * Test initialize method
	 *
	 * @return void
	 */
	public function testInitialize() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationDefault method
	 *
	 * @return void
	 */
	public function testValidationDefault() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
