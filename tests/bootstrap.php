<?php
/**
 * Test runner bootstrap.
 *
 * Add additional configuration/setup your application needs when running
 * unit tests in this file.
 */

use Cake\Core\Configure;

// Set cache to different files to prevent cross-contamination
define('CACHE_PREFIX', 'cli_');

require dirname(__DIR__) . '/vendor/autoload.php';

define('PHPUNIT_TESTSUITE', true);
require dirname(__DIR__) . '/config/bootstrap.php';

$_SERVER['PHP_SELF'] = '/index.php';
$_SERVER['SERVER_NAME'] = 'zuluru31.zuluru.org';
$_SERVER['HTTP_HOST'] = 'zuluru31.zuluru.org';
$_SERVER['REQUEST_SCHEME'] = 'https';
$_SERVER['HTTPS'] = 1;


//\Cake\Core\Plugin::load('Migrations');
//$migrations = new \Migrations\Migrations();
//$migrations->markMigrated(null, ['target' => 20180622171412]);
//$migrations->migrate();

// When testing controllers, set notice frequency to something under 0 so we don't need the fixture everywhere.
Configure::write('notice_frequency', -1);

