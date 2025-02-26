<?php
namespace App\Test\TestCase\Authentication;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Event\EventManager;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;

/**
 * App\Authentication\ActAsIdentity Test Case
 */
class ActAsIdentityTest extends TestCase {

	use IntegrationTestTrait;
	use TruncateDirtyTables;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		foreach (Configure::read('App.globalListeners') as $listener) {
			EventManager::instance()->on($listener);
		}
	}

	public function tearDown(): void {
		Cache::clear('long_term');
		parent::tearDown();
	}

	/**
	 * Test applicableAffiliateIDs method as an admin
	 */
	public function testApplicableAffiliateIDsAsAdmin(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test applicableAffiliateIDs method as a manager
	 */
	public function testApplicableAffiliateIDsAsManager(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test applicableAffiliateIDs method as a coordinator
	 */
	public function testApplicableAffiliateIDsAsCoordinator(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test applicableAffiliateIDs method as a captain
	 */
	public function testApplicableAffiliateIDsAsCaptain(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test applicableAffiliateIDs method as a player
	 */
	public function testApplicableAffiliateIDsAsPlayer(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test applicableAffiliateIDs method as someone else
	 */
	public function testApplicableAffiliateIDsAsVisitor(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test applicableAffiliateIDs method without being logged in
	 */
	public function testApplicableAffiliateIDsAsAnonymous(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
