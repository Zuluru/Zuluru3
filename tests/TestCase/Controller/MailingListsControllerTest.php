<?php
namespace App\Test\TestCase\Controller;

use App\PasswordHasher\HasherTrait;
use App\Test\Factory\MailingListFactory;
use App\Test\Scenario\DiverseUsersScenario;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\MailingListsController Test Case
 */
class MailingListsControllerTest extends ControllerTestCase {

	use HasherTrait;
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

	private $unsubscribeMessage = 'You have successfully unsubscribed from this mailing list. Note that you may still be on other mailing lists for this site, and some emails (e.g. roster, attendance and score reminders) cannot be opted out of.';

	/**
	 * Test index method
	 */
	public function testIndex(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$list = MailingListFactory::make(['affiliate_id' => $affiliates[0]->id])->persist();
		$other_list = MailingListFactory::make(['affiliate_id' => $affiliates[1]->id])->persist();

		// Admins are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'MailingLists', 'action' => 'index'], $admin->id);
		$this->assertResponseContains('/mailing_lists/edit?mailing_list=' . $list->id);
		$this->assertResponseContains('/mailing_lists/delete?mailing_list=' . $list->id);
		$this->assertResponseContains('/mailing_lists/edit?mailing_list=' . $other_list->id);
		$this->assertResponseContains('/mailing_lists/delete?mailing_list=' . $other_list->id);

		// Managers are allowed to see the index, but don't see mailing lists in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'MailingLists', 'action' => 'index'], $manager->id);
		$this->assertResponseContains('/mailing_lists/edit?mailing_list=' . $list->id);
		$this->assertResponseContains('/mailing_lists/delete?mailing_list=' . $list->id);
		$this->assertResponseNotContains('/mailing_lists/edit?mailing_list=' . $other_list->id);
		$this->assertResponseNotContains('/mailing_lists/delete?mailing_list=' . $other_list->id);

		// Others are not allowed to see the index
		$this->assertGetAsAccessDenied(['controller' => 'MailingLists', 'action' => 'index'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'MailingLists', 'action' => 'index'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'MailingLists', 'action' => 'index']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test view method
	 */
	public function testView(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$list = MailingListFactory::make(['affiliate_id' => $affiliates[0]->id])->persist();
		$other_list = MailingListFactory::make(['affiliate_id' => $affiliates[1]->id])->persist();

		// Admins are allowed to view mailing lists
		$this->assertGetAsAccessOk(['controller' => 'MailingLists', 'action' => 'view', 'mailing_list' => $list->id], $admin->id);
		$this->assertResponseContains('/mailing_lists/edit?mailing_list=' . $list->id);
		$this->assertResponseContains('/mailing_lists/delete?mailing_list=' . $list->id);

		$this->assertGetAsAccessOk(['controller' => 'MailingLists', 'action' => 'view', 'mailing_list' => $other_list->id], $admin->id);
		$this->assertResponseContains('/mailing_lists/edit?mailing_list=' . $other_list->id);
		$this->assertResponseContains('/mailing_lists/delete?mailing_list=' . $other_list->id);

		// Managers are allowed to view mailing lists
		$this->assertGetAsAccessOk(['controller' => 'MailingLists', 'action' => 'view', 'mailing_list' => $list->id], $manager->id);
		$this->assertResponseContains('/mailing_lists/edit?mailing_list=' . $list->id);
		$this->assertResponseContains('/mailing_lists/delete?mailing_list=' . $list->id);

		// Others are not allowed to view mailing lists
		$this->assertGetAsAccessDenied(['controller' => 'MailingLists', 'action' => 'view', 'mailing_list' => $list->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'MailingLists', 'action' => 'view', 'mailing_list' => $list->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'MailingLists', 'action' => 'view', 'mailing_list' => $list->id]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test preview method
	 */
	public function testPreview(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$list = MailingListFactory::make(['affiliate_id' => $affiliates[0]->id])->persist();

		// Admins are allowed to preview
		$this->assertGetAsAccessOk(['controller' => 'MailingLists', 'action' => 'preview', 'mailing_list' => $list->id], $admin->id);

		// Managers are allowed to preview
		$this->assertGetAsAccessOk(['controller' => 'MailingLists', 'action' => 'preview', 'mailing_list' => $list->id], $manager->id);

		// Others are not allowed to preview
		$this->assertGetAsAccessDenied(['controller' => 'MailingLists', 'action' => 'preview', 'mailing_list' => $list->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'MailingLists', 'action' => 'preview', 'mailing_list' => $list->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'MailingLists', 'action' => 'preview', 'mailing_list' => $list->id]);
	}

	/**
	 * Test add method as an admin
	 */
	public function testAddAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		// Admins are allowed to add mailing lists
		$this->assertGetAsAccessOk(['controller' => 'MailingLists', 'action' => 'add'], $admin->id);
		// TODO: Database has default value of "1" for event affiliate_id, which auto-selects the primary affiliate in normal use.
		// Unit tests get some other ID for the affiliates, #1 doesn't exist, so there is no option selected. Either fix the
		// test or fix the default in the template or get rid of the default in the database. All only applies when there are
		// multiple affiliates anyway, otherwise the form makes the affiliate_id a hidden input.
		$this->assertResponseContains('<option value="' . $affiliates[0]->id . '">' . $affiliates[0]->name . '</option>');
		$this->assertResponseContains('<option value="' . $affiliates[1]->id . '">' . $affiliates[1]->name . '</option>');
	}

	/**
	 * Test add method as a manager
	 */
	public function testAddAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		// Managers are allowed to add mailing lists
		$this->assertGetAsAccessOk(['controller' => 'MailingLists', 'action' => 'add'], $manager->id);
		$this->assertResponseContains('<input type="hidden" name="affiliate_id" value="' . $affiliates[0]->id . '"/>');
		$this->assertResponseNotContains('<option value="' . $affiliates[1]->id . '">' . $affiliates[1]->name . '</option>');
	}

	/**
	 * Test add method as others
	 */
	public function testAddAsOthers(): void {
		[, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Others are not allowed to add mailing lists
		$this->assertGetAsAccessDenied(['controller' => 'MailingLists', 'action' => 'add'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'MailingLists', 'action' => 'add'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'MailingLists', 'action' => 'add']);
	}

	/**
	 * Test edit method as an admin
	 */
	public function testEditAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$list = MailingListFactory::make(['affiliate_id' => $affiliates[0]->id])->persist();
		$other_list = MailingListFactory::make(['affiliate_id' => $affiliates[1]->id])->persist();

		// Admins are allowed to edit mailing lists
		$this->assertGetAsAccessOk(['controller' => 'MailingLists', 'action' => 'edit', 'mailing_list' => $list->id], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'MailingLists', 'action' => 'edit', 'mailing_list' => $other_list->id], $admin->id);
	}

	/**
	 * Test edit method as a manager
	 */
	public function testEditAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$list = MailingListFactory::make(['affiliate_id' => $affiliates[0]->id])->persist();
		$other_list = MailingListFactory::make(['affiliate_id' => $affiliates[1]->id])->persist();

		// Managers are allowed to edit mailing lists
		$this->assertGetAsAccessOk(['controller' => 'MailingLists', 'action' => 'edit', 'mailing_list' => $list->id], $manager->id);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'MailingLists', 'action' => 'edit', 'mailing_list' => $other_list->id], $manager->id);
	}

	/**
	 * Test edit method as others
	 */
	public function testEditAsOthers(): void {
		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$list = MailingListFactory::make(['affiliate_id' => $affiliates[0]->id])->persist();

		// Others are not allowed to edit mailing lists
		$this->assertGetAsAccessDenied(['controller' => 'MailingLists', 'action' => 'edit', 'mailing_list' => $list->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'MailingLists', 'action' => 'edit', 'mailing_list' => $list->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'MailingLists', 'action' => 'edit', 'mailing_list' => $list->id]);
	}

	/**
	 * Test delete method as an admin
	 */
	public function testDeleteAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$list = MailingListFactory::make(['affiliate_id' => $affiliates[0]->id])->persist();
		$dependency_list = MailingListFactory::make(['affiliate_id' => $affiliates[0]->id])
			->with('Newsletters')
			->persist();

		// Admins are allowed to delete mailing lists
		$this->assertPostAsAccessRedirect(['controller' => 'MailingLists', 'action' => 'delete', 'mailing_list' => $list->id],
			$admin->id, [], ['controller' => 'MailingLists', 'action' => 'index'],
			'The mailing list has been deleted.');

		// But not ones with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'MailingLists', 'action' => 'delete', 'mailing_list' => $dependency_list->id],
			$admin->id, [], ['controller' => 'MailingLists', 'action' => 'index'],
			'#The following records reference this mailing list, so it cannot be deleted#');
	}

	/**
	 * Test delete method as a manager
	 */
	public function testDeleteAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$list = MailingListFactory::make(['affiliate_id' => $affiliates[0]->id])->persist();
		$other_list = MailingListFactory::make(['affiliate_id' => $affiliates[1]->id])->persist();

		// Managers are allowed to delete mailing lists in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'MailingLists', 'action' => 'delete', 'mailing_list' => $list->id],
			$manager->id, [], ['controller' => 'MailingLists', 'action' => 'index'],
			'The mailing list has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'MailingLists', 'action' => 'delete', 'mailing_list' => $other_list->id],
			$manager->id);
	}

	/**
	 * Test delete method as others
	 */
	public function testDeleteAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$list = MailingListFactory::make(['affiliate_id' => $affiliates[0]->id])->persist();

		// Others are not allowed to delete mailing lists
		$this->assertPostAsAccessDenied(['controller' => 'MailingLists', 'action' => 'delete', 'mailing_list' => $list->id],
			$volunteer->id);
		$this->assertPostAsAccessDenied(['controller' => 'MailingLists', 'action' => 'delete', 'mailing_list' => $list->id],
			$player->id);
		$this->assertPostAnonymousAccessDenied(['controller' => 'MailingLists', 'action' => 'delete', 'mailing_list' => $list->id]);
	}

	/**
	 * Test unsubscribe method as an admin
	 */
	public function testUnsubscribeAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$list = MailingListFactory::make(['affiliate_id' => $affiliates[0]->id])->persist();
		$unsubscribed_list = MailingListFactory::make(['affiliate_id' => $affiliates[1]->id])
			->with('Subscriptions', ['person_id' => $admin->id, 'subscribed' => false])
			->persist();

		// Admins are allowed to unsubscribe
		$this->assertGetAsAccessRedirect(['controller' => 'MailingLists', 'action' => 'unsubscribe', 'list' => $list->id],
			$admin->id, '/',
			$this->unsubscribeMessage);
		$this->assertGetAsAccessRedirect(['controller' => 'MailingLists', 'action' => 'unsubscribe', 'list' => $unsubscribed_list->id],
			$admin->id, '/',
			'You are not subscribed to this mailing list.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unsubscribe method as a manager
	 */
	public function testUnsubscribeAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$list = MailingListFactory::make(['affiliate_id' => $affiliates[0]->id])->persist();

		// Managers are allowed to unsubscribe
		$this->assertGetAsAccessRedirect(['controller' => 'MailingLists', 'action' => 'unsubscribe', 'list' => $list->id],
			$manager->id, '/',
			$this->unsubscribeMessage);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unsubscribe method as a coordinator
	 */
	public function testUnsubscribeAsCoordinator(): void {
		[$admin, , $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$list = MailingListFactory::make(['affiliate_id' => $affiliates[0]->id])->persist();

		// Coordinators are allowed to unsubscribe
		$this->assertGetAsAccessRedirect(['controller' => 'MailingLists', 'action' => 'unsubscribe', 'list' => $list->id],
			$volunteer->id, '/',
			$this->unsubscribeMessage);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unsubscribe method as a player
	 */
	public function testUnsubscribeAsPlayer(): void {
		[$admin, , , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$list = MailingListFactory::make(['affiliate_id' => $affiliates[0]->id])->persist();

		// Players are allowed to unsubscribe
		$this->assertGetAsAccessRedirect(['controller' => 'MailingLists', 'action' => 'unsubscribe', 'list' => $list->id],
			$player->id, '/',
			$this->unsubscribeMessage);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unsubscribe method without being logged in
	 */
	public function testUnsubscribeAsAnonymous(): void {
		[$admin, , , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$list = MailingListFactory::make(['affiliate_id' => $affiliates[0]->id])->persist();

		// Others are allowed to unsubscribe
		$this->assertGetAnonymousAccessRedirect(['controller' => 'MailingLists', 'action' => 'unsubscribe', 'list' => $list->id, 'person' => $player->id, 'code' => $this->_makeHash([$player->id, $list->id])],
			'/', $this->unsubscribeMessage);
		$this->markTestIncomplete('Not implemented yet.');
	}

}
