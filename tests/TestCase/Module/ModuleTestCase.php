<?php
namespace App\Test\TestCase\Module;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Event\Event as CakeEvent;
use Cake\Event\EventManager;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class ModuleTestCase extends TestCase {

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		foreach (Configure::read('App.globalListeners') as $listener) {
			EventManager::instance()->on($listener);
		}

		$event = new CakeEvent('Controller.initialize', $this);
		EventManager::instance()->dispatch($event);
	}

	public function tearDown() {
		parent::tearDown();
		Cache::clear(false, 'long_term');
		FrozenTime::setTestNow();
		FrozenDate::setTestNow();
	}

}
