<?php
namespace App\Test\TestCase\Model\Table;

use Cake\I18n\FrozenDate;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use App\Model\Table\WaiversTable;

/**
 * App\Model\Table\WaiversTable Test Case
 */
class WaiversTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\WaiversTable
	 */
	public $WaiversTable;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.Affiliates',
			'app.Users',
				'app.People',
					'app.AffiliatesPeople',
			'app.Waivers',
				'app.WaiversPeople',
		'app.I18n',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Waivers') ? [] : ['className' => 'App\Model\Table\WaiversTable'];
		$this->WaiversTable = TableRegistry::get('Waivers', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->WaiversTable);

		parent::tearDown();
	}

	/**
	 * Test signed method
	 *
	 * @return void
	 */
	public function testSigned() {
		$person = $this->WaiversTable->People->get(PERSON_ID_ADMIN, [
			'contain' => ['Waivers' => [
				'queryBuilder' => function (Query $q) {
					return $q->where(['Waivers.id' => WAIVER_ID_ANNUAL]);
				},
			]],
		]);
		$signed = $this->WaiversTable->signed($person->waivers, FrozenDate::now());
		$this->assertEquals(true, $signed);
		$signed = $this->WaiversTable->signed($person->waivers, FrozenDate::now()->subYear());
		$this->assertEquals(false, $signed);
		$signed = $this->WaiversTable->signed($person->waivers, FrozenDate::now()->addYear());
		$this->assertEquals(false, $signed);
	}

	/**
	 * Test affiliate method
	 *
	 * @return void
	 */
	public function testAffiliate() {
		$this->assertEquals(AFFILIATE_ID_CLUB, $this->WaiversTable->affiliate(1));
	}

}
