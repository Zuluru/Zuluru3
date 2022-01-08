<?php
namespace App\Test\TestCase\Controller;

use App\Test\Factory\AffiliateFactory;
use App\Test\Factory\AffiliatesPersonFactory;
use App\Test\Factory\PersonFactory;
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
		'app.Groups',
		'app.Settings',
	];

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex() {
		$affiliate = AffiliateFactory::make()->persist();
		$admin = PersonFactory::makeAdmin()->with('Affiliates', $affiliate)->persist();

		// Admins are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'index'], $admin->id);
		$this->assertResponseContains('/affiliates/edit?affiliate=' . $affiliate->id);
		$this->assertResponseContains('/affiliates/delete?affiliate=' . $affiliate->id);
	}

	/**
	 * Test index method as others
	 *
	 * @return void
	 */
	public function testIndexAsOthers() {
		$affiliate = AffiliateFactory::make()->persist();
		$manager = PersonFactory::makeManager()->with('Affiliates', $affiliate)->persist();
		$volunteer = PersonFactory::makeVolunteer()->with('Affiliates', $affiliate)->persist();
		$player = PersonFactory::makePlayer()->with('Affiliates', $affiliate)->persist();

		// Others are not allowed to see the index
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'index'], $manager->id);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'index'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'index'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Affiliates', 'action' => 'index']);
	}

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testView() {
		$affiliate = AffiliateFactory::make()->persist();
		$admin = PersonFactory::makeAdmin()->with('Affiliates', $affiliate)->persist();

		// Admins are allowed to view affiliates
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'view', 'affiliate' => $affiliate->id], $admin->id);
		$this->assertResponseContains('/affiliates/edit?affiliate=' . $affiliate->id);
		$this->assertResponseContains('/affiliates/delete?affiliate=' . $affiliate->id);
	}

	/**
	 * Test view method as others
	 *
	 * @return void
	 */
	public function testViewAsOthers() {
		$affiliate = AffiliateFactory::make()->persist();
		$manager = PersonFactory::makeManager()->with('Affiliates', $affiliate)->persist();
		$volunteer = PersonFactory::makeVolunteer()->with('Affiliates', $affiliate)->persist();
		$player = PersonFactory::makePlayer()->with('Affiliates', $affiliate)->persist();

		// Others are not allowed to view affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'view', 'affiliate' => $affiliate->id], $manager->id);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'view', 'affiliate' => $affiliate->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'view', 'affiliate' => $affiliate->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Affiliates', 'action' => 'index']);
	}

	/**
	 * Test add method as an admin
	 *
	 * @return void
	 */
	public function testAddAsAdmin() {
		$admin = PersonFactory::makeAdmin()->persist();

		// Admins are allowed to add affiliates
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'add'], $admin->id);
	}

	/**
	 * Test add method as others
	 *
	 * @return void
	 */
	public function testAddAsOthers() {
		$manager = PersonFactory::makeManager()->persist();
		$volunteer = PersonFactory::makeVolunteer()->persist();
		$player = PersonFactory::makePlayer()->persist();

		// Others are not allowed to add affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'add'], $manager->id);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'add'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'add'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Affiliates', 'action' => 'add']);
	}

	/**
	 * Test edit method as an admin
	 *
	 * @return void
	 */
	public function testEditAsAdmin() {
		$affiliate = AffiliateFactory::make()->persist();
		$admin = PersonFactory::makeAdmin()->with('Affiliates', $affiliate)->persist();

		// Admins are allowed to edit affiliates
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'edit', 'affiliate' => $affiliate->id], $admin->id);
	}

	/**
	 * Test edit method as others
	 *
	 * @return void
	 */
	public function testEditAsOthers() {
		$affiliate = AffiliateFactory::make()->persist();
		$manager = PersonFactory::makeManager()->with('Affiliates', $affiliate)->persist();
		$volunteer = PersonFactory::makeVolunteer()->with('Affiliates', $affiliate)->persist();
		$player = PersonFactory::makePlayer()->with('Affiliates', $affiliate)->persist();

		// Others are not allowed to edit affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'edit', 'affiliate' => $affiliate->id], $manager->id);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'edit', 'affiliate' => $affiliate->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'edit', 'affiliate' => $affiliate->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Affiliates', 'action' => 'edit', 'affiliate' => $affiliate->id]);
	}

	/**
	 * Test delete method as an admin
	 *
	 * @return void
	 */
	public function testDeleteAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		$affiliate = AffiliateFactory::make()->persist();
		$admin = PersonFactory::makeAdmin()->with('Affiliates', $affiliate)->persist();
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'edit', 'affiliate' => $affiliate->id], $admin->id);

		// Admins are allowed to delete affiliates
		$this->assertPostAsAccessRedirect(['controller' => 'Affiliates', 'action' => 'delete', 'affiliate' => $affiliate->id],
			$admin->id, [], ['controller' => 'Affiliates', 'action' => 'index'],
			'The affiliate has been deleted.');
		// TODOLATER: Add checks for success messages everywhere

		// But not ones with dependencies
		$affiliate = AffiliateFactory::make()->with('Leagues')->persist();
		$this->assertPostAsAccessRedirect(['controller' => 'Affiliates', 'action' => 'delete', 'affiliate' => $affiliate->id],
			$admin->id, [], ['controller' => 'Affiliates', 'action' => 'index'],
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

		$affiliate = AffiliateFactory::make()->persist();
		$manager = PersonFactory::makeManager()->with('Affiliates', $affiliate)->persist();
		$volunteer = PersonFactory::makeVolunteer()->with('Affiliates', $affiliate)->persist();
		$player = PersonFactory::makePlayer()->with('Affiliates', $affiliate)->persist();

		// Others are not allowed to delete affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Affiliates', 'action' => 'delete', 'affiliate' => $affiliate->id], $manager->id);
		$this->assertPostAsAccessDenied(['controller' => 'Affiliates', 'action' => 'delete', 'affiliate' => $affiliate->id], $volunteer->id);
		$this->assertPostAsAccessDenied(['controller' => 'Affiliates', 'action' => 'delete', 'affiliate' => $affiliate->id], $player->id);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Affiliates', 'action' => 'delete', 'affiliate' => $affiliate->id]);
	}

	/**
	 * Test add_manager method as an admin
	 *
	 * @return void
	 */
	public function testAddManagerAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		$affiliate = AffiliateFactory::make()->persist();
		$admin = PersonFactory::makeAdmin()->with('Affiliates', $affiliate)->persist();
		// TODOLATER: Shouldn't need gender fields to be specified for non-players
		$manager = PersonFactory::makeManager(['gender' => 'Woman', 'roster_designation' => 'Woman'])->with('Affiliates', $affiliate)->persist();
		$volunteer = PersonFactory::makeVolunteer()->with('Affiliates', $affiliate)->persist();

		// Admins are allowed to add managers
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'add_manager', 'affiliate' => $affiliate->id], $admin->id);

		// Try the search page for an ineligible person
		$this->assertPostAsAccessOk(['controller' => 'Affiliates', 'action' => 'add_manager', 'affiliate' => $affiliate->id],
			$admin->id, [
				'affiliate_id' => $affiliate->id,
				'first_name' => $volunteer->first_name,
				'last_name' => '',
				'sort' => 'last_name',
				'direction' => 'asc',
			]
		);
		$this->assertResponseContains('showing 0 records out of 0 total');

		// Try someone that is eligible
		$this->assertPostAsAccessOk(['controller' => 'Affiliates', 'action' => 'add_manager', 'affiliate' => $affiliate->id],
			$admin->id, [
				'affiliate_id' => $affiliate->id,
				'first_name' => $manager->first_name,
				'last_name' => '',
				'sort' => 'last_name',
				'direction' => 'asc',
			]
		);
		$this->assertResponseContains('showing 1 records out of 1 total');
		$return = urlencode(\App\Lib\base64_url_encode(Configure::read('App.base') . '/affiliates/add_manager?affiliate=' . $affiliate->id));
		$this->assertResponseContains('/affiliates/add_manager?person=' . $manager->id . '&amp;return=' . $return . '&amp;affiliate=' . $affiliate->id);

		// Try to add the manager
		$this->assertGetAsAccessRedirect(['controller' => 'Affiliates', 'action' => 'add_manager', 'person' => $manager->id, 'affiliate' => $affiliate->id],
			$admin->id, ['controller' => 'Affiliates', 'action' => 'view', 'affiliate' => $affiliate->id],
			'Added ' . $manager->full_name . ' as manager.');

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'view', 'affiliate' => $affiliate->id], $admin->id);
		$this->assertResponseContains('/affiliates/remove_manager?affiliate=' . $affiliate->id . '&amp;person=' . $manager->id);
	}

	/**
	 * Test add_manager method as others
	 *
	 * @return void
	 */
	public function testAddManagerAsOthers() {
		$affiliate = AffiliateFactory::make()->persist();
		$manager = PersonFactory::makeManager()->with('Affiliates', $affiliate)->persist();
		$volunteer = PersonFactory::makeVolunteer()->with('Affiliates', $affiliate)->persist();
		$player = PersonFactory::makePlayer()->with('Affiliates', $affiliate)->persist();

		// Others are not allowed to add managers
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'add_manager', 'affiliate' => $affiliate->id], $manager->id);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'add_manager', 'affiliate' => $affiliate->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Affiliates', 'action' => 'add_manager', 'affiliate' => $affiliate->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Affiliates', 'action' => 'add_manager', 'affiliate' => $affiliate->id]);
	}

	/**
	 * Test remove_manager method as an admin
	 *
	 * @return void
	 */
	public function testRemoveManagerAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		$affiliate = AffiliateFactory::make()->persist();
		$admin = PersonFactory::makeAdmin()->with('Affiliates', $affiliate)->persist();
		$manager = PersonFactory::makeManager()
			->with('Affiliates', $affiliate)
			->with('AffiliatesPeople', AffiliatesPersonFactory::make(['position' => 'manager', 'affiliate_id' => $affiliate->id]))
			->persist();

		// Admins are allowed to remove managers
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'view', 'affiliate' => $affiliate->id], $admin->id);
		$this->assertResponseContains('/affiliates/remove_manager?affiliate=' . $affiliate->id . '&amp;person=' . $manager->id);

		$this->assertPostAsAccessRedirect(['controller' => 'Affiliates', 'action' => 'remove_manager', 'affiliate' => $affiliate->id, 'person' => $manager->id],
			$admin->id, [], ['controller' => 'Affiliates', 'action' => 'view', 'affiliate' => $affiliate->id],
			'Successfully removed manager.');
		$this->assertEquals('If this person is no longer going to be managing anything, you should also edit their profile and deselect the "Manager" option.', $this->_requestSession->read('Flash.flash.1.message'));

		// Make sure they were removed successfully
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'view', 'affiliate' => $affiliate->id], $admin->id);
		$this->assertResponseNotContains('/affiliates/remove_manager?affiliate=' . $affiliate->id . '&amp;person=' . $manager->id);
	}

	/**
	 * Test remove_manager method as others
	 *
	 * @return void
	 */
	public function testRemoveManagerAsOthers() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		$affiliate = AffiliateFactory::make()->persist();
		$manager = PersonFactory::makeManager()->with('Affiliates', $affiliate)->persist();
		$volunteer = PersonFactory::makeVolunteer()->with('Affiliates', $affiliate)->persist();
		$player = PersonFactory::makePlayer()->with('Affiliates', $affiliate)->persist();

		// Others are not allowed to remove managers
		$this->assertPostAsAccessDenied(['controller' => 'Affiliates', 'action' => 'remove_manager', 'affiliate' => $affiliate->id, 'person' => $manager->id], $manager->id);
		$this->assertPostAsAccessDenied(['controller' => 'Affiliates', 'action' => 'remove_manager', 'affiliate' => $affiliate->id, 'person' => $manager->id], $volunteer->id);
		$this->assertPostAsAccessDenied(['controller' => 'Affiliates', 'action' => 'remove_manager', 'affiliate' => $affiliate->id, 'person' => $manager->id], $player->id);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Affiliates', 'action' => 'remove_manager', 'affiliate' => $affiliate->id, 'person' => $manager->id]);
	}

	/**
	 * Test select method
	 *
	 * @return void
	 */
	public function testSelect() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		$affiliates = AffiliateFactory::make(2)->persist();
		$admin = PersonFactory::makeAdmin()->with('Affiliates', $affiliates[0])->persist();
		$manager = PersonFactory::makeManager()->with('Affiliates', $affiliates[0])->persist();
		$volunteer = PersonFactory::makeVolunteer()->with('Affiliates', $affiliates[0])->persist();
		$player = PersonFactory::makePlayer()->with('Affiliates', $affiliates[0])->persist();

		// Anyone logged in is allowed to select their affiliate(s) for this session
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'select'], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'select'], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'select'], $volunteer->id);
		$this->assertGetAsAccessOk(['controller' => 'Affiliates', 'action' => 'select'], $player->id);
		$this->assertResponseContains("<option value=\"{$affiliates[0]->id}\">{$affiliates[0]->name}</option><option value=\"{$affiliates[1]->id}\">{$affiliates[1]->name}</option>");
		$this->assertPostAsAccessRedirect(['controller' => 'Affiliates', 'action' => 'select'],
			$player->id, [
				'affiliate' => $affiliates[0]->id,
			], '/', false);
		$this->assertSession((string)$affiliates[0]->id, 'Zuluru.CurrentAffiliate');

		// Others are not allowed to select affiliates
		$this->assertGetAnonymousAccessDenied(['controller' => 'Affiliates', 'action' => 'select']);
	}

	/**
	 * Test view_all method
	 *
	 * @return void
	 */
	public function testViewAll() {
		$affiliate = AffiliateFactory::make()->persist();
		$admin = PersonFactory::makeAdmin()->with('Affiliates', $affiliate)->persist();
		$manager = PersonFactory::makeManager()->with('Affiliates', $affiliate)->persist();
		$volunteer = PersonFactory::makeVolunteer()->with('Affiliates', $affiliate)->persist();
		$player = PersonFactory::makePlayer()->with('Affiliates', $affiliate)->persist();

		// Anyone logged in is allowed to reset their affiliate selection for this session
		$this->session(['Zuluru.CurrentAffiliate' => $affiliate->id]);
		$this->assertGetAsAccessRedirect(['controller' => 'Affiliates', 'action' => 'view_all'],
			$admin->id, '/', false);
		$this->assertSession(null, 'Zuluru.CurrentAffiliate');

		$this->session(['Zuluru.CurrentAffiliate' => $affiliate->id]);
		$this->assertGetAsAccessRedirect(['controller' => 'Affiliates', 'action' => 'view_all'],
			$manager->id, '/', false);
		$this->assertSession(null, 'Zuluru.CurrentAffiliate');

		$this->session(['Zuluru.CurrentAffiliate' => $affiliate->id]);
		$this->assertGetAsAccessRedirect(['controller' => 'Affiliates', 'action' => 'view_all'],
			$volunteer->id, '/', false);
		$this->assertSession(null, 'Zuluru.CurrentAffiliate');

		$this->session(['Zuluru.CurrentAffiliate' => $affiliate->id]);
		$this->assertGetAsAccessRedirect(['controller' => 'Affiliates', 'action' => 'view_all'],
			$player->id, '/', false);
		$this->assertSession(null, 'Zuluru.CurrentAffiliate');

		// Others are not allowed to view all
		$this->assertGetAnonymousAccessDenied(['controller' => 'Affiliates', 'action' => 'view_all']);
	}

}
