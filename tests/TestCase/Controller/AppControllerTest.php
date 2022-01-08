<?php
namespace App\Test\TestCase\Controller;

use App\Application;
use App\Test\Factory\PersonFactory;
use App\Test\Factory\SettingFactory;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\I18n\I18n;
use Cake\ORM\TableRegistry;
use App\Controller\AppController;

/**
 * App\Controller\AppController Test Case
 */
class AppControllerTest extends ControllerTestCase {

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
	 * Test initialize method
	 *
	 * @return void
	 */
	public function testInitialize() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test afterIdentify method
	 *
	 * @return void
	 */
	public function testAfterIdentify() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test beforeFilter method
	 *
	 * @return void
	 */
	public function testBeforeFilter() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test flashEmail method
	 *
	 * @return void
	 */
	public function testFlashEmail() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test flash method
	 *
	 * @return void
	 */
	public function testFlash() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test beforeRender method
	 *
	 * @return void
	 */
	public function testBeforeRender() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test redirect method
	 *
	 * @return void
	 */
	public function testRedirect() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _addTeamMenuItems method
	 *
	 * @return void
	 */
	public function testAddTeamMenuItems() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _addFranchiseMenuItems method
	 *
	 * @return void
	 */
	public function testAddFranchiseMenuItems() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _addDivisionMenuItems method
	 *
	 * @return void
	 */
	public function testAddDivisionMenuItems() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _addMenuItem method
	 *
	 * @return void
	 */
	public function testAddMenuItem() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _sendMail method
	 *
	 * @return void
	 */
	public function testSendMail() {
		$players = PersonFactory::makePlayer(2)->persist();
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

		$messages = Configure::consume('test_emails');
		$this->assertEquals(1, count($messages));

		$this->assertTextContains($en_sub, $messages[0]);
		$this->assertTextNotContains($fr_sub, $messages[0]);
		$this->assertTextContains($en_text, $messages[0]);
		$this->assertTextNotContains($fr_text, $messages[0]);

		// Should send in English and French (system language plus sub's preference)
		AppController::_sendMail([
			'to' => $players[1],
			'subject' => function() use ($players) { return __('{0} approved your relative request', $players[1]->full_name); },
			'template' => 'relative_approve',
			'sendAs' => 'both',
			'viewVars' => ['person' => $players[0], 'relative' => $players[1]],
		]);

		$messages = Configure::consume('test_emails');
		$this->assertEquals(1, count($messages));

		// TODO: Subject tests don't work, due to UTF encoding and line breaks
		//$this->assertTextContains($en_sub, $messages[0]);
		//$this->assertTextContains($fr_sub, $messages[0]);
		$this->assertTextContains($en_text, $messages[0]);
		$this->assertTextContains($fr_text, $messages[0]);

		// Should send in Spanish and French (system language plus sub's preference)
		Configure::write('App.defaultLocale', 'es');
		AppController::_sendMail([
			'to' => $players[1],
			'subject' => function() use ($players) { return __('{0} approved your relative request', $players[1]->full_name); },
			'template' => 'relative_approve',
			'sendAs' => 'both',
			'viewVars' => ['person' => $players[1], 'relative' => $players[1]],
		]);

		$messages = Configure::consume('test_emails');
		$this->assertEquals(1, count($messages));

		// TODO: Subject tests don't work, due to UTF encoding and line breaks
		//$this->assertTextNotContains($en_sub, $messages[0]);
		//$this->assertTextContains($es_sub, $messages[0]);
		//$this->assertTextContains($fr_sub, $messages[0]);
		$this->assertTextNotContains($en_text, $messages[0]);
		$this->assertTextContains($es_text, $messages[0]);
		$this->assertTextContains($fr_text, $messages[0]);
	}

	/**
	 * Test _extractEmails method
	 *
	 * @return void
	 */
	public function testExtractEmails() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _extractLocales method
	 *
	 * @return void
	 */
	public function testExtractLocales() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _isChild method
	 *
	 * @return void
	 */
	public function testIsChild() {
		$admin = PersonFactory::makeAdmin()->getEntity();
		$adult = PersonFactory::makePlayer(['birthdate' => FrozenDate::now()->subYears(19)])->getEntity();
		$child = PersonFactory::makePlayer(['birthdate' => FrozenDate::now()->subYears(17)])->getEntity();

		$this->assertFalse(AppController::_isChild($admin));
		$this->assertFalse(AppController::_isChild($adult));
		$this->assertTrue(AppController::_isChild($child));
	}

}
