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
	 * @var ScoreEntriesTable
	 */
	public $ScoreEntriesTable;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.Settings',
	];

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('ScoreEntries') ? [] : ['className' => ScoreEntriesTable::class];
		$this->ScoreEntriesTable = TableRegistry::getTableLocator()->get('ScoreEntries', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->ScoreEntriesTable);

		parent::tearDown();
	}

	/**
	 * Test beforeMarshal method
	 */
	public function testBeforeMarshal(): void {
		ConfigurationLoader::loadConfiguration();

		$game = GameFactory::make()
			->with('HomeTeam')
			->with('AwayTeam')
			->persist();

		$data = new \ArrayObject([
			'status' => 'normal',
			'team_id' => $game->home_team_id,
			'game_id' => $game->id,
			'score_for' => 15,
			'score_against' => 10,
		]);
		$this->ScoreEntriesTable->beforeMarshal(new CakeEvent('testing'), $data, new \ArrayObject());
		$this->assertEquals(15, $data['score_for']);
		$this->assertEquals(10, $data['score_against']);

		$data = new \ArrayObject([
			'status' => 'home_default',
			'team_id' => $game->home_team_id,
			'game_id' => $game->id,
		]);
		$this->ScoreEntriesTable->beforeMarshal(new CakeEvent('testing'), $data, new \ArrayObject());
		$this->assertEquals(0, $data['score_for']);
		$this->assertEquals(6, $data['score_against']);

		$data = new \ArrayObject([
			'status' => 'away_default',
			'team_id' => $game->home_team_id,
			'game_id' => $game->id,
		]);
		$this->ScoreEntriesTable->beforeMarshal(new CakeEvent('testing'), $data, new \ArrayObject());
		$this->assertEquals(6, $data['score_for']);
		$this->assertEquals(0, $data['score_against']);

		$data = new \ArrayObject([
			'status' => 'home_default',
			'team_id' => $game->away_team_id,
			'game_id' => $game->id,
		]);
		$this->ScoreEntriesTable->beforeMarshal(new CakeEvent('testing'), $data, new \ArrayObject());
		$this->assertEquals(6, $data['score_for']);
		$this->assertEquals(0, $data['score_against']);

		$data = new \ArrayObject([
			'status' => 'away_default',
			'team_id' => $game->away_team_id,
			'game_id' => $game->id,
		]);
		$this->ScoreEntriesTable->beforeMarshal(new CakeEvent('testing'), $data, new \ArrayObject());
		$this->assertEquals(0, $data['score_for']);
		$this->assertEquals(6, $data['score_against']);

		$data = new \ArrayObject([
			'status' => 'cancelled',
			'team_id' => $game->away_team_id,
			'game_id' => $game->id,
		]);
		$this->ScoreEntriesTable->beforeMarshal(new CakeEvent('testing'), $data, new \ArrayObject());
		$this->assertNull($data['score_for']);
		$this->assertNull($data['score_against']);
	}

	/**
	 * Test beforeSave method
	 */
	public function testBeforeSave(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
