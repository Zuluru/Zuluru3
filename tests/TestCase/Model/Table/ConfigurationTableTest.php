<?php
namespace App\Test\TestCase\Model\Table;

use App\Test\Factory\AffiliateFactory;
use App\Test\Factory\PersonFactory;
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
	 * @var ConfigurationTable
	 */
	public $ConfigurationTable;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.Countries',
		'app.Provinces',
		'app.Settings',
	];

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('Configuration') ? [] : ['className' => ConfigurationTable::class];
		$this->ConfigurationTable = TableRegistry::getTableLocator()->get('Configuration', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->ConfigurationTable);

		parent::tearDown();
	}

	/**
	 * Test loadSystem method
	 */
	public function testLoadSystem(): void {
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
	 */
	public function testLoadAffiliate(): void {
		Configure::load('options');

		$this->ConfigurationTable->loadSystem();
		$this->assertEquals('Test Zuluru Affiliate', Configure::read('organization.name'));

		$affiliate = AffiliateFactory::make()->with('Settings', [
			'category' => 'organization',
			'name' => 'name',
			'value' => 'Test Sub Affiliate',
		])->persist();
		$this->ConfigurationTable->loadAffiliate($affiliate->id);
		$this->assertEquals('Test Sub Affiliate', Configure::read('organization.name'));
	}

	/**
	 * Test loadUser method
	 */
	public function testLoadUser(): void {
		$this->assertEmpty(Configure::read('personal'), 'Personal settings should not have been loaded yet');

		$person = PersonFactory::make()->with('Settings', [
			'category' => 'personal',
			'name' => 'test',
		])->persist();
		$this->ConfigurationTable->loadUser($person->id);
		$this->assertNotEmpty(Configure::read('personal'), 'Personal settings should have been loaded');
	}

}
