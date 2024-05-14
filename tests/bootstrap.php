<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.0.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Chronos\Chronos;
use Cake\Core\Configure;
use Cake\Database\Connection;
use Cake\Database\Driver\Mysql;
use Cake\Datasource\ConnectionManager;
use Migrations\TestSuite\Migrator;

/**
 * Test runner bootstrap.
 *
 * Add additional configuration/setup your application needs when running
 * unit tests in this file.
 */
// Set cache to different files to prevent cross-contamination
define('CACHE_PREFIX', 'cli_');

require dirname(__DIR__) . '/vendor/autoload.php';

define('PHPUNIT_TESTSUITE', true);
require dirname(__DIR__) . '/config/bootstrap.php';

if (empty($_SERVER['HTTP_HOST']) && !Configure::read('App.fullBaseUrl')) {
    Configure::write('App.fullBaseUrl', 'http://localhost');

	$_SERVER['PHP_SELF'] = '/index.php';
	$_SERVER['SERVER_NAME'] = 'test.zuluru.org';
	$_SERVER['HTTP_HOST'] = 'test.zuluru.org';
	$_SERVER['REQUEST_SCHEME'] = 'https';
	$_SERVER['HTTPS'] = 1;
}

// DebugKit skips settings these connection config if PHP SAPI is CLI / PHPDBG.
// But since PagesControllerTest is run with debug enabled and DebugKit is loaded
// in application, without setting up these config DebugKit errors out.
ConnectionManager::setConfig('test_debug_kit', [
	'className' => Connection::class,
	'driver' => 'Cake\\Database\\Driver\\' . env('SQL_DRIVER'),
	'persistent' => false,
	'timezone' => env('APP_DEFAULT_TIMEZONE', 'UTC'),
	'host' => env('SQL_TEST_HOSTNAME'),
	'port' => env('SQL_TEST_PORT'),
	'username' => env('SQL_TEST_USERNAME'),
	'password' => env('SQL_TEST_PASSWORD'),
	'database' => env('SQL_TEST_DATABASE'),
	'url' => env('DATABASE_TEST_URL', null),
	'encoding' => 'utf8mb4',
	'flags' => [],
	'cacheMetadata' => true,
	'log' => filter_var(env('DEBUG_SQL_LOG', false), FILTER_VALIDATE_BOOLEAN),
	'quoteIdentifiers' => false,
]);

ConnectionManager::alias('test_debug_kit', 'debug_kit');

// Fixate now to avoid one-second-leap-issues
Chronos::setTestNow(Chronos::now());

// Fixate sessionid early on, as php7.2+
// does not allow the sessionid to be set after stdout
// has been written to.
session_id('cli');

// Use migrations to build test database schema.
(new Migrator())->run();

// When testing controllers, set notice frequency to something under 0 so we don't need the fixture everywhere.
Configure::write('notice_frequency', -1);
