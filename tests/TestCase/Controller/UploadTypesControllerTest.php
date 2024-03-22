<?php
namespace App\Test\TestCase\Controller;

use App\Test\Factory\UploadTypeFactory;
use App\Test\Scenario\DiverseUsersScenario;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\UploadTypesController Test Case
 */
class UploadTypesControllerTest extends ControllerTestCase {

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

		$type = UploadTypeFactory::make(['affiliate_id' => $admin->affiliates[0]->id])->persist();
		$affiliate_type = UploadTypeFactory::make(['affiliate_id' => $admin->affiliates[1]->id])->persist();

		// Admins are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'UploadTypes', 'action' => 'index'], $admin->id);
		$this->assertResponseContains('/upload_types/edit?type=' . $type->id);
		$this->assertResponseContains('/upload_types/delete?type=' . $type->id);
		$this->assertResponseContains('/upload_types/edit?type=' . $affiliate_type->id);
		$this->assertResponseContains('/upload_types/delete?type=' . $affiliate_type->id);

		// Managers are allowed to see the index, but don't see upload types in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'UploadTypes', 'action' => 'index'], $manager->id);
		$this->assertResponseContains('/upload_types/edit?type=' . $type->id);
		$this->assertResponseContains('/upload_types/delete?type=' . $type->id);
		$this->assertResponseNotContains('/upload_types/edit?type=' . $affiliate_type->id);
		$this->assertResponseNotContains('/upload_types/delete?type=' . $affiliate_type->id);

