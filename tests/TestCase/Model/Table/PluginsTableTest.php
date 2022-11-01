<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\PluginsTable;
use Cake\ORM\TableRegistry;

/**
 * App\Model\Table\PluginsTable Test Case
 */
class PluginsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var PluginsTable
	 */
	public $Plugins;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
        'app.Plugins'
    ];

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('Plugins') ? [] : ['className' => PluginsTable::class];
		$this->Plugins = TableRegistry::getTableLocator()->get('Plugins', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->Plugins);

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
