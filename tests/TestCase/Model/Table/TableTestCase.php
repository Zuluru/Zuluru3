<?php
namespace App\Test\TestCase\Model\Table;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Event\EventList;
use Cake\Event\EventManager;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * Base class for all table tests
 */
class TableTestCase extends TestCase {

	use IntegrationTestTrait;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		EventManager::instance()->setEventList(new EventList());
		foreach (Configure::read('App.globalListeners') as $listener) {
			EventManager::instance()->on($listener);
		}
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		Cache::clear('long_term');
		FrozenTime::setTestNow();
		parent::tearDown();
	}

}