		// Others are not allowed to see the index
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'index'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'index'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'UploadTypes', 'action' => 'index']);
	}

	/**
	 * Test view method
	 */
	public function testView(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		$type = UploadTypeFactory::make(['affiliate_id' => $admin->affiliates[0]->id])->persist();
		$affiliate_type = UploadTypeFactory::make(['affiliate_id' => $admin->affiliates[1]->id])->persist();

		// Admins are allowed to view upload_types
		$this->assertGetAsAccessOk(['controller' => 'UploadTypes', 'action' => 'view', '?' => ['type' => $type->id]], $admin->id);
		$this->assertResponseContains('/upload_types/edit?type=' . $type->id);
		$this->assertResponseContains('/upload_types/delete?type=' . $type->id);

		$this->assertGetAsAccessOk(['controller' => 'UploadTypes', 'action' => 'view', '?' => ['type' => $affiliate_type->id]], $admin->id);
		$this->assertResponseContains('/upload_types/edit?type=' . $affiliate_type->id);
		$this->assertResponseContains('/upload_types/delete?type=' . $affiliate_type->id);

		// Managers are allowed to view upload_types
		$this->assertGetAsAccessOk(['controller' => 'UploadTypes', 'action' => 'view', '?' => ['type' => $type->id]], $manager->id);
		$this->assertResponseContains('/upload_types/edit?type=' . $type->id);
		$this->assertResponseContains('/upload_types/delete?type=' . $type->id);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'view', '?' => ['type' => $affiliate_type->id]], $manager->id);

		// Others are not allowed to view upload_types
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'view', '?' => ['type' => $type->id]], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'view', '?' => ['type' => $type->id]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'UploadTypes', 'action' => 'view', '?' => ['type' => $type->id]]);
	}

	/**
	 * Test add method as an admin
	 */
	public function testAddAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		// Admins are allowed to add upload_types
		$this->assertGetAsAccessOk(['controller' => 'UploadTypes', 'action' => 'add'], $admin->id);
	}

	/**
	 * Test add method as a manager
	 */
	public function testAddAsManager(): void {
		[$manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['manager']);

		// Managers are allowed to add upload_types
		$this->assertGetAsAccessOk(['controller' => 'UploadTypes', 'action' => 'add'], $manager->id);
	}

	/**
	 * Test add method as others
	 */
	public function testAddAsOthers(): void {
		[$volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['volunteer', 'player']);

		// Others are not allowed to add upload_types
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'add'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'add'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'UploadTypes', 'action' => 'add']);
	}

	/**
	 * Test edit method as an admin
	 */
	public function testEditAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		$type = UploadTypeFactory::make(['affiliate_id' => $admin->affiliates[0]->id])->persist();
		$affiliate_type = UploadTypeFactory::make(['affiliate_id' => $admin->affiliates[1]->id])->persist();

		// Admins are allowed to edit upload types
		$this->assertGetAsAccessOk(['controller' => 'UploadTypes', 'action' => 'edit', '?' => ['type' => $type->id]], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'UploadTypes', 'action' => 'edit', '?' => ['type' => $affiliate_type->id]], $admin->id);
	}

	/**
	 * Test edit method as a manager
	 */
	public function testEditAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);

		$type = UploadTypeFactory::make(['affiliate_id' => $admin->affiliates[0]->id])->persist();
		$affiliate_type = UploadTypeFactory::make(['affiliate_id' => $admin->affiliates[1]->id])->persist();

		// Managers are allowed to edit upload types
		$this->assertGetAsAccessOk(['controller' => 'UploadTypes', 'action' => 'edit', '?' => ['type' => $type->id]], $manager->id);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'edit', '?' => ['type' => $affiliate_type->id]], $manager->id);
	}

	/**
	 * Test edit method as others
	 */
	public function testEditAsOthers(): void {
		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);

		$type = UploadTypeFactory::make(['affiliate_id' => $admin->affiliates[0]->id])->persist();

		// Others are not allowed to edit upload types
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'edit', '?' => ['type' => $type->id]], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'edit', '?' => ['type' => $type->id]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'UploadTypes', 'action' => 'edit', '?' => ['type' => $type->id]]);
	}

	/**
	 * Test delete method as an admin
	 */
	public function testDeleteAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		$type = UploadTypeFactory::make(['affiliate_id' => $admin->affiliates[0]->id])->persist();
		$other_type = UploadTypeFactory::make(['affiliate_id' => $admin->affiliates[0]->id])
			->with('Uploads.People')
			->persist();

		// Admins are allowed to delete upload types
		$this->assertPostAsAccessRedirect(['controller' => 'UploadTypes', 'action' => 'delete', '?' => ['type' => $type->id]],
			$admin->id, [], ['controller' => 'UploadTypes', 'action' => 'index'],
			'The upload type has been deleted.');

		// But not ones with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'UploadTypes', 'action' => 'delete', '?' => ['type' => $other_type->id]],
			$admin->id, [], ['controller' => 'UploadTypes', 'action' => 'index'],
			'#The following records reference this upload type, so it cannot be deleted#');
	}

	/**
	 * Test delete method as a manager
	 */
	public function testDeleteAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);

		$type = UploadTypeFactory::make(['affiliate_id' => $admin->affiliates[0]->id])->persist();
		$affiliate_type = UploadTypeFactory::make(['affiliate_id' => $admin->affiliates[1]->id])->persist();

		// Managers are allowed to delete upload types in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'UploadTypes', 'action' => 'delete', '?' => ['type' => $type->id]],
			$manager->id, [], ['controller' => 'UploadTypes', 'action' => 'index'],
			'The upload type has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'delete', '?' => ['type' => $affiliate_type->id]],
			$manager->id);
	}

	/**
	 * Test delete method as others
	 */
	public function testDeleteAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);

		$type = UploadTypeFactory::make(['affiliate_id' => $admin->affiliates[0]->id])->persist();

		// Others are not allowed to delete
		$this->assertPostAjaxAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'delete', '?' => ['type' => $type->id]], $volunteer->id);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'UploadTypes', 'action' => 'delete', '?' => ['type' => $type->id]], $player->id);
		$this->assertPostAjaxAnonymousAccessDenied(['controller' => 'UploadTypes', 'action' => 'delete', '?' => ['type' => $type->id]]);
	}

}
