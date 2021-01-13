<?php
namespace App\Test\TestCase\Authentication;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Event\EventManager;
use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Authentication\ActAsIdentity Test Case
 */
class ActAsIdentityTest extends IntegrationTestCase {

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
	}

	public function tearDown() {
		Cache::clear(false, 'long_term');
		parent::tearDown();
	}

	/**
	 * Test applicableAffiliateIDs method as an admin
	 *
	 * @return void
	 */
	public function testApplicableAffiliateIDsAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test applicableAffiliateIDs method as a manager
	 *
	 * @return void
	 */
	public function testApplicableAffiliateIDsAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test applicableAffiliateIDs method as a coordinator
	 *
	 * @return void
	 */
	public function testApplicableAffiliateIDsAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test applicableAffiliateIDs method as a captain
	 *
	 * @return void
	 */
	public function testApplicableAffiliateIDsAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test applicableAffiliateIDs method as a player
	 *
	 * @return void
	 */
	public function testApplicableAffiliateIDsAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test applicableAffiliateIDs method as someone else
	 *
	 * @return void
	 */
	public function testApplicableAffiliateIDsAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test applicableAffiliateIDs method without being logged in
	 *
	 * @return void
	 */
	public function testApplicableAffiliateIDsAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
