<?php
namespace App\Test\TestCase\Controller;

/**
 * App\Controller\RegionsController Test Case
 */
class RegionsControllerTest extends ControllerTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.Affiliates',
			'app.Users',
				'app.People',
					'app.AffiliatesPeople',
					'app.PeoplePeople',
			'app.Groups',
				'app.GroupsPeople',
			'app.Regions',
				'app.Facilities',
					'app.Fields',
			'app.Leagues',
				'app.Divisions',
					'app.Teams',
					'app.DivisionsPeople',
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
		// Admins are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Regions', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertResponseContains('/regions/edit?region=' . REGION_ID_EAST);
		$this->assertResponseContains('/regions/delete?region=' . REGION_ID_EAST);
		$this->assertResponseContains('/regions/edit?region=' . REGION_ID_SOUTH);
		$this->assertResponseContains('/regions/delete?region=' . REGION_ID_SOUTH);

		// Managers are allowed to see the index, but don't see regions in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'Regions', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertResponseContains('/regions/edit?region=' . REGION_ID_EAST);
		$this->assertResponseContains('/regions/delete?region=' . REGION_ID_EAST);
		$this->assertResponseNotContains('/regions/edit?region=' . REGION_ID_SOUTH);
		$this->assertResponseNotContains('/regions/delete?region=' . REGION_ID_SOUTH);

		// Others are not allowed to see the index
		$this->assertGetAsAccessDenied(['controller' => 'Regions', 'action' => 'index'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Regions', 'action' => 'index'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Regions', 'action' => 'index'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Regions', 'action' => 'index'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Regions', 'action' => 'index']);
	}

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testView() {
		// Admins are allowed to view regions
		$this->assertGetAsAccessOk(['controller' => 'Regions', 'action' => 'view', 'region' => REGION_ID_EAST], PERSON_ID_ADMIN);
		$this->assertResponseContains('/regions/edit?region=' . REGION_ID_EAST);
		$this->assertResponseContains('/regions/delete?region=' . REGION_ID_EAST);

		// Managers are allowed to view regions
		$this->assertGetAsAccessOk(['controller' => 'Regions', 'action' => 'view', 'region' => REGION_ID_EAST], PERSON_ID_MANAGER);
		$this->assertResponseContains('/regions/edit?region=' . REGION_ID_EAST);
		$this->assertResponseContains('/regions/delete?region=' . REGION_ID_EAST);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Regions', 'action' => 'view', 'region' => REGION_ID_SOUTH], PERSON_ID_MANAGER);

		// Others are not allowed to view regions
		$this->assertGetAsAccessDenied(['controller' => 'Regions', 'action' => 'view', 'region' => REGION_ID_EAST], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Regions', 'action' => 'view', 'region' => REGION_ID_EAST], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Regions', 'action' => 'view', 'region' => REGION_ID_EAST], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Regions', 'action' => 'view', 'region' => REGION_ID_EAST], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Regions', 'action' => 'view', 'region' => REGION_ID_EAST]);
	}

	/**
	 * Test add method as an admin
	 *
	 * @return void
	 */
	public function testAddAsAdmin() {
		// Admins are allowed to add regions
		$this->assertGetAsAccessOk(['controller' => 'Regions', 'action' => 'add'], PERSON_ID_ADMIN);
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers are allowed to add regions
		$this->assertGetAsAccessOk(['controller' => 'Regions', 'action' => 'add'], PERSON_ID_MANAGER);
	}

	/**
	 * Test add method as others
	 *
	 * @return void
	 */
	public function testAddAsOthers() {
		// Others are not allowed to add regions
		$this->assertGetAsAccessDenied(['controller' => 'Regions', 'action' => 'add'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Regions', 'action' => 'add'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Regions', 'action' => 'add'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Regions', 'action' => 'add'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Regions', 'action' => 'add']);
	}

	/**
	 * Test edit method as an admin
	 *
	 * @return void
	 */
	public function testEditAsAdmin() {
		// Admins are allowed to edit regions
		$this->assertGetAsAccessOk(['controller' => 'Regions', 'action' => 'edit', 'region' => REGION_ID_EAST], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Regions', 'action' => 'edit', 'region' => REGION_ID_SOUTH], PERSON_ID_ADMIN);
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers are allowed to edit regions
		$this->assertGetAsAccessOk(['controller' => 'Regions', 'action' => 'edit', 'region' => REGION_ID_EAST], PERSON_ID_MANAGER);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Regions', 'action' => 'edit', 'region' => REGION_ID_SOUTH], PERSON_ID_MANAGER);
	}

	/**
	 * Test edit method as others
	 *
	 * @return void
	 */
	public function testEditAsOthers() {
		// Others are not allowed to edit regions
		$this->assertGetAsAccessDenied(['controller' => 'Regions', 'action' => 'edit', 'region' => REGION_ID_EAST], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Regions', 'action' => 'edit', 'region' => REGION_ID_EAST], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Regions', 'action' => 'edit', 'region' => REGION_ID_EAST], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Regions', 'action' => 'edit', 'region' => REGION_ID_EAST], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Regions', 'action' => 'edit', 'region' => REGION_ID_EAST]);
	}

	/**
	 * Test delete method as an admin
	 *
	 * @return void
	 */
	public function testDeleteAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to delete regions
		$this->assertPostAsAccessRedirect(['controller' => 'Regions', 'action' => 'delete', 'region' => REGION_ID_NORTH],
			PERSON_ID_ADMIN, [], ['controller' => 'Regions', 'action' => 'index'],
			'The region has been deleted.');

		// But not ones with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'Regions', 'action' => 'delete', 'region' => REGION_ID_EAST],
			PERSON_ID_ADMIN, [], ['controller' => 'Regions', 'action' => 'index'],
			'#The following records reference this region, so it cannot be deleted#');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to delete regions in their own affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Regions', 'action' => 'delete', 'region' => REGION_ID_NORTH],
			PERSON_ID_MANAGER, [], ['controller' => 'Regions', 'action' => 'index'],
			'The region has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Regions', 'action' => 'delete', 'region' => REGION_ID_SOUTH],
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

		// Others are not allowed to delete regions
		$this->assertPostAsAccessDenied(['controller' => 'Regions', 'action' => 'delete', 'region' => REGION_ID_SOUTH],
			PERSON_ID_COORDINATOR);
		$this->assertPostAsAccessDenied(['controller' => 'Regions', 'action' => 'delete', 'region' => REGION_ID_SOUTH],
			PERSON_ID_CAPTAIN);
		$this->assertPostAsAccessDenied(['controller' => 'Regions', 'action' => 'delete', 'region' => REGION_ID_SOUTH],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'Regions', 'action' => 'delete', 'region' => REGION_ID_SOUTH],
			PERSON_ID_VISITOR);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Regions', 'action' => 'delete', 'region' => REGION_ID_SOUTH]);
	}

}
