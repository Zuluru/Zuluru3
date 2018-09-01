<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\NewslettersTable;

/**
 * App\Model\Table\NewslettersTable Test Case
 */
class NewslettersTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\NewslettersTable
	 */
	public $NewslettersTable;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.affiliates',
			'app.users',
				'app.people',
					'app.affiliates_people',
			'app.mailing_lists',
				'app.newsletters',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Newsletters') ? [] : ['className' => 'App\Model\Table\NewslettersTable'];
		$this->NewslettersTable = TableRegistry::get('Newsletters', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->NewslettersTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 *
	 * @return void
	 */
	public function testAffiliate() {
		$this->assertEquals(AFFILIATE_ID_CLUB, $this->NewslettersTable->affiliate(1));
	}

}
