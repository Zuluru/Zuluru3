<?php
namespace App\Test\TestCase\Controller;

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
		'app.event_types',
		'app.affiliates',
			'app.users',
				'app.people',
					'app.affiliates_people',
					'app.people_people',
			'app.groups',
				'app.groups_people',
			'app.leagues',
				'app.divisions',
					'app.teams',
						'app.teams_people',
					'app.divisions_days',
			'app.franchises',
				'app.franchises_people',
				'app.franchises_teams',
			'app.settings',
	];

	/**
	 * Test index method as an admin
	 *
	 * @return void
	 */
	public function testIndexAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test index method as a manager
	 *
	 * @return void
	 */
	public function testIndexAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test index method as a coordinator
	 *
	 * @return void
	 */
	public function testIndexAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test index method as a captain
	 *
	 * @return void
	 */
	public function testIndexAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test index method as a player
	 *
	 * @return void
	 */
	public function testIndexAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test index method as someone else
	 *
	 * @return void
	 */
	public function testIndexAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test index method without being logged in
	 *
	 * @return void
	 */
	public function testIndexAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test letter method as an admin
	 *
	 * @return void
	 */
	public function testLetterAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test letter method as a manager
	 *
	 * @return void
	 */
	public function testLetterAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test letter method as a coordinator
	 *
	 * @return void
	 */
	public function testLetterAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test letter method as a captain
	 *
	 * @return void
	 */
	public function testLetterAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test letter method as a player
	 *
	 * @return void
	 */
	public function testLetterAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test letter method as someone else
	 *
	 * @return void
	 */
	public function testLetterAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test letter method without being logged in
	 *
	 * @return void
	 */
	public function testLetterAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test view method as an admin
	 *
	 * @return void
	 */
	public function testViewAsAdmin() {
		// Admins are allowed to view franchises, with full edit permissions
		$this->assertAccessOk(['controller' => 'Franchises', 'action' => 'view', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/franchises/edit\?franchise=' . FRANCHISE_ID_RED . '#ms');
		$this->assertResponseRegExp('#/franchises/delete\?franchise=' . FRANCHISE_ID_RED . '#ms');

		$this->assertAccessOk(['controller' => 'Franchises', 'action' => 'view', 'franchise' => FRANCHISE_ID_LIONS], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/franchises/edit\?franchise=' . FRANCHISE_ID_LIONS . '#ms');
		$this->assertResponseRegExp('#/franchises/delete\?franchise=' . FRANCHISE_ID_LIONS . '#ms');
	}

	/**
	 * Test view method as a manager
	 *
	 * @return void
	 */
	public function testViewAsManager() {
		// Managers are allowed to view franchises
		$this->assertAccessOk(['controller' => 'Franchises', 'action' => 'view', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/franchises/edit\?franchise=' . FRANCHISE_ID_RED . '#ms');
		$this->assertResponseRegExp('#/franchises/delete\?franchise=' . FRANCHISE_ID_RED . '#ms');

		// But cannot edit ones in other affiliates
		$this->assertAccessOk(['controller' => 'Franchises', 'action' => 'view', 'franchise' => FRANCHISE_ID_LIONS], PERSON_ID_MANAGER);
		$this->assertResponseNotRegExp('#/franchises/edit\?franchise=' . FRANCHISE_ID_LIONS . '#ms');
		$this->assertResponseNotRegExp('#/franchises/delete\?franchise=' . FRANCHISE_ID_LIONS . '#ms');
	}

	/**
	 * Test view method as a coordinator
	 *
	 * @return void
	 */
	public function testViewAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test view method as franchise owner
	 *
	 * @return void
	 */
	public function testViewAsOwner() {
		// Owners are allowed to view and edit their franchises, but not delete; that happens automatically if the last team is removed
		$this->assertAccessOk(['controller' => 'Franchises', 'action' => 'view', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_CAPTAIN);
		$this->assertResponseRegExp('#/franchises/edit\?franchise=' . FRANCHISE_ID_RED . '#ms');
		$this->assertResponseNotRegExp('#/franchises/delete\?franchise=' . FRANCHISE_ID_RED . '#ms');
	}

	/**
	 * Test view method as a player
	 *
	 * @return void
	 */
	public function testViewAsPlayer() {
		// Others are allowed to view franchises, but have no edit permissions
		$this->assertAccessOk(['controller' => 'Franchises', 'action' => 'view', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_PLAYER);
		$this->assertResponseNotRegExp('#/franchises/edit\?franchise=' . FRANCHISE_ID_RED . '#ms');
		$this->assertResponseNotRegExp('#/franchises/delete\?franchise=' . FRANCHISE_ID_RED . '#ms');
	}

	/**
	 * Test view method as someone else
	 *
	 * @return void
	 */
	public function testViewAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test view method without being logged in
	 *
	 * @return void
	 */
	public function testViewAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as an admin
	 *
	 * @return void
	 */
	public function testAddAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as a coordinator
	 *
	 * @return void
	 */
	public function testAddAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as a captain
	 *
	 * @return void
	 */
	public function testAddAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as a player
	 *
	 * @return void
	 */
	public function testAddAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as someone else
	 *
	 * @return void
	 */
	public function testAddAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method without being logged in
	 *
	 * @return void
	 */
	public function testAddAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as an admin
	 *
	 * @return void
	 */
	public function testEditAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as a coordinator
	 *
	 * @return void
	 */
	public function testEditAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as a captain
	 *
	 * @return void
	 */
	public function testEditAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as a player
	 *
	 * @return void
	 */
	public function testEditAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as someone else
	 *
	 * @return void
	 */
	public function testEditAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method without being logged in
	 *
	 * @return void
	 */
	public function testEditAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
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
		$this->assertAccessRedirect(['controller' => 'Franchises', 'action' => 'delete', 'franchise' => FRANCHISE_ID_MAPLES],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Franchises', 'action' => 'index'],
			'The franchise has been deleted.', 'Flash.flash.0.message');

		// But not ones with dependencies
		$this->assertAccessRedirect(['controller' => 'Franchises', 'action' => 'delete', 'franchise' => FRANCHISE_ID_RED],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Franchises', 'action' => 'index'],
			'#The following records reference this franchise, so it cannot be deleted#', 'Flash.flash.0.message');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers can delete franchises in their affiliate
		$this->assertAccessRedirect(['controller' => 'Franchises', 'action' => 'delete', 'franchise' => FRANCHISE_ID_MAPLES],
			PERSON_ID_MANAGER, 'post', [], ['controller' => 'Franchises', 'action' => 'index'],
			'The franchise has been deleted.', 'Flash.flash.0.message');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Franchises', 'action' => 'delete', 'franchise' => FRANCHISE_ID_LIONS],
			PERSON_ID_MANAGER, 'post');
	}

	/**
	 * Test delete method as a coordinator
	 *
	 * @return void
	 */
	public function testDeleteAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete method as franchise owner
	 *
	 * @return void
	 */
	public function testDeleteAsOwner() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Franchise owners can delete their own franchises
		$this->assertAccessRedirect(['controller' => 'Franchises', 'action' => 'delete', 'franchise' => FRANCHISE_ID_LIONS],
			PERSON_ID_ANDY_SUB, 'post', [], ['controller' => 'Franchises', 'action' => 'index'],
			'The franchise has been deleted.', 'Flash.flash.0.message');

		// But not others
		$this->assertAccessRedirect(['controller' => 'Franchises', 'action' => 'delete', 'franchise' => FRANCHISE_ID_RED],
			PERSON_ID_ANDY_SUB, 'post');
	}

	/**
	 * Test delete method as a player
	 *
	 * @return void
	 */
	public function testDeleteAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete method as someone else
	 *
	 * @return void
	 */
	public function testDeleteAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete method without being logged in
	 *
	 * @return void
	 */
	public function testDeleteAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_team method as an admin
	 *
	 * @return void
	 */
	public function testAddTeamAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_team method as a manager
	 *
	 * @return void
	 */
	public function testAddTeamAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_team method as a coordinator
	 *
	 * @return void
	 */
	public function testAddTeamAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
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
		$this->assertAccessOk(['controller' => 'Franchises', 'action' => 'add_team', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_CAPTAIN);
		$this->assertResponseRegExp('#<option value="' . TEAM_ID_RED_PAST . '">Red \(' . (FrozenDate::now()->year - 1) . ' Summer Monday Night Ultimate Competitive\)</option>#ms');

		// Post the form
		$this->assertAccessRedirect(['controller' => 'Franchises', 'action' => 'add_team', 'franchise' => FRANCHISE_ID_RED],
			PERSON_ID_CAPTAIN, 'post', [
				'team_id' => TEAM_ID_RED_PAST,
			], ['controller' => 'Franchises', 'action' => 'view', 'franchise' => FRANCHISE_ID_RED],
			'The selected team has been added to this franchise.', 'Flash.flash.0.message');

		// Make sure they were added successfully
		$this->assertAccessOk(['controller' => 'Franchises', 'action' => 'view', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_CAPTAIN);
		$this->assertResponseRegExp('#/franchises/remove_team\?franchise=' . FRANCHISE_ID_RED . '&amp;team=' . TEAM_ID_RED_PAST . '#ms');
	}

	/**
	 * Test add_team method as a player
	 *
	 * @return void
	 */
	public function testAddTeamAsPlayer() {
		// Others are not allowed to add teams
		$this->assertAccessRedirect(['controller' => 'Franchises', 'action' => 'add_team', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_PLAYER);
	}

	/**
	 * Test add_team method as someone else
	 *
	 * @return void
	 */
	public function testAddTeamAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_team method without being logged in
	 *
	 * @return void
	 */
	public function testAddTeamAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_team method as an admin
	 *
	 * @return void
	 */
	public function testRemoveTeamAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_team method as a manager
	 *
	 * @return void
	 */
	public function testRemoveTeamAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_team method as a coordinator
	 *
	 * @return void
	 */
	public function testRemoveTeamAsCoordinator() {
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
		$this->assertAccessRedirect(['controller' => 'Franchises', 'action' => 'remove_team', 'franchise' => FRANCHISE_ID_RED2, 'team' => TEAM_ID_RED],
			PERSON_ID_CAPTAIN, 'post', [], null,
			'The selected team has been removed from this franchise. As there were no other teams in the franchise, it has been deleted as well.', 'Flash.flash.0.message');
	}

	/**
	 * Test remove_team method as a captain
	 *
	 * @return void
	 */
	public function testRemoveTeamAsCaptain() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Others are not allowed to remove teams
		$this->assertAccessRedirect(['controller' => 'Franchises', 'action' => 'remove_team', 'franchise' => FRANCHISE_ID_RED, 'team' => TEAM_ID_RED],
			PERSON_ID_CAPTAIN2, 'post');
	}

	/**
	 * Test remove_team method as a player
	 *
	 * @return void
	 */
	public function testRemoveTeamAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_team method as someone else
	 *
	 * @return void
	 */
	public function testRemoveTeamAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_team method without being logged in
	 *
	 * @return void
	 */
	public function testRemoveTeamAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_owner method as an admin
	 *
	 * @return void
	 */
	public function testAddOwnerAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_owner method as a manager
	 *
	 * @return void
	 */
	public function testAddOwnerAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_owner method as a coordinator
	 *
	 * @return void
	 */
	public function testAddOwnerAsCoordinator() {
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
		$this->assertAccessOk(['controller' => 'Franchises', 'action' => 'add_owner', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_CAPTAIN);

		// Try the search page
		$this->assertAccessOk(['controller' => 'Franchises', 'action' => 'add_owner', 'franchise' => FRANCHISE_ID_RED],
			PERSON_ID_CAPTAIN, 'post', [
			'affiliate_id' => '1',
			'first_name' => '',
			'last_name' => 'player',
			'sort' => 'last_name',
			'direction' => 'asc',
		]);
		$return = urlencode(\App\Lib\base64_url_encode('/franchises/add_owner?franchise=' . FRANCHISE_ID_RED));
		$this->assertResponseRegExp('#/franchises/add_owner\?person=' . PERSON_ID_PLAYER . '&amp;return=' . $return . '&amp;franchise=' . FRANCHISE_ID_RED . '#ms');

		$this->assertAccessRedirect(['controller' => 'Franchises', 'action' => 'add_owner', 'person' => PERSON_ID_PLAYER, 'franchise' => FRANCHISE_ID_RED],
			PERSON_ID_CAPTAIN, 'get', [], ['controller' => 'Franchises', 'action' => 'view', 'franchise' => FRANCHISE_ID_RED],
			'Added Pam Player as owner.', 'Flash.flash.0.message');

		// Make sure they were added successfully
		$this->assertAccessOk(['controller' => 'Franchises', 'action' => 'view', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_CAPTAIN);
		$this->assertResponseRegExp('#/franchises/remove_owner\?franchise=' . FRANCHISE_ID_RED . '&amp;person=' . PERSON_ID_PLAYER . '#ms');
	}

	/**
	 * Test add_owner method as a captain
	 *
	 * @return void
	 */
	public function testAddOwnerAsCaptain() {
		// Others are not allowed to add owners
		$this->assertAccessRedirect(['controller' => 'Franchises', 'action' => 'add_owner', 'franchise' => FRANCHISE_ID_RED], PERSON_ID_CAPTAIN2);
	}

	/**
	 * Test add_owner method as a player
	 *
	 * @return void
	 */
	public function testAddOwnerAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_owner method as someone else
	 *
	 * @return void
	 */
	public function testAddOwnerAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_owner method without being logged in
	 *
	 * @return void
	 */
	public function testAddOwnerAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_owner method as an admin
	 *
	 * @return void
	 */
	public function testRemoveOwnerAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_owner method as a manager
	 *
	 * @return void
	 */
	public function testRemoveOwnerAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_owner method as a coordinator
	 *
	 * @return void
	 */
	public function testRemoveOwnerAsCoordinator() {
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
		$this->assertAccessRedirect(['controller' => 'Franchises', 'action' => 'remove_owner', 'franchise' => FRANCHISE_ID_BLUE, 'person' => PERSON_ID_DUPLICATE],
			PERSON_ID_CAPTAIN2, 'post', [], ['controller' => 'Franchises', 'action' => 'view', 'franchise' => FRANCHISE_ID_BLUE],
			'Successfully removed owner.', 'Flash.flash.0.message');
	}

	/**
	 * Test remove_owner method as a captain
	 *
	 * @return void
	 */
	public function testRemoveOwnerAsCaptain() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Others are not allowed to remove owners
		$this->assertAccessRedirect(['controller' => 'Franchises', 'action' => 'remove_owner', 'franchise' => FRANCHISE_ID_BLUE, 'person' => PERSON_ID_DUPLICATE],
			PERSON_ID_CAPTAIN, 'post');
	}

	/**
	 * Test remove_owner method as a player
	 *
	 * @return void
	 */
	public function testRemoveOwnerAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_owner method as someone else
	 *
	 * @return void
	 */
	public function testRemoveOwnerAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_owner method without being logged in
	 *
	 * @return void
	 */
	public function testRemoveOwnerAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
