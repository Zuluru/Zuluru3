<?php
namespace App\Test\TestCase\Model\Table;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Event\EventList;
use Cake\Event\EventManager;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\TestSuite\IntegrationTestCase;

/**
 * Base class for all table tests
 */
class TableTestCase extends IntegrationTestCase {

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		EventManager::instance()->setEventList(new EventList());
		foreach (Configure::read('App.globalListeners') as $listener) {
			EventManager::instance()->on($listener);
		}
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		Cache::clear(false, 'long_term');
		FrozenTime::setTestNow();
		FrozenDate::setTestNow();
		parent::tearDown();
	}

}
