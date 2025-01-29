<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Entity\Person;
use App\Test\Factory\PersonFactory;
use App\Test\Factory\WaiverFactory;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use App\Model\Table\WaiversTable;

/**
 * App\Model\Table\WaiversTable Test Case
 */
class WaiversTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var WaiversTable
	 */
	public $WaiversTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('Waivers') ? [] : ['className' => WaiversTable::class];
		$this->WaiversTable = TableRegistry::getTableLocator()->get('Waivers', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->WaiversTable);

		parent::tearDown();
	}

	/**
	 * Test signed method
	 */
	public function testSigned(): void {
		/** @var Person $person */
		$person = PersonFactory::make()
			->with('WaiversPeople', [
				'created' => FrozenDate::now(),
				'valid_from' => FrozenTime::now()->startOfYear(),
				'valid_until' => FrozenTime::now()->endOfYear(),
			])
			->persist();

		$signed = $this->WaiversTable::signed($person->waivers_people, FrozenDate::now());
		$this->assertTrue($signed);
		$signed = $this->WaiversTable::signed($person->waivers_people, FrozenDate::now()->subYears(1));
		$this->assertFalse($signed);
		$signed = $this->WaiversTable::signed($person->waivers_people, FrozenDate::now()->addYears(1));
		$this->assertFalse($signed);
	}

	/**
	 * Test affiliate method
	 */
	public function testAffiliate(): void {
        $affiliateId = mt_rand();
        $entity = WaiverFactory::make(['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->WaiversTable->affiliate($entity->id));
	}

}
