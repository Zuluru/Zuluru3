<?php
namespace App\Test\TestCase\Model\Table;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use App\Model\Table\ConfigurationTable;

/**
 * App\Model\Table\ConfigurationTable Test Case
 */
class ConfigurationTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\ConfigurationTable
	 */
	public $ConfigurationTable;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.affiliates',
			'app.users',
				'app.people',
			'app.settings',
		'app.i18n',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Configuration') ? [] : ['className' => 'App\Model\Table\ConfigurationTable'];
		$this->ConfigurationTable = TableRegistry::get('Configuration', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->ConfigurationTable);

		parent::tearDown();
	}

	/**
	 * Test loadSystem method
	 *
	 * @return void
	 */
	public function testLoadSystem() {
		Configure::load('options');

		$this->assertEmpty(Configure::read('provinces'), 'Province list should not have been loaded yet');
		$this->assertEmpty(Configure::read('countries'), 'Country list should not have been loaded yet');
		$this->assertEmpty(Configure::read('profile'), 'Profile settings should not have been loaded yet');
		$this->assertEmpty(Configure::read('personal'), 'Personal settings should not have been loaded');
		$this->ConfigurationTable->loadSystem();
		$this->assertNotEmpty(Configure::read('provinces'), 'Province list should have been loaded now');
		$this->assertNotEmpty(Configure::read('countries'), 'Country list should have been loaded now');
		$this->assertNotEmpty(Configure::read('profile'), 'Profile settings should have been loaded now');
		$this->assertEmpty(Configure::read('personal'), 'Personal settings should not have been loaded');
	}

	/**
	 * Test loadAffiliate method
	 *
	 * @return void
	 */
	public function testLoadAffiliate() {
		Configure::load('options');

		$this->ConfigurationTable->loadSystem();
		$this->assertEquals('Test Zuluru Affiliate', Configure::read('organization.name'));
		$this->ConfigurationTable->loadAffiliate(AFFILIATE_ID_SUB);
		$this->assertEquals('Test Sub Affiliate', Configure::read('organization.name'));
	}

	/**
	 * Test loadUser method
	 *
	 * @return void
	 */
	public function testLoadUser() {
		$this->assertEmpty(Configure::read('personal'), 'Personal settings should not have been loaded yet');
		$this->ConfigurationTable->loadUser(PERSON_ID_MANAGER);
		$this->assertNotEmpty(Configure::read('personal'), 'Personal settings should have been loaded');
	}

}
