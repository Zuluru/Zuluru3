<?php
namespace App\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\I18n\FrozenDate;

/**
 * App\Controller\FranchisesController Test Case
 */
class FranchisesControllerTest extends ControllerTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.EventTypes',
		'app.Affiliates',
			'app.Users',
				'app.People',
					'app.AffiliatesPeople',
					'app.PeoplePeople',
			'app.Groups',
				'app.GroupsPeople',
			'app.Leagues',
				'app.Divisions',
					'app.DivisionsPeople',
					'app.Teams',
						'app.TeamsPeople',
					'app.DivisionsDays',
			'app.Franchises',
				'app.FranchisesPeople',
				'app.FranchisesTeams',
			'app.Settings',
		'app.I18n',
		'app.Plugins',
	];

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex() {
		// Anyone is allowed to get the index
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'index'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'index'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'index'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'index'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessOk(['controller' => 'Franchises', 'action' => 'index']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test letter method
	 *
	 * @return void
	 */
	public function testLetter() {
		// Anyone is allowed to get the list by letter
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'letter', 'letter' => 'B'], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'letter', 'letter' => 'B'], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'letter', 'letter' => 'B'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'letter', 'letter' => 'B'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'letter', 'letter' => 'B'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'letter', 'letter' => 'B'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessOk(['controller' => 'Franchises', 'action' => 'letter', 'letter' => 'B']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testView() {
		// Admins are allowed to view franchises, with full edit permissions
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'view', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_ADMIN);
		$this->assertResponseContains('/franchises/edit?franchise=' . FRANCHISE_ID_RED);
		$this->assertResponseContains('/franchises/delete?franchise=' . FRANCHISE_ID_RED);

		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'view', 'franchise' => FRANCHISE_ID_LIONS], PERSON_ID_ADMIN);
		$this->assertResponseContains('/franchises/edit?franchise=' . FRANCHISE_ID_LIONS);
		$this->assertResponseContains('/franchises/delete?franchise=' . FRANCHISE_ID_LIONS);

		// Managers are allowed to view franchises
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'view', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_MANAGER);
		$this->assertResponseContains('/franchises/edit?franchise=' . FRANCHISE_ID_RED);
		$this->assertResponseContains('/franchises/delete?franchise=' . FRANCHISE_ID_RED);

		// But are not allowed to edit ones in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'view', 'franchise' => FRANCHISE_ID_LIONS], PERSON_ID_MANAGER);
		$this->assertResponseNotContains('/franchises/edit?franchise=' . FRANCHISE_ID_LIONS);
		$this->assertResponseNotContains('/franchises/delete?franchise=' . FRANCHISE_ID_LIONS);

		// Coordinators are allowed to view
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'view', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_COORDINATOR);

		// Owners are allowed to view and edit their franchises, but not delete; that happens automatically if the last team is removed
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'view', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_CAPTAIN);
		$this->assertResponseContains('/franchises/edit?franchise=' . FRANCHISE_ID_RED);
		$this->assertResponseNotContains('/franchises/delete?franchise=' . FRANCHISE_ID_RED);

		// Others are allowed to view franchises, but have no edit permissions
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'view', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_PLAYER);
		$this->assertResponseNotContains('/franchises/edit?franchise=' . FRANCHISE_ID_RED);
		$this->assertResponseNotContains('/franchises/delete?franchise=' . FRANCHISE_ID_RED);

		// Visitors are allowed to view
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'view', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_VISITOR);

		// Others are allowed to view
		$this->assertGetAnonymousAccessOk(['controller' => 'Franchises', 'action' => 'view', 'franchise' => FRANCHISE_ID_RED]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add method as an admin
	 *
	 * @return void
	 */
	public function testAddAsAdmin() {
		// Admins are allowed to add franchises
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'add'], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers are allowed to add franchises
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'add'], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as a coordinator
	 *
	 * @return void
	 */
	public function testAddAsCoordinator() {
		// Coordinators are allowed to add franchises
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'add'], PERSON_ID_COORDINATOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as a captain
	 *
	 * @return void
	 */
	public function testAddAsCaptain() {
		// Captains are allowed to add franchises
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'add'], PERSON_ID_CAPTAIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as a player
	 *
	 * @return void
	 */
	public function testAddAsPlayer() {
		// Players are allowed to add franchises
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'add'], PERSON_ID_PLAYER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as someone else
	 *
	 * @return void
	 */
	public function testAddAsVisitor() {
		// Visitors are allowed to add franchises
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'add'], PERSON_ID_VISITOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method without being logged in
	 *
	 * @return void
	 */
	public function testAddAsAnonymous() {
		// Others are not allowed to add franchises
		$this->assertGetAnonymousAccessDenied(['controller' => 'Franchises', 'action' => 'add']);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as an admin
	 *
	 * @return void
	 */
	public function testEditAsAdmin() {
		// Admins are allowed to edit franchises
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'edit', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers are allowed to edit franchises
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'edit', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as a captain
	 *
	 * @return void
	 */
	public function testEditAsCaptain() {
		// Captains are allowed to edit franchises
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'edit', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Franchises', 'action' => 'edit', 'franchise' => FRANCHISE_ID_BLUE], PERSON_ID_CAPTAIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as others
	 *
	 * @return void
	 */
	public function testEditAsOthers() {
		// Others are not allowed to edit franchises
		$this->assertGetAsAccessDenied(['controller' => 'Franchises', 'action' => 'edit', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Franchises', 'action' => 'edit', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Franchises', 'action' => 'edit', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Franchises', 'action' => 'edit', 'franchise' => FRANCHISE_ID_RED]);
	}

	/**
	 * Test delete method as an admin
	 *
	 * @return void
	 */
	public function testDeleteAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to delete franchises
		$this->assertPostAsAccessRedirect(['controller' => 'Franchises', 'action' => 'delete', 'franchise' => FRANCHISE_ID_MAPLES],
			PERSON_ID_ADMIN, [], ['controller' => 'Franchises', 'action' => 'index'],
			'The franchise has been deleted.');

		// But not ones with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'Franchises', 'action' => 'delete', 'franchise' => FRANCHISE_ID_RED],
			PERSON_ID_ADMIN, [], ['controller' => 'Franchises', 'action' => 'index'],
			'#The following records reference this franchise, so it cannot be deleted#');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to delete franchises in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Franchises', 'action' => 'delete', 'franchise' => FRANCHISE_ID_MAPLES],
			PERSON_ID_MANAGER, [], ['controller' => 'Franchises', 'action' => 'index'],
			'The franchise has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Franchises', 'action' => 'delete', 'franchise' => FRANCHISE_ID_LIONS],
			PERSON_ID_MANAGER);
	}

	/**
	 * Test delete method as others
	 *
	 * @return void
	 */
	public function testDeleteAsOthers() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Others are not allowed to delete franchises. Captains are allowed to do so, but only indirectly by removing the last team in it.
		$this->assertPostAsAccessDenied(['controller' => 'Franchises', 'action' => 'delete', 'franchise' => FRANCHISE_ID_MAPLES],
			PERSON_ID_COORDINATOR);
		$this->assertPostAsAccessDenied(['controller' => 'Franchises', 'action' => 'delete', 'franchise' => FRANCHISE_ID_RED],
			PERSON_ID_CAPTAIN);
		$this->assertPostAsAccessDenied(['controller' => 'Franchises', 'action' => 'delete', 'franchise' => FRANCHISE_ID_MAPLES],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'Franchises', 'action' => 'delete', 'franchise' => FRANCHISE_ID_MAPLES],
			PERSON_ID_VISITOR);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Franchises', 'action' => 'delete', 'franchise' => FRANCHISE_ID_MAPLES]);
	}

	/**
	 * Test add_team method as franchise owner
	 *
	 * @return void
	 */
	public function testAddTeamAsOwner() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Franchise owners are allowed to add teams
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'add_team', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_CAPTAIN);
		$this->assertResponseContains('<option value="' . TEAM_ID_RED_PAST . '">Red (' . (FrozenDate::now()->year - 1) . ' Summer Monday Night Ultimate Competitive)</option>');

		// Post the form
		$this->assertPostAsAccessRedirect(['controller' => 'Franchises', 'action' => 'add_team', 'franchise' => FRANCHISE_ID_RED],
			PERSON_ID_CAPTAIN, [
				'team_id' => TEAM_ID_RED_PAST,
			], ['controller' => 'Franchises', 'action' => 'view', 'franchise' => FRANCHISE_ID_RED],
			'The selected team has been added to this franchise.');

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'view', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_CAPTAIN);
		$this->assertResponseContains('/franchises/remove_team?franchise=' . FRANCHISE_ID_RED . '&amp;team=' . TEAM_ID_RED_PAST);
	}

	/**
	 * Test add_team method as others
	 *
	 * @return void
	 */
	public function testAddTeamAsOthers() {
		// Others are not allowed to add teams
		$this->assertGetAsAccessDenied(['controller' => 'Franchises', 'action' => 'add_team', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_ADMIN);
		$this->assertGetAsAccessDenied(['controller' => 'Franchises', 'action' => 'add_team', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_MANAGER);
		$this->assertGetAsAccessDenied(['controller' => 'Franchises', 'action' => 'add_team', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Franchises', 'action' => 'add_team', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Franchises', 'action' => 'add_team', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Franchises', 'action' => 'add_team', 'franchise' => FRANCHISE_ID_RED]);
	}

	/**
	 * Test remove_team method as an admin
	 *
	 * @return void
	 */
	public function testRemoveTeamAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to remove teams
		$this->assertPostAsAccessRedirect(['controller' => 'Franchises', 'action' => 'remove_team', 'franchise' => FRANCHISE_ID_RED, 'team' => TEAM_ID_RED],
			PERSON_ID_ADMIN, [], ['controller' => 'Franchises', 'action' => 'view', 'franchise' => FRANCHISE_ID_RED],
			'The selected team has been removed from this franchise.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_team method as a manager
	 *
	 * @return void
	 */
	public function testRemoveTeamAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to remove teams
		$this->assertPostAsAccessRedirect(['controller' => 'Franchises', 'action' => 'remove_team', 'franchise' => FRANCHISE_ID_RED, 'team' => TEAM_ID_RED],
			PERSON_ID_MANAGER, [], ['controller' => 'Franchises', 'action' => 'view', 'franchise' => FRANCHISE_ID_RED],
			'The selected team has been removed from this franchise.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_team method as franchise owner
	 *
	 * @return void
	 */
	public function testRemoveTeamAsOwner() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Franchise owners are allowed to remove teams
		$this->assertPostAsAccessRedirect(['controller' => 'Franchises', 'action' => 'remove_team', 'franchise' => FRANCHISE_ID_RED2, 'team' => TEAM_ID_RED],
			PERSON_ID_CAPTAIN, [], '/',
			'The selected team has been removed from this franchise. As there were no other teams in the franchise, it has been deleted as well.');
	}

	/**
	 * Test remove_team method as others
	 *
	 * @return void
	 */
	public function testRemoveTeamAsCaptain() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Others are not allowed to remove teams
		$this->assertPostAsAccessDenied(['controller' => 'Franchises', 'action' => 'remove_team', 'franchise' => FRANCHISE_ID_RED, 'team' => TEAM_ID_RED],
			PERSON_ID_COORDINATOR);
		$this->assertPostAsAccessDenied(['controller' => 'Franchises', 'action' => 'remove_team', 'franchise' => FRANCHISE_ID_RED, 'team' => TEAM_ID_RED],
			PERSON_ID_CAPTAIN2);
		$this->assertPostAsAccessDenied(['controller' => 'Franchises', 'action' => 'remove_team', 'franchise' => FRANCHISE_ID_RED, 'team' => TEAM_ID_RED],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'Franchises', 'action' => 'remove_team', 'franchise' => FRANCHISE_ID_RED, 'team' => TEAM_ID_RED],
			PERSON_ID_VISITOR);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Franchises', 'action' => 'remove_team', 'franchise' => FRANCHISE_ID_RED, 'team' => TEAM_ID_RED]);
	}

	/**
	 * Test add_owner method as an admin
	 *
	 * @return void
	 */
	public function testAddOwnerAsAdmin() {
		// Admins are allowed to add owner
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'add_owner', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_owner method as a manager
	 *
	 * @return void
	 */
	public function testAddOwnerAsManager() {
		// Managers are allowed to add owner
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'add_owner', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_owner method as franchise owner
	 *
	 * @return void
	 */
	public function testAddOwnerAsOwner() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Franchise owners are allowed to add owners
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'add_owner', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_CAPTAIN);

		// Try the search page
		$this->assertPostAsAccessOk(['controller' => 'Franchises', 'action' => 'add_owner', 'franchise' => FRANCHISE_ID_RED],
			PERSON_ID_CAPTAIN, [
				'affiliate_id' => '1',
				'first_name' => '',
				'last_name' => 'player',
				'sort' => 'last_name',
				'direction' => 'asc',
			]
		);
		$return = urlencode(\App\Lib\base64_url_encode(Configure::read('App.base') . '/franchises/add_owner?franchise=' . FRANCHISE_ID_RED));
		$this->assertResponseContains('/franchises/add_owner?person=' . PERSON_ID_PLAYER . '&amp;return=' . $return . '&amp;franchise=' . FRANCHISE_ID_RED);

		$this->assertGetAsAccessRedirect(['controller' => 'Franchises', 'action' => 'add_owner', 'person' => PERSON_ID_PLAYER, 'franchise' => FRANCHISE_ID_RED],
			PERSON_ID_CAPTAIN, ['controller' => 'Franchises', 'action' => 'view', 'franchise' => FRANCHISE_ID_RED],
			'Added Pam Player as owner.');

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'Franchises', 'action' => 'view', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_CAPTAIN);
		$this->assertResponseContains('/franchises/remove_owner?franchise=' . FRANCHISE_ID_RED . '&amp;person=' . PERSON_ID_PLAYER);
	}

	/**
	 * Test add_owner method as others
	 *
	 * @return void
	 */
	public function testAddOwnerAsOthers() {
		// Others are not allowed to add owners
		$this->assertGetAsAccessDenied(['controller' => 'Franchises', 'action' => 'add_owner', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Franchises', 'action' => 'add_owner', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_CAPTAIN2);
		$this->assertGetAsAccessDenied(['controller' => 'Franchises', 'action' => 'add_owner', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Franchises', 'action' => 'add_owner', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Franchises', 'action' => 'add_owner', 'franchise' => FRANCHISE_ID_RED]);
	}

	/**
	 * Test remove_owner method as an admin
	 *
	 * @return void
	 */
	public function testRemoveOwnerAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to remove owners
		$this->assertPostAsAccessRedirect(['controller' => 'Franchises', 'action' => 'remove_owner', 'franchise' => FRANCHISE_ID_BLUE, 'person' => PERSON_ID_DUPLICATE],
			PERSON_ID_ADMIN, [], ['controller' => 'Franchises', 'action' => 'view', 'franchise' => FRANCHISE_ID_BLUE],
			'Successfully removed owner.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_owner method as a manager
	 *
	 * @return void
	 */
	public function testRemoveOwnerAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to remove owners
		$this->assertPostAsAccessRedirect(['controller' => 'Franchises', 'action' => 'remove_owner', 'franchise' => FRANCHISE_ID_BLUE, 'person' => PERSON_ID_DUPLICATE],
			PERSON_ID_MANAGER, [], ['controller' => 'Franchises', 'action' => 'view', 'franchise' => FRANCHISE_ID_BLUE],
			'Successfully removed owner.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_owner method as franchise owner
	 *
	 * @return void
	 */
	public function testRemoveOwnerAsOwner() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Franchise owners are allowed to remove other owners
		$this->assertPostAsAccessRedirect(['controller' => 'Franchises', 'action' => 'remove_owner', 'franchise' => FRANCHISE_ID_BLUE, 'person' => PERSON_ID_DUPLICATE],
			PERSON_ID_CAPTAIN2, [], ['controller' => 'Franchises', 'action' => 'view', 'franchise' => FRANCHISE_ID_BLUE],
			'Successfully removed owner.');
	}

	/**
	 * Test remove_owner method as others
	 *
	 * @return void
	 */
	public function testRemoveOwnerAsOthers() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Others are not allowed to remove owners
		$this->assertPostAsAccessDenied(['controller' => 'Franchises', 'action' => 'remove_owner', 'franchise' => FRANCHISE_ID_BLUE, 'person' => PERSON_ID_DUPLICATE],
			PERSON_ID_COORDINATOR);
		$this->assertPostAsAccessDenied(['controller' => 'Franchises', 'action' => 'remove_owner', 'franchise' => FRANCHISE_ID_BLUE, 'person' => PERSON_ID_DUPLICATE],
			PERSON_ID_CAPTAIN);
		$this->assertPostAsAccessDenied(['controller' => 'Franchises', 'action' => 'remove_owner', 'franchise' => FRANCHISE_ID_BLUE, 'person' => PERSON_ID_DUPLICATE],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'Franchises', 'action' => 'remove_owner', 'franchise' => FRANCHISE_ID_BLUE, 'person' => PERSON_ID_DUPLICATE],
			PERSON_ID_VISITOR);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Franchises', 'action' => 'remove_owner', 'franchise' => FRANCHISE_ID_BLUE, 'person' => PERSON_ID_DUPLICATE]);
	}

}
