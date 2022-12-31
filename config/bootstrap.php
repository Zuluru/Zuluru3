<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.10.8
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Configure paths required to find CakePHP + general filepath constants
 */
require __DIR__ . '/paths.php';

/**
 * Bootstrap CakePHP.
 *
 * Does the various bits of setup that CakePHP needs to do.
 * This includes:
 *
 * - Registering the CakePHP autoloader.
 * - Setting the default application paths.
 */
require CORE_PATH . 'config' . DS . 'bootstrap.php';

use App\Core\ModuleRegistry;
use App\Event\GameListener;
use App\Event\InitializationListener;
use App\Event\PaymentListener;
use App\Event\RegistrationListener;
use Cake\Cache\Cache;
use Cake\Console\ConsoleErrorHandler;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Database\Type;
use Cake\Datasource\ConnectionManager;
use Cake\Error\ErrorHandler;
use Cake\Event\EventManager;
use Cake\Log\Log;
use Cake\Mailer\Email;
use Cake\Mailer\TransportFactory;
use Cake\Network\Request;
use Cake\Utility\Security;
use Detection\MobileDetect;
use josegonzalez\Dotenv\Loader;

/**
 * Read .env file(s).
 */
if (!env('APP_NAME') && file_exists(ZULURU_CONFIG . '.env')) {
	$dotenv = new Loader([ZULURU_CONFIG . '.env']);
	$dotenv->parse()
		->putenv(true)
		->toEnv(true)
		->toServer(true);
}
if (defined('PHPUNIT_TESTSUITE') && PHPUNIT_TESTSUITE && file_exists(ZULURU_CONFIG . '.env_test')) {
	$dotenv = new Loader([ZULURU_CONFIG . '.env_test']);
	$dotenv->parse()
		->putenv(true)
		->toEnv(true)
		->toServer(true);
}

/**
 * Read configuration file and inject configuration into various
 * CakePHP classes.
 *
 * By default there is only one configuration file. It is often a good
 * idea to create multiple configuration files, and separate the configuration
 * that changes from configuration that does not. This makes deployment simpler.
 */
try {
	Configure::config('default', new PhpConfig());
	Configure::load('app', 'default', false);
} catch (\Exception $e) {
	exit($e->getMessage() . "\n");
}

/*
 * Load an environment local configuration file.
 * You can use a file like app_local.php to provide local overrides to your
 * shared configuration.
 */
try {
	if (defined('DOMAIN_PLUGIN')) {
		Configure::load(DOMAIN_PLUGIN . '.app_local');
	} else {
		Configure::load('app_local');
	}
} catch (\Exception $ex) {
	// File might just not exist
}

Configure::load('features');

/*
 * When debug = true the metadata cache should only last
 * for a short time.
 */
if (Configure::read('debug')) {
	Configure::write('Cache._cake_model_.duration', '+2 minutes');
	Configure::write('Cache._cake_core_.duration', '+2 minutes');
}

/**
 * Set server timezone to UTC. You can change it to another timezone of your
 * choice but using UTC makes time calculations / conversions easier.
 */
date_default_timezone_set(Configure::read('App.timezone.name'));

/**
 * Configure the mbstring extension to use the correct encoding.
 */
mb_internal_encoding(Configure::read('App.encoding'));

/**
 * Set the default locale. This controls how dates, number and currency is
 * formatted and sets the default language to use for translations.
 */
ini_set('intl.default_locale', Configure::read('App.defaultLocale'));

/**
 * Register application error and exception handlers.
 */
$isCli = PHP_SAPI === 'cli';
if ($isCli) {
	(new ConsoleErrorHandler(Configure::read('Error')))->register();
} else {
	(new ErrorHandler(Configure::read('Error')))->register();
}

// Include the CLI bootstrap overrides.
if ($isCli) {
	require __DIR__ . '/bootstrap_cli.php';
	Configure::load('app_cli');
}

/**
 * Set the full base URL.
 * This URL is used as the base of all absolute links.
 *
 * If you define fullBaseUrl in your config file you can remove this.
 */
if (!Configure::read('App.fullBaseUrl')) {
	$s = null;
	if (env('HTTPS')) {
		$s = 's';
	}

	$httpHost = env('HTTP_HOST');
	if (isset($httpHost)) {
		Configure::write('App.fullBaseUrl', 'http' . $s . '://' . $httpHost);
	}
	unset($httpHost, $s);
}

Cache::setConfig(Configure::consume('Cache'));
ConnectionManager::setConfig(Configure::consume('Datasources'));
TransportFactory::setConfig(Configure::consume('EmailTransport'));
Email::setConfig(Configure::consume('Email'));
Log::setConfig(Configure::consume('Log'));
Security::setSalt(Configure::consume('Security.salt'));

/**
 * Setup detectors for mobile and tablet.
 */
Request::addDetector('mobile', function ($request) {
	$detector = new MobileDetect();
	return $detector->isMobile();
});
Request::addDetector('tablet', function ($request) {
	$detector = new MobileDetect();
	return $detector->isTablet();
});

/**
 * Custom Inflector rules, can be set to correctly pluralize or singularize
 * table, model, controller names or whatever other string is passed to the
 * inflection functions.
 *
 * Inflector::rules('plural', ['/^(inflect)or$/i' => '\1ables']);
 * Inflector::rules('irregular', ['red' => 'redlings']);
 * Inflector::rules('uninflected', ['dontinflectme']);
 * Inflector::rules('transliteration', ['/å/' => 'aa']);
 */

/**
 * Enable immutable time objects in the ORM.
 *
 * You can enable default locale format parsing by adding calls
 * to `useLocaleParser()`. This enables the automatic conversion of
 * locale specific date formats. For details see
 * @link https://book.cakephp.org/3.0/en/core-libraries/internationalization-and-localization.html#parsing-localized-datetime-data
 */
Type::build('time')->useImmutable();
Type::build('date')->useImmutable();
Type::build('datetime')->useImmutable();
Type::build('timestamp')->useImmutable();

/**
 * Enable default locale format parsing.
 * This is needed for matching the auto-localized string output of Time() class when parsing dates.
 */
Type::build('datetime')->useLocaleParser();

/**
 * Create and register all the required listener objects
 */
$globalListeners = [
	new InitializationListener(),
	new RegistrationListener(),
	new GameListener(),
	new PaymentListener(),
];
$callbacks = Configure::read('App.callbacks');
foreach ($callbacks as $name => $config) {
	if (is_numeric($name) && is_string($config)) {
		$name = $config;
		$config = [];
	}
	$globalListeners[] = ModuleRegistry::getInstance()->load("Callback:{$name}", $config);
}
foreach ($globalListeners as $listener) {
	EventManager::instance()->on($listener);
}
if (Configure::check('App.globalListeners')) {
	$globalListeners = array_merge($globalListeners, Configure::read('App.globalListeners'));
}
Configure::write('App.globalListeners', $globalListeners);

if (!defined('ZULURU_VERSION')) {
	define('ZULURU_MAJOR', 3);
	define('ZULURU_MINOR', 3);
	define('ZULURU_REVISION', 2);
}
