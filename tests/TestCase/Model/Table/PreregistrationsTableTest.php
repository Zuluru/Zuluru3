<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\PreregistrationsTable;

/**
 * App\Model\Table\PreregistrationsTable Test Case
 */
class PreregistrationsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\PreregistrationsTable
	 */
	public $PreregistrationsTable;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.event_types',
		'app.affiliates',
			'app.users',
				'app.people',
					'app.affiliates_people',
			'app.leagues',
				'app.divisions',
			'app.events',
				'app.prices',
					'app.registrations',
						'app.payments',
							'app.registration_audits',
						'app.responses',
				'app.preregistrations',
		'app.i18n',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Preregistrations') ? [] : ['className' => 'App\Model\Table\PreregistrationsTable'];
		$this->PreregistrationsTable = TableRegistry::get('Preregistrations', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->PreregistrationsTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 *
	 * @return void
	 */
	public function testAffiliate() {
		$this->assertEquals(AFFILIATE_ID_CLUB, $this->PreregistrationsTable->affiliate(1));
	}

}
