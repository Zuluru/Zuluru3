<?php
namespace App\Test\TestCase\Controller;

use App\Test\Factory\AffiliateFactory;
use App\Test\Factory\AffiliatesPersonFactory;
use App\Test\Factory\ContactFactory;
use App\Test\Factory\PersonFactory;
use App\Test\Scenario\DiverseUsersScenario;
use Cake\TestSuite\EmailTrait;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\ContactsController Test Case
 */
class ContactsControllerTest extends ControllerTestCase {

	use EmailTrait;
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

	public function tearDown(): void {
		// Cleanup any emails that were sent
		$this->cleanupEmailTrait();

		parent::tearDown();
	}

	/**
	 * Test index method
	 */
	public function testIndex(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;
		$contacts = ContactFactory::make([
			['affiliate_id' => $affiliates[0]->id],
			['affiliate_id' => $affiliates[1]->id],
		])->persist();

		// Admins are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Contacts', 'action' => 'index'], $admin->id);
		$this->assertResponseContains('/contacts/edit?contact=' . $contacts[0]->id);
		$this->assertResponseContains('/contacts/delete?contact=' . $contacts[0]->id);
		$this->assertResponseContains('/contacts/edit?contact=' . $contacts[1]->id);
		$this->assertResponseContains('/contacts/delete?contact=' . $contacts[1]->id);

		// Managers are allowed to see the index, but don't see contacts in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'Contacts', 'action' => 'index'], $manager->id);
		$this->assertResponseContains('/contacts/edit?contact=' . $contacts[0]->id);
		$this->assertResponseContains('/contacts/delete?contact=' . $contacts[0]->id);
		$this->assertResponseNotContains('/contacts/edit?contact=' . $contacts[1]->id);
		$this->assertResponseNotContains('/contacts/delete?contact=' . $contacts[1]->id);

		// Others are not allowed to see the index
		$this->assertGetAsAccessDenied(['controller' => 'Contacts', 'action' => 'index'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Contacts', 'action' => 'index'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Contacts', 'action' => 'index']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add method
	 */
	public function testAdd(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Admins are allowed to add contacts
		$this->assertGetAsAccessOk(['controller' => 'Contacts', 'action' => 'add'], $admin->id);

		// Managers are allowed to add contacts
		$this->assertGetAsAccessOk(['controller' => 'Contacts', 'action' => 'add'], $manager->id);

		// Others are not allowed to add contacts
		$this->assertGetAsAccessDenied(['controller' => 'Contacts', 'action' => 'add'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Contacts', 'action' => 'add'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Contacts', 'action' => 'add']);
	}

	/**
	 * Test edit method
	 */
	public function testEdit(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;
		$contacts = ContactFactory::make([
			['affiliate_id' => $affiliates[0]->id],
			['affiliate_id' => $affiliates[1]->id],
		])->persist();

		// Admins are allowed to edit contacts
		$this->assertGetAsAccessOk(['controller' => 'Contacts', 'action' => 'edit', 'contact' => $contacts[0]->id], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Contacts', 'action' => 'edit', 'contact' => $contacts[1]->id], $admin->id);

		// Managers are allowed to edit contacts
		$this->assertGetAsAccessOk(['controller' => 'Contacts', 'action' => 'edit', 'contact' => $contacts[0]->id], $manager->id);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Contacts', 'action' => 'edit', 'contact' => $contacts[1]->id], $manager->id);

		// Others are not allowed to edit contacts
		$this->assertGetAsAccessDenied(['controller' => 'Contacts', 'action' => 'edit', 'contact' => $contacts[0]->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Contacts', 'action' => 'edit', 'contact' => $contacts[1]->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Contacts', 'action' => 'edit', 'contact' => $contacts[0]->id], $player->id);
		$this->assertGetAsAccessDenied(['controller' => 'Contacts', 'action' => 'edit', 'contact' => $contacts[1]->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Contacts', 'action' => 'edit', 'contact' => $contacts[0]->id]);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Contacts', 'action' => 'edit', 'contact' => $contacts[1]->id]);
	}

	/**
	 * Test delete method as an admin
	 */
	public function testDeleteAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		$affiliates = AffiliateFactory::make(2)->persist();
		$admin = PersonFactory::makeAdmin()->with('Affiliates', $affiliates)->persist();
		$contacts = ContactFactory::make([
			['affiliate_id' => $affiliates[0]->id],
			['affiliate_id' => $affiliates[1]->id],
		])->persist();

		// Admins are allowed to delete contacts
		$this->assertPostAsAccessRedirect(['controller' => 'Contacts', 'action' => 'delete', 'contact' => $contacts[0]->id],
			$admin->id, [], ['controller' => 'Contacts', 'action' => 'index'],
			'The contact has been deleted.');
	}

	/**
	 * Test delete method as a manager
	 */
	public function testDeleteAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		$affiliates = AffiliateFactory::make(2)->persist();
		$manager = PersonFactory::makeManager()
			->with('AffiliatesPeople', AffiliatesPersonFactory::make(['position' => 'manager', 'affiliate_id' => $affiliates[0]->id]))
			->persist();
		$contacts = ContactFactory::make([
			['affiliate_id' => $affiliates[0]->id],
			['affiliate_id' => $affiliates[1]->id],
		])->persist();

		// Managers are allowed to delete contacts in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Contacts', 'action' => 'delete', 'contact' => $contacts[0]->id],
			$manager->id, [], ['controller' => 'Contacts', 'action' => 'index'],
			'The contact has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Contacts', 'action' => 'delete', 'contact' => $contacts[1]->id], $manager->id);
	}

	/**
	 * Test delete method as others
	 */
	public function testDeleteAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		$affiliates = AffiliateFactory::make(2)->persist();
		$player = PersonFactory::makePlayer()->with('Affiliates', $affiliates[0])->persist();
		$contacts = ContactFactory::make([
			['affiliate_id' => $affiliates[0]->id],
			['affiliate_id' => $affiliates[1]->id],
		])->persist();

		// Others are not allowed to delete contacts
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Contacts', 'action' => 'delete', 'contact' => $contacts[0]->id], $player->id);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Contacts', 'action' => 'delete', 'contact' => $contacts[1]->id], $player->id);
		$this->assertPostAjaxAnonymousAccessDenied(['controller' => 'Contacts', 'action' => 'delete', 'contact' => $contacts[0]->id]);
		$this->assertPostAjaxAnonymousAccessDenied(['controller' => 'Contacts', 'action' => 'delete', 'contact' => $contacts[1]->id]);
	}

	/**
	 * Test message method
	 */
	public function testMessage(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;
		$contacts = ContactFactory::make([
			['affiliate_id' => $affiliates[0]->id],
			['affiliate_id' => $affiliates[0]->id],
		])->persist();

		// Anyone logged in is allowed to see the message page
		$this->assertGetAsAccessOk(['controller' => 'Contacts', 'action' => 'message'], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Contacts', 'action' => 'message'], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'Contacts', 'action' => 'message'], $volunteer->id);

		$this->assertGetAsAccessOk(['controller' => 'Contacts', 'action' => 'message'], $player->id);
		$this->assertResponseContains('<option value="' . $contacts[0]->id . '">' . $contacts[0]->name . '</option>');
		$this->assertResponseContains('<option value="' . $contacts[1]->id . '">' . $contacts[1]->name . '</option>');

		// Anyone not logged in is not allowed to send messages
		$this->assertGetAnonymousAccessDenied(['controller' => 'Contacts', 'action' => 'message']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test message method as a player with one contact
	 */
	public function testMessageAsPlayerWithOneContact(): void {
		$player = PersonFactory::makePlayer()->with('Affiliates')->persist();
		$contact = ContactFactory::make(['affiliate_id' => $player->affiliates[0]->id])->persist();

		// Someone logged in on an affiliate that has only one contact doesn't get a drop-down
		$this->assertGetAsAccessOk(['controller' => 'Contacts', 'action' => 'message'], $player->id);
		$this->assertResponseContains('<input type="hidden" name="contact_id" value="' . $contact->id . '"/>');
	}

	/**
	 * Test execute method
	 */
	public function testExecute(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		$player = PersonFactory::makePlayer()->with('Affiliates')->persist();
		$contact = ContactFactory::make(['affiliate_id' => $player->affiliates[0]->id])->persist();

		$this->assertPostAsAccessRedirect(['controller' => 'Contacts', 'action' => 'message'],
			$player->id, [
				'contact_id' => $contact->id,
				'subject' => 'Test',
				'message' => 'Testing',
				'cc' => false,
			], '/', 'Your message has been sent.');
		$this->assertMailCount(1);
		$this->assertMailSentFrom('admin@zuluru.org');
		$this->assertMailSentWith([$player->user->email => $player->full_name], 'ReplyTo');
		$this->assertMailSentTo($contact->email);
		$this->assertMailSentWith([], 'CC');
		$this->assertMailSentWith('Test', 'Subject');
		$this->assertMailContains('Testing');
	}

	/**
	 * Test execute with CC method
	 */
	public function testExecuteWithCC(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		$player = PersonFactory::makePlayer()->with('Affiliates')->persist();
		$contact = ContactFactory::make(['affiliate_id' => $player->affiliates[0]->id])->persist();

		$this->assertPostAsAccessRedirect(['controller' => 'Contacts', 'action' => 'message'],
			$player->id, [
				'contact_id' => $contact->id,
				'subject' => 'Test',
				'message' => 'Testing',
				'cc' => true,
			], '/', 'Your message has been sent.');
		$this->assertMailCount(1);
		$this->assertMailSentFrom('admin@zuluru.org');
		$this->assertMailSentWith([$player->user->email => $player->full_name], 'ReplyTo');
		$this->assertMailSentTo($contact->email);
		$this->assertMailSentWith([$player->user->email => $player->full_name], 'CC');
		$this->assertMailSentWith('Test', 'Subject');
		$this->assertMailContains('Testing');
	}

}
