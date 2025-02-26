<?php
namespace App\Test\TestCase\Model\Table;

use App\Test\Factory\AffiliatesPersonFactory;
use App\Test\Factory\PersonFactory;
use Cake\ORM\TableRegistry;
use App\Model\Table\AffiliatesTable;

/**
 * App\Model\Table\AffiliatesTable Test Case
 */
class AffiliatesTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var AffiliatesTable
	 */
	public $AffiliatesTable;

	/**
	 * Test readByPlayerId method
	 */
	public function testReadByPlayerId(): void {
        $player = PersonFactory::make()->with('Affiliates')->persist();
		$affiliates = TableRegistry::getTableLocator()->get('Affiliates')->readByPlayerId($player->id);
		$this->assertCount(1, $affiliates);
		$this->assertArrayHasKey(0, $affiliates);
		$this->assertTrue($affiliates[0]->has('id'));
		$this->assertArrayHasKey('People', $affiliates[0]->_matchingData);
		$this->assertTrue($affiliates[0]->_matchingData['People']->has('id'));
		$this->assertEquals($player->id, $affiliates[0]->_matchingData['People']->id);
	}

	/**
	 * Test mergeList method
	 */
	public function testMergeList(): void {
		$original = PersonFactory::make()->with('AffiliatesPeople', AffiliatesPersonFactory::make(['position' => 'manager'])->with('Affiliates'))->persist();
		$original_affiliate = $original->affiliates_people[0]->affiliate_id;
		$new = AffiliatesPersonFactory::make(['person_id' => $original->id])->with('Affiliates')->persist();
		$new_affiliate = $new->affiliate_id;

		$affiliates = TableRegistry::getTableLocator()->get('AffiliatesPeople')->mergeList($original->affiliates_people, [$new]);

		$this->assertCount(2, $affiliates);

		$this->assertArrayHasKey(0, $affiliates);
		$this->assertEquals($new_affiliate, $affiliates[0]->affiliate_id);
		$this->assertEquals('player', $affiliates[0]->position);

		$this->assertArrayHasKey(1, $affiliates);
		$this->assertEquals($original_affiliate, $affiliates[1]->affiliate_id);
		$this->assertEquals('manager', $affiliates[1]->position);
	}

}
