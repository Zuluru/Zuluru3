<?php
namespace App\Test\TestCase\Controller;

use App\Application;
use App\Model\Entity\Person;
use App\Test\Factory\PersonFactory;
use App\Test\Factory\SettingFactory;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\I18n\I18n;
use Cake\ORM\TableRegistry;
use App\Controller\AppController;
use Cake\TestSuite\EmailTrait;

/**
 * App\Controller\AppController Test Case
 */
class AppControllerTest extends ControllerTestCase {

	use EmailTrait;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.UserGroups',
		'app.Settings',
	];

	public function tearDown(): void {
		// Cleanup any emails that were sent
		$this->cleanupEmailTrait();

		parent::tearDown();
	}

	/**
	 * Test initialize method
	 */
	public function testInitialize(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test afterIdentify method
	 */
	public function testAfterIdentify(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test beforeFilter method
	 */
	public function testBeforeFilter(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test flashEmail method
	 */
	public function testFlashEmail(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test flash method
	 */
	public function testFlash(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test beforeRender method
	 */
	public function testBeforeRender(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test redirect method
	 */
	public function testRedirect(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _addTeamMenuItems method
	 */
	public function testAddTeamMenuItems(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _addFranchiseMenuItems method
	 */
	public function testAddFranchiseMenuItems(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _addDivisionMenuItems method
	 */
	public function testAddDivisionMenuItems(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _addMenuItem method
	 */
	public function testAddMenuItem(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _sendMail method
	 */
	public function testSendMail(): void {
		$players = PersonFactory::make(2)->player()->persist();
		SettingFactory::make(['person_id' => $players[1]->id, 'category' => 'personal', 'name' => 'language', 'value' => 'fr'])->persist();

		Configure::load('options');
		$config = TableRegistry::getTableLocator()->exists('Configuration') ? [] : ['className' => 'App\Model\Table\ConfigurationTable'];
		$configurationTable = TableRegistry::getTableLocator()->get('Configuration', $config);
		$configurationTable->loadSystem();
		Application::getLocales();

		$en_sub = __('{0} approved your relative request', $players[1]->full_name);
		$en_text = __('Your relative request to {0} on the {1} web site has been approved.', $players[1]->full_name, Configure::read('organization.name'));
		I18n::setLocale('fr');
		$fr_sub = __('{0} approved your relative request', $players[1]->full_name);
		$fr_text = __('Your relative request to {0} on the {1} web site has been approved.', $players[1]->full_name, Configure::read('organization.name'));
		I18n::setLocale('es');
		$es_sub = __('{0} approved your relative request', $players[1]->full_name);
		$es_text = __('Your relative request to {0} on the {1} web site has been approved.', $players[1]->full_name, Configure::read('organization.name'));
		I18n::setLocale('en');
		$this->assertTextContains('Your relative request', $en_text);
		$this->assertTextNotContains('Your relative request', $fr_text);

		// Should send in English only (system language; captain has no preference)
		AppController::_sendMail([
			'to' => $players[0],
			'subject' => function() use ($players) { return __('{0} approved your relative request', $players[1]->full_name); },
			'template' => 'relative_approve',
			'sendAs' => 'both',
			'viewVars' => ['person' => $players[0], 'relative' => $players[1]],
		]);

		$this->assertMailCount(1);

		$this->assertMailSentWith($en_sub, 'Subject');
		$this->assertMailContains($en_text);

		$this->cleanupEmailTrait();

		// Should send in English and French (system language plus sub's preference)
		AppController::_sendMail([
			'to' => $players[1],
			'subject' => function() use ($players) { return __('{0} approved your relative request', $players[1]->full_name); },
			'template' => 'relative_approve',
			'sendAs' => 'both',
			'viewVars' => ['person' => $players[0], 'relative' => $players[1]],
		]);

		$this->assertMailCount(1);

		// TODO: Subject tests don't work, due to UTF encoding and line breaks
		//$this->assertMailSentWith($en_sub, 'Subject');
		//$this->assertMailSentWith($fr_sub, 'Subject');
		$this->assertMailContains($en_text);
		$this->assertMailContains($fr_text);

		$this->cleanupEmailTrait();

		// Should send in Spanish and French (system language plus sub's preference)
		Configure::write('App.defaultLocale', 'es');
		AppController::_sendMail([
			'to' => $players[1],
			'subject' => function() use ($players) { return __('{0} approved your relative request', $players[1]->full_name); },
			'template' => 'relative_approve',
			'sendAs' => 'both',
			'viewVars' => ['person' => $players[1], 'relative' => $players[1]],
		]);

		$this->assertMailCount(1);

		// TODO: Subject tests don't work, due to UTF encoding and line breaks
		//$this->assertMailNotSentWith($en_sub, 'Subject');
		//$this->assertMailSentWith($es_sub, 'Subject');
		//$this->assertMailSentWith($fr_sub, 'Subject');
		//$this->assertMailNotContains($en_text);
		$this->assertMailContains($es_text);
		$this->assertMailContains($fr_text);
	}

	/**
	 * Test _extractEmails method
	 */
	public function testExtractEmails(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _extractLocales method
	 */
	public function testExtractLocales(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _isChild method
	 */
	public function testIsChild(): void {
		/** @var Person $admin */
		$admin = PersonFactory::make()->admin()->getEntity();
		$this->assertFalse(AppController::_isChild($admin));

		/** @var Person $adult */
		$adult = PersonFactory::make(['birthdate' => FrozenDate::now()->subYears(19)])->player()->getEntity();
		$this->assertFalse(AppController::_isChild($adult));

		/** @var Person $child */
		$child = PersonFactory::make(['birthdate' => FrozenDate::now()->subYears(17)])->player()->getEntity();
		$this->assertTrue(AppController::_isChild($child));
	}

}
