<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\CreditsTable;

/**
 * App\Model\Table\CreditsTable Test Case
 */
class CreditsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\CreditsTable
	 */
	public $CreditsTable;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.Affiliates',
			'app.Users',
				'app.People',
					'app.Credits',
		'app.I18n',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Credits') ? [] : ['className' => 'App\Model\Table\CreditsTable'];
		$this->CreditsTable = TableRegistry::get('Credits', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->CreditsTable);

		parent::tearDown();
	}

}
