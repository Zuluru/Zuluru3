<?php
namespace App\Test\TestCase\Controller;

use App\Test\Factory\AffiliateFactory;
use App\Test\Factory\AffiliatesPersonFactory;
use App\Test\Factory\CategoryFactory;
use App\Test\Factory\PersonFactory;
use App\Test\Factory\TaskFactory;
use App\Test\Scenario\DiverseUsersScenario;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\CategoriesController Test Case
 */
class CategoriesControllerTest extends ControllerTestCase {

	use ScenarioAwareTrait;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.Groups',
		'app.Settings',
	];

	/**
	 * Test index method
	 */
	public function testIndex(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;
		$categories = CategoryFactory::make([
			['affiliate_id' => $affiliates[0]->id],
			['affiliate_id' => $affiliates[1]->id],
		])->persist();

		// Admins are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Categories', 'action' => 'index'], $admin->id);
		$this->assertResponseContains('/categories/edit?category=' . $categories[0]->id);
		$this->assertResponseContains('/categories/delete?category=' . $categories[0]->id);
		$this->assertResponseContains('/categories/edit?category=' . $categories[1]->id);
		$this->assertResponseContains('/categories/delete?category=' . $categories[1]->id);

		// Managers are allowed to see the index, but don't see categories in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'Categories', 'action' => 'index'], $manager->id);
		$this->assertResponseContains('/categories/edit?category=' . $categories[0]->id);
		$this->assertResponseContains('/categories/delete?category=' . $categories[0]->id);
		$this->assertResponseNotContains('/categories/edit?category=' . $categories[1]->id);
		$this->assertResponseNotContains('/categories/delete?category=' . $categories[1]->id);

		// Others are not allowed to see the index
		$this->assertGetAsAccessDenied(['controller' => 'Categories', 'action' => 'index'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Categories', 'action' => 'index'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Categories', 'action' => 'index']);
	}

	/**
	 * Test view method
	 */
	public function testView(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;
		$categories = CategoryFactory::make([
			['affiliate_id' => $affiliates[0]->id],
			['affiliate_id' => $affiliates[1]->id],
		])->persist();

		// Admins are allowed to view categories
		$this->assertGetAsAccessOk(['controller' => 'Categories', 'action' => 'view', 'category' => $categories[0]->id], $admin->id);
		$this->assertResponseContains('/categories/edit?category=' . $categories[0]->id);
		$this->assertResponseContains('/categories/delete?category=' . $categories[0]->id);

		$this->assertGetAsAccessOk(['controller' => 'Categories', 'action' => 'view', 'category' => $categories[1]->id], $admin->id);
		$this->assertResponseContains('/categories/edit?category=' . $categories[1]->id);
		$this->assertResponseContains('/categories/delete?category=' . $categories[1]->id);

		// Managers are allowed to view categories
		$this->assertGetAsAccessOk(['controller' => 'Categories', 'action' => 'view', 'category' => $categories[0]->id], $manager->id);
		$this->assertResponseContains('/categories/edit?category=' . $categories[0]->id);
		$this->assertResponseContains('/categories/delete?category=' . $categories[0]->id);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Categories', 'action' => 'view', 'category' => $categories[1]->id], $manager->id);

		// Others are not allowed to view categories
		$this->assertGetAsAccessDenied(['controller' => 'Categories', 'action' => 'view', 'category' => $categories[0]->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Categories', 'action' => 'view', 'category' => $categories[0]->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Categories', 'action' => 'view', 'category' => $categories[0]->id]);
	}

	/**
	 * Test add method
	 */
	public function testAdd(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Admins are allowed to add categories
		$this->assertGetAsAccessOk(['controller' => 'Categories', 'action' => 'add'], $admin->id);

		// Managers are allowed to add categories
		$this->assertGetAsAccessOk(['controller' => 'Categories', 'action' => 'add'], $manager->id);

		// Others are not allowed to add categories
		$this->assertGetAsAccessDenied(['controller' => 'Categories', 'action' => 'add'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Categories', 'action' => 'add'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Categories', 'action' => 'add']);
	}

	/**
	 * Test edit method
	 */
	public function testEdit(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;
		$categories = CategoryFactory::make([
			['affiliate_id' => $affiliates[0]->id],
			['affiliate_id' => $affiliates[1]->id],
		])->persist();

		// Admins are allowed to edit categories
		$this->assertGetAsAccessOk(['controller' => 'Categories', 'action' => 'edit', 'category' => $categories[0]->id], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Categories', 'action' => 'edit', 'category' => $categories[1]->id], $admin->id);

		// Managers are allowed to edit categories
		$this->assertGetAsAccessOk(['controller' => 'Categories', 'action' => 'edit', 'category' => $categories[0]->id], $manager->id);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Categories', 'action' => 'edit', 'category' => $categories[1]->id], $manager->id);

		// Others are not allowed to edit categories
		$this->assertGetAsAccessDenied(['controller' => 'Categories', 'action' => 'edit', 'category' => $categories[0]->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Categories', 'action' => 'edit', 'category' => $categories[1]->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Categories', 'action' => 'edit', 'category' => $categories[0]->id], $player->id);
		$this->assertGetAsAccessDenied(['controller' => 'Categories', 'action' => 'edit', 'category' => $categories[1]->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Categories', 'action' => 'edit', 'category' => $categories[0]->id]);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Categories', 'action' => 'edit', 'category' => $categories[1]->id]);
	}

	/**
	 * Test delete method as an admin
	 */
	public function testDeleteAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		$affiliates = AffiliateFactory::make(2)->persist();
		$admin = PersonFactory::make()->admin()->with('Affiliates', $affiliates)->persist();
		$categories = CategoryFactory::make([
			['affiliate_id' => $affiliates[0]->id],
			['affiliate_id' => $affiliates[1]->id],
		])->persist();
		TaskFactory::make()->with('Categories', $categories[1])->persist();

		// Admins are allowed to delete categories
		$this->assertPostAsAccessRedirect(['controller' => 'Categories', 'action' => 'delete', 'category' => $categories[0]->id],
			$admin->id, [], ['controller' => 'Categories', 'action' => 'index'],
			'The category has been deleted.');

		// But not ones with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'Categories', 'action' => 'delete', 'category' => $categories[1]->id],
			$admin->id, [], ['controller' => 'Categories', 'action' => 'index'],
			'#The following records reference this category, so it cannot be deleted#');
	}

	/**
	 * Test delete method as a manager
	 */
	public function testDeleteAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		$affiliates = AffiliateFactory::make(2)->persist();
		$manager = PersonFactory::make()->manager()
			->with('AffiliatesPeople', AffiliatesPersonFactory::make(['position' => 'manager', 'affiliate_id' => $affiliates[0]->id]))
			->persist();
		$categories = CategoryFactory::make([
			['affiliate_id' => $affiliates[0]->id],
			['affiliate_id' => $affiliates[1]->id],
		])->persist();

		// Managers are allowed to delete categories in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Categories', 'action' => 'delete', 'category' => $categories[0]->id],
			$manager->id, [], ['controller' => 'Categories', 'action' => 'index'],
			'The category has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Categories', 'action' => 'delete', 'category' => $categories[1]->id], $manager->id);
	}

	/**
	 * Test delete method as others
	 */
	public function testDeleteAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;
		$categories = CategoryFactory::make([
			['affiliate_id' => $affiliates[0]->id],
			['affiliate_id' => $affiliates[1]->id],
		])->persist();

		// Others are not allowed to delete categories
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Categories', 'action' => 'delete', 'category' => $categories[0]->id], $volunteer->id);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Categories', 'action' => 'delete', 'category' => $categories[1]->id], $volunteer->id);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Categories', 'action' => 'delete', 'category' => $categories[0]->id], $player->id);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Categories', 'action' => 'delete', 'category' => $categories[1]->id], $player->id);
		$this->assertPostAjaxAnonymousAccessDenied(['controller' => 'Categories', 'action' => 'delete', 'category' => $categories[0]->id]);
		$this->assertPostAjaxAnonymousAccessDenied(['controller' => 'Categories', 'action' => 'delete', 'category' => $categories[1]->id]);
	}

}
