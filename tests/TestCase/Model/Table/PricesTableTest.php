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
	 * @var PricesTable
	 */
	public $PricesTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('Prices') ? [] : ['className' => PricesTable::class];
		$this->PricesTable = TableRegistry::getTableLocator()->get('Prices', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->PricesTable);

		parent::tearDown();
	}

	/**
	 * Test beforeMarshal method
	 */
	public function testBeforeMarshal(): void {
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
	 */
	public function testAfterSave(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test affiliate method
	 */
	public function testAffiliate(): void {
        $affiliateId = mt_rand();
        $entity = PriceFactory::make()->with('Events', ['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->PricesTable->affiliate($entity->id));
	}

	/**
	 * Test duration method
	 */
	public function testDuration(): void {
		$this->assertEquals('1 day, 1 hour, 15 minutes', $this->PricesTable->duration(1515));
	}

}
