<?php
namespace App\Test\TestCase\Module;

use App\Middleware\ConfigurationLoader;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Event\EventManager;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\TestSuite\TestCase;

class ModuleTestCase extends TestCase {

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();

		foreach (Configure::read('App.globalListeners') as $listener) {
			EventManager::instance()->on($listener);
		}

		ConfigurationLoader::loadConfiguration();
	}

	public function tearDown(): void {
		parent::tearDown();
		Cache::clear('long_term');
		FrozenTime::setTestNow();
	}

}
