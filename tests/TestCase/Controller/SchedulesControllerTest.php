<?php
namespace App\Test\TestCase\Controller;

use App\Model\Entity\Game;
use App\Model\Entity\GameSlot;
use App\Test\Factory\DivisionsGameslotFactory;
use App\Test\Factory\GameSlotFactory;
use App\Test\Scenario\DiverseUsersScenario;
use App\Test\Scenario\LeagueWithMinimalScheduleScenario;
use App\Test\Scenario\SingleGameScenario;
use Cake\Cache\Cache;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\FrozenDate;
use Cake\ORM\TableRegistry;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\SchedulesController Test Case
 */
class SchedulesControllerTest extends ControllerTestCase {

	use ScenarioAwareTrait;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.Groups',
		'app.RosterRoles',
		'app.Settings',
	];

	/**
	 * Test add method as an admin
	 */
	public function testAddAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithMinimalScheduleScenario::class, ['affiliate' => $admin->affiliates[0]]);
		$season = $league->divisions[0];

		/** @var \App\Model\Entity\League $affiliate_league */
		$affiliate_league = $this->loadFixtureScenario(LeagueWithMinimalScheduleScenario::class, ['affiliate' => $admin->affiliates[1]]);
		$affiliate_season = $affiliate_league->divisions[0];

		// Admins are allowed to add to schedules anywhere
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'add', '?' => ['division' => $season->id]], $admin->id);
		$this->assertResponseRegExp('#<input type="radio" name="_options\[type\]" value="single" id="options-type-single">\s*Single blank, unscheduled game \(2 teams, one field\)#ms');
		$this->assertResponseRegExp('#<input type="radio" name="_options\[type\]" value="oneset_ratings_ladder" id="options-type-oneset_ratings_ladder">\s*Set of ratings-scheduled games for all teams \(2 teams, 1 games, one day\)#ms');
		$this->assertResponseContains('<input type="checkbox" name="_options[publish]" value="1" id="options-publish">');
		$this->assertResponseContains('<input type="checkbox" name="_options[double_header]" value="1" id="options-double-header">');
		$this->assertResponseContains('/schedules/add?division=' . $season->id . '&amp;playoff=1');

		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'add', '?' => ['division' => $affiliate_season->id]], $admin->id);
	}

	/**
	 * Test add method as a manager
	 */
	public function testAddAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithMinimalScheduleScenario::class, ['affiliate' => $admin->affiliates[0]]);
		$season = $league->divisions[0];

		/** @var \App\Model\Entity\League $affiliate_league */
		$affiliate_league = $this->loadFixtureScenario(LeagueWithMinimalScheduleScenario::class, ['affiliate' => $admin->affiliates[1]]);
		$affiliate_season = $affiliate_league->divisions[0];

		// Managers are allowed to add to schedules in their own affiliate
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'add', '?' => ['division' => $season->id]], $manager->id);
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'add', '?' => ['division' => $affiliate_season->id]], $manager->id);
	}

	/**
	 * Test add method as a coordinator
	 */
	public function testAddAsCoordinator(): void {
		[$admin, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer']);

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithMinimalScheduleScenario::class, ['affiliate' => $admin->affiliates[0], 'coordinator' => $volunteer]);
		$season = $league->divisions[0];

		/** @var \App\Model\Entity\League $other_league */
		$other_league = $this->loadFixtureScenario(LeagueWithMinimalScheduleScenario::class, ['affiliate' => $admin->affiliates[0]]);
		$other_season = $other_league->divisions[0];

		// Coordinators are allowed to add to schedules in their divisions
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'add', '?' => ['division' => $season->id]], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'add', '?' => ['division' => $other_season->id]], $volunteer->id);
	}

	/**
	 * Test add method as others
	 */
	public function testAddAsOthers(): void {
		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithMinimalScheduleScenario::class, ['affiliate' => $admin->affiliates[0]]);
		$season = $league->divisions[0];

		// Others are not allowed to add to schedules
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'add', '?' => ['division' => $season->id]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Schedules', 'action' => 'add', '?' => ['division' => $season->id]]);
	}

	/**
	 * Test delete method as an admin
	 */
	public function testDeleteAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithMinimalScheduleScenario::class, ['affiliate' => $admin->affiliates[0]]);
		$season = $league->divisions[0];
		$game = $season->games[0];
		$date = $game->game_slot->game_date;

		/** @var \App\Model\Entity\League $affiliate_league */
		$affiliate_league = $this->loadFixtureScenario(LeagueWithMinimalScheduleScenario::class, ['affiliate' => $admin->affiliates[1]]);
		$affiliate_season = $affiliate_league->divisions[0];

		// Admins are allowed to delete schedules for any division
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'delete', '?' => ['division' => $season->id, 'date' => $date->toDateString()]], $admin->id);
		$this->assertResponseContains('<p>You have requested to delete games on ' . $date->i18nFormat('MMMM d, yyyy') . '.</p><p>This will remove 1 games, of which 1 are published.</p>');
		$this->assertResponseContains('/schedules/delete?division=' . $season->id . '&amp;date=' . $date->toDateString() . '&amp;confirm=1');

		// Or any league
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'delete', '?' => ['league' => $league->id, 'date' => $date->toDateString()]], $admin->id);
		$this->assertResponseContains('<p>You have requested to delete games on ' . $date->i18nFormat('MMMM d, yyyy') . '.</p><p>This will remove 1 games, of which 1 are published.</p>');

		// Or any affiliate
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'delete', '?' => ['division' => $affiliate_season->id, 'date' => $date->toDateString()]], $admin->id);

		// Check the errors for dates with no games
		$this->assertGetAsAccessRedirect(['controller' => 'Schedules', 'action' => 'delete', '?' => ['division' => $season->id, 'date' => $date->addWeeks(3)->toDateString()]],
			$admin->id, ['controller' => 'Divisions', 'action' => 'schedule', '?' => ['division' => $season->id]],
			'#There are no games to delete on that date.#');

		$this->assertGetAsAccessRedirect(['controller' => 'Schedules', 'action' => 'delete', '?' => ['league' => $league->id, 'date' => $date->addWeeks(3)->toDateString()]],
			$admin->id, ['controller' => 'Leagues', 'action' => 'schedule', '?' => ['league' => $league->id]],
			'#There are no games to delete on that date.#');

		// Make sure that deleting games actually deletes them, and frees up game slots
		/** @var GameSlot $slot */
		$slot = TableRegistry::getTableLocator()->get('GameSlots')->get($game->game_slot_id);
		$this->assertTrue($slot->assigned);

		$this->assertGetAsAccessRedirect(['controller' => 'Schedules', 'action' => 'delete', '?' => ['division' => $season->id, 'date' => $date->toDateString(), 'confirm' => true]],
			$admin->id, ['controller' => 'Divisions', 'action' => 'schedule', '?' => ['division' => $season->id]],
			'#Deleted games on the requested date.#');

		try {
			$game = TableRegistry::getTableLocator()->get('Games')->get($game->id);
			$this->assertNull($game, 'The game was not successfully deleted.');
		} catch (RecordNotFoundException $ex) {
			// Expected result; the team should be gone
		}
		/** @var GameSlot $slot */
		$slot = TableRegistry::getTableLocator()->get('GameSlots')->get($game->game_slot_id);
		$this->assertFalse($slot->assigned);

		$this->markTestIncomplete('Test deleting of playoff games to track down bug with slots not being unassigned.');
	}

	/**
	 * Test delete method as a manager
	 */
	public function testDeleteAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithMinimalScheduleScenario::class, ['affiliate' => $admin->affiliates[0]]);
		$season = $league->divisions[0];
		$game = $season->games[0];
		$date = $game->game_slot->game_date;

		/** @var \App\Model\Entity\League $affiliate_league */
		$affiliate_league = $this->loadFixtureScenario(LeagueWithMinimalScheduleScenario::class, ['affiliate' => $admin->affiliates[1]]);
		$affiliate_season = $affiliate_league->divisions[0];

		// Managers are allowed to delete schedules for any division
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'delete', '?' => ['division' => $season->id, 'date' => $date->toDateString()]], $manager->id);
		$this->assertResponseContains('<p>You have requested to delete games on ' . $date->i18nFormat('MMMM d, yyyy') . '.</p><p>This will remove 1 games, of which 1 are published.</p>');

		// Or any league
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'delete', '?' => ['league' => $league->id, 'date' => $date->toDateString()]], $manager->id);
		$this->assertResponseContains('<p>You have requested to delete games on ' . $date->i18nFormat('MMMM d, yyyy') . '.</p><p>This will remove 1 games, of which 1 are published.</p>');

		// But not other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'delete', '?' => ['division' => $affiliate_season->id, 'date' => $date->toDateString()]], $manager->id);
	}

	/**
	 * Test delete method as a coordinator
	 */
	public function testDeleteAsCoordinator(): void {
		[$admin, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer']);

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithMinimalScheduleScenario::class, ['affiliate' => $admin->affiliates[0], 'coordinator' => $volunteer, 'divisions' => 2]);
		$season = $league->divisions[0];
		$game = $season->games[0];
		$date = $game->game_slot->game_date;

		/** @var \App\Model\Entity\League $other_league */
		$other_league = $this->loadFixtureScenario(LeagueWithMinimalScheduleScenario::class, ['affiliate' => $admin->affiliates[0]]);
		$other_season = $other_league->divisions[0];

		// Coordinators are allowed to delete schedules for any or their divisions
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'delete', '?' => ['division' => $season->id, 'date' => $date->toDateString()]], $volunteer->id);
		$this->assertResponseContains('<p>You have requested to delete games on ' . $date->i18nFormat('MMMM d, yyyy') . '.</p><p>This will remove 1 games, of which 1 are published.</p>');

		// Or any league where they coordinate all of the divisions
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'delete', '?' => ['league' => $league->id, 'date' => $date->toDateString()]], $volunteer->id);
		$this->assertResponseContains('<p>You have requested to delete games on ' . $date->i18nFormat('MMMM d, yyyy') . '.</p><p>This will remove 2 games, of which 2 are published.</p>');

		// But not other divisions
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'delete', '?' => ['division' => $other_season->id, 'date' => $date->toDateString()]], $volunteer->id);

		// And not leagues where they coordinate only some divisions
		TableRegistry::getTableLocator()->get('DivisionsPeople')->deleteAll(['division_id' => $season->id]);
		Cache::clear('long_term');
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'delete', '?' => ['league' => $league->id, 'date' => $date->toDateString()]], $volunteer->id);
	}

	/**
	 * Test delete method as others
	 */
	public function testDeleteAsOthers(): void {
		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);
		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithMinimalScheduleScenario::class, ['affiliate' => $admin->affiliates[0], 'coordinator' => $volunteer]);
		$season = $league->divisions[0];
		$game = $season->games[0];
		$date = $game->game_slot->game_date;

		// Others are not allowed to delete schedules for any division
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'delete', '?' => ['division' => $season->id, 'date' => $date->toDateString()]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Schedules', 'action' => 'delete', '?' => ['division' => $season->id, 'date' => $date->toDateString()]]);

		// Or any league
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'delete', '?' => ['league' => $league->id, 'date' => $date->toDateString()]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Schedules', 'action' => 'delete', '?' => ['league' => $league->id, 'date' => $date->toDateString()]]);
	}

	/**
	 * Test reschedule method as an admin
	 */
	public function testRescheduleAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithMinimalScheduleScenario::class, ['affiliate' => $admin->affiliates[0]]);
		$season = $league->divisions[0];
		$game = $season->games[0];
		$date = $game->game_slot->game_date;

		$slot = GameSlotFactory::make(['date' => $date->addWeeks(2)])
			->persist();
		DivisionsGameslotFactory::make(['division_id' => $season->id, 'game_slot_id' => $slot->id])
			->persist();

		// Admins are allowed to reschedule
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'reschedule', '?' => ['division' => $season->id, 'date' => $date->toDateString()]], $admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test reschedule method as a manager
	 */
	public function testRescheduleAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithMinimalScheduleScenario::class, ['affiliate' => $admin->affiliates[0]]);
		$season = $league->divisions[0];
		$game = $season->games[0];
		$date = $game->game_slot->game_date;

		$slot = GameSlotFactory::make(['date' => $date->addWeeks(2)])
			->persist();
		DivisionsGameslotFactory::make(['division_id' => $season->id, 'game_slot_id' => $slot->id])
			->persist();

		// Managers are allowed to reschedule
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'reschedule', '?' => ['division' => $season->id, 'date' => $date->toDateString()]], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test reschedule method as a coordinator
	 */
	public function testRescheduleAsCoordinator(): void {
		[$admin, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer']);
		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithMinimalScheduleScenario::class, ['affiliate' => $admin->affiliates[0], 'coordinator' => $volunteer]);
		$season = $league->divisions[0];
		$game = $season->games[0];
		$date = $game->game_slot->game_date;

		$slot = GameSlotFactory::make(['date' => $date->addWeeks(2)])
			->persist();
		DivisionsGameslotFactory::make(['division_id' => $season->id, 'game_slot_id' => $slot->id])
			->persist();

		// Coordinators are allowed to reschedule
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'reschedule', '?' => ['division' => $season->id, 'date' => $date->toDateString()]], $volunteer->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test reschedule method as others
	 */
	public function testRescheduleAsOthers(): void {
		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);
		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithMinimalScheduleScenario::class, ['affiliate' => $admin->affiliates[0]]);
		$season = $league->divisions[0];
		$game = $season->games[0];
		$date = $game->game_slot->game_date;

		$slot = GameSlotFactory::make(['date' => $date->addWeeks(2)])
			->persist();
		DivisionsGameslotFactory::make(['division_id' => $season->id, 'game_slot_id' => $slot->id])
			->persist();

		// Others are not allowed to reschedule
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'reschedule', '?' => ['division' => $season->id, 'date' => $date->toDateString()]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Schedules', 'action' => 'reschedule', '?' => ['division' => $season->id, 'date' => $date->toDateString()]]);
	}

	/**
	 * Test publish method as an admin
	 */
	public function testPublishAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'published' => false,
		]);
		$date = $game->game_slot->game_date;
		$division = $game->division;

		$this->assertFalse($game->published);

		/** @var \App\Model\Entity\Game $affiliate_game */
		$affiliate_game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[1],
			'published' => false,
		]);
		$affiliate_division = $affiliate_game->division;

		// Admins are allowed to publish schedules anywhere
		$this->assertGetAsAccessRedirect(['controller' => 'Schedules', 'action' => 'publish', '?' => ['division' => $division->id, 'date' => $date->toDateString()]],
			$admin->id, ['controller' => 'Divisions', 'action' => 'schedule', '?' => ['division' => $division->id]],
			'#Published games on the requested date.#');

		/** @var Game $game */
		$game = TableRegistry::getTableLocator()->get('Games')->get($game->id);
		$this->assertTrue($game->published);

		$this->assertGetAsAccessRedirect(['controller' => 'Schedules', 'action' => 'publish', '?' => ['division' => $affiliate_division->id, 'date' => $date->toDateString()]],
			$admin->id, ['controller' => 'Divisions', 'action' => 'schedule', '?' => ['division' => $affiliate_division->id]],
			'#Published games on the requested date.#');
	}

	/**
	 * Test publish method as a manager
	 */
	public function testPublishAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'published' => false,
		]);
		$date = $game->game_slot->game_date;
		$division = $game->division;

		/** @var \App\Model\Entity\Game $affiliate_game */
		$affiliate_game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[1],
			'published' => false,
		]);
		$affiliate_division = $affiliate_game->division;

		// Managers are allowed to publish schedules anywhere
		$this->assertGetAsAccessRedirect(['controller' => 'Schedules', 'action' => 'publish', '?' => ['division' => $division->id, 'date' => $date->toDateString()]],
			$manager->id, ['controller' => 'Divisions', 'action' => 'schedule', '?' => ['division' => $division->id]],
			'#Published games on the requested date.#');

		// But not in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'publish', '?' => ['division' => $affiliate_division->id, 'date' => $date->toDateString()]], $manager->id);
	}

	/**
	 * Test publish method as a coordinator
	 */
	public function testPublishAsCoordinator(): void {
		[$admin, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer']);

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'published' => false,
			'coordinator' => $volunteer,
		]);
		$date = $game->game_slot->game_date;
		$division = $game->division;

		/** @var \App\Model\Entity\Game $other_game */
		$other_game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'published' => false,
		]);
		$other_division = $other_game->division;

		// Coordinators are allowed to publish schedules in their own divisions
		$this->assertGetAsAccessRedirect(['controller' => 'Schedules', 'action' => 'publish', '?' => ['division' => $division->id, 'date' => $date->toDateString()]],
			$volunteer->id, ['controller' => 'Divisions', 'action' => 'schedule', '?' => ['division' => $division->id]],
			'#Published games on the requested date.#');

		// But not in other divisions
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'publish', '?' => ['division' => $other_division->id, 'date' => $date->toDateString()]], $volunteer->id);
	}

	/**
	 * Test publish method as others
	 */
	public function testPublishAsOthers(): void {
		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);
		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'published' => false,
		]);
		$date = $game->game_slot->game_date;
		$division = $game->division;

		// Others are not allowed to publish schedules at all
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'publish', '?' => ['division' => $division->id, 'date' => $date->toDateString()]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Schedules', 'action' => 'publish', '?' => ['division' => $division->id, 'date' => $date->toDateString()]]);
	}

	/**
	 * Test unpublish method as an admin
	 */
	public function testUnpublishAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);
		$date = $game->game_slot->game_date;
		$division = $game->division;

		$this->assertTrue($game->published);

		/** @var \App\Model\Entity\Game $affiliate_game */
		$affiliate_game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[1],
		]);
		$affiliate_division = $affiliate_game->division;

		// Admins are allowed to unpublish schedules anywhere
		$this->assertGetAsAccessRedirect(['controller' => 'Schedules', 'action' => 'unpublish', '?' => ['division' => $division->id, 'date' => $date->toDateString()]],
			$admin->id, ['controller' => 'Divisions', 'action' => 'schedule', '?' => ['division' => $division->id]],
			'#Unpublished games on the requested date.#');

		/** @var Game $game */
		$game = TableRegistry::getTableLocator()->get('Games')->get($game->id);
		$this->assertFalse($game->published);

		$this->assertGetAsAccessRedirect(['controller' => 'Schedules', 'action' => 'unpublish', '?' => ['division' => $affiliate_division->id, 'date' => $date->toDateString()]],
			$admin->id, ['controller' => 'Divisions', 'action' => 'schedule', '?' => ['division' => $affiliate_division->id]],
			'#Unpublished games on the requested date.#');
	}

	/**
	 * Test unpublish method as a manager
	 */
	public function testUnpublishAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);
		$date = $game->game_slot->game_date;
		$division = $game->division;

		/** @var \App\Model\Entity\Game $affiliate_game */
		$affiliate_game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[1],
		]);
		$affiliate_division = $affiliate_game->division;

		// Managers are allowed to unpublish schedules anywhere
		$this->assertGetAsAccessRedirect(['controller' => 'Schedules', 'action' => 'unpublish', '?' => ['division' => $division->id, 'date' => $date->toDateString()]],
			$manager->id, ['controller' => 'Divisions', 'action' => 'schedule', '?' => ['division' => $division->id]],
			'#Unpublished games on the requested date.#');

		// But not in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'unpublish', '?' => ['division' => $affiliate_division->id, 'date' => $date->toDateString()]], $manager->id);
	}

	/**
	 * Test unpublish method as a coordinator
	 */
	public function testUnpublishAsCoordinator(): void {
		[$admin, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer']);

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'coordinator' => $volunteer,
		]);
		$date = $game->game_slot->game_date;
		$division = $game->division;

		/** @var \App\Model\Entity\Game $other_game */
		$other_game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);
		$other_division = $other_game->division;

		// Coordinators are allowed to unpublish schedules in their own divisions
		$this->assertGetAsAccessRedirect(['controller' => 'Schedules', 'action' => 'unpublish', '?' => ['division' => $division->id, 'date' => $date->toDateString()]],
			$volunteer->id, ['controller' => 'Divisions', 'action' => 'schedule', '?' => ['division' => $division->id]],
			'#Unpublished games on the requested date.#');

		// But not in other divisions
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'unpublish', '?' => ['division' => $other_division->id, 'date' => $date->toDateString()]], $volunteer->id);
	}

	/**
	 * Test unpublish method as others
	 */
	public function testUnpublishAsOthers(): void {
		[$admin, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'player']);
		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);
		$date = $game->game_slot->game_date;
		$division = $game->division;

		// Others are not allowed to unpublish schedules at all
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'unpublish', '?' => ['division' => $division->id, 'date' => $date->toDateString()]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Schedules', 'action' => 'unpublish', '?' => ['division' => $division->id, 'date' => $date->toDateString()]]);
	}

	/**
	 * Test today method
	 */
	public function testToday(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithMinimalScheduleScenario::class, ['affiliate' => $admin->affiliates[0], 'coordinator' => $volunteer]);
		$season = $league->divisions[0];
		$game = $season->games[0];
		FrozenDate::setTestNow($game->game_slot->game_date);

		// Anyone is allowed to get today's schedule
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'today'], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'today'], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'today'], $volunteer->id);
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'today'], $player->id);
		$this->assertGetAnonymousAccessOk(['controller' => 'Schedules', 'action' => 'today']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test day method
	 */
	public function testDay(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithMinimalScheduleScenario::class, ['affiliate' => $admin->affiliates[0], 'coordinator' => $volunteer]);
		$season = $league->divisions[0];
		$game = $season->games[0];
		$date = $game->game_slot->game_date;

		// Anyone is allowed to get a day's schedule
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'day', '?' => ['date' => $date->toDateString()]], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'day', '?' => ['date' => $date->toDateString()]], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'day', '?' => ['date' => $date->toDateString()]], $volunteer->id);
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'day', '?' => ['date' => $date->toDateString()]], $player->id);
		$this->assertGetAnonymousAccessOk(['controller' => 'Schedules', 'action' => 'day', '?' => ['date' => $date->toDateString()]]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

}
