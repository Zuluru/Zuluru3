<?php
namespace App\Test\TestCase\Model\Table;

use App\Test\Factory\PriceFactory;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use App\Model\Table\PricesTable;

/**
 * App\Model\Table\PricesTable Test Case
 */
class PricesTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\PricesTable
	 */
	public $PricesTable;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Prices') ? [] : ['className' => 'App\Model\Table\PricesTable'];
		$this->PricesTable = TableRegistry::get('Prices', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->PricesTable);

		parent::tearDown();
	}

	/**
	 * Test beforeMarshal method
	 *
	 * @return void
	 */
	public function testBeforeMarshal() {
		$data = new \ArrayObject([
			'online_payment_option' => ONLINE_FULL_PAYMENT,
			'minimum_deposit' => 100,
		]);
		$this->PricesTable->beforeMarshal(new Event('testing'), $data, new \ArrayObject());
		$this->assertEquals(0, $data['minimum_deposit']);

		$data = new \ArrayObject([
			'online_payment_option' => ONLINE_NO_MINIMUM,
			'minimum_deposit' => 100,
		]);
		$this->PricesTable->beforeMarshal(new Event('testing'), $data, new \ArrayObject());
		$this->assertEquals(0, $data['minimum_deposit']);

		$data = new \ArrayObject([
			'online_payment_option' => ONLINE_NO_PAYMENT,
			'minimum_deposit' => 100,
		]);
		$this->PricesTable->beforeMarshal(new Event('testing'), $data, new \ArrayObject());
		$this->assertEquals(0, $data['minimum_deposit']);

		$data = new \ArrayObject([
			'online_payment_option' => ONLINE_DEPOSIT_ONLY,
			'minimum_deposit' => 100,
		]);
		$this->PricesTable->beforeMarshal(new Event('testing'), $data, new \ArrayObject());
		$this->assertEquals(100, $data['minimum_deposit']);
	}

	/**
	 * Test afterSave method
	 *
	 * @return void
	 */
	public function testAfterSave() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test affiliate method
	 *
	 * @return void
	 */
	public function testAffiliate() {
        $affiliateId = rand();
        $entity = PriceFactory::make()->with('Events', ['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->PricesTable->affiliate($entity->id));
	}

	/**
	 * Test duration method
	 *
	 * @return void
	 */
	public function testDuration() {
		$this->assertEquals('1 day, 1 hour, 15 minutes', $this->PricesTable->duration(1515));
	}

}
