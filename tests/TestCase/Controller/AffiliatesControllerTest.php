<?php
namespace App\Test\TestCase\Controller;

use Cake\Core\Configure;

/**
 * App\Controller\AffiliatesController Test Case
 */
class AffiliatesControllerTest extends ControllerTestCase {

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
					'app.credits',
			'app.groups',
				'app.groups_people',
			'app.upload_types',
			'app.regions',
			'app.leagues',
				'app.divisions',
					'app.teams',
					'app.divisions_people',
			'app.franchises',
			'app.questions',
			'app.questionnaires',
			'app.events',
			'app.categories',
			'app.badges',
			'app.contacts',
			'app.holidays',
			'app.mailing_lists',
			'app.settings',
			'app.waivers',
	];

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex() {
		// Admins are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertResponseContains('/affiliates/edit?affiliate=' . AFFILIATE_ID_CLUB);
		$this->assertResponseContains('/affiliates/delete?affiliate=' . AFFILIATE_ID_CLUB);
	}

	/**
	 * Test index method as others
	 *
	 * @return void
	 */
	public function testIndexAsOthers() {
		// Others are not allowed to see the index
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'index'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'index'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'index'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'index'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Affiliates', 'action' => 'index']);
	}

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testView() {
		// Admins are allowed to view affiliates
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'view', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_ADMIN);
		$this->assertResponseContains('/affiliates/edit?affiliate=' . AFFILIATE_ID_CLUB);
		$this->assertResponseContains('/affiliates/delete?affiliate=' . AFFILIATE_ID_CLUB);
	}

	/**
	 * Test view method as others
	 *
	 * @return void
	 */
	public function testViewAsOthers() {
		// Others are not allowed to view affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'view', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_MANAGER);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'view', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'view', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'view', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'view', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Affiliates', 'action' => 'view', 'affiliate' => AFFILIATE_ID_CLUB]);
	}

	/**
	 * Test add method as an admin
	 *
	 * @return void
	 */
	public function testAddAsAdmin() {
		// Admins are allowed to add affiliates
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'add'], PERSON_ID_ADMIN);
	}

	/**
	 * Test add method as others
	 *
	 * @return void
	 */
	public function testAddAsOthers() {
		// Others are not allowed to add affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'add'], PERSON_ID_MANAGER);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'add'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'add'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'add'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'add'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Affiliates', 'action' => 'add']);
	}

	/**
	 * Test edit method as an admin
	 *
	 * @return void
	 */
	public function testEditAsAdmin() {
		// Admins are allowed to edit affiliates
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'edit', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_ADMIN);
	}

	/**
	 * Test edit method as others
	 *
	 * @return void
	 */
	public function testEditAsOthers() {
		// Others are not allowed to edit affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'edit', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_MANAGER);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'edit', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'edit', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'edit', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'edit', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Affiliates', 'action' => 'edit', 'affiliate' => AFFILIATE_ID_CLUB]);
	}

	/**
	 * Test delete method as an admin
	 *
	 * @return void
	 */
	public function testDeleteAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to delete affiliates
		$this->assertPostAsAccessRedirect(['controller' => 'Affiliates', 'action' => 'delete', 'affiliate' => AFFILIATE_ID_EMPTY],
			PERSON_ID_ADMIN, [], ['controller' => 'Affiliates', 'action' => 'index'],
			'The affiliate has been deleted.');
		// TODOLATER: Add checks for success messages everywhere

		// But not ones with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'Affiliates', 'action' => 'delete', 'affiliate' => AFFILIATE_ID_CLUB],
			PERSON_ID_ADMIN, [], ['controller' => 'Affiliates', 'action' => 'index'],
			'#The following records reference this affiliate, so it cannot be deleted#');
	}

	/**
	 * Test delete method as others
	 *
	 * @return void
	 */
	public function testDeleteAsOthers() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Others are not allowed to delete affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Affiliates', 'action' => 'delete', 'affiliate' => AFFILIATE_ID_CLUB],
			PERSON_ID_MANAGER);
		$this->assertPostAsAccessDenied(['controller' => 'Affiliates', 'action' => 'delete', 'affiliate' => AFFILIATE_ID_CLUB],
			PERSON_ID_COORDINATOR);
		$this->assertPostAsAccessDenied(['controller' => 'Affiliates', 'action' => 'delete', 'affiliate' => AFFILIATE_ID_CLUB],
			PERSON_ID_CAPTAIN);
		$this->assertPostAsAccessDenied(['controller' => 'Affiliates', 'action' => 'delete', 'affiliate' => AFFILIATE_ID_CLUB],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'Affiliates', 'action' => 'delete', 'affiliate' => AFFILIATE_ID_CLUB],
			PERSON_ID_VISITOR);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Affiliates', 'action' => 'delete', 'affiliate' => AFFILIATE_ID_CLUB]);
	}

	/**
	 * Test add_manager method as an admin
	 *
	 * @return void
	 */
	public function testAddManagerAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to add managers
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'add_manager', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_ADMIN);

		// Try the search page
		$this->assertPostAsAccessOk(['controller' => 'Affiliates', 'action' => 'add_manager', 'affiliate' => AFFILIATE_ID_CLUB],
			PERSON_ID_ADMIN, [
				'affiliate_id' => '1',
				'first_name' => 'pam',
				'last_name' => '',
				'sort' => 'last_name',
				'direction' => 'asc',
			]
		);
		$return = urlencode(\App\Lib\base64_url_encode(Configure::read('App.base') . '/affiliates/add_manager?affiliate=' . AFFILIATE_ID_CLUB));
		$this->assertResponseContains('/affiliates/add_manager?person=' . PERSON_ID_PLAYER . '&amp;return=' . $return . '&amp;affiliate=' . AFFILIATE_ID_CLUB);

		// Try to add the manager
		$this->assertGetAsAccessRedirect(['controller' => 'Affiliates', 'action' => 'add_manager', 'person' => PERSON_ID_PLAYER, 'affiliate' => AFFILIATE_ID_CLUB],
			PERSON_ID_ADMIN, ['controller' => 'Affiliates', 'action' => 'view', 'affiliate' => AFFILIATE_ID_CLUB],
			'Added Pam Player as manager.');

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'view', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_ADMIN);
		$this->assertResponseContains('/affiliates/remove_manager?affiliate=' . AFFILIATE_ID_CLUB . '&amp;person=' . PERSON_ID_PLAYER);
	}

	/**
	 * Test add_manager method as others
	 *
	 * @return void
	 */
	public function testAddManagerAsOthers() {
		// Others are not allowed to add managers
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'add_manager', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_MANAGER);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'add_manager', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'add_manager', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'add_manager', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'add_manager', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Affiliates', 'action' => 'add_manager', 'affiliate' => AFFILIATE_ID_CLUB]);
	}

	/**
	 * Test remove_manager method as an admin
	 *
	 * @return void
	 */
	public function testRemoveManagerAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to remove managers
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'view', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_ADMIN);
		$this->assertResponseContains('/affiliates/remove_manager?affiliate=' . AFFILIATE_ID_CLUB . '&amp;person=' . PERSON_ID_MANAGER);

		$this->assertPostAsAccessRedirect(['controller' => 'Affiliates', 'action' => 'remove_manager', 'affiliate' => AFFILIATE_ID_CLUB, 'person' => PERSON_ID_MANAGER],
			PERSON_ID_ADMIN, [], ['controller' => 'Affiliates', 'action' => 'view', 'affiliate' => AFFILIATE_ID_CLUB],
			'Successfully removed manager.');
		$this->assertEquals('If this person is no longer going to be managing anything, you should also edit their profile and deselect the "Manager" option.', $this->_requestSession->read('Flash.flash.1.message'));

		// Make sure they were removed successfully
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'view', 'affiliate' => AFFILIATE_ID_CLUB], PERSON_ID_ADMIN);
		$this->assertResponseNotContains('/affiliates/remove_manager?affiliate=' . AFFILIATE_ID_CLUB . '&amp;person=' . PERSON_ID_MANAGER);
	}

	/**
	 * Test remove_manager method as others
	 *
	 * @return void
	 */
	public function testRemoveManagerAsOthers() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Others are not allowed to remove managers
		$this->assertPostAsAccessDenied(['controller' => 'Affiliates', 'action' => 'remove_manager', 'affiliate' => AFFILIATE_ID_CLUB, 'person' => PERSON_ID_MANAGER],
			PERSON_ID_MANAGER);
		$this->assertPostAsAccessDenied(['controller' => 'Affiliates', 'action' => 'remove_manager', 'affiliate' => AFFILIATE_ID_CLUB, 'person' => PERSON_ID_MANAGER],
			PERSON_ID_COORDINATOR);
		$this->assertPostAsAccessDenied(['controller' => 'Affiliates', 'action' => 'remove_manager', 'affiliate' => AFFILIATE_ID_CLUB, 'person' => PERSON_ID_MANAGER],
			PERSON_ID_CAPTAIN);
		$this->assertPostAsAccessDenied(['controller' => 'Affiliates', 'action' => 'remove_manager', 'affiliate' => AFFILIATE_ID_CLUB, 'person' => PERSON_ID_MANAGER],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'Affiliates', 'action' => 'remove_manager', 'affiliate' => AFFILIATE_ID_CLUB, 'person' => PERSON_ID_MANAGER],
			PERSON_ID_VISITOR);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Affiliates', 'action' => 'remove_manager', 'affiliate' => AFFILIATE_ID_CLUB, 'person' => PERSON_ID_MANAGER]);
	}

	/**
	 * Test select method
	 *
	 * @return void
	 */
	public function testSelect() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Anyone logged in is allowed to select their affiliate(s) for this session
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'select'], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'select'], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'select'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'select'], PERSON_ID_CAPTAIN);

		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'select'], PERSON_ID_PLAYER);
		$this->assertResponseContains('<option value="1">Club</option><option value="2">Sub</option>');
		$this->assertPostAsAccessRedirect(['controller' => 'Affiliates', 'action' => 'select'],
			PERSON_ID_PLAYER, [
				'affiliate' => '1',
			], '/', false);
		$this->assertSession('1', 'Zuluru.CurrentAffiliate');

		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'select'], PERSON_ID_VISITOR);

		// Others are not allowed to select affiliates
		$this->assertGetAnonymousAccessDenied(['controller' => 'Affiliates', 'action' => 'select']);
	}

	/**
	 * Test view_all method
	 *
	 * @return void
	 */
	public function testViewAll() {
		// Anyone logged in is allowed to reset their affiliate selection for this session
		$this->session(['Zuluru.CurrentAffiliate' => AFFILIATE_ID_CLUB]);
		$this->assertGetAsAccessRedirect(['controller' => 'Affiliates', 'action' => 'view_all'],
			PERSON_ID_ADMIN, '/', false);
		$this->assertSession(null, 'Zuluru.CurrentAffiliate');

		$this->session(['Zuluru.CurrentAffiliate' => AFFILIATE_ID_CLUB]);
		$this->assertGetAsAccessRedirect(['controller' => 'Affiliates', 'action' => 'view_all'],
			PERSON_ID_MANAGER, '/', false);
		$this->assertSession(null, 'Zuluru.CurrentAffiliate');

		$this->session(['Zuluru.CurrentAffiliate' => AFFILIATE_ID_CLUB]);
		$this->assertGetAsAccessRedirect(['controller' => 'Affiliates', 'action' => 'view_all'],
			PERSON_ID_COORDINATOR, '/', false);
		$this->assertSession(null, 'Zuluru.CurrentAffiliate');

		$this->session(['Zuluru.CurrentAffiliate' => AFFILIATE_ID_CLUB]);
		$this->assertGetAsAccessRedirect(['controller' => 'Affiliates', 'action' => 'view_all'],
			PERSON_ID_CAPTAIN, '/', false);
		$this->assertSession(null, 'Zuluru.CurrentAffiliate');

		$this->session(['Zuluru.CurrentAffiliate' => AFFILIATE_ID_CLUB]);
		$this->assertGetAsAccessRedirect(['controller' => 'Affiliates', 'action' => 'view_all'],
			PERSON_ID_PLAYER, '/', false);
		$this->assertSession(null, 'Zuluru.CurrentAffiliate');

		$this->session(['Zuluru.CurrentAffiliate' => AFFILIATE_ID_CLUB]);
		$this->assertGetAsAccessRedirect(['controller' => 'Affiliates', 'action' => 'view_all'],
			PERSON_ID_VISITOR, '/', false);
		$this->assertSession(null, 'Zuluru.CurrentAffiliate');

		// Others are not allowed to view all
		$this->assertGetAnonymousAccessDenied(['controller' => 'Affiliates', 'action' => 'view_all']);
	}

}
