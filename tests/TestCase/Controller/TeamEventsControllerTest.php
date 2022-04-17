<?php
namespace App\Test\TestCase\Controller;

use App\Model\Entity\TeamEvent;
use App\Test\Factory\AttendanceFactory;
use App\Test\Factory\TeamEventFactory;
use App\Test\Scenario\DiverseUsersScenario;
use App\Test\Scenario\SingleGameScenario;
use Cake\I18n\FrozenTime;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\TeamEventsController Test Case
 */
class TeamEventsControllerTest extends ControllerTestCase {

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
	 * Test view method
	 */
	public function testView(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Easy way to set up a whole team structure
		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'coordinator' => $volunteer,
			'home_captain' => true,
			'home_player' => $player,
		]);
		$captain = $game->home_team->people[0];

		$events = TeamEventFactory::make([
			['team_id' => $game->home_team_id],
			['team_id' => $game->away_team_id],
		])
			->persist();

		// Admins are allowed to view
		$this->assertGetAsAccessOk(['controller' => 'TeamEvents', 'action' => 'view', 'event' => $events[0]->id], $admin->id);

		// Managers are allowed to view
		$this->assertGetAsAccessOk(['controller' => 'TeamEvents', 'action' => 'view', 'event' => $events[0]->id], $manager->id);

		// Captains from the team in question are allowed to view their team's events, with full edit permissions
		$this->assertGetAsAccessOk(['controller' => 'TeamEvents', 'action' => 'view', 'event' => $events[0]->id], $captain->id);
		$this->assertResponseContains('/team_events/edit?event=' . $events[0]->id);
		$this->assertResponseContains('/team_events/delete?event=' . $events[0]->id);

		// But not other team's events
		$this->assertGetAsAccessDenied(['controller' => 'TeamEvents', 'action' => 'view', 'event' => $events[1]->id], $captain->id);

		// Players are allowed to view their team's events, but have no edit permissions
		$this->assertGetAsAccessOk(['controller' => 'TeamEvents', 'action' => 'view', 'event' => $events[0]->id], $player->id);
		$this->assertResponseNotContains('/team_events/edit?event=' . $events[0]->id);
		$this->assertResponseNotContains('/team_events/delete?event=' . $events[0]->id);

		// Others are not allowed to view
		$this->assertGetAsAccessDenied(['controller' => 'TeamEvents', 'action' => 'view', 'event' => $events[0]->id], $volunteer->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'TeamEvents', 'action' => 'view', 'event' => $events[0]->id]);
	}

	/**
	 * Test add method as an admin
	 */
	public function testAddAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		// Admins are allowed to add events
		$this->assertGetAsAccessOk(['controller' => 'TeamEvents', 'action' => 'add', 'team' => $game->home_team_id], $admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add method as a manager
	 */
	public function testAddAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		// Managers are allowed to add events
		$this->assertGetAsAccessOk(['controller' => 'TeamEvents', 'action' => 'add', 'team' => $game->home_team_id], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add method as a captain
	 */
	public function testAddAsCaptain(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'home_captain' => true,
		]);
		$captain = $game->home_team->people[0];

		// Captains are allowed to add events to their own teams
		$this->assertGetAsAccessOk(['controller' => 'TeamEvents', 'action' => 'add', 'team' => $game->home_team_id], $captain->id);
		$this->assertGetAsAccessDenied(['controller' => 'TeamEvents', 'action' => 'add', 'team' => $game->away_team_id], $captain->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add method as others
	 */
	public function testAddAsOthers(): void {
		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'coordinator' => $volunteer,
		]);

		// Others are not allowed to add events
		$this->assertGetAsAccessDenied(['controller' => 'TeamEvents', 'action' => 'add', 'team' => $game->home_team_id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'TeamEvents', 'action' => 'add', 'team' => $game->home_team_id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'TeamEvents', 'action' => 'add', 'team' => $game->home_team_id]);
	}

	/**
	 * Test edit method as an admin
	 */
	public function testEditAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		$events = TeamEventFactory::make([
			['team_id' => $game->home_team_id],
			['team_id' => $game->away_team_id],
		])
			->persist();

		// Admins are allowed to edit team events
		$this->assertGetAsAccessOk(['controller' => 'TeamEvents', 'action' => 'edit', 'event' => $events[0]->id], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'TeamEvents', 'action' => 'edit', 'event' => $events[1]->id], $admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test edit method as a manager
	 */
	public function testEditAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		$event = TeamEventFactory::make(['team_id' => $game->home_team_id])
			->persist();

		/** @var \App\Model\Entity\Game $affiliate_game */
		$affiliate_game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[1],
		]);

		$affiliate_event = TeamEventFactory::make(['team_id' => $affiliate_game->home_team_id])
			->persist();

		// Managers are allowed to edit team events in their affiliate
		$this->assertGetAsAccessOk(['controller' => 'TeamEvents', 'action' => 'edit', 'event' => $event->id], $manager->id);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'TeamEvents', 'action' => 'edit', 'event' => $affiliate_event->id], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test edit method as a captain
	 */
	public function testEditAsCaptain(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'home_captain' => true,
		]);
		$captain = $game->home_team->people[0];

		$events = TeamEventFactory::make([
			['team_id' => $game->home_team_id],
			['team_id' => $game->away_team_id],
		])
			->persist();

		// Captains are allowed to edit their own team's events
		$this->assertGetAsAccessOk(['controller' => 'TeamEvents', 'action' => 'edit', 'event' => $events[0]->id], $captain->id);
		$this->assertGetAsAccessDenied(['controller' => 'TeamEvents', 'action' => 'edit', 'event' => $events[1]->id], $captain->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test edit method as others
	 */
	public function testEditAsOthers(): void {
		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'home_player' => $player,
		]);

		$event = TeamEventFactory::make(['team_id' => $game->home_team_id])
			->persist();

		// Others are not allowed to edit
		$this->assertGetAsAccessDenied(['controller' => 'TeamEvents', 'action' => 'edit', 'event' => $event->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'TeamEvents', 'action' => 'edit', 'event' => $event->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'TeamEvents', 'action' => 'edit', 'event' => $event->id]);
	}

	/**
	 * Test delete method as an admin
	 */
	public function testDeleteAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		$event = TeamEventFactory::make(['team_id' => $game->home_team_id])
			->persist();

		// Admins are allowed to delete team events
		$this->assertPostAsAccessRedirect(['controller' => 'TeamEvents', 'action' => 'delete', 'event' => $event->id],
			$admin->id, [], '/',
			'The team event has been deleted.');
	}

	/**
	 * Test delete method as a manager
	 */
	public function testDeleteAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		$event = TeamEventFactory::make(['team_id' => $game->home_team_id])
			->persist();

		/** @var \App\Model\Entity\Game $affiliate_game */
		$affiliate_game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[1],
		]);

		$affiliate_event = TeamEventFactory::make(['team_id' => $affiliate_game->home_team_id])
			->persist();

		// Managers are allowed to delete team events in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'TeamEvents', 'action' => 'delete', 'event' => $event->id],
			$manager->id, [], '/',
			'The team event has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'TeamEvents', 'action' => 'delete', 'event' => $affiliate_event->id],
			$manager->id);
	}

	/**
	 * Test delete method as a captain
	 */
	public function testDeleteAsCaptain(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'home_captain' => true,
		]);
		$captain = $game->home_team->people[0];

		$events = TeamEventFactory::make([
			['team_id' => $game->home_team_id],
			['team_id' => $game->away_team_id],
		])
			->persist();

		// Captains are allowed to delete their team's events
		$this->assertPostAsAccessRedirect(['controller' => 'TeamEvents', 'action' => 'delete', 'event' => $events[0]->id],
			$captain->id, [], '/',
			'The team event has been deleted.');

		$this->assertPostAsAccessDenied(['controller' => 'TeamEvents', 'action' => 'delete', 'event' => $events[1]->id], $captain->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test delete method as others
	 */
	public function testDeleteAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'coordinator' => $volunteer,
			'home_player' => $player,
		]);

		$event = TeamEventFactory::make(['team_id' => $game->home_team_id])
			->persist();

		// Others are not allowed to delete team events
		$this->assertPostAsAccessDenied(['controller' => 'TeamEvents', 'action' => 'delete', 'event' => $event->id],
			$volunteer->id);
		$this->assertPostAsAccessDenied(['controller' => 'TeamEvents', 'action' => 'delete', 'event' => $event->id],
			$player->id);
		$this->assertPostAnonymousAccessDenied(['controller' => 'TeamEvents', 'action' => 'delete', 'event' => $event->id]);
	}

	/**
	 * Test attendance_change method as an admin
	 */
	public function testAttendanceChangeAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'home_captain' => true,
		]);
		$captain = $game->home_team->people[0];

		/** @var TeamEvent $event */
		$event = TeamEventFactory::make(['team_id' => $game->home_team_id])
			->persist();
		AttendanceFactory::make(['team_id' => $event->team_id, 'team_event_id' => $event->id, 'person_id' => $captain->id, 'status' => ATTENDANCE_ATTENDING])
			->persist();

		// Admins are allowed to change attendance
		$this->assertGetAsAccessOk(['controller' => 'TeamEvents', 'action' => 'attendance_change', 'event' => $event->id, 'person' => $captain->id], $admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test attendance_change method as a manager
	 */
	public function testAttendanceChangeAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'home_captain' => true,
		]);
		$captain = $game->home_team->people[0];

		/** @var TeamEvent $event */
		$event = TeamEventFactory::make(['team_id' => $game->home_team_id])
			->persist();
		AttendanceFactory::make(['team_id' => $event->team_id, 'team_event_id' => $event->id, 'person_id' => $captain->id, 'status' => ATTENDANCE_ATTENDING])
			->persist();

		// Managers are allowed to change attendance
		$this->assertGetAsAccessOk(['controller' => 'TeamEvents', 'action' => 'attendance_change', 'event' => $event->id, 'person' => $captain->id], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test attendance_change method as a coordinator
	 * TODO: Why can coordinnators change attendance when they can't even see the event? Work to do here on practices for youth leagues?
	 */
	public function testAttendanceChangeAsCoordinator(): void {
		[$admin, , $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'coordinator' => $volunteer,
			'home_captain' => true,
		]);
		$captain = $game->home_team->people[0];

		/** @var TeamEvent $event */
		$event = TeamEventFactory::make(['team_id' => $game->home_team_id])
			->persist();
		AttendanceFactory::make(['team_id' => $event->team_id, 'team_event_id' => $event->id, 'person_id' => $captain->id, 'status' => ATTENDANCE_ATTENDING])
			->persist();

		// Coordinators are allowed to change attendance
		$this->assertGetAsAccessOk(['controller' => 'TeamEvents', 'action' => 'attendance_change', 'event' => $event->id, 'person' => $captain->id], $volunteer->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test attendance_change method as a captain
	 */
	public function testAttendanceChangeAsCaptain(): void {
		[$admin, , , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'home_captain' => true,
			'home_player' => $player,
		]);
		$captain = $game->home_team->people[0];

		/** @var TeamEvent $event */
		$event = TeamEventFactory::make(['team_id' => $game->home_team_id])
			->persist();
		AttendanceFactory::make([
			['team_id' => $event->team_id, 'team_event_id' => $event->id, 'person_id' => $captain->id, 'status' => ATTENDANCE_ATTENDING],
			['team_id' => $event->team_id, 'team_event_id' => $event->id, 'person_id' => $player->id, 'status' => ATTENDANCE_ATTENDING],
		])
			->persist();

		// Captains are allowed to change attendance
		$this->assertGetAsAccessOk(['controller' => 'TeamEvents', 'action' => 'attendance_change', 'event' => $event->id], $captain->id);
		$this->assertGetAsAccessOk(['controller' => 'TeamEvents', 'action' => 'attendance_change', 'event' => $event->id, 'person' => $player->id], $captain->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test attendance_change method as a player
	 */
	public function testAttendanceChangeAsPlayer(): void {
		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[0],
			'coordinator' => $volunteer,
			'home_player' => $player,
		]);

		/** @var TeamEvent[] $events */
		$events = TeamEventFactory::make([
			['team_id' => $game->home_team_id],
			['team_id' => $game->away_team_id],
		])
			->persist();
		AttendanceFactory::make(['team_id' => $events[0]->team_id, 'team_event_id' => $events[0]->id, 'person_id' => $player->id, 'status' => ATTENDANCE_ATTENDING])
			->persist();

		// Players are allowed to change attendance
		$this->assertGetAsAccessOk(['controller' => 'TeamEvents', 'action' => 'attendance_change', 'event' => $events[0]->id], $player->id);

		// But not for teams they're not on at all
		$this->assertGetAsAccessDenied(['controller' => 'TeamEvents', 'action' => 'attendance_change', 'event' => $events[1]->id], $player->id);

		// TODO: or only just invited to

		// And not for long after the event
		FrozenTime::setTestNow($events[0]->date->addDays(15));
		$this->assertGetAsAccessDenied(['controller' => 'TeamEvents', 'action' => 'attendance_change', 'event' => $events[0]->id], $player->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test attendance_change method as others
	 */
	public function testAttendanceChangeAsOthers(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'affiliate' => $admin->affiliates[0],
		]);

		$event = TeamEventFactory::make(['team_id' => $game->home_team_id])
			->persist();

		// Others are not allowed to change attendance
		$this->assertGetAnonymousAccessDenied(['controller' => 'TeamEvents', 'action' => 'attendance_change', 'event' => $event->id]);
	}

}
