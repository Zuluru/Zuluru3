<?php
namespace App\Test\TestCase\Model\Table;

use App\Test\Factory\GameFactory;
use App\Test\Factory\WaiverFactory;
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
        $this->markTestSkipped(GameFactory::TODO_FACTORIES);
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
        $affiliateId = rand();
        $entity = WaiverFactory::make(['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->WaiversTable->affiliate($entity->id));
	}

}
