<?php
namespace App\Test\TestCase\Controller;

/**
 * App\Controller\CategoriesController Test Case
 */
class CategoriesControllerTest extends ControllerTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
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
					'app.divisions_people',
			'app.categories',
				'app.tasks',
			'app.settings',
	];

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex() {
		// Admins are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Categories', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertResponseContains('/categories/edit?category=' . CATEGORY_ID_EVENTS);
		$this->assertResponseContains('/categories/delete?category=' . CATEGORY_ID_EVENTS);
		$this->assertResponseContains('/categories/edit?category=' . CATEGORY_ID_EVENTS_SUB);
		$this->assertResponseContains('/categories/delete?category=' . CATEGORY_ID_EVENTS_SUB);

		// Managers are allowed to see the index, but don't see categories in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'Categories', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertResponseContains('/categories/edit?category=' . CATEGORY_ID_EVENTS);
		$this->assertResponseContains('/categories/delete?category=' . CATEGORY_ID_EVENTS);
		$this->assertResponseNotContains('/categories/edit?category=' . CATEGORY_ID_EVENTS_SUB);
		$this->assertResponseNotContains('/categories/delete?category=' . CATEGORY_ID_EVENTS_SUB);

		// Others are not allowed to see the index
		$this->assertGetAsAccessDenied(['controller' => 'Categories', 'action' => 'index'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Categories', 'action' => 'index'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Categories', 'action' => 'index'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Categories', 'action' => 'index'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Categories', 'action' => 'index']);
	}

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testView() {
		// Admins are allowed to view categories
		$this->assertGetAsAccessOk(['controller' => 'Categories', 'action' => 'view', 'category' => CATEGORY_ID_EVENTS], PERSON_ID_ADMIN);
		$this->assertResponseContains('/categories/edit?category=' . CATEGORY_ID_EVENTS);
		$this->assertResponseContains('/categories/delete?category=' . CATEGORY_ID_EVENTS);

		$this->assertGetAsAccessOk(['controller' => 'Categories', 'action' => 'view', 'category' => CATEGORY_ID_EVENTS_SUB], PERSON_ID_ADMIN);
		$this->assertResponseContains('/categories/edit?category=' . CATEGORY_ID_EVENTS_SUB);
		$this->assertResponseContains('/categories/delete?category=' . CATEGORY_ID_EVENTS_SUB);

		// Managers are allowed to view categories
		$this->assertGetAsAccessOk(['controller' => 'Categories', 'action' => 'view', 'category' => CATEGORY_ID_EVENTS], PERSON_ID_MANAGER);
		$this->assertResponseContains('/categories/edit?category=' . CATEGORY_ID_EVENTS);
		$this->assertResponseContains('/categories/delete?category=' . CATEGORY_ID_EVENTS);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Categories', 'action' => 'view', 'category' => CATEGORY_ID_EVENTS_SUB], PERSON_ID_MANAGER);

		// Others are not allowed to view categories
		$this->assertGetAsAccessDenied(['controller' => 'Categories', 'action' => 'view', 'category' => CATEGORY_ID_EVENTS], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Categories', 'action' => 'view', 'category' => CATEGORY_ID_EVENTS], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Categories', 'action' => 'view', 'category' => CATEGORY_ID_EVENTS], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Categories', 'action' => 'view', 'category' => CATEGORY_ID_EVENTS], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Categories', 'action' => 'view', 'category' => CATEGORY_ID_EVENTS]);
	}

	/**
	 * Test add method as an admin
	 *
	 * @return void
	 */
	public function testAddAsAdmin() {
		// Admins are allowed to add categories
		$this->assertGetAsAccessOk(['controller' => 'Categories', 'action' => 'add'], PERSON_ID_ADMIN);
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers are allowed to add categories
		$this->assertGetAsAccessOk(['controller' => 'Categories', 'action' => 'add'], PERSON_ID_MANAGER);
	}

	/**
	 * Test add method as others
	 *
	 * @return void
	 */
	public function testAddAsOthers() {
		// Others are not allowed to add categories
		$this->assertGetAsAccessDenied(['controller' => 'Categories', 'action' => 'add'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Categories', 'action' => 'add'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Categories', 'action' => 'add'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Categories', 'action' => 'add'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Categories', 'action' => 'add']);
	}

	/**
	 * Test edit method as an admin
	 *
	 * @return void
	 */
	public function testEditAsAdmin() {
		// Admins are allowed to edit categories
		$this->assertGetAsAccessOk(['controller' => 'Categories', 'action' => 'edit', 'category' => CATEGORY_ID_EVENTS], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Categories', 'action' => 'edit', 'category' => CATEGORY_ID_EVENTS_SUB], PERSON_ID_ADMIN);
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers are allowed to edit categories
		$this->assertGetAsAccessOk(['controller' => 'Categories', 'action' => 'edit', 'category' => CATEGORY_ID_EVENTS], PERSON_ID_MANAGER);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Categories', 'action' => 'edit', 'category' => CATEGORY_ID_EVENTS_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test edit method as others
	 *
	 * @return void
	 */
	public function testEditAsOthers() {
		// Others are not allowed to edit categories
		$this->assertGetAsAccessDenied(['controller' => 'Categories', 'action' => 'edit', 'category' => CATEGORY_ID_EVENTS], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Categories', 'action' => 'edit', 'category' => CATEGORY_ID_EVENTS], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Categories', 'action' => 'edit', 'category' => CATEGORY_ID_EVENTS], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Categories', 'action' => 'edit', 'category' => CATEGORY_ID_EVENTS], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Categories', 'action' => 'edit', 'category' => CATEGORY_ID_EVENTS]);
	}

	/**
	 * Test delete method as an admin
	 *
	 * @return void
	 */
	public function testDeleteAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to delete categories
		$this->assertPostAsAccessRedirect(['controller' => 'Categories', 'action' => 'delete', 'category' => CATEGORY_ID_CLINICS],
			PERSON_ID_ADMIN, [], ['controller' => 'Categories', 'action' => 'index'],
			'The category has been deleted.');

		// But not ones with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'Categories', 'action' => 'delete', 'category' => CATEGORY_ID_EVENTS],
			PERSON_ID_ADMIN, [], ['controller' => 'Categories', 'action' => 'index'],
			'#The following records reference this category, so it cannot be deleted#');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to delete categories in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Categories', 'action' => 'delete', 'category' => CATEGORY_ID_CLINICS],
			PERSON_ID_MANAGER, [], ['controller' => 'Categories', 'action' => 'index'],
			'The category has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Categories', 'action' => 'delete', 'category' => CATEGORY_ID_EVENTS_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test delete method as others
	 *
	 * @return void
	 */
	public function testDeleteAsOthers() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Others are not allowed to delete categories
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Categories', 'action' => 'delete', 'category' => CATEGORY_ID_CLINICS], PERSON_ID_COORDINATOR);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Categories', 'action' => 'delete', 'category' => CATEGORY_ID_CLINICS], PERSON_ID_CAPTAIN);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Categories', 'action' => 'delete', 'category' => CATEGORY_ID_CLINICS], PERSON_ID_PLAYER);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Categories', 'action' => 'delete', 'category' => CATEGORY_ID_CLINICS], PERSON_ID_VISITOR);
		$this->assertPostAjaxAnonymousAccessDenied(['controller' => 'Categories', 'action' => 'delete', 'category' => CATEGORY_ID_CLINICS]);
	}

}
