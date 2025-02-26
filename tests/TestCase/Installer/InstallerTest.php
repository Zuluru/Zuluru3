<?php
namespace App\Test\TestCase\Installer;

use App\Test\TestCase\Controller\ControllerTestCase;

/**
 * App\Controller\AllController Test Case
 */
class InstallerTest extends ControllerTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [];

	/**
	 * Test install method
	 */
	public function testInstall(): void {
		// Test cases don't set the environment. But the bootstrap process needs to know whether we're in
		// the installer, before the routing runs. Setting the REQUEST_URI here lets this all work.
		$_SERVER['REQUEST_URI'] = '/installer/';

		$this->enableSecurityToken();

		$this->assertGetAnonymousAccessOk(['plugin' => 'CakePHPAppInstaller', 'controller' => 'Install', 'action' => 'index']);
		$this->assertResponseNotContains('Website already configured');
		$this->assertGetAnonymousAccessOk(['plugin' => 'CakePHPAppInstaller', 'controller' => 'Install', 'action' => 'connection']);

		/*
		 * We don't actually run this, because it overwrites existing config files with test data.
		 * Could perhaps be done later by changing our config/installer.php to skip the changes or
		 * save them to some other file.
		$this->assertPostAnonymousAccessRedirect(['plugin' => 'CakePHPAppInstaller', 'controller' => 'Install', 'action' => 'connection'],
			['host' => env('SQL_TEST_HOSTNAME'), 'username' => env('SQL_TEST_USERNAME'), 'password' => env('SQL_TEST_PASSWORD'), 'database' => env('SQL_TEST_DATABASE'), 'change_salt' => 0],
			['plugin' => 'CakePHPAppInstaller', 'controller' => 'Install', 'action' => 'data'],
			'Connected to the database'
		);
		*/

		$this->assertGetAnonymousAccessOk(['plugin' => 'CakePHPAppInstaller', 'controller' => 'Install', 'action' => 'data']);
		$this->assertResponseContains('We are successfully connected to the database, click on the link below to construct it.');
		/*
		$this->assertPostAnonymousAccessRedirect(['plugin' => 'CakePHPAppInstaller', 'controller' => 'Install', 'action' => 'data'],
			[],
			['plugin' => 'CakePHPAppInstaller', 'controller' => 'Install', 'action' => 'finish'],
			'Your admin password has been set to . Log in right away and change it to something more memorable.'
		);
		*/

		// If we don't unset it, it stays set for all future requests. :-(
		unset($_SERVER['REQUEST_URI']);
	}
}
