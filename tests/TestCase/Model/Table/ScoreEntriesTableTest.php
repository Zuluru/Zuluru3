<?php
namespace App\Test\TestCase\Model\Table;

use App\Middleware\ConfigurationLoader;
use App\Test\Factory\GameFactory;
use Cake\Event\Event as CakeEvent;
use Cake\ORM\TableRegistry;
use App\Model\Table\ScoreEntriesTable;

/**
 * App\Model\Table\ScoreEntriesTable Test Case
 */
class ScoreEntriesTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\ScoreEntriesTable
	 */
	public $ScoreEntriesTable;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('ScoreEntries') ? [] : ['className' => 'App\Model\Table\ScoreEntriesTable'];
		$this->ScoreEntriesTable = TableRegistry::get('ScoreEntries', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->ScoreEntriesTable);

		parent::tearDown();
	}

	/**
	 * Test beforeMarshal method
	 *
	 * @return void
	 */
	public function testBeforeMarshal() {
        $this->markTestSkipped(GameFactory::TODO_FACTORIES);
		ConfigurationLoader::loadConfiguration();

		$data = new \ArrayObject([
			'status' => 'normal',
			'team_id' => TEAM_ID_RED,
			'game_id' => GAME_ID_LADDER_MATCHED_SCORES,
			'score_for' => 15,
			'score_against' => 10,
		]);
		$this->ScoreEntriesTable->beforeMarshal(new CakeEvent('testing'), $data, new \ArrayObject());
		$this->assertEquals(15, $data['score_for']);
		$this->assertEquals(10, $data['score_against']);

		$data = new \ArrayObject([
			'status' => 'home_default',
			'team_id' => TEAM_ID_RED,
			'game_id' => GAME_ID_LADDER_MATCHED_SCORES,
		]);
		$this->ScoreEntriesTable->beforeMarshal(new CakeEvent('testing'), $data, new \ArrayObject());
		$this->assertEquals(0, $data['score_for']);
		$this->assertEquals(6, $data['score_against']);

		$data = new \ArrayObject([
			'status' => 'away_default',
			'team_id' => TEAM_ID_RED,
			'game_id' => GAME_ID_LADDER_MATCHED_SCORES,
		]);
		$this->ScoreEntriesTable->beforeMarshal(new CakeEvent('testing'), $data, new \ArrayObject());
		$this->assertEquals(6, $data['score_for']);
		$this->assertEquals(0, $data['score_against']);

		$data = new \ArrayObject([
			'status' => 'home_default',
			'team_id' => TEAM_ID_BLUE,
			'game_id' => GAME_ID_LADDER_MATCHED_SCORES,
		]);
		$this->ScoreEntriesTable->beforeMarshal(new CakeEvent('testing'), $data, new \ArrayObject());
		$this->assertEquals(6, $data['score_for']);
		$this->assertEquals(0, $data['score_against']);

		$data = new \ArrayObject([
			'status' => 'away_default',
			'team_id' => TEAM_ID_BLUE,
			'game_id' => GAME_ID_LADDER_MATCHED_SCORES,
		]);
		$this->ScoreEntriesTable->beforeMarshal(new CakeEvent('testing'), $data, new \ArrayObject());
		$this->assertEquals(0, $data['score_for']);
		$this->assertEquals(6, $data['score_against']);

		$data = new \ArrayObject([
			'status' => 'cancelled',
			'team_id' => TEAM_ID_BLUE,
			'game_id' => GAME_ID_LADDER_MATCHED_SCORES,
		]);
		$this->ScoreEntriesTable->beforeMarshal(new CakeEvent('testing'), $data, new \ArrayObject());
		$this->assertNull($data['score_for']);
		$this->assertNull($data['score_against']);
	}

	/**
	 * Test beforeSave method
	 *
	 * @return void
	 */
	public function testBeforeSave() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
