<?php
namespace App\Test\TestCase\Controller;

use App\Test\Factory\DivisionFactory;
use App\Test\Factory\FranchiseFactory;
use App\Test\Factory\FranchisesTeamFactory;
use App\Test\Factory\TeamFactory;
use App\Test\Factory\TeamsPersonFactory;
use App\Test\Scenario\DiverseUsersScenario;
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\TableRegistry;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\FranchisesController Test Case
 */
class FranchisesControllerTest extends ControllerTestCase {

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
	 * Test index method
	 *
	 * @return void
	 * @throws \PHPUnit\Exception
	 */
	public function testIndex(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Anyone is allowed to get the index
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'index'], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'index'], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'index'], $volunteer->id);
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'index'], $player->id);
		$this->assertGetAnonymousAccessOk(['controller' => 'Franchises', 'action' => 'index']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test letter method
	 *
	 * @return void
	 * @throws \PHPUnit\Exception
	 */
	public function testLetter(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Anyone is allowed to get the list by letter
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'letter', 'letter' => 'B'], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'letter', 'letter' => 'B'], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'letter', 'letter' => 'B'], $volunteer->id);
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'letter', 'letter' => 'B'], $player->id);
		$this->assertGetAnonymousAccessOk(['controller' => 'Franchises', 'action' => 'letter', 'letter' => 'B']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test view method
	 *
	 * @return void
	 * @throws \PHPUnit\Exception
	 */
	public function testView(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Franchise $franchise */
		$franchise = FranchiseFactory::make(['affiliate_id' => $affiliates[0]->id])
			->with('Teams')
			->with('People.Users')
			->persist();
		$owner = $franchise->people[0];

		/** @var \App\Model\Entity\Franchise $other_franchise */
		$other_franchise = FranchiseFactory::make(['affiliate_id' => $affiliates[1]->id])
			->with('Teams')
			->persist();

		// Admins are allowed to view franchises, with full edit permissions
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'view', 'franchise' => $franchise->id], $admin->id);
		$this->assertResponseContains('/franchises/edit?franchise=' . $franchise->id);
		$this->assertResponseContains('/franchises/delete?franchise=' . $franchise->id);

		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'view', 'franchise' => $other_franchise->id], $admin->id);
		$this->assertResponseContains('/franchises/edit?franchise=' . $other_franchise->id);
		$this->assertResponseContains('/franchises/delete?franchise=' . $other_franchise->id);

		// Managers are allowed to view franchises
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'view', 'franchise' => $franchise->id], $manager->id);
		$this->assertResponseContains('/franchises/edit?franchise=' . $franchise->id);
		$this->assertResponseContains('/franchises/delete?franchise=' . $franchise->id);

		// But are not allowed to edit ones in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'view', 'franchise' => $other_franchise->id], $manager->id);
		$this->assertResponseNotContains('/franchises/edit?franchise=' . $other_franchise->id);
		$this->assertResponseNotContains('/franchises/delete?franchise=' . $other_franchise->id);

		// Owners are allowed to view and edit their franchises, but not delete; that happens automatically if the last team is removed
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'view', 'franchise' => $franchise->id], $owner->id);
		$this->assertResponseContains('/franchises/edit?franchise=' . $franchise->id);
		$this->assertResponseNotContains('/franchises/delete?franchise=' . $franchise->id);

		// Others are allowed to view franchises, but have no edit permissions
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'view', 'franchise' => $franchise->id], $player->id);
		$this->assertResponseNotContains('/franchises/edit?franchise=' . $franchise->id);
		$this->assertResponseNotContains('/franchises/delete?franchise=' . $franchise->id);
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'view', 'franchise' => $franchise->id], $volunteer->id);
		$this->assertResponseNotContains('/franchises/edit?franchise=' . $franchise->id);
		$this->assertResponseNotContains('/franchises/delete?franchise=' . $franchise->id);

		// Others are allowed to view
		$this->assertGetAnonymousAccessOk(['controller' => 'Franchises', 'action' => 'view', 'franchise' => $franchise->id]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add method as an admin
	 *
	 * @return void
	 * @throws \PHPUnit\Exception
	 */
	public function testAddAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Admins are allowed to add franchises
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'add'], $admin->id);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 * @throws \PHPUnit\Exception
	 */
	public function testAddAsManager(): void {
		[, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Managers are allowed to add franchises
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'add'], $manager->id);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as a coordinator
	 *
	 * @return void
	 * @throws \PHPUnit\Exception
	 */
	public function testAddAsCoordinator(): void {
		[, , $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Coordinators are allowed to add franchises
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'add'], $volunteer->id);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as a player
	 *
	 * @return void
	 * @throws \PHPUnit\Exception
	 */
	public function testAddAsPlayer(): void {
		[, , , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Players are allowed to add franchises
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'add'], $player->id);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method without being logged in
	 *
	 * @return void
	 */
	public function testAddAsAnonymous(): void {
		// Others are not allowed to add franchises
		$this->assertGetAnonymousAccessDenied(['controller' => 'Franchises', 'action' => 'add']);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as an admin
	 *
	 * @return void
	 * @throws \PHPUnit\Exception
	 */
	public function testEditAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Franchise $franchise */
		$franchise = FranchiseFactory::make(['affiliate_id' => $affiliates[0]->id])->persist();
		/** @var \App\Model\Entity\Franchise $other_franchise */
		$other_franchise = FranchiseFactory::make(['affiliate_id' => $affiliates[1]->id])->persist();

		// Admins are allowed to edit franchises
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'edit', 'franchise' => $franchise->id], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'edit', 'franchise' => $other_franchise->id], $admin->id);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 * @throws \PHPUnit\Exception
	 */
	public function testEditAsManager(): void {
		[$admin, $manager,] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Franchise $franchise */
		$franchise = FranchiseFactory::make(['affiliate_id' => $affiliates[0]->id])->persist();
		/** @var \App\Model\Entity\Franchise $other_franchise */
		$other_franchise = FranchiseFactory::make(['affiliate_id' => $affiliates[1]->id])->persist();

		// Managers are allowed to edit franchises
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'edit', 'franchise' => $franchise->id], $manager->id);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Franchises', 'action' => 'edit', 'franchise' => $other_franchise->id], $manager->id);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as the owner
	 *
	 * @return void
	 * @throws \PHPUnit\Exception
	 */
	public function testEditAsOwner(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Franchise $franchise */
		$franchise = FranchiseFactory::make(['affiliate_id' => $affiliates[0]->id])
			->with('People.Users')
			->persist();
		$owner = $franchise->people[0];

		/** @var \App\Model\Entity\Franchise $other_franchise */
		$other_franchise = FranchiseFactory::make(['affiliate_id' => $affiliates[0]->id])->persist();

		// Captains are allowed to edit their own franchises only
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'edit', 'franchise' => $franchise->id], $owner->id);
		$this->assertGetAsAccessDenied(['controller' => 'Franchises', 'action' => 'edit', 'franchise' => $other_franchise->id], $owner->id);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as others
	 *
	 * @return void
	 */
	public function testEditAsOthers(): void {
		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Franchise $franchise */
		$franchise = FranchiseFactory::make(['affiliate_id' => $affiliates[0]->id])->persist();

		// Others are not allowed to edit franchises
		$this->assertGetAsAccessDenied(['controller' => 'Franchises', 'action' => 'edit', 'franchise' => $franchise->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Franchises', 'action' => 'edit', 'franchise' => $franchise->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Franchises', 'action' => 'edit', 'franchise' => $franchise->id]);
	}

	/**
	 * Test delete method as an admin
	 *
	 * @return void
	 */
	public function testDeleteAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Franchise $franchise */
		$franchise = FranchiseFactory::make(['affiliate_id' => $affiliates[0]->id])
			->with('People.Users')
			->persist();

		/** @var \App\Model\Entity\Franchise $other_franchise */
		$other_franchise = FranchiseFactory::make(['affiliate_id' => $affiliates[1]->id])
			->with('People')
			->with('Teams')
			->persist();

		// Admins are allowed to delete franchises
		$this->assertPostAsAccessRedirect(['controller' => 'Franchises', 'action' => 'delete', 'franchise' => $franchise->id],
			$admin->id, [], ['controller' => 'Franchises', 'action' => 'index'],
			'The franchise has been deleted.');

		// But not ones with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'Franchises', 'action' => 'delete', 'franchise' => $other_franchise->id],
			$admin->id, [], ['controller' => 'Franchises', 'action' => 'index'],
			'#The following records reference this franchise, so it cannot be deleted#');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Franchise $franchise */
		$franchise = FranchiseFactory::make(['affiliate_id' => $affiliates[0]->id])->persist();
		/** @var \App\Model\Entity\Franchise $other_franchise */
		$other_franchise = FranchiseFactory::make(['affiliate_id' => $affiliates[1]->id])->persist();

		// Managers are allowed to delete franchises in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Franchises', 'action' => 'delete', 'franchise' => $franchise->id],
			$manager->id, [], ['controller' => 'Franchises', 'action' => 'index'],
			'The franchise has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Franchises', 'action' => 'delete', 'franchise' => $other_franchise->id],
			$manager->id);
	}

	/**
	 * Test delete method as others
	 *
	 * @return void
	 */
	public function testDeleteAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Franchise $franchise */
		$franchise = FranchiseFactory::make(['affiliate_id' => $affiliates[0]->id])
			->with('People.Users')
			->persist();
		$owner = $franchise->people[0];

		// Others are not allowed to delete franchises. Captains are allowed to do so, but only indirectly by removing the last team in it.
		$this->assertPostAsAccessDenied(['controller' => 'Franchises', 'action' => 'delete', 'franchise' => $franchise->id],
			$volunteer->id);
		$this->assertPostAsAccessDenied(['controller' => 'Franchises', 'action' => 'delete', 'franchise' => $franchise->id],
			$player->id);
		$this->assertPostAsAccessDenied(['controller' => 'Franchises', 'action' => 'delete', 'franchise' => $franchise->id],
			$owner->id);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Franchises', 'action' => 'delete', 'franchise' => $franchise->id]);
	}

	/**
	 * Test add_team method as franchise owner
	 *
	 * @return void
	 * @throws \PHPUnit\Exception
	 */
	public function testAddTeamAsOwner(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Franchise $franchise */
		$franchise = FranchiseFactory::make(['affiliate_id' => $affiliates[0]->id])
			->with('People.Users')
			->persist();
		$owner = $franchise->people[0];

		/** @var \App\Model\Entity\Team $team */
		$team = TeamFactory::make()
			->with('Divisions', DivisionFactory::make()->with('Leagues', ['affiliate_id' => $affiliates[0]->id]))
			->persist();
		TeamsPersonFactory::make(['person_id' => $owner->id, 'team_id' => $team->id, 'role' => 'captain'])->persist();

		// Franchise owners are allowed to add teams
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'add_team', 'franchise' => $franchise->id], $owner->id);
		$this->assertResponseContains("<option value=\"{$team->id}\">{$team->name} ({$team->division->full_league_name})</option>");

		// Post the form
		$this->assertPostAsAccessRedirect(['controller' => 'Franchises', 'action' => 'add_team', 'franchise' => $franchise->id],
			$owner->id, [
				'team_id' => $team->id,
			], ['controller' => 'Franchises', 'action' => 'view', 'franchise' => $franchise->id],
			'The selected team has been added to this franchise.');

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'view', 'franchise' => $franchise->id], $owner->id);
		$this->assertResponseContains('/franchises/remove_team?franchise=' . $franchise->id . '&amp;team=' . $team->id);
	}

	/**
	 * Test add_team method as others
	 *
	 * @return void
	 */
	public function testAddTeamAsOthers(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Franchise $franchise */
		$franchise = FranchiseFactory::make(['affiliate_id' => $affiliates[0]->id])->persist();

		// Others are not allowed to add teams
		$this->assertGetAsAccessDenied(['controller' => 'Franchises', 'action' => 'add_team', 'franchise' => $franchise->id], $admin->id);
		$this->assertGetAsAccessDenied(['controller' => 'Franchises', 'action' => 'add_team', 'franchise' => $franchise->id], $manager->id);
		$this->assertGetAsAccessDenied(['controller' => 'Franchises', 'action' => 'add_team', 'franchise' => $franchise->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Franchises', 'action' => 'add_team', 'franchise' => $franchise->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Franchises', 'action' => 'add_team', 'franchise' => $franchise->id]);
	}

	/**
	 * Test remove_team method as an admin
	 *
	 * @return void
	 */
	public function testRemoveTeamAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Franchise $franchise */
		$franchise = FranchiseFactory::make(['affiliate_id' => $affiliates[0]->id])
			->with('Teams[2]')
			->persist();
		$franchise2 = FranchiseFactory::make(['affiliate_id' => $affiliates[0]->id])->persist();
		FranchisesTeamFactory::make(['franchise_id' => $franchise2->id, 'team_id' => $franchise->teams[0]->id])->persist();

		// Admins are allowed to remove teams
		$this->assertPostAsAccessRedirect(['controller' => 'Franchises', 'action' => 'remove_team', 'franchise' => $franchise->id, 'team' => $franchise->teams[0]->id],
			$admin->id, [], ['controller' => 'Franchises', 'action' => 'view', 'franchise' => $franchise->id],
			'The selected team has been removed from this franchise.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_team method as a manager
	 *
	 * @return void
	 */
	public function testRemoveTeamAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Franchise $franchise */
		$franchise = FranchiseFactory::make(['affiliate_id' => $affiliates[0]->id])
			->with('Teams[2]')
			->persist();
		$franchise2 = FranchiseFactory::make(['affiliate_id' => $affiliates[0]->id])->persist();
		FranchisesTeamFactory::make(['franchise_id' => $franchise2->id, 'team_id' => $franchise->teams[0]->id])->persist();

		// Managers are allowed to remove teams
		$this->assertPostAsAccessRedirect(['controller' => 'Franchises', 'action' => 'remove_team', 'franchise' => $franchise->id, 'team' => $franchise->teams[0]->id],
			$manager->id, [], ['controller' => 'Franchises', 'action' => 'view', 'franchise' => $franchise->id],
			'The selected team has been removed from this franchise.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_team method as franchise owner
	 *
	 * @return void
	 */
	public function testRemoveTeamAsOwner(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Franchise $franchise */
		$franchise = FranchiseFactory::make(['affiliate_id' => $affiliates[0]->id])
			->with('People.Users')
			->with('Teams')
			->persist();
		$owner = $franchise->people[0];
		$franchise2 = FranchiseFactory::make(['affiliate_id' => $affiliates[0]->id])->persist();
		FranchisesTeamFactory::make(['franchise_id' => $franchise2->id, 'team_id' => $franchise->teams[0]->id])->persist();

		// Franchise owners are allowed to remove teams
		$this->assertPostAsAccessRedirect(['controller' => 'Franchises', 'action' => 'remove_team', 'franchise' => $franchise->id, 'team' => $franchise->teams[0]->id],
			$owner->id, [], '/',
			'The selected team has been removed from this franchise. As there were no other teams in the franchise, it has been deleted as well.');
		$this->expectException(RecordNotFoundException::class);
		TableRegistry::getTableLocator()->get('Franchises')->get($franchise->id);
	}

	/**
	 * Test remove_team method as others
	 *
	 * @return void
	 */
	public function testRemoveTeamAsOther(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Franchise $franchise */
		$franchise = FranchiseFactory::make(['affiliate_id' => $affiliates[0]->id])
			->with('Teams[2]')
			->persist();
		$franchise2 = FranchiseFactory::make(['affiliate_id' => $affiliates[0]->id])->persist();
		FranchisesTeamFactory::make(['franchise_id' => $franchise2->id, 'team_id' => $franchise->teams[0]->id])->persist();

		// Others are not allowed to remove teams
		$this->assertPostAsAccessDenied(['controller' => 'Franchises', 'action' => 'remove_team', 'franchise' => $franchise->id, 'team' => $franchise->teams[0]->id],
			$volunteer->id);
		$this->assertPostAsAccessDenied(['controller' => 'Franchises', 'action' => 'remove_team', 'franchise' => $franchise->id, 'team' => $franchise->teams[0]->id],
			$player->id);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Franchises', 'action' => 'remove_team', 'franchise' => $franchise->id, 'team' => $franchise->teams[0]->id]);
	}

	/**
	 * Test add_owner method as an admin
	 *
	 * @return void
	 * @throws \PHPUnit\Exception
	 */
	public function testAddOwnerAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Franchise $franchise */
		$franchise = FranchiseFactory::make(['affiliate_id' => $affiliates[0]->id])->persist();

		// Admins are allowed to add owner
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'add_owner', 'franchise' => $franchise->id], $admin->id);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_owner method as a manager
	 *
	 * @return void
	 * @throws \PHPUnit\Exception
	 */
	public function testAddOwnerAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Franchise $franchise */
		$franchise = FranchiseFactory::make(['affiliate_id' => $affiliates[0]->id])->persist();

		// Managers are allowed to add owner
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'add_owner', 'franchise' => $franchise->id], $manager->id);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_owner method as franchise owner
	 *
	 * @return void
	 * @throws \PHPUnit\Exception
	 */
	public function testAddOwnerAsOwner(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, , , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Franchise $franchise */
		$franchise = FranchiseFactory::make(['affiliate_id' => $affiliates[0]->id])
			->with('People.Users')
			->persist();
		$owner = $franchise->people[0];

		// Franchise owners are allowed to add owners
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'add_owner', 'franchise' => $franchise->id], $owner->id);

		// Try the search page
		$this->assertPostAsAccessOk(['controller' => 'Franchises', 'action' => 'add_owner', 'franchise' => $franchise->id],
			$owner->id, [
				'affiliate_id' => $affiliates[0]->id,
				'first_name' => '',
				'last_name' => $player->last_name,
				'sort' => 'last_name',
				'direction' => 'asc',
			]
		);
		$return = urlencode(\App\Lib\base64_url_encode(Configure::read('App.base') . '/franchises/add_owner?franchise=' . $franchise->id));
		$this->assertResponseContains('/franchises/add_owner?person=' . $player->id . '&amp;return=' . $return . '&amp;franchise=' . $franchise->id);

		$this->assertGetAsAccessRedirect(['controller' => 'Franchises', 'action' => 'add_owner', 'person' => $player->id, 'franchise' => $franchise->id],
			$owner->id, ['controller' => 'Franchises', 'action' => 'view', 'franchise' => $franchise->id],
			"Added {$player->full_name} as owner.");

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'view', 'franchise' => $franchise->id], $owner->id);
		$this->assertResponseContains('/franchises/remove_owner?franchise=' . $franchise->id . '&amp;person=' . $player->id);
	}

	/**
	 * Test add_owner method as others
	 *
	 * @return void
	 */
	public function testAddOwnerAsOthers(): void {
		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Franchise $franchise */
		$franchise = FranchiseFactory::make(['affiliate_id' => $affiliates[0]->id])->persist();

		// Others are not allowed to add owners
		$this->assertGetAsAccessDenied(['controller' => 'Franchises', 'action' => 'add_owner', 'franchise' => $franchise->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Franchises', 'action' => 'add_owner', 'franchise' => $franchise->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Franchises', 'action' => 'add_owner', 'franchise' => $franchise->id]);
	}

	/**
	 * Test remove_owner method as an admin
	 *
	 * @return void
	 */
	public function testRemoveOwnerAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Franchise $franchise */
		$franchise = FranchiseFactory::make(['affiliate_id' => $affiliates[0]->id])
			->with('People[2].Users')
			->persist();
		[$owner, $owner2] = $franchise->people;

		// Admins are allowed to remove owners
		$this->assertPostAsAccessRedirect(['controller' => 'Franchises', 'action' => 'remove_owner', 'franchise' => $franchise->id, 'person' => $owner->id],
			$admin->id, [], ['controller' => 'Franchises', 'action' => 'view', 'franchise' => $franchise->id],
			'Successfully removed owner.');

		// But not the last owner
		$this->assertPostAsAccessRedirect(['controller' => 'Franchises', 'action' => 'remove_owner', 'franchise' => $franchise->id, 'person' => $owner2->id],
			$admin->id, [], ['controller' => 'Franchises', 'action' => 'view', 'franchise' => $franchise->id],
			'You cannot remove the only owner of a franchise!');

		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_owner method as a manager
	 *
	 * @return void
	 */
	public function testRemoveOwnerAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Franchise $franchise */
		$franchise = FranchiseFactory::make(['affiliate_id' => $affiliates[0]->id])
			->with('People[2].Users')
			->persist();
		$owner = $franchise->people[0];

		// Managers are allowed to remove owners
		$this->assertPostAsAccessRedirect(['controller' => 'Franchises', 'action' => 'remove_owner', 'franchise' => $franchise->id, 'person' => $owner->id],
			$manager->id, [], ['controller' => 'Franchises', 'action' => 'view', 'franchise' => $franchise->id],
			'Successfully removed owner.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_owner method as franchise owner
	 *
	 * @return void
	 */
	public function testRemoveOwnerAsOwner(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Franchise $franchise */
		$franchise = FranchiseFactory::make(['affiliate_id' => $affiliates[0]->id])
			->with('People[2].Users')
			->persist();
		[$owner, $owner2] = $franchise->people;

		// Franchise owners are allowed to remove other owners
		$this->assertPostAsAccessRedirect(['controller' => 'Franchises', 'action' => 'remove_owner', 'franchise' => $franchise->id, 'person' => $owner2->id],
			$owner->id, [], ['controller' => 'Franchises', 'action' => 'view', 'franchise' => $franchise->id],
			'Successfully removed owner.');
	}

	/**
	 * Test remove_owner method as others
	 *
	 * @return void
	 */
	public function testRemoveOwnerAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var \App\Model\Entity\Franchise $franchise */
		$franchise = FranchiseFactory::make(['affiliate_id' => $affiliates[0]->id])
			->with('People[2].Users')
			->persist();
		$owner = $franchise->people[0];

		// Others are not allowed to remove owners
		$this->assertPostAsAccessDenied(['controller' => 'Franchises', 'action' => 'remove_owner', 'franchise' => $franchise->id, 'person' => $owner->id],
			$volunteer->id);
		$this->assertPostAsAccessDenied(['controller' => 'Franchises', 'action' => 'remove_owner', 'franchise' => $franchise->id, 'person' => $owner->id],
			$player->id);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Franchises', 'action' => 'remove_owner', 'franchise' => $franchise->id, 'person' => $owner->id]);
	}

}
