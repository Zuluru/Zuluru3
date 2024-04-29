<?php
namespace App\Test\TestCase\Controller;

use App\Model\Entity\Person;
use App\Test\Factory\AttendanceFactory;
use App\Test\Factory\GameFactory;
use App\Test\Factory\LeaguesStatTypeFactory;
use App\Test\Factory\NoteFactory;
use App\Test\Factory\PeoplePersonFactory;
use App\Test\Factory\PersonFactory;
use App\Test\Factory\ScoreDetailFactory;
use App\Test\Factory\ScoreEntryFactory;
use App\Test\Factory\SpiritEntryFactory;
use App\Test\Factory\StatFactory;
use App\Test\Factory\TeamsPersonFactory;
use App\Test\Scenario\DiverseUsersScenario;
use App\Test\Scenario\SingleGameScenario;
use App\TestSuite\ZuluruEmailTrait;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\Utility\Text;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\GamesController Test Case
 */
class GamesControllerTest extends ControllerTestCase {

	use ZuluruEmailTrait;
	use ScenarioAwareTrait;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.Days',
		'app.Groups',
		'app.RosterRoles',
		'app.Settings',
		'app.StatTypes',
	];

	public function tearDown(): void {
		// Cleanup any emails that were sent
		$this->cleanupEmailTrait();

		parent::tearDown();
	}

	/**
	 * Test view method
	 */
	public function testView(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'coordinator' => $volunteer,
			// Make sure that we're after the game date
			'game_date' => FrozenDate::now()->subDays(1),
			'home_score' => 17,
			'home_captain' => true,
			'home_player' => $player,
			'away_score' => 12,
			// Both teams need captains
			'away_captain' => true,
		]);

		$home = $game->home_team;
		$away = $game->away_team;

		/** @var \App\Model\Entity\Game $affiliate_game */
		$affiliate_game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[1],
			'home_captain' => true,
		]);

		// Admins are allowed to view games, with full edit permissions
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]], $admin->id);
		$this->assertResponseContains('currently rated');
		$this->assertResponseContains('chance to win');
		$this->assertResponseRegExp('#<dt class="col-sm-2 text-end">Game Status</dt>\s*<dd class="col-sm-10 mb-0">Normal</dd>#ms');
		$this->assertResponseNotContains('<dt class="col-sm-2 text-end">Round</dt>');
		$this->assertResponseContains('Captain Emails');
		$this->assertResponseContains('Ratings Table');
		$this->assertResponseNotContains('/games/attendance');
		$this->assertResponseContains('/games/note?game=' . $game->id);
		$this->assertResponseContains('/games/edit?game=' . $game->id);
		$this->assertResponseContains('/games/delete?game=' . $game->id);
		$this->assertResponseNotContains('/games/stats?game=' . $game->id);
		$this->assertResponseNotContains('<dt class="col-sm-2 text-end">Score Approved By</dt>');
		$this->assertResponseContains('<p>The score of this game has not yet been finalized.</p>');
		$this->assertResponseContains('Score as entered');
		$home_name = Text::truncate($home->name, 23);
		$away_name = Text::truncate($away->name, 23);
		$this->assertResponseRegExp('#<th>' . $home_name . ' \(home\)</th>\s*<th>' . $away_name . ' \(away\)</th>#ms');
		$this->assertResponseRegExp('#<td>Home Score</td>\s*<td>17</td>\s*<td>17</td>#ms');
		$this->assertResponseRegExp('#<td>Away Score</td>\s*<td>12</td>\s*<td>12</td>#ms');
		$this->assertResponseRegExp('#<td>Defaulted\?</td>\s*<td>no</td>\s*<td>no</td>#ms');

		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $affiliate_game->id]], $admin->id);
		$this->assertResponseContains('chance to win');
		$this->assertResponseContains('Captain Emails');
		$this->assertResponseContains('Ratings Table');
		$this->assertResponseContains('/games/edit?game=' . $affiliate_game->id);
		$this->assertResponseContains('/games/delete?game=' . $affiliate_game->id);
		$this->assertResponseNotContains('/games/stats?game=' . $affiliate_game->id);

		// Managers are allowed to view games; the game view won't include a team ID, so no attendance link
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]], $manager->id);
		$this->assertResponseContains('Captain Emails');
		$this->assertResponseContains('Ratings Table');
		$this->assertResponseNotContains('/games/attendance');
		$this->assertResponseContains('/games/note?game=' . $game->id);
		$this->assertResponseContains('/games/edit?game=' . $game->id);
		$this->assertResponseContains('/games/delete?game=' . $game->id);

		// But are not allowed to edit ones in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $affiliate_game->id]], $manager->id);
		$this->assertResponseNotContains('Captain Emails');
		$this->assertResponseContains('Ratings Table');
		$this->assertResponseNotContains('/games/edit?game=' . $affiliate_game->id);
		$this->assertResponseNotContains('/games/delete?game=' . $affiliate_game->id);

		// Coordinators are allowed to view games
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]], $volunteer->id);
		$this->assertResponseContains('Captain Emails');
		$this->assertResponseNotContains('/games/attendance');
		$this->assertResponseContains('/games/note?game=' . $game->id);
		$this->assertResponseContains('/games/edit?game=' . $game->id);
		$this->assertResponseContains('/games/delete?game=' . $game->id);

		// Captains are allowed to view games, perhaps with slightly more permission than players
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]], $home->people[0]->id);
		$this->assertResponseContains('Captain Emails');
		$this->assertResponseContains('/games/attendance?team=' . $home->id . '&amp;game=' . $game->id);
		$this->assertResponseContains('/games/note?game=' . $game->id);
		$this->assertResponseNotContains('/games/edit?game=' . $game->id);
		$this->assertResponseNotContains('/games/delete?game=' . $game->id);

		// Others are allowed to view games, but have no edit permissions
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]], $player->id);
		$this->assertResponseNotContains('Captain Emails');
		$this->assertResponseContains('/games/attendance?team=' . $home->id . '&amp;game=' . $game->id);
		$this->assertResponseContains('/games/note?game=' . $game->id);
		$this->assertResponseNotContains('/games/edit?game=' . $game->id);
		$this->assertResponseNotContains('/games/delete?game=' . $game->id);

		$this->assertGetAnonymousAccessOk(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]]);

		// TODO: All the different options for carbon flips, spirit, rating points, approved by.
		//$this->assertResponseRegExp('#<dt class="col-sm-2 text-end">Carbon Flip</dt>\s*<dd class="col-sm-10 mb-0">Red won</dd>#ms');
		//$this->assertResponseRegExp('#<dt class="col-sm-2 text-end">Rating Points</dt>\s*<dd class="col-sm-10 mb-0">.*gain 5 points\s*</dd>#ms');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test view method for unpublished games
	 */
	public function testViewUnpublished(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'coordinator' => $volunteer,
			'published' => false,
			'home_captain' => true,
			'home_player' => $player,
			'away_score' => 12,
			// Both teams need captains
			'away_captain' => true,
		]);

		$home = $game->home_team;

		/** @var \App\Model\Entity\Game $other_game */
		$other_game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'published' => false,
		]);

		// Admins, managers and coordinators are allowed to view unpublished games, with full edit permissions
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]], $admin->id);
		$this->assertResponseContains('/games/edit?game=' . $game->id);
		$this->assertResponseContains('/games/delete?game=' . $game->id);
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]], $manager->id);
		$this->assertResponseContains('/games/edit?game=' . $game->id);
		$this->assertResponseContains('/games/delete?game=' . $game->id);
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]], $volunteer->id);
		$this->assertResponseContains('/games/edit?game=' . $game->id);
		$this->assertResponseContains('/games/delete?game=' . $game->id);

		// Coordinators can't see unpublished ones in divisions they don't run
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $other_game->id]], $volunteer->id);

		// No viewing of unpublished games for anyone else
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]], $home->people[0]->id);
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]], $player->id);
		$this->assertGetAnonymousAccessRedirect(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]],
			'/', 'You do not have permission to access that page.');
	}

	/**
	 * Test view method for round-robin games
	 */
	public function testViewRoundRobin(): void {
		[$admin, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'division_details' => ['schedule_type' => 'roundrobin'],
			'home_score' => 17,
			'away_score' => 12,
		]);

		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]], $volunteer->id);
		$this->assertResponseContains('<dt class="col-sm-2 text-end">Round</dt>');

		// Round-robin games don't have ratings tables
		$this->assertResponseNotContains('Ratings Table');

		// We didn't add the volunteer as coordinator for this division, so they don't have other permissions either
		$this->assertResponseNotContains('Captain Emails');
		$this->assertResponseNotContains('/games/edit?game=' . $game->id);
		$this->assertResponseNotContains('/games/delete?game=' . $game->id);

		// Confirm different output for finalized games
		/** @var \App\Model\Entity\Game $finalized_game */
		$finalized_game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'division_details' => ['schedule_type' => 'roundrobin'],
			'home_score' => 15,
			'away_score' => 14,
			'approved_by_id' => APPROVAL_AUTOMATIC,
		]);

		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $finalized_game->id]], $volunteer->id);
		$this->assertResponseNotContains('chance to win');
		$this->assertResponseNotContains('Ratings Table');
		$home_name = Text::truncate($finalized_game->home_team->name, 28);
		$away_name = Text::truncate($finalized_game->away_team->name, 28);
		$this->assertResponseRegExp('#<dt class="col-sm-2 text-end">' . $home_name . '</dt>\s*<dd class="col-sm-10 mb-0">15</dd>#ms');
		$this->assertResponseRegExp('#<dt class="col-sm-2 text-end">' . $away_name . '</dt>\s*<dd class="col-sm-10 mb-0">14</dd>#ms');
		$this->assertResponseRegExp('#<dt class="col-sm-2 text-end">Score Approved By</dt>\s*<dd class="col-sm-10 mb-0">automatic approval</dd>#ms');
	}

	/**
	 * Test view method for tournament / playoff games
	 */
	public function testViewTournament(): void {
		[$admin, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'division_details' => ['schedule_type' => 'tournament'],
		]);

		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]], $volunteer->id);
		$this->assertResponseRegExp('#<dt class="col-sm-2 text-end">Home Team</dt>\s*<dd class="col-sm-10 mb-0">A1 \[1st seed\]</dd>#ms');
		$this->assertResponseRegExp('#<dt class="col-sm-2 text-end">Away Team</dt>\s*<dd class="col-sm-10 mb-0">A4 \[4th seed\]</dd>#ms');
		$this->assertResponseNotContains('currently rated');
		$this->assertResponseNotContains('chance to win');
		$this->assertResponseNotContains('Captain Emails');
		// Uninitialized playoff games don't have ratings tables
		$this->assertResponseNotContains('Ratings Table');
		$this->assertResponseNotContains('/games/edit?game=' . $game->id);
		$this->assertResponseNotContains('/games/delete?game=' . $game->id);
	}

	/**
	 * Test tooltip method
	 */
	public function testTooltip(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
		]);
		$facility = $game->game_slot->field->facility;

		// Anyone is allowed to view game tooltips
		$this->assertGetAjaxAsAccessOk(['controller' => 'Games', 'action' => 'tooltip', '?' => ['game' => $game->id]], $admin->id);
		$this->assertResponseContains('/facilities\\/view?facility=' . $facility->id);
		$this->assertResponseContains('/teams\\/view?team=' . $game->home_team_id);
		$this->assertResponseContains('/teams\\/view?team=' . $game->away_team_id);

		$this->assertGetAjaxAsAccessRedirect(['controller' => 'Games', 'action' => 'tooltip', '?' => ['game' => 0]],
			$admin->id, '/',
			'Invalid game.');

		// Anyone is allowed to view game tooltips
		$this->assertGetAjaxAsAccessOk(['controller' => 'Games', 'action' => 'tooltip', '?' => ['game' => $game->id]],
			$manager->id);
		$this->assertResponseContains('/facilities\\/view?facility=' . $facility->id);
		$this->assertResponseContains('/teams\\/view?team=' . $game->home_team_id);
		$this->assertResponseContains('/teams\\/view?team=' . $game->away_team_id);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Games', 'action' => 'tooltip', '?' => ['game' => $game->id]],
			$volunteer->id);
		$this->assertResponseContains('/facilities\\/view?facility=' . $facility->id);
		$this->assertResponseContains('/teams\\/view?team=' . $game->home_team_id);
		$this->assertResponseContains('/teams\\/view?team=' . $game->away_team_id);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Games', 'action' => 'tooltip', '?' => ['game' => $game->id]],
			$player->id);
		$this->assertResponseContains('/facilities\\/view?facility=' . $facility->id);
		$this->assertResponseContains('/teams\\/view?team=' . $game->home_team_id);
		$this->assertResponseContains('/teams\\/view?team=' . $game->away_team_id);

		$this->assertGetAjaxAnonymousAccessOk(['controller' => 'Games', 'action' => 'tooltip', '?' => ['game' => $game->id]]);
		$this->assertResponseContains('/facilities\/view?facility=' . $facility->id);
		$this->assertResponseContains('/teams\\/view?team=' . $game->home_team_id);
		$this->assertResponseContains('/teams\\/view?team=' . $game->away_team_id);
	}

	/**
	 * Test ratings_table method
	 */
	public function testRatingsTable(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
		]);

		// Anyone logged in is allowed to view ratings tables
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'ratings_table', '?' => ['game' => $game->id]], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'ratings_table', '?' => ['game' => $game->id]], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'ratings_table', '?' => ['game' => $game->id]], $volunteer->id);
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'ratings_table', '?' => ['game' => $game->id]], $player->id);

		// Others are not allowed to ratings table
		$this->assertGetAnonymousAccessDenied(['controller' => 'Games', 'action' => 'ratings_table', '?' => ['game' => $game->id]]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test ical method
	 */
	public function testIcal(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			// Make sure that the game date is in the future
			'game_date' => FrozenDate::now()->addDays(1),
		]);

		// Can get the ical feed for any game in the future
		$this->assertGetAnonymousAccessOk(['controller' => 'Games', 'action' => 'ical', $game->id, $game->home_team_id]);

		// Or up to two weeks after the division has been closed
		FrozenDate::setTestNow($game->division->close->addDays(14));
		$this->assertGetAnonymousAccessOk(['controller' => 'Games', 'action' => 'ical', $game->id, $game->home_team_id]);

		// But not after the division has been closed for more than two weeks
		FrozenDate::setTestNow($game->division->close->addDays(15));
		$this->get(['controller' => 'Games', 'action' => 'ical', $game->id, $game->home_team_id]);
		$this->assertResponseCode(410);
	}

	/**
	 * Test edit method as an admin
	 */
	public function testEditAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
		]);

		// Admins are allowed to edit
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'edit', '?' => ['game' => $game->id]], $admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test edit method as a manager
	 */
	public function testEditAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
		]);

		// Managers are allowed to edit games
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'edit', '?' => ['game' => $game->id]], $manager->id);

		/** @var \App\Model\Entity\Game $affiliate_game */
		$affiliate_game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[1],
		]);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'edit', '?' => ['game' => $affiliate_game->id]], $manager->id);
	}

	/**
	 * Test edit method as a coordinator
	 */
	public function testEditAsCoordinator(): void {
		[$admin, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'coordinator' => $volunteer,
		]);

		// Coordinators are allowed to edit games
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'edit', '?' => ['game' => $game->id]], $volunteer->id);

		/** @var \App\Model\Entity\Game $other_game */
		$other_game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
		]);

		// But not ones in divisions they don't run
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'edit', '?' => ['game' => $other_game->id]], $volunteer->id);
	}

	/**
	 * Test edit method as others
	 */
	public function testEditAsOthers(): void {
		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'home_captain' => true,
			'home_player' => $player,
		]);
		$captain = $game->home_team->people[0];

		// Others are not allowed to edit games
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'edit', '?' => ['game' => $game->id]], $captain->id);
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'edit', '?' => ['game' => $game->id]], [$captain->id, $admin->id]);
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'edit', '?' => ['game' => $game->id]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Games', 'action' => 'edit', '?' => ['game' => $game->id]]);
	}

	/**
	 * Test edit_boxscore method as an admin
	 */
	public function testEditBoxscoreAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
		]);

		// Admins are allowed to edit boxscore
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'edit_boxscore', '?' => ['game' => $game->id]], $admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test edit_boxscore method as a manager
	 */
	public function testEditBoxscoreAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
		]);

		// Managers are allowed to edit boxscore
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'edit_boxscore', '?' => ['game' => $game->id]], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test edit_boxscore method as a coordinator
	 */
	public function testEditBoxscoreAsCoordinator(): void {
		[$admin, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'coordinator' => $volunteer,
		]);

		// Coordinators are allowed to edit boxscore
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'edit_boxscore', '?' => ['game' => $game->id]], $volunteer->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test edit_boxscore method as others
	 */
	public function testEditBoxscoreAsOthers(): void {
		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'home_captain' => true,
		]);

		// Others are not allowed to edit boxscores
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'edit_boxscore', '?' => ['game' => $game->id]], $game->home_team->people[0]->id);
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'edit_boxscore', '?' => ['game' => $game->id]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Games', 'action' => 'edit_boxscore', '?' => ['game' => $game->id]]);
	}

	/**
	 * Test delete_score method as an admin
	 */
	public function testDeleteScoreAsAdmin(): void {
		$this->enableSecurityToken();

		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
		]);

		/** @var \App\Model\Entity\ScoreDetail $detail */
		$detail = ScoreDetailFactory::make([
			'game_id' => $game->id,
			'team_id' => $game->home_team_id,
			'created_team_id' => $game->away_team_id,
		])->persist();

		// Admins are allowed to delete scores
		$this->assertPostAjaxAsAccessOk(['controller' => 'Games', 'action' => 'delete_score', '?' => ['detail' => $detail->id, 'game' => $game->id]], $admin->id);

		$this->expectException(RecordNotFoundException::class);
		ScoreDetailFactory::get($detail->id);
	}

	/**
	 * Test delete_score method as a manager
	 */
	public function testDeleteScoreAsManager(): void {
		$this->enableSecurityToken();

		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
		]);

		/** @var \App\Model\Entity\ScoreDetail $detail */
		$detail = ScoreDetailFactory::make([
			'game_id' => $game->id,
			'team_id' => $game->home_team_id,
			'created_team_id' => $game->away_team_id,
		])->persist();

		// Managers are allowed to delete scores
		$this->assertPostAjaxAsAccessOk(['controller' => 'Games', 'action' => 'delete_score', '?' => ['detail' => $detail->id, 'game' => $game->id]], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test delete_score method as a coordinator
	 */
	public function testDeleteScoreAsCoordinator(): void {
		$this->enableSecurityToken();

		[$admin, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'coordinator' => $volunteer,
		]);

		/** @var \App\Model\Entity\ScoreDetail $detail */
		$detail = ScoreDetailFactory::make([
			'game_id' => $game->id,
			'team_id' => $game->home_team_id,
			'created_team_id' => $game->away_team_id,
		])->persist();

		// Coordinators are allowed to delete scores
		$this->assertPostAjaxAsAccessOk(['controller' => 'Games', 'action' => 'delete_score', '?' => ['detail' => $detail->id, 'game' => $game->id]], $volunteer->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test delete_score method as others
	 */
	public function testDeleteScoreAsOthers(): void {
		$this->enableSecurityToken();

		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'home_captain' => true,
		]);

		/** @var \App\Model\Entity\ScoreDetail $detail */
		$detail = ScoreDetailFactory::make([
			'game_id' => $game->id,
			'team_id' => $game->home_team_id,
			'created_team_id' => $game->away_team_id,
		])->persist();

		// Others are not allowed to delete scores
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Games', 'action' => 'delete_score', '?' => ['detail' => $detail->id, 'game' => $game->id]],
			$game->home_team->people[0]->id);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Games', 'action' => 'delete_score', '?' => ['detail' => $detail->id, 'game' => $game->id]],
			$player->id);
		$this->assertPostAjaxAnonymousAccessDenied(['controller' => 'Games', 'action' => 'delete_score', '?' => ['detail' => $detail->id, 'game' => $game->id]]);
	}

	/**
	 * Test add_score method as an admin
	 */
	public function testAddScoreAsAdmin(): void {
		$this->enableSecurityToken();

		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$affiliates = $admin->affiliates;

		// Game date
		$date = (new FrozenDate('last Monday of May'))->addWeeks(2);

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'game_date' => $date,
		]);

		// Admins are allowed to add scores
		$this->assertPostAjaxAsAccessOk(['controller' => 'Games', 'action' => 'add_score', '?' => ['game' => $game->id]],
			$admin->id, ['add_detail' => [
				'team_id' => $game->home_team_id,
				'created' => ['year' => $date->year, 'month' => $date->month, 'day' => $date->day, 'hour' => 19, 'minute' => 10],
				'play' => 'Timeout',
			]]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add_score method as a manager
	 */
	public function testAddScoreAsManager(): void {
		$this->enableSecurityToken();

		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		// Game date
		$date = (new FrozenDate('last Monday of May'))->addWeeks(2);

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'game_date' => $date,
		]);

		// Managers are allowed to add scores
		$this->assertPostAjaxAsAccessOk(['controller' => 'Games', 'action' => 'add_score', '?' => ['game' => $game->id]],
			$manager->id, ['add_detail' => [
				'team_id' => $game->home_team_id,
				'created' => ['year' => $date->year, 'month' => $date->month, 'day' => $date->day, 'hour' => 19, 'minute' => 10],
				'play' => 'Timeout',
			]]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add_score method as a coordinator
	 */
	public function testAddScoreAsCoordinator(): void {
		$this->enableSecurityToken();

		[$admin, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer']);
		$affiliates = $admin->affiliates;

		// Game date
		$date = (new FrozenDate('last Monday of May'))->addWeeks(2);

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'coordinator' => $volunteer,
		]);

		// Coordinators are allowed to add scores
		$this->assertPostAjaxAsAccessOk(['controller' => 'Games', 'action' => 'add_score', '?' => ['game' => $game->id]],
			$volunteer->id, ['add_detail' => [
				'team_id' => $game->home_team_id,
				'created' => ['year' => $date->year, 'month' => $date->month, 'day' => $date->day, 'hour' => 19, 'minute' => 10],
				'play' => 'Timeout',
			]]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add_score method as others
	 */
	public function testAddScoreAsOthers(): void {
		$this->enableSecurityToken();

		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'home_captain' => true,
		]);

		// Others are not allowed to add scores
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Games', 'action' => 'add_score', '?' => ['game' => $game->id]],
			$game->home_team->people[0]->id);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Games', 'action' => 'add_score', '?' => ['game' => $game->id]],
			$player->id);
		$this->assertPostAjaxAnonymousAccessDenied(['controller' => 'Games', 'action' => 'add_score', '?' => ['game' => $game->id]]);
	}

	/**
	 * Test note method as an admin
	 */
	public function testNoteAsAdmin(): void {
		$this->enableSecurityToken();

		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
		]);

		NoteFactory::make([
			'game_id' => $game->id,
			'visibility' => VISIBILITY_ADMIN,
			'note' => 'Admin note from admin about game.',
			'created_person_id' => $admin->id,
		])->persist();

		// Admins are allowed to add notes
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'note', '?' => ['game' => $game->id]], $admin->id);

		// Empty notes don't get added
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'note', '?' => ['game' => $game->id]],
			$admin->id, [
				'game_id' => $game->id,
				'visibility' => VISIBILITY_PRIVATE,
				'note' => '',
			], ['action' => 'view', '?' => ['game' => $game->id]], 'You entered no text, so no note was added.');

		// Add a private note
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'note', '?' => ['game' => $game->id]],
			$admin->id, [
				'game_id' => $game->id,
				'visibility' => VISIBILITY_PRIVATE,
				'note' => 'This is a private note.',
			], ['action' => 'view', '?' => ['game' => $game->id]], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertNoMailSent();

		// Add a note for all admins to see
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'note', '?' => ['game' => $game->id]],
			$admin->id, [
				'game_id' => $game->id,
				'visibility' => VISIBILITY_ADMIN,
				'note' => 'This is an admin note.',
			], ['action' => 'view', '?' => ['game' => $game->id]], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertNoMailSent();

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]], $admin->id);
		$this->assertResponseContains('This is a private note.');
		$this->assertResponseContains('This is an admin note.');
		// And the old one is still there
		$this->assertResponseContains('Admin note from admin about game.');

		// Check the manager can also see the admin one
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]], $manager->id);
		$this->assertResponseNotContains('This is a private note.');
		$this->assertResponseContains('This is an admin note.');
	}

	/**
	 * Test note method as a manager
	 */
	public function testNoteAsManager(): void {
		$this->enableSecurityToken();

		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
		]);

		// Managers are allowed to add notes
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'note', '?' => ['game' => $game->id]], $manager->id);

		// Add a private note
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'note', '?' => ['game' => $game->id]],
			$manager->id, [
				'game_id' => $game->id,
				'visibility' => VISIBILITY_PRIVATE,
				'note' => 'This is a private note.',
			], ['action' => 'view', '?' => ['game' => $game->id]], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertNoMailSent();

		// Add a note for all admins to see
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'note', '?' => ['game' => $game->id]],
			$manager->id, [
				'game_id' => $game->id,
				'visibility' => VISIBILITY_ADMIN,
				'note' => 'This is an admin note.',
			], ['action' => 'view', '?' => ['game' => $game->id]], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertNoMailSent();

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]], $manager->id);
		$this->assertResponseContains('This is a private note.');
		$this->assertResponseContains('This is an admin note.');

		// Check the admin can also see the admin one
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]], $admin->id);
		$this->assertResponseNotContains('This is a private note.');
		$this->assertResponseContains('This is an admin note.');
	}

	/**
	 * Test note method as a coordinator
	 */
	public function testNoteAsCoordinator(): void {
		$this->enableSecurityToken();

		[$admin, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'coordinator' => $volunteer,
		]);

		// Coordinators are allowed to add notes
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'note', '?' => ['game' => $game->id]], $volunteer->id);

		// Add a private note
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'note', '?' => ['game' => $game->id]],
			$volunteer->id, [
				'game_id' => $game->id,
				'visibility' => VISIBILITY_PRIVATE,
				'note' => 'This is a private note.',
			], ['action' => 'view', '?' => ['game' => $game->id]], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertNoMailSent();

		// Add a note for all coordinators to see
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'note', '?' => ['game' => $game->id]],
			$volunteer->id, [
				'game_id' => $game->id,
				'visibility' => VISIBILITY_COORDINATOR,
				'note' => 'This is a coordinator note.',
			], ['action' => 'view', '?' => ['game' => $game->id]], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertNoMailSent();

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]], $volunteer->id);
		$this->assertResponseContains('This is a private note.');
		$this->assertResponseContains('This is a coordinator note.');
	}

	/**
	 * Test note method as a captain
	 */
	public function testNoteAsCaptain(): void {
		$this->enableSecurityToken();

		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'home_captain' => true,
			// Note that the player is intentionally NOT added to the team, so the only way that they get the note emailed to them is via their child
		]);
		$captain = $game->home_team->people[0];

		// Add a second captain for this test
		/** @var Person $captain2 */
		$captain2 = PersonFactory::make()->player()
			->with('TeamsPeople', TeamsPersonFactory::make(['team_id' => $game->home_team_id, 'role' => 'captain']))
			->persist();

		// Also add a child to the player and the roster
		/** @var Person $child */
		$child = PersonFactory::make()
			->withGroup(GROUP_PLAYER)
			->with('TeamsPeople', TeamsPersonFactory::make(['team_id' => $game->home_team_id, 'role' => 'player']))
			->persist();
		PeoplePersonFactory::make(['person_id' => $player->id, 'relative_id' => $child->id])->persist();

		// Captains are allowed to add notes
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'note', '?' => ['game' => $game->id]], $captain->id);

		// Add a note for all captains to see
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'note', '?' => ['game' => $game->id]],
			$captain->id, [
				'game_id' => $game->id,
				'visibility' => VISIBILITY_CAPTAINS,
				'note' => 'This is a captain note.',
			], ['action' => 'view', '?' => ['game' => $game->id]], 'The note has been saved.');

		// Confirm the notification email
		$this->assertMailCount(1);
		$this->assertMailSentFrom('admin@zuluru.org');
		$this->assertMailSentWithArray([$captain->user->email => $captain->full_name], 'ReplyTo');
		$this->assertMailSentTo($captain2->user->email);
		$this->assertMailNotSentTo($player->user->email);
		$this->assertMailSentWithArray([], 'CC');
		$this->assertMailSentWith("{$game->home_team->name} game note", 'Subject');
		$this->assertMailContains("{$captain->full_name} has added a note");
		$this->assertMailContains('This is a captain note.');
		$this->cleanupEmailTrait();;

		// Make sure it was added successfully
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]], $captain->id);
		$this->assertResponseContains('This is a captain note.');
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]], $captain2->id);
		$this->assertResponseContains('This is a captain note.');

		// But players can't see it
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]], [$player->id, $child->id]);
		$this->assertResponseNotContains('This is a captain note.');

		// Add a note for the team to see
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'note', '?' => ['game' => $game->id]],
			$captain->id, [
				'game_id' => $game->id,
				'visibility' => VISIBILITY_TEAM,
				'note' => 'This is a team note.',
			], ['action' => 'view', '?' => ['game' => $game->id]], 'The note has been saved.');

		// Confirm the notification email
		$this->assertMailCount(1);
		$this->assertMailSentFrom('admin@zuluru.org');
		$this->assertMailSentWithArray([$captain->user->email => $captain->full_name], 'ReplyTo');
		$this->assertMailSentTo($captain2->user->email);
		// Children don't have their own email addresses; test that the email goes to the parent
		$this->assertMailSentTo($player->user->email);
		$this->assertMailSentWithArray([], 'CC');
		$this->assertMailSentWith("{$game->home_team->name} game note", 'Subject');
		$this->assertMailContains("{$captain->full_name} has added a note");
		$this->assertMailContains('This is a team note.');

		// Make sure it was added successfully
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]], $captain->id);
		$this->assertResponseContains('This is a team note.');

		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]], [$player->id, $child->id]);
		$this->assertResponseContains('This is a team note.');
	}

	/**
	 * Test note method as a player
	 */
	public function testNoteAsPlayer(): void {
		$this->enableSecurityToken();

		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'home_captain' => true,
			'home_player' => $player,
		]);
		$captain = $game->home_team->people[0];

		/** @var \App\Model\Entity\Game $other_game */
		$other_game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
		]);

		// Players are only allowed to add notes on games they are playing in
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'note', '?' => ['game' => $game->id]], $player->id);
		$this->assertGetAsAccessRedirect(['controller' => 'Games', 'action' => 'note', '?' => ['game' => $other_game->id]],
			$player->id, ['controller' => 'Games', 'action' => 'view', '?' => ['game' => $other_game->id]],
			'You are not on the roster of a team playing in this game.');

		// Add a note for all captains to see
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'note', '?' => ['game' => $game->id]],
			$player->id, [
				'game_id' => $game->id,
				'visibility' => VISIBILITY_CAPTAINS,
				'note' => 'This is a captain note.',
			], ['action' => 'view', '?' => ['game' => $game->id]], 'The note has been saved.');

		// Confirm the notification email
		$this->assertMailCount(1);
		$this->assertMailSentFrom('admin@zuluru.org');
		$this->assertMailSentWithArray([$player->user->email => $player->full_name], 'ReplyTo');
		$this->assertMailSentTo($captain->user->email);
		$this->assertMailSentWithArray([], 'CC');
		$this->assertMailSentWith("{$game->home_team->name} game note", 'Subject');
		$this->assertMailContains("{$player->full_name} has added a note");
		$this->assertMailContains('This is a captain note.');
		$this->cleanupEmailTrait();

		// Make sure it was added successfully
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]], $player->id);
		$this->assertResponseContains('This is a captain note.');

		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]], $game->home_team->people[0]->id);
		$this->assertResponseContains('This is a captain note.');

		// Add a note for the team to see
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'note', '?' => ['game' => $game->id]],
			$player->id, [
				'game_id' => $game->id,
				'visibility' => VISIBILITY_TEAM,
				'note' => 'This is a team note.',
			], ['action' => 'view', '?' => ['game' => $game->id]], 'The note has been saved.');

		// Confirm the notification email
		$this->assertMailCount(1);
		$this->assertMailSentFrom('admin@zuluru.org');
		$this->assertMailSentWithArray([$player->user->email => $player->full_name], 'ReplyTo');
		$this->assertMailSentTo($captain->user->email);
		$this->assertMailSentWithArray([], 'CC');
		$this->assertMailSentWith("{$game->home_team->name} game note", 'Subject');
		$this->assertMailContains("{$player->full_name} has added a note");
		$this->assertMailContains('This is a team note.');

		// Make sure it was added successfully
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]], $player->id);
		$this->assertResponseContains('This is a team note.');

		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]], $game->home_team->people[0]->id);
		$this->assertResponseContains('This is a team note.');
	}

	/**
	 * Test note method as others
	 */
	public function testNoteAsOthers(): void {
		$this->enableSecurityToken();

		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
		]);

		// Others are not allowed to add notes
		$this->assertGetAsAccessRedirect(['controller' => 'Games', 'action' => 'note', '?' => ['game' => $game->id]],
			$player->id, ['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]],
			'You are not on the roster of a team playing in this game.');
		$this->assertGetAnonymousAccessDenied(['controller' => 'Games', 'action' => 'note', '?' => ['game' => $game->id]]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test delete_note method as an admin
	 */
	public function testDeleteAdminNoteAsAdmin(): void {
		$this->enableSecurityToken();

		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
		]);

		// Admins are allowed to delete admin notes
		$note = NoteFactory::make([
			'game_id' => $game->id,
			'visibility' => VISIBILITY_ADMIN,
			'note' => 'Admin note from admin about game.',
			'created_person_id' => $admin->id,
		])->persist();

		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'delete_note', '?' => ['note' => $note->id]],
			$admin->id, [], ['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]],
			'The note has been deleted.');
		$this->expectException(RecordNotFoundException::class);
		NoteFactory::get($note->id);
	}

	/**
	 * Test delete_note method as an admin
	 */
	public function testDeleteCoordinatorNoteAsAdmin(): void {
		$this->enableSecurityToken();

		[$admin, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
		]);

		// Admins are allowed to delete coordinator notes
		$note = NoteFactory::make([
			'game_id' => $game->id,
			'visibility' => VISIBILITY_COORDINATOR,
			'note' => 'Coordinator note from admin about game.',
			'created_person_id' => $volunteer->id,
		])->persist();

		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'delete_note', '?' => ['note' => $note->id]],
			$admin->id, [], ['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]],
			'The note has been deleted.');
		$this->expectException(RecordNotFoundException::class);
		NoteFactory::get($note->id);
	}

	/**
	 * Test delete_note method as an admin
	 */
	public function testDeleteOtherNoteAsAdmin(): void {
		$this->enableSecurityToken();

		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'home_captain' => true,
		]);

		// Admins are not allowed to delete other notes
		$captain_note = NoteFactory::make([
			'game_id' => $game->id,
			'visibility' => VISIBILITY_TEAM,
			'note' => 'Team note from captain about game.',
			'created_person_id' => $game->home_team->people[0]->id,
		])->persist();
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', '?' => ['note' => $captain_note->id]],
			$admin->id);

		$player_note = NoteFactory::make([
			'game_id' => $game->id,
			'visibility' => VISIBILITY_TEAM,
			'note' => 'Team note from player about game.',
			'created_person_id' => $player->id,
		])->persist();
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', '?' => ['note' => $player_note->id]],
			$admin->id);

		$other_note = NoteFactory::make([
			'game_id' => $game->id,
			'visibility' => VISIBILITY_PRIVATE,
			'note' => 'Private note from volunteer about game.',
			'created_person_id' => $volunteer->id,
		])->persist();
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', '?' => ['note' => $other_note->id]],
			$admin->id);
	}

	/**
	 * Test delete_note method as a manager
	 */
	public function testDeleteAdminNoteAsManager(): void {
		$this->enableSecurityToken();

		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
		]);

		// Managers are allowed to delete admin notes
		$note = NoteFactory::make([
			'game_id' => $game->id,
			'visibility' => VISIBILITY_ADMIN,
			'note' => 'Admin note from admin about game.',
			'created_person_id' => $admin->id,
		])->persist();

		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'delete_note', '?' => ['note' => $note->id]],
			$manager->id, [], ['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]],
			'The note has been deleted.');
		$this->expectException(RecordNotFoundException::class);
		NoteFactory::get($note->id);
	}

	/**
	 * Test delete_note method as a manager
	 */
	public function testDeleteCoordinatorNoteAsManager(): void {
		$this->enableSecurityToken();

		[$admin, $manager, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager', 'volunteer']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
		]);

		// Managers are allowed to delete coordinator notes
		$note = NoteFactory::make([
			'game_id' => $game->id,
			'visibility' => VISIBILITY_COORDINATOR,
			'note' => 'Coordinator note from admin about game.',
			'created_person_id' => $volunteer->id,
		])->persist();

		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'delete_note', '?' => ['note' => $note->id]],
			$manager->id, [], ['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]],
			'The note has been deleted.');
		$this->expectException(RecordNotFoundException::class);
		NoteFactory::get($note->id);
	}

	/**
	 * Test delete_note method as a manager
	 */
	public function testDeleteOtherNoteAsManager(): void {
		$this->enableSecurityToken();

		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'home_captain' => true,
		]);

		// Managers are not allowed to delete other notes
		$captain_note = NoteFactory::make([
			'game_id' => $game->id,
			'visibility' => VISIBILITY_TEAM,
			'note' => 'Team note from captain about game.',
			'created_person_id' => $game->home_team->people[0]->id,
		])->persist();
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', '?' => ['note' => $captain_note->id]],
			$manager->id);

		$player_note = NoteFactory::make([
			'game_id' => $game->id,
			'visibility' => VISIBILITY_TEAM,
			'note' => 'Team note from player about game.',
			'created_person_id' => $player->id,
		])->persist();
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', '?' => ['note' => $player_note->id]],
			$manager->id);

		$other_note = NoteFactory::make([
			'game_id' => $game->id,
			'visibility' => VISIBILITY_PRIVATE,
			'note' => 'Private note from volunteer about game.',
			'created_person_id' => $volunteer->id,
		])->persist();
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', '?' => ['note' => $other_note->id]],
			$manager->id);
	}

	/**
	 * Test delete_note method as a coordinator
	 */
	public function testDeleteCoordinatorNoteAsCoordinator(): void {
		$this->enableSecurityToken();

		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'home_captain' => true,
		]);

		// Coordinators are not allowed to delete other notes
		$admin_note = NoteFactory::make([
			'game_id' => $game->id,
			'visibility' => VISIBILITY_ADMIN,
			'note' => 'Admin note from admin about game.',
			'created_person_id' => $admin->id,
		])->persist();
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', '?' => ['note' => $admin_note->id]],
			$volunteer->id);

		$captain_note = NoteFactory::make([
			'game_id' => $game->id,
			'visibility' => VISIBILITY_TEAM,
			'note' => 'Team note from captain about game.',
			'created_person_id' => $game->home_team->people[0]->id,
		])->persist();
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', '?' => ['note' => $captain_note->id]],
			$volunteer->id);

		$player_note = NoteFactory::make([
			'game_id' => $game->id,
			'visibility' => VISIBILITY_PRIVATE,
			'note' => 'Private note from player about game.',
			'created_person_id' => $player->id,
		])->persist();
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', '?' => ['note' => $player_note->id]],
			$volunteer->id);

		// But can delete coordinator notes
		$coordinator_note = NoteFactory::make([
			'game_id' => $game->id,
			'visibility' => VISIBILITY_COORDINATOR,
			'note' => 'Coordinator note from admin about game.',
			'created_person_id' => $volunteer->id,
		])->persist();

		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'delete_note', '?' => ['note' => $coordinator_note->id]],
			$volunteer->id, [], ['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]],
			'The note has been deleted.');
		$this->expectException(RecordNotFoundException::class);
		NoteFactory::get($coordinator_note->id);
	}

	/**
	 * Test delete_note method as a captain
	 */
	public function testDeleteNoteAsCaptain(): void {
		$this->enableSecurityToken();

		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'home_captain' => true,
		]);
		$captain = $game->home_team->people[0];

		// Captains are not allowed to delete other notes
		$admin_note = NoteFactory::make([
			'game_id' => $game->id,
			'visibility' => VISIBILITY_ADMIN,
			'note' => 'Admin note from admin about game.',
			'created_person_id' => $admin->id,
		])->persist();
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', '?' => ['note' => $admin_note->id]],
			$captain->id);

		$coordinator_note = NoteFactory::make([
			'game_id' => $game->id,
			'visibility' => VISIBILITY_COORDINATOR,
			'note' => 'Coordinator note from admin about game.',
			'created_person_id' => $volunteer->id,
		])->persist();
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', '?' => ['note' => $coordinator_note->id]],
			$captain->id);

		$player_note = NoteFactory::make([
			'game_id' => $game->id,
			'visibility' => VISIBILITY_PRIVATE,
			'note' => 'Private note from player about game.',
			'created_person_id' => $player->id,
		])->persist();
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', '?' => ['note' => $player_note->id]],
			$captain->id);

		// But can delete notes they created
		$captain_note = NoteFactory::make([
			'game_id' => $game->id,
			'visibility' => VISIBILITY_TEAM,
			'note' => 'Team note from captain about game.',
			'created_person_id' => $captain->id,
		])->persist();

		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'delete_note', '?' => ['note' => $captain_note->id]],
			$captain->id, [], ['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]],
			'The note has been deleted.');
		$this->expectException(RecordNotFoundException::class);
		NoteFactory::get($captain_note->id);
	}

	/**
	 * Test delete_note method as a player
	 */
	public function testDeleteNoteAsPlayer(): void {
		$this->enableSecurityToken();

		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'home_captain' => true,
		]);

		// Players are not allowed to delete other notes
		$admin_note = NoteFactory::make([
			'game_id' => $game->id,
			'visibility' => VISIBILITY_ADMIN,
			'note' => 'Admin note from admin about game.',
			'created_person_id' => $admin->id,
		])->persist();
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', '?' => ['note' => $admin_note->id]],
			$player->id);

		$coordinator_note = NoteFactory::make([
			'game_id' => $game->id,
			'visibility' => VISIBILITY_COORDINATOR,
			'note' => 'Coordinator note from admin about game.',
			'created_person_id' => $volunteer->id,
		])->persist();
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', '?' => ['note' => $coordinator_note->id]],
			$player->id);

		$captain_note = NoteFactory::make([
			'game_id' => $game->id,
			'visibility' => VISIBILITY_TEAM,
			'note' => 'Team note from captain about game.',
			'created_person_id' => $game->home_team->people[0]->id,
		])->persist();
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', '?' => ['note' => $captain_note->id]],
			$player->id);

		// But can delete notes they created
		$player_note = NoteFactory::make([
			'game_id' => $game->id,
			'visibility' => VISIBILITY_PRIVATE,
			'note' => 'Private note from player about game.',
			'created_person_id' => $player->id,
		])->persist();

		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'delete_note', '?' => ['note' => $player_note->id]],
			$player->id, [], ['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]],
			'The note has been deleted.');
		$this->expectException(RecordNotFoundException::class);
		NoteFactory::get($player_note->id);
	}

	/**
	 * Test delete method as an admin
	 */
	public function testDeleteAsAdmin(): void {
		$this->enableSecurityToken();

		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$affiliates = $admin->affiliates;

		// Admins are allowed to delete games
		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
		]);

		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'delete', '?' => ['game' => $game->id]],
			$admin->id, [], ['controller' => 'Divisions', 'action' => 'schedule', '?' => ['division' => $game->division_id]],
			'The game has been deleted.');

		// But not ones with dependencies
		/** @var \App\Model\Entity\Game $other_game */
		$other_game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'home_score' => 15,
			'away_score' => 10,
		]);

		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'delete', '?' => ['game' => $other_game->id]],
			$admin->id, [], ['controller' => 'Games', 'action' => 'view', '?' => ['game' => $other_game->id]],
			['A score has already been submitted for this game.', 'If you are absolutely sure that you want to delete it anyway, {0}. <b>This cannot be undone!</b>']);
		$this->assertSession(['action' => 'delete', '?' => ['game' => (string)$other_game->id, 'force' => true]], 'Flash.flash.0.params.replacements.0.target');

		// Unless we force it
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'delete', '?' => ['game' => $other_game->id, 'force' => true]],
			$admin->id, [], ['controller' => 'Divisions', 'action' => 'schedule', '?' => ['division' => $other_game->division_id]],
			'The game has been deleted.');

		// Make sure the score for the game was also deleted
		$query = ScoreEntryFactory::find();
		$this->assertEquals(0, $query->count());
	}

	/**
	 * Test delete method as a manager
	 */
	public function testDeleteAsManager(): void {
		$this->enableSecurityToken();

		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		// Managers are allowed to delete games in their affiliate
		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
		]);

		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'delete', '?' => ['game' => $game->id]],
			$manager->id, [], ['controller' => 'Divisions', 'action' => 'schedule', '?' => ['division' => $game->division_id]],
			'The game has been deleted.');

		// But not ones in other affiliates
		/** @var \App\Model\Entity\Game $affiliate_game */
		$affiliate_game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[1],
		]);

		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete', '?' => ['game' => $affiliate_game->id]],
			$manager->id);
	}

	/**
	 * Test delete method as a coordinator
	 */
	public function testDeleteAsCoordinator(): void {
		$this->enableSecurityToken();

		[$admin, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer']);
		$affiliates = $admin->affiliates;

		// Coordinators are allowed to delete their own games
		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'coordinator' => $volunteer,
		]);

		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'delete', '?' => ['game' => $game->id]],
			$volunteer->id, [], ['controller' => 'Divisions', 'action' => 'schedule', '?' => ['division' => $game->division_id]],
			'The game has been deleted.');

		// But not ones in other divisions
		/** @var \App\Model\Entity\Game $other_game */
		$other_game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
		]);

		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete', '?' => ['game' => $other_game->id]],
			$volunteer->id);
	}

	/**
	 * Test delete method as others
	 */
	public function testDeleteAsOthers(): void {
		$this->enableSecurityToken();

		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);
		$affiliates = $admin->affiliates;

		// Others are not allowed to delete games
		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
		]);

		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete', '?' => ['game' => $game->id]],
			$player->id);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Games', 'action' => 'delete', '?' => ['game' => $game->id]]);
	}

	/**
	 * Test attendance method
	 */
	public function testAttendance(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'coordinator' => $volunteer,
			'home_captain' => true,
			'home_player' => $player,
		]);

		// Admins are allowed to see attendance
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'attendance', '?' => ['game' => $game->id, 'team' => $game->home_team_id]], $admin->id);

		// Managers are allowed to see attendance
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'attendance', '?' => ['game' => $game->id, 'team' => $game->home_team_id]], $manager->id);

		// Coordinators are not allowed to see attendance
		$this->assertGetAsAccessRedirect(['controller' => 'Games', 'action' => 'attendance', '?' => ['game' => $game->id, 'team' => $game->home_team_id]],
			$volunteer->id, ['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]],
			'You are not on the roster of a team playing in this game.');

		// Captains are allowed to see attendance for their games
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'attendance', '?' => ['game' => $game->id, 'team' => $game->home_team_id]], $game->home_team->people[0]->id);

		// Players are allowed to see attendance for their games
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'attendance', '?' => ['game' => $game->id, 'team' => $game->home_team_id]], $player->id);

		// Others are not allowed to see attendance
		$this->assertGetAnonymousAccessDenied(['controller' => 'Games', 'action' => 'attendance', '?' => ['game' => $game->id, 'team' => $game->home_team_id]]);
	}

	/**
	 * Test add_sub method as an admin
	 */
	public function testAddSubAsAdmin(): void {
		// Admins are allowed to add sub
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test add_sub method as a manager
	 */
	public function testAddSubAsManager(): void {
		// Managers are allowed to add sub
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test add_sub method as a coordinator
	 */
	public function testAddSubAsCoordinator(): void {
		// Coordinators are allowed to add sub
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test add_sub method as a captain
	 */
	public function testAddSubAsCaptain(): void {
		// Captains are allowed to add sub
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test add_sub method as a player
	 */
	public function testAddSubAsPlayer(): void {
		// Players are allowed to add sub
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test add_sub method as someone else
	 */
	public function testAddSubAsVisitor(): void {
		// Visitors are allowed to add sub
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test add_sub method without being logged in
	 */
	public function testAddSubAsAnonymous(): void {
		// Others are allowed to add sub
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test attendance_change method as an admin
	 */
	public function testAttendanceChangeAsAdmin(): void {
		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);
		$affiliates = $admin->affiliates;

		// Admins are allowed to change attendance
		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'home_player' => $player,
		]);

		AttendanceFactory::make([
			'team_id' => $game->home_team_id,
			'game_date' => $game->game_slot->game_date,
			'game_id' => $game->id,
			'person_id' => $player->id,
			'status' => ATTENDANCE_ATTENDING,
		])->persist();

		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'attendance_change', '?' => ['game' => $game->id, 'team' => $game->home_team_id, 'person' => $player->id]], $admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test attendance_change method as a manager
	 */
	public function testAttendanceChangeAsManager(): void {
		[$admin, $manager, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager', 'player']);
		$affiliates = $admin->affiliates;

		// Managers are allowed to change attendance
		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'home_player' => $player,
		]);

		AttendanceFactory::make([
			'team_id' => $game->home_team_id,
			'game_date' => $game->game_slot->game_date,
			'game_id' => $game->id,
			'person_id' => $player->id,
			'status' => ATTENDANCE_ATTENDING,
		])->persist();

		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'attendance_change', '?' => ['game' => $game->id, 'team' => $game->home_team_id, 'person' => $player->id]], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test attendance_change method as a coordinator
	 */
	public function testAttendanceChangeAsCoordinator(): void {
		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);
		$affiliates = $admin->affiliates;

		// Coordinators are allowed to change attendance
		// TODO: Why, and how, can they change it, if they can't see it?
		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'coordinator' => $volunteer,
			'home_player' => $player,
		]);

		AttendanceFactory::make([
			'team_id' => $game->home_team_id,
			'game_date' => $game->game_slot->game_date,
			'game_id' => $game->id,
			'person_id' => $player->id,
			'status' => ATTENDANCE_ATTENDING,
		])->persist();

		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'attendance_change', '?' => ['game' => $game->id, 'team' => $game->home_team_id, 'person' => $player->id]], $volunteer->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test attendance_change method as a captain
	 */
	public function testAttendanceChangeAsCaptain(): void {
		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);
		$affiliates = $admin->affiliates;

		// Captains are allowed to change attendance
		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'home_captain' => true,
			'home_player' => $player,
		]);
		$captain = $game->home_team->people[0];

		AttendanceFactory::make([
			'team_id' => $game->home_team_id,
			'game_date' => $game->game_slot->game_date,
			'game_id' => $game->id,
			'person_id' => $captain->id,
			'status' => ATTENDANCE_ATTENDING,
		])->persist();

		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'attendance_change', '?' => ['game' => $game->id, 'team' => $game->home_team_id]], $captain->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test attendance_change method as a player
	 */
	public function testAttendanceChangeAsPlayer(): void {
		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);
		$affiliates = $admin->affiliates;

		// Players are allowed to change attendance
		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'home_captain' => true,
			'home_player' => $player,
		]);

		AttendanceFactory::make([
			'team_id' => $game->home_team_id,
			'game_date' => $game->game_slot->game_date,
			'game_id' => $game->id,
			'person_id' => $player->id,
			'status' => ATTENDANCE_ATTENDING,
		])->persist();

		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'attendance_change', '?' => ['game' => $game->id, 'team' => $game->home_team_id]], $player->id);

		// But not more than 2 weeks after the game.
		FrozenTime::setTestNow(FrozenDate::now()->addDays(15));
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'attendance_change', '?' => ['game' => $game->id, 'team' => $game->home_team_id]], $player->id);

		// And not for teams they're not on at all
		/** @var \App\Model\Entity\Game $other_game */
		$other_game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
		]);

		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'attendance_change', '?' => ['game' => $other_game->id, 'team' => $other_game->home_team_id]], $player->id);

		// Or only just invited to
		TeamsPersonFactory::make(['team_id' => $game->home_team_id, 'person_id' => $player->id, 'role' => 'player', 'status' => ROSTER_INVITED])->persist();
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'attendance_change', '?' => ['game' => $other_game->id, 'team' => $other_game->home_team_id]], $player->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test attendance_change method as others
	 */
	public function testAttendanceChangeAsOthers(): void {
		FrozenTime::setTestNow(new FrozenTime('last Monday of May'));

		[$admin, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer']);
		$affiliates = $admin->affiliates;

		// Others are not allowed to change attendance
		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
		]);

		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'attendance_change', '?' => ['game' => $game->id, 'team' => $game->home_team_id]], $volunteer->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Games', 'action' => 'attendance_change', '?' => ['game' => $game->id, 'team' => $game->home_team_id]]);
	}

	/**
	 * Test stat_sheet method
	 */
	public function testStatSheet(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		// Players are allowed to change attendance
		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'coordinator' => $volunteer,
			'league_details' => ['stat_tracking' => 'always'],
			'home_captain' => true,
		]);

		LeaguesStatTypeFactory::make(['league_id' => $game->division->league_id, 'stat_type_id' => STAT_TYPE_ID_ULTIMATE_GOALS])->persist();

		// Admins are allowed to see the stat sheet
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'stat_sheet', '?' => ['game' => $game->id, 'team' => $game->home_team_id]], $admin->id);

		// Managers are allowed to see the stat sheet
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'stat_sheet', '?' => ['game' => $game->id, 'team' => $game->home_team_id]], $manager->id);

		// Coordinators are allowed to see the stat sheet
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'stat_sheet', '?' => ['game' => $game->id, 'team' => $game->home_team_id]], $volunteer->id);

		// Captains are allowed to see the stat sheet
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'stat_sheet', '?' => ['game' => $game->id, 'team' => $game->home_team_id]], $game->home_team->people[0]->id);

		// Others are not allowed to see the stat sheet
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'stat_sheet', '?' => ['game' => $game->id, 'team' => $game->home_team_id]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Games', 'action' => 'stat_sheet', '?' => ['game' => $game->id, 'team' => $game->home_team_id]]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test live_score method as an admin
	 */
	public function testLiveScoreAsAdmin(): void {
		// Admins are allowed to live score
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test live_score method as a manager
	 */
	public function testLiveScoreAsManager(): void {
		// Managers are allowed to live score
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test live_score method as a coordinator
	 */
	public function testLiveScoreAsCoordinator(): void {
		// Coordinators are allowed to live score
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test live_score method as a captain
	 */
	public function testLiveScoreAsCaptain(): void {
		// Captains are allowed to live score
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test live_score method as a player
	 */
	public function testLiveScoreAsPlayer(): void {
		// Players are allowed to live score
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test live_score method as someone else
	 */
	public function testLiveScoreAsVisitor(): void {
		// Visitors are allowed to live score
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test live_score method without being logged in
	 */
	public function testLiveScoreAsAnonymous(): void {
		// Others are allowed to live score
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test score_up method as an admin
	 */
	public function testScoreUpAsAdmin(): void {
		// Admins are allowed to score up
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test score_up method as a manager
	 */
	public function testScoreUpAsManager(): void {
		// Managers are allowed to score up
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test score_up method as a coordinator
	 */
	public function testScoreUpAsCoordinator(): void {
		// Coordinators are allowed to score up
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test score_up method as a captain
	 */
	public function testScoreUpAsCaptain(): void {
		// Captains are allowed to score up
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test score_up method as a player
	 */
	public function testScoreUpAsPlayer(): void {
		// Players are allowed to score up
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test score_up method as someone else
	 */
	public function testScoreUpAsVisitor(): void {
		// Visitors are allowed to score up
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test score_up method without being logged in
	 */
	public function testScoreUpAsAnonymous(): void {
		// Others are allowed to score up
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test score_down method as an admin
	 */
	public function testScoreDownAsAdmin(): void {
		// Admins are allowed to score down
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test score_down method as a manager
	 */
	public function testScoreDownAsManager(): void {
		// Managers are allowed to score down
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test score_down method as a coordinator
	 */
	public function testScoreDownAsCoordinator(): void {
		// Coordinators are allowed to score down
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test score_down method as a captain
	 */
	public function testScoreDownAsCaptain(): void {
		// Captains are allowed to score down
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test score_down method as a player
	 */
	public function testScoreDownAsPlayer(): void {
		// Players are allowed to score down
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test score_down method as someone else
	 */
	public function testScoreDownAsVisitor(): void {
		// Visitors are allowed to score down
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test score_down method without being logged in
	 */
	public function testScoreDownAsAnonymous(): void {
		// Others are allowed to score down
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test timeout method as an admin
	 */
	public function testTimeoutAsAdmin(): void {
		// Admins are allowed to timeout
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test timeout method as a manager
	 */
	public function testTimeoutAsManager(): void {
		// Managers are allowed to timeout
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test timeout method as a coordinator
	 */
	public function testTimeoutAsCoordinator(): void {
		// Coordinators are allowed to timeout
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test timeout method as a captain
	 */
	public function testTimeoutAsCaptain(): void {
		// Captains are allowed to timeout
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test timeout method as a player
	 */
	public function testTimeoutAsPlayer(): void {
		// Players are allowed to timeout
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test timeout method as someone else
	 */
	public function testTimeoutAsVisitor(): void {
		// Visitors are allowed to timeout
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test timeout method without being logged in
	 */
	public function testTimeoutAsAnonymous(): void {
		// Others are allowed to timeout
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test play method as an admin
	 */
	public function testPlayAsAdmin(): void {
		// Admins are allowed to play
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test play method as a manager
	 */
	public function testPlayAsManager(): void {
		// Managers are allowed to play
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test play method as a coordinator
	 */
	public function testPlayAsCoordinator(): void {
		// Coordinators are allowed to play
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test play method as a captain
	 */
	public function testPlayAsCaptain(): void {
		// Captains are allowed to play
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test play method as a player
	 */
	public function testPlayAsPlayer(): void {
		// Players are allowed to play
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test play method as someone else
	 */
	public function testPlayAsVisitor(): void {
		// Visitors are allowed to play
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test play method without being logged in
	 */
	public function testPlayAsAnonymous(): void {
		// Others are allowed to play
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test tweet method as an admin
	 */
	public function testTweetAsAdmin(): void {
		// Admins are allowed to tweet
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test tweet method as a manager
	 */
	public function testTweetAsManager(): void {
		// Managers are allowed to tweet
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test tweet method as a coordinator
	 */
	public function testTweetAsCoordinator(): void {
		// Coordinators are allowed to tweet
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test tweet method as a captain
	 */
	public function testTweetAsCaptain(): void {
		// Captains are allowed to tweet
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test tweet method as a player
	 */
	public function testTweetAsPlayer(): void {
		// Players are allowed to tweet
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test tweet method as someone else
	 */
	public function testTweetAsVisitor(): void {
		// Visitors are allowed to tweet
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test tweet method without being logged in
	 */
	public function testTweetAsAnonymous(): void {
		// Others are allowed to tweet
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test submit_score method as a captain
	 */
	public function testSubmitScoreAsCaptain(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'home_captain' => true,
			'away_captain' => true,
		]);
		$captain = $game->home_team->people[0];

		$url = ['controller' => 'Games', 'action' => 'submit_score', '?' => ['game' => $game->id, 'team' => $game->home_team_id]];

		// Scores can only be submitted after the game, so we need to set "today" for this test to be reliable
		FrozenTime::setTestNow(new FrozenTime($game->game_slot->game_date->subDays(1)));
		$this->assertGetAsAccessRedirect($url,
			$captain->id, ['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]],
			'That game has not yet occurred!');

		FrozenTime::setTestNow(new FrozenTime($game->game_slot->game_date->addDays(1)));
		$this->assertGetAsAccessOk($url, $captain->id);

		$this->enableSecurityToken();
		$this->assertPostAsAccessRedirect($url,
			$captain->id, [
				'score_entries' => [
					[
						'team_id' => $game->home_team_id,
						'game_id' => $game->id,
						'status' => 'normal',
						'score_for' => '17',
						'score_against' => '10',
						'home_carbon_flip' => 1,
					],
				],
				'spirit_entries' => [
					[
						'team_id' => $game->away_team_id,
						'created_team_id' => $game->home_team_id,
						'q1' => 2,
						'q2' => 2,
						'q3' => 2,
						'q4' => 2,
						'q5' => 2,
						'comments' => '',
						'highlights' => '',
					]
				],
				'has_incident' => false,
			], '/', 'This score has been saved. Once your opponent has entered their score, it will be officially posted. The score you have submitted indicates that this game was {0}. If this is incorrect, you can {1} to correct it.'
		);
		$this->assertSession('a win for your team', 'Flash.flash.0.params.replacements.0.text');

		$this->assertMailCount(1);
		$this->assertMailSentFrom('admin@zuluru.org');
		$this->assertMailSentWithArray([$captain->user->email => $captain->full_name], 'ReplyTo');
		$this->assertMailSentTo($game->away_team->people[0]->user->email);
		$this->assertMailSentWithArray([], 'CC');
		$this->assertMailSentWith('Opponent score submission', 'Subject');
		$this->assertMailContains("Your opponent has indicated that the game between your team {$game->away_team->name} and {$game->home_team->name}, starting at 7:00PM on {$game->game_slot->game_date->format('M j, Y')} in {$game->division->full_league_name} was a 17-10 loss for your team.");

		$game = GameFactory::get($game->id, ['contain' => ['ScoreEntries', 'SpiritEntries']]);
		$this->assertFalse($game->isFinalized());

		$this->assertEquals(1, count($game->score_entries));
		$this->assertEquals($captain->id, $game->score_entries[0]->person_id);
		$this->assertEquals($game->home_team_id, $game->score_entries[0]->team_id);
		$this->assertEquals($game->id, $game->score_entries[0]->game_id);
		$this->assertEquals('normal', $game->score_entries[0]->status);
		$this->assertEquals(17, $game->score_entries[0]->score_for);
		$this->assertEquals(10, $game->score_entries[0]->score_against);
		$this->assertEquals(1, $game->score_entries[0]->home_carbon_flip);

		$this->assertEquals(1, count($game->spirit_entries));
		$this->assertEquals($game->away_team_id, $game->spirit_entries[0]->team_id);
		$this->assertEquals($game->home_team_id, $game->spirit_entries[0]->created_team_id);
		$this->assertEquals($game->id, $game->spirit_entries[0]->game_id);
		$this->assertEquals(2, $game->spirit_entries[0]->q1);
		$this->assertEquals(2, $game->spirit_entries[0]->q2);
		$this->assertEquals(2, $game->spirit_entries[0]->q3);
		$this->assertEquals(2, $game->spirit_entries[0]->q4);
		$this->assertEquals(2, $game->spirit_entries[0]->q5);
	}

	/**
	 * Test submit_score method while acting as a captain. All exactly the same assertions as above, just acting as the captain.
	 */
	public function testSubmitScoreActingAsCaptain(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'home_captain' => true,
			'away_captain' => true,
		]);
		$captain = $game->home_team->people[0];

		$url = ['controller' => 'Games', 'action' => 'submit_score', '?' => ['game' => $game->id, 'team' => $game->home_team_id]];

		// Scores can only be submitted after the game, so we need to set "today" for this test to be reliable
		FrozenTime::setTestNow(new FrozenTime($game->game_slot->game_date->subDays(1)));
		$this->assertGetAsAccessRedirect($url,
			[$admin->id, $captain->id], ['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]],
			'That game has not yet occurred!');
		FrozenTime::setTestNow(new FrozenTime($game->game_slot->game_date->addDays(1)));
		$this->assertGetAsAccessOk($url, $captain->id);

		$this->enableSecurityToken();
		$this->assertPostAsAccessRedirect($url,
			[$admin->id, $captain->id], [
				'score_entries' => [
					[
						'team_id' => $game->home_team_id,
						'game_id' => $game->id,
						'status' => 'normal',
						'score_for' => '17',
						'score_against' => '10',
						'home_carbon_flip' => 1,
					],
				],
				'spirit_entries' => [
					[
						'team_id' => $game->away_team_id,
						'created_team_id' => $game->home_team_id,
						'q1' => 2,
						'q2' => 2,
						'q3' => 2,
						'q4' => 2,
						'q5' => 2,
						'comments' => '',
						'highlights' => '',
					]
				],
				'has_incident' => false,
			], '/', 'This score has been saved. Once your opponent has entered their score, it will be officially posted. The score you have submitted indicates that this game was {0}. If this is incorrect, you can {1} to correct it.'
		);
		$this->assertSession('a win for your team', 'Flash.flash.0.params.replacements.0.text');

		$this->assertMailCount(1);
		$this->assertMailSentFrom('admin@zuluru.org');
		$this->assertMailSentWithArray([$captain->user->email => $captain->full_name], 'ReplyTo');
		$this->assertMailSentTo($game->away_team->people[0]->user->email);
		$this->assertMailSentWithArray([], 'CC');
		$this->assertMailSentWith('Opponent score submission', 'Subject');
		$this->assertMailContains("Your opponent has indicated that the game between your team {$game->away_team->name} and {$game->home_team->name}, starting at 7:00PM on {$game->game_slot->game_date->format('M j, Y')} in {$game->division->full_league_name} was a 17-10 loss for your team.");

		$game = GameFactory::get($game->id, ['contain' => ['ScoreEntries', 'SpiritEntries']]);
		$this->assertFalse($game->isFinalized());

		$this->assertEquals(1, count($game->score_entries));
		$this->assertEquals($captain->id, $game->score_entries[0]->person_id);
		$this->assertEquals($game->home_team_id, $game->score_entries[0]->team_id);
		$this->assertEquals($game->id, $game->score_entries[0]->game_id);
		$this->assertEquals('normal', $game->score_entries[0]->status);
		$this->assertEquals(17, $game->score_entries[0]->score_for);
		$this->assertEquals(10, $game->score_entries[0]->score_against);
		$this->assertEquals(1, $game->score_entries[0]->home_carbon_flip);

		$this->assertEquals(1, count($game->spirit_entries));
		$this->assertEquals($game->away_team_id, $game->spirit_entries[0]->team_id);
		$this->assertEquals($game->home_team_id, $game->spirit_entries[0]->created_team_id);
		$this->assertEquals($game->id, $game->spirit_entries[0]->game_id);
		$this->assertEquals(2, $game->spirit_entries[0]->q1);
		$this->assertEquals(2, $game->spirit_entries[0]->q2);
		$this->assertEquals(2, $game->spirit_entries[0]->q3);
		$this->assertEquals(2, $game->spirit_entries[0]->q4);
		$this->assertEquals(2, $game->spirit_entries[0]->q5);
	}

	/**
	 * Test submit_score method as a captain, matching existing score
	 */
	public function testSubmitMatchingScoreAsCaptain(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'home_captain' => true,
			'away_captain' => true,
		]);
		$home = $game->home_team;
		$away = $game->away_team;
		$captain = $home->people[0];

		ScoreEntryFactory::make([
			'game_id' => $game->id,
			'team_id' => $away->id,
			'score_for' => 5,
			'score_against' => 4,
			'home_carbon_flip' => 1,
			'person_id' => $admin->id,
		])->persist();
		SpiritEntryFactory::make(['created_team_id' => $away->id, 'team_id' => $home->id, 'game_id' => $game->id])
			->persist();

		$url = ['controller' => 'Games', 'action' => 'submit_score', '?' => ['game' => $game->id, 'team' => $home->id]];

		// Scores can only be submitted after the game, so we need to set "today" for this test to be reliable
		FrozenTime::setTestNow(new FrozenTime($game->game_slot->game_date->addDays(1)));
		$this->assertGetAsAccessOk($url, $captain->id);

		$this->enableSecurityToken();
		$this->assertPostAsAccessRedirect($url,
			$captain->id, [
				'score_entries' => [
					[
						'team_id' => $home->id,
						'game_id' => $game->id,
						'status' => 'normal',
						'score_for' => '4',
						'score_against' => '5',
						'home_carbon_flip' => 1,
					],
				],
				'spirit_entries' => [
					[
						'team_id' => $away->id,
						'created_team_id' => $home->id,
						'q1' => 0,
						'q2' => 1,
						'q3' => 2,
						'q4' => 3,
						'q5' => 4,
						'comments' => '',
						'highlights' => '',
					]
				],
				'has_incident' => false,
			], '/', 'This score agrees with the score submitted by your opponent. It will now be posted as an official game result.'
		);

		$this->assertNoMailSent();

		$game = GameFactory::get($game->id, ['contain' => ['SpiritEntries']]);
		$this->assertTrue($game->isFinalized());
		$this->assertEquals(4, $game->home_score);
		$this->assertEquals(5, $game->away_score);
		$this->assertEquals(2, count($game->spirit_entries));
		$this->assertEquals($home->id, $game->spirit_entries[0]->team_id);
		$this->assertEquals($away->id, $game->spirit_entries[0]->created_team_id);
		$this->assertEquals($game->id, $game->spirit_entries[0]->game_id);
		$this->assertEquals(2, $game->spirit_entries[0]->q1);
		$this->assertEquals(2, $game->spirit_entries[0]->q2);
		$this->assertEquals(2, $game->spirit_entries[0]->q3);
		$this->assertEquals(2, $game->spirit_entries[0]->q4);
		$this->assertEquals(2, $game->spirit_entries[0]->q5);
		$this->assertEquals($away->id, $game->spirit_entries[1]->team_id);
		$this->assertEquals($home->id, $game->spirit_entries[1]->created_team_id);
		$this->assertEquals($game->id, $game->spirit_entries[1]->game_id);
		$this->assertEquals(0, $game->spirit_entries[1]->q1);
		$this->assertEquals(1, $game->spirit_entries[1]->q2);
		$this->assertEquals(2, $game->spirit_entries[1]->q3);
		$this->assertEquals(3, $game->spirit_entries[1]->q4);
		$this->assertEquals(4, $game->spirit_entries[1]->q5);
	}

	/**
	 * Test submit_score method as a captain, not matching existing score
	 */
	public function testSubmitMismatchedScoreAsCaptain(): void {
		[$admin, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'coordinator' => $volunteer,
			'home_captain' => true,
			'away_captain' => true,
		]);
		$home = $game->home_team;
		$away = $game->away_team;
		$captain = $home->people[0];

		ScoreEntryFactory::make([
			'game_id' => $game->id,
			'team_id' => $away->id,
			'score_for' => 5,
			'score_against' => 4,
			'home_carbon_flip' => 1,
			'person_id' => $admin->id,
		])->persist();
		SpiritEntryFactory::make(['created_team_id' => $away->id, 'team_id' => $home->id, 'game_id' => $game->id])
			->persist();

		$url = ['controller' => 'Games', 'action' => 'submit_score', '?' => ['game' => $game->id, 'team' => $home->id]];

		// Scores can only be submitted after the game, so we need to set "today" for this test to be reliable
		FrozenTime::setTestNow(new FrozenTime($game->game_slot->game_date->addDays(1)));
		$this->assertGetAsAccessOk($url, $captain->id);

		$this->enableSecurityToken();
		$this->assertPostAsAccessRedirect($url,
			$captain->id, [
				'score_entries' => [
					[
						'team_id' => $home->id,
						'game_id' => $game->id,
						'status' => 'normal',
						'score_for' => '5',
						'score_against' => '5',
						'home_carbon_flip' => 1,
					],
				],
				'spirit_entries' => [
					[
						'team_id' => $away->id,
						'created_team_id' => $home->id,
						'q1' => 0,
						'q2' => 1,
						'q3' => 2,
						'q4' => 3,
						'q5' => 4,
						'comments' => '',
						'highlights' => '',
					]
				],
				'has_incident' => false,
			], '/', 'This score doesn\'t agree with the one your opponent submitted. Because of this, the score will not be posted until your coordinator approves it. Alternately, whichever coach or captain made an error can {0}.'
		);

		$this->assertMailCount(2);
		$this->assertMailSentFromAt(0, 'admin@zuluru.org');
		$this->assertMailSentToAt(0, $volunteer->user->email);
		$this->assertMailSentWithArrayAt(0, [], 'CC');
		$this->assertMailSentWithAt(0, 'Score entry mismatch', 'Subject');
		$this->assertMailContainsAt(0, "The {$game->game_slot->game_date->format('M j, Y')} game between {$home->name} and {$away->name} in {$game->division->league->name} has score entries which do not match. You can edit the game here:");

		$this->assertMailSentFromAt(1, 'admin@zuluru.org');
		$this->assertMailSentWithArrayAt(1, [$captain->user->email => $captain->full_name], 'ReplyTo');
		$this->assertMailSentToAt(1, $game->away_team->people[0]->user->email);
		$this->assertMailSentWithArrayAt(1, [], 'CC');
		$this->assertMailSentWithAt(1, 'Opponent score submission', 'Subject');
		$this->assertMailContainsAt(1, "Your opponent has indicated that the game between your team {$away->name} and {$home->name}, starting at 7:00PM on {$game->game_slot->game_date->format('M j, Y')} in {$game->division->full_league_name} was a 5-5 tie.");

		$game = GameFactory::get($game->id, ['contain' => ['SpiritEntries']]);
		$this->assertFalse($game->isFinalized());
		$this->assertNull($game->home_score);
		$this->assertNull($game->away_score);
		$this->assertEquals(2, count($game->spirit_entries));
		$this->assertEquals($home->id, $game->spirit_entries[0]->team_id);
		$this->assertEquals($away->id, $game->spirit_entries[0]->created_team_id);
		$this->assertEquals($game->id, $game->spirit_entries[0]->game_id);
		$this->assertEquals(2, $game->spirit_entries[0]->q1);
		$this->assertEquals(2, $game->spirit_entries[0]->q2);
		$this->assertEquals(2, $game->spirit_entries[0]->q3);
		$this->assertEquals(2, $game->spirit_entries[0]->q4);
		$this->assertEquals(2, $game->spirit_entries[0]->q5);
		$this->assertEquals($away->id, $game->spirit_entries[1]->team_id);
		$this->assertEquals($home->id, $game->spirit_entries[1]->created_team_id);
		$this->assertEquals($game->id, $game->spirit_entries[1]->game_id);
		$this->assertEquals(0, $game->spirit_entries[1]->q1);
		$this->assertEquals(1, $game->spirit_entries[1]->q2);
		$this->assertEquals(2, $game->spirit_entries[1]->q3);
		$this->assertEquals(3, $game->spirit_entries[1]->q4);
		$this->assertEquals(4, $game->spirit_entries[1]->q5);
	}

	/**
	 * Test submit_score method as a captain, correcting an earlier incorrect submission
	 */
	public function testSubmitCorrectScoreAsCaptain(): void {
		[$admin, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer']);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'coordinator' => $volunteer,
			'home_captain' => true,
			'away_captain' => true,
		]);
		$home = $game->home_team;
		$away = $game->away_team;
		$captain = $home->people[0];

		$scores = ScoreEntryFactory::make([
			[
				'game_id' => $game->id,
				'team_id' => $away->id,
				'score_for' => 14,
				'score_against' => 15,
				'home_carbon_flip' => 1,
				'person_id' => $admin->id,
			],
			[
				'game_id' => $game->id,
				'team_id' => $home->id,
				'score_for' => 15,
				'score_against' => 13,
				'home_carbon_flip' => 1,
				'person_id' => $captain->id,
			],
		])->persist();
		$spirits = SpiritEntryFactory::make([
			['created_team_id' => $away->id, 'team_id' => $home->id, 'game_id' => $game->id],
			['created_team_id' => $home->id, 'team_id' => $away->id, 'game_id' => $game->id],
		])
			->persist();

		$url = ['controller' => 'Games', 'action' => 'submit_score', '?' => ['game' => $game->id, 'team' => $home->id]];

		// Scores can only be submitted after the game, so we need to set "today" for this test to be reliable
		FrozenTime::setTestNow(new FrozenTime($game->game_slot->game_date->addDays(1)));
		$this->assertGetAsAccessOk($url, $captain->id);

		$this->enableSecurityToken();
		$this->assertPostAsAccessRedirect($url,
			$captain->id, [
				'score_entries' => [
					[
						'id' => $scores[1]->id,
						'team_id' => $home->id,
						'game_id' => $game->id,
						'status' => 'normal',
						'score_for' => '15',
						'score_against' => '14',
						'home_carbon_flip' => 1,
					],
				],
				'spirit_entries' => [
					[
						'id' => $spirits[1]->id,
						'team_id' => $away->id,
						'created_team_id' => $home->id,
						'q1' => 0,
						'q2' => 1,
						'q3' => 2,
						'q4' => 3,
						'q5' => 4,
						'comments' => '',
						'highlights' => '',
					]
				],
				'has_incident' => false,
			], '/', 'This score agrees with the score submitted by your opponent. It will now be posted as an official game result.'
		);

		$this->assertNoMailSent();

		$game = GameFactory::get($game->id, ['contain' => ['SpiritEntries']]);
		$this->assertTrue($game->isFinalized());
		$this->assertEquals(15, $game->home_score);
		$this->assertEquals(14, $game->away_score);
		$this->assertEquals(2, count($game->spirit_entries));
		$this->assertEquals($home->id, $game->spirit_entries[0]->team_id);
		$this->assertEquals($away->id, $game->spirit_entries[0]->created_team_id);
		$this->assertEquals($game->id, $game->spirit_entries[0]->game_id);
		$this->assertEquals(2, $game->spirit_entries[0]->q1);
		$this->assertEquals(2, $game->spirit_entries[0]->q2);
		$this->assertEquals(2, $game->spirit_entries[0]->q3);
		$this->assertEquals(2, $game->spirit_entries[0]->q4);
		$this->assertEquals(2, $game->spirit_entries[0]->q5);
		$this->assertEquals($away->id, $game->spirit_entries[1]->team_id);
		$this->assertEquals($home->id, $game->spirit_entries[1]->created_team_id);
		$this->assertEquals($game->id, $game->spirit_entries[1]->game_id);
		$this->assertEquals(0, $game->spirit_entries[1]->q1);
		$this->assertEquals(1, $game->spirit_entries[1]->q2);
		$this->assertEquals(2, $game->spirit_entries[1]->q3);
		$this->assertEquals(3, $game->spirit_entries[1]->q4);
		$this->assertEquals(4, $game->spirit_entries[1]->q5);
	}

	/**
	 * Test submit_score method as others
	 */
	public function testSubmitScoreAsOthers(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'coordinator' => $volunteer,
			'home_captain' => true,
		]);

		$url = ['controller' => 'Games', 'action' => 'submit_score', '?' => ['game' => $game->id, 'team' => $game->home_team_id]];

		// Scores can only be submitted after the game, so we need to set "today" for this test to be reliable
		FrozenTime::setTestNow(new FrozenTime($game->game_slot->game_date->addDays(1)));

		// Others are not allowed to submit scores
		$this->assertGetAsAccessDenied($url, $admin->id);
		$this->assertGetAsAccessDenied($url, $manager->id);
		$this->assertGetAsAccessDenied($url, $volunteer->id);
		$this->assertGetAsAccessDenied($url, $player->id);
		$this->assertGetAnonymousAccessDenied($url);
	}

	/**
	 * Test submit_stats method as an admin
	 */
	public function testSubmitStatsAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$affiliates = $admin->affiliates;

		// Players are allowed to change attendance
		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'league_details' => ['stat_tracking' => 'always'],
			'home_score' => 15,
			'away_score' => 14,
			'approved_by_id' => APPROVAL_AUTOMATIC,
		]);

		LeaguesStatTypeFactory::make(['league_id' => $game->division->league_id, 'stat_type_id' => STAT_TYPE_ID_ULTIMATE_GOALS])->persist();

		// Make sure that we're after the game date
		FrozenDate::setTestNow($game->game_slot->game_date->addDays(1));

		// Admins are allowed to submit stats
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'submit_stats', '?' => ['game' => $game->id, 'team' => $game->home_team_id]], $admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test submit_stats method as a manager
	 */
	public function testSubmitStatsAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		// Players are allowed to change attendance
		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'league_details' => ['stat_tracking' => 'always'],
			'home_score' => 15,
			'away_score' => 14,
			'approved_by_id' => APPROVAL_AUTOMATIC,
		]);

		LeaguesStatTypeFactory::make(['league_id' => $game->division->league_id, 'stat_type_id' => STAT_TYPE_ID_ULTIMATE_GOALS])->persist();

		// Make sure that we're after the game date
		FrozenDate::setTestNow($game->game_slot->game_date->addDays(1));

		// Managers are allowed to submit stats
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'submit_stats', '?' => ['game' => $game->id, 'team' => $game->home_team_id]], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test submit_stats method as a coordinator
	 */
	public function testSubmitStatsAsCoordinator(): void {
		[$admin, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer']);
		$affiliates = $admin->affiliates;

		// Players are allowed to change attendance
		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'coordinator' => $volunteer,
			'league_details' => ['stat_tracking' => 'always'],
			'home_score' => 15,
			'away_score' => 14,
			'approved_by_id' => APPROVAL_AUTOMATIC,
		]);

		LeaguesStatTypeFactory::make(['league_id' => $game->division->league_id, 'stat_type_id' => STAT_TYPE_ID_ULTIMATE_GOALS])->persist();

		// Make sure that we're after the game date
		FrozenDate::setTestNow($game->game_slot->game_date->addDays(1));

		// Coordinators are allowed to submit stats
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'submit_stats', '?' => ['game' => $game->id, 'team' => $game->home_team_id]], $volunteer->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test submit_stats method as a captain
	 */
	public function testSubmitStatsAsCaptain(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$affiliates = $admin->affiliates;

		// Players are allowed to change attendance
		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'league_details' => ['stat_tracking' => 'always'],
			'home_captain' => true,
			'home_score' => 15,
			'away_score' => 14,
			'approved_by_id' => APPROVAL_AUTOMATIC,
		]);

		LeaguesStatTypeFactory::make(['league_id' => $game->division->league_id, 'stat_type_id' => STAT_TYPE_ID_ULTIMATE_GOALS])->persist();

		// Make sure that we're after the game date
		FrozenDate::setTestNow($game->game_slot->game_date->addDays(1));

		// Captains are allowed to submit stats
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'submit_stats', '?' => ['game' => $game->id, 'team' => $game->home_team_id]], $game->home_team->people[0]->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test submit_stats method as others
	 */
	public function testSubmitStatsAsOthers(): void {
		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);
		$affiliates = $admin->affiliates;

		// Players are allowed to change attendance
		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'league_details' => ['stat_tracking' => 'always'],
			'home_score' => 15,
			'away_score' => 14,
			'approved_by_id' => APPROVAL_AUTOMATIC,
		]);

		LeaguesStatTypeFactory::make(['league_id' => $game->division->league_id, 'stat_type_id' => STAT_TYPE_ID_ULTIMATE_GOALS])->persist();

		// Make sure that we're after the game date
		FrozenDate::setTestNow($game->game_slot->game_date->addDays(1));

		// Others are not allowed to submit stats
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'submit_stats', '?' => ['game' => $game->id, 'team' => $game->home_team_id]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Games', 'action' => 'submit_stats', '?' => ['game' => $game->id, 'team' => $game->home_team_id]]);
	}

	/**
	 * Test stats method
	 */
	public function testViewStats(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		// Players are allowed to change attendance
		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'coordinator' => $volunteer,
			'league_details' => ['stat_tracking' => 'always'],
			'home_score' => 15,
			'away_score' => 14,
			'approved_by_id' => APPROVAL_AUTOMATIC,
		]);

		LeaguesStatTypeFactory::make(['league_id' => $game->division->league_id, 'stat_type_id' => STAT_TYPE_ID_ULTIMATE_GOALS])->persist();

		// Make sure that we're after the game date
		FrozenDate::setTestNow($game->game_slot->game_date->addDays(1));

		// Redirect when there are no stats yet
		$this->assertGetAsAccessRedirect(['controller' => 'Games', 'action' => 'stats', '?' => ['game' => $game->id]],
			$admin->id, ['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]],
			'No stats have been entered for this game.');

		StatFactory::make(['game_id' => $game->id, 'team_id' => $game->home_team_id, 'person_id' => $player->id, 'stat_type_id' => STAT_TYPE_ID_ULTIMATE_GOALS])->persist();

		// Anyone logged in is allowed to see game stats
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'stats', '?' => ['game' => $game->id]], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'stats', '?' => ['game' => $game->id]], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'stats', '?' => ['game' => $game->id]], $volunteer->id);
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'stats', '?' => ['game' => $game->id]], $player->id);

		// Others are not allowed to see game stats
		$this->assertGetAnonymousAccessDenied(['controller' => 'Games', 'action' => 'stats', '?' => ['game' => $game->id]]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test future method
	 */
	public function testFuture(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'coordinator' => $volunteer,
		]);

		// Make sure that we're before the game date
		FrozenDate::setTestNow($game->game_slot->game_date->subDays(1));

		// Anyone logged in is allowed to see future games
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'future', '_ext' => 'json'], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'future', '_ext' => 'json'], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'future', '_ext' => 'json'], $volunteer->id);
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'future', '_ext' => 'json'], $player->id);

		// Others are not allowed to see future games
		$this->assertGetAnonymousAccessDenied(['controller' => 'Games', 'action' => 'future', '_ext' => 'json']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test results method
	 */
	public function testResults(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $affiliates[0],
			'coordinator' => $volunteer,
			'home_score' => 15,
			'away_score' => 14,
			'approved_by_id' => APPROVAL_AUTOMATIC,
		]);

		// Make sure that we're after the game date
		FrozenDate::setTestNow($game->game_slot->game_date->addDays(1));

		// Anyone is allowed to see recent results
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'results', '_ext' => 'json'], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'results', '_ext' => 'json'], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'results', '_ext' => 'json'], $volunteer->id);
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'results', '_ext' => 'json'], $player->id);
		$this->assertGetAnonymousAccessOk(['controller' => 'Games', 'action' => 'results', '_ext' => 'json']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

}
