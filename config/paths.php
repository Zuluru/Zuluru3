<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         3.0.0
 * @license       MIT License (https://opensource.org/licenses/mit-license.php)
 */

/*
 * Use the DS to separate the directories in other defines
 */

use Cake\Core\Plugin;

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

/*
 * These defines should only be edited if you have cake installed in
 * a directory layout other than the way it is distributed.
 * When using custom settings be sure to use the DS and do not add a trailing DS.
 */

/*
 * The full path to the directory which holds "src", WITHOUT a trailing DS.
 */
define('ROOT', dirname(__DIR__));

/*
 * The actual directory name for the application directory. Normally
 * named 'src'.
 */
define('APP_DIR', 'src');

/*
 * Path to the application's directory.
 */
define('APP', ROOT . DS . APP_DIR . DS);

/*
 * Path to the config directory.
 */
define('CONFIG', ROOT . DS . 'config' . DS);

/*
 * File path to the webroot directory.
 *
 * To derive your webroot from your webserver change this to:
 *
 * `define('WWW_ROOT', rtrim($_SERVER['DOCUMENT_ROOT'], DS) . DS);`
 */
define('WWW_ROOT', ROOT . DS . 'webroot' . DS);

/*
 * Path to the tests directory.
 */
define('TESTS', ROOT . DS . 'tests' . DS);

if (defined('DOMAIN_PLUGIN')) {
	$plugins = Plugin::getCollection();
	$path = $plugins->findPath(DOMAIN_PLUGIN);

	/*
	 * Path to the temporary files directory.
	 */
	define('TMP', $path . 'tmp' . DS);

	/*
	 * Path to the logs directory.
	 */
	define('LOGS', $path . 'logs' . DS);

	/*
	 * Path to Zuluru's config directory; may be different from CONFIG in the case of multi-hosting.
	 */
	define('ZULURU_CONFIG', $path . 'config' . DS);

	/*
	 * Path to Zuluru's resources directory.
	 */
	define('ZULURU_RESOURCES', $path . 'resources' . DS);
} else {
	/*
	 * Path to the temporary files directory.
	 */
	define('TMP', ROOT . DS . 'tmp' . DS);

	/*
	 * Path to the logs directory.
	 */
	define('LOGS', ROOT . DS . 'logs' . DS);

	/*
	 * Path to Zuluru's config directory; may be different from CONFIG in the case of multi-hosting.
	 */
	define('ZULURU_CONFIG', ROOT . DS . 'config' . DS);

	/*
	 * Path to Zuluru's resources directory.
	 */
	define('ZULURU_RESOURCES', ROOT . DS . 'resources' . DS);
}

/*
 * Path to the cache files directory. It can be shared between hosts in a multi-server setup.
 */
define('CACHE', TMP . 'cache' . DS);

/*
 * Path to the resources directory.
 */
define('RESOURCES', ROOT . DS . 'resources' . DS);

/*
 * The absolute path to the "cake" directory, WITHOUT a trailing DS.
 *
 * CakePHP should always be installed with composer, so look there.
 */
define('CAKE_CORE_INCLUDE_PATH', ROOT . DS . 'vendor' . DS . 'cakephp' . DS . 'cakephp');

/*
 * Path to the cake directory.
 */
define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);
define('CAKE', CORE_PATH . 'src' . DS);
