<?php
use Cake\Utility\Inflector;

/**
 * This file contains system settings, but should not be changed. Anything
 * that normally needs to be configured on a per-system basis is set in the
 * .env file (copy .env.default to .env and edit that). If you need to
 * override something that doesn't allow for this (e.g. themes, callbacks),
 * copying app_local.default.php to app_local.php and make your changes there.
 */
$domain = env('HTTP_HOST');
if (strpos('www.', $domain) === 0) {
	$domain = substr($domain, 4);
}
if (strpos('zuluru.', $domain) === 0) {
	$domain = substr($domain, 7);
}

if (!defined('CACHE_PREFIX')) {
	define('CACHE_PREFIX', '');
}

// If we don't have a database set up yet, we can't store sessions in it.
if (!empty($_SERVER['REQUEST_URI']) && substr($_SERVER['REQUEST_URI'], 0, 10) == '/installer') {
	$session_defaults = 'php';
} else {
	$session_defaults = 'database';
}

$field = env('FIELD_NAME', 'field');

if (!defined('ZULURU')) {
	// This changes the name under which Zuluru presents itself.
	// It can only be changed if you are also making substantial
	// changes to the code, e.g. to customize for a specific use.
	// Even in that case, you are required to retain the Zuluru
	// trademark in the "Powered by" notice.
	define('ZULURU', 'Zuluru');
}

return [
	/**
	 * Debug Level:
	 *
	 * Production Mode:
	 * false: No error messages, errors, or warnings shown.
	 *
	 * Development Mode:
	 * true: Errors and warnings shown.
	 */
	'debug' => filter_var(env('DEBUG', false), FILTER_VALIDATE_BOOLEAN),

	/**
	 * Configure basic information about the application.
	 */
	'App' => [
		// The namespace to find app classes under.
		'namespace' => 'App',

		// The encoding used for HTML + database connections.
		'encoding' => env('APP_ENCODING', 'UTF-8'),

		// The default locale for translation, formatting currencies and
		// numbers, date and time.
		'defaultLocale' => env('APP_DEFAULT_LOCALE', 'en_US'),

		// Set up timezone information. See details in .env
		'timezone' => [
			'name' => env('TIMEZONE'),
		],

		// The base directory the app resides in. If false this will be auto
		// detected.
		'base' => false,

		// Name of app directory.
		'dir' => 'src',

		// The webroot directory.
		'webroot' => 'webroot',

		// The file path to webroot.
		'wwwRoot' => WWW_ROOT,

		// A base URL to use for absolute links. If false this will be auto
		// detected.
		'fullBaseUrl' => false,

		// Web path to the public images directory under webroot.
		'imageBaseUrl' => 'img/',

		// Web path to the public css directory under webroot.
		'cssBaseUrl' => 'css/',

		// Web path to the public js directory under webroot.
		'jsBaseUrl' => 'js/',

		// Web path to the public files directory, where permits, etc. live.
		'filesBaseUrl' => 'files/',

		// Configure paths for non class based resources. Supports the
		// `plugins`, `templates`, `locales`, `files`, `imgBase` and `uploads`
		// subkeys, which allow the definition of paths for plugins; view
		// templates; locale files; permits, exported standings, etc.; icon
		// packs; and uploaded files respectively.
		'paths' => [
			'plugins' => [ROOT . DS . 'plugins' . DS],
			'templates' => [APP . 'Template' . DS],
			'locales' => [APP . 'Locale' . DS],
			'files' => WWW_ROOT . 'files',
			'imgBase' => WWW_ROOT . 'img',
			'uploads' => ROOT . DS . 'upload',
		],

		// The domain this copy of Zuluru is running on.
		'domain' => $domain,

		// Configure URLs for some pages that Zuluru may need to link to, but
		// might not be under Zuluru control. Supports the `privacyPolicy`,
		// `register`, `login`, `logout` and `resetPassword` subkeys. Default
		// values use the CakePHP array notation, which will allow Zuluru to
		// generate correct URLs regardless of site configuration. You might
		// need to override them with strings if you are using a third-party
		// CMS. `privacyPolicy` and `logout` are optional; set them to false
		// to entirely disable generation of that type of link, e.g. Joomla
		// logout requires a token that Zuluru doesn't have access to, so
		// you'll need to rely on Joomla's own logout being visible somewhere.
		'urls' => [
			'privacyPolicy' => ['controller' => 'Pages', 'action' => 'privacy'],
			// TODO: Determine these dynamically in the most common situations and
			// only require configuration here in extreme cases.
			'register' => ['controller' => 'Users', 'action' => 'create_account'],
			'login' => ['controller' => 'Users', 'action' => 'login'],
			'logout' => ['controller' => 'Users', 'action' => 'logout'],
			'resetPassword' => ['controller' => 'Users', 'action' => 'reset_password'],
		],

		// Author information to include in HTML meta tags.
		'author' => 'Zuluru, https://zuluru.org/',

		// Which theme to use. Themes allow you to substantially customize the
		// look of your site, without making changes to the distributed files.
		// TODOLATER: See "docs/themes.txt" for more details.
		'theme' => null,

		// Which icon pack to use. Icon packs replace some or all of the
		// default icons by placing image files with the same name in a
		// subfolder of the base icon folder (see imgBase setting above).
		// For example, to use an icon pack called "bubbles", change this
		// setting to 'bubbles', create an {imgBase}/bubbles folder, and put
		// your new images in there. They will automatically be used by the
		// system. For any image that doesn't exist there, it will fall back
		// to the default one, no need to copy everything over.
		'iconPack' => 'default',

		// Any additional CSS files required. By adding CSS files, you can make
		// basic changes to the layout without changing the layout file.
		'additionalCss' => [],

		// Any callbacks (e.g. for third-party mailing list management) to use.
		// TODOLATER: See "docs/callbacks.txt" for more details.
		'callbacks' => [
		],

		// Approximate time at which reminder emails (attendance, rosters) will
		// be sent out. Must be in military (24 hour) format. Exact timing may
		// depend on how you configure your cron task. TODOLATER: See "docs/install.txt"
		// for more details.
		'reminderEmailTime' => '13:00',
	],

	/**
	 * Security and encryption configuration
	 */
	'Security' => [
		// A random string used in security hashing methods. See details in .env
		'salt' => env('SECURITY_SALT'),
		'authenticators' => [],

		// Which model to use for user authentication. Use 'Users' if you're
		// not sure.
		'authModel' => 'Users',
	],

	/**
	 * Apply timestamps with the last modified time to static assets (js, css, images).
	 * Will append a querystring parameter containing the time the file was modified.
	 * This is useful for busting browser caches.
	 *
	 * Set to true to apply timestamps when debug is true. Set to 'force' to always
	 * enable timestamping regardless of debug value.
	 */
	'Asset' => [
		'timestamp' => 'force',
	],

	/**
	 * Configure the cache adapters.
	 */
	'Cache' => [
		'default' => [
			'className' => 'File',
			'path' => CACHE,
			'url' => env('CACHE_DEFAULT_URL', null),
		],

		/**
		 * Configure the cache used for general framework caching.
		 * Translation cache files are stored with this configuration.
		 * Duration will be set to '+2 minutes' in bootstrap.php when debug = true
		 * If you set 'className' => 'Null' core cache will be disabled.
		 */
		'_cake_core_' => [
			'className' => 'File',
			'prefix' => CACHE_PREFIX . 'zuluru_cake_core_',
			'path' => CACHE . 'persistent' . DS,
			'serialize' => true,
			'duration' => '+1 years',
			'url' => env('CACHE_CAKECORE_URL', null),
		],

		/**
		 * Configure the cache for model and datasource caches. This cache
		 * configuration is used to store schema descriptions, and table listings
		 * in connections.
		 * Duration will be set to '+2 minutes' in bootstrap.php when debug = true
		 */
		'_cake_model_' => [
			'className' => 'File',
			'prefix' => CACHE_PREFIX . 'zuluru_cake_model_',
			'path' => CACHE . 'models' . DS,
			'serialize' => true,
			'duration' => '+1 years',
			'url' => env('CACHE_CAKEMODEL_URL', null),
		],

		/**
		 * Configure the cache for long-term Zuluru data. This cache configuration
		 * is used to store player, league and division data that changes infrequently
		 * and is heavy to load from the database and/or requires intensive calculations
		 * (for example standings and stats).
		 */
		'long_term' => [
			'className' => 'File',
			'prefix' => CACHE_PREFIX . 'zuluru_',
			'path' => CACHE . 'queries' . DS,
			'serialize' => true,
			'duration' => YEAR,
			'url' => env('CACHE_CAKEQUERIES_URL', null),
		],

		/**
		 * Configure the cache for short-term Zuluru data, good for only today.
		 */
		'today' => [
			'className' => 'File',
			'prefix' => CACHE_PREFIX . 'zuluru_',
			'path' => CACHE . 'queries' . DS,
			'serialize' => true,
			'duration' => '12:01am tomorrow',
			'url' => env('CACHE_CAKEQUERIES_URL', null),
		],
	],

	/**
	 * Configure the Error and Exception handlers used by your application.
	 *
	 * By default errors are displayed using Debugger, when debug is true and logged
	 * by Cake\Log\Log when debug is false.
	 *
	 * In CLI environments exceptions will be printed to stderr with a backtrace.
	 * In web environments an HTML page will be displayed for the exception.
	 * With debug true, framework errors like Missing Controller will be displayed.
	 * When debug is false, framework errors will be coerced into generic HTTP errors.
	 *
	 * Options:
	 *
	 * - `errorLevel` - int - The level of errors you are interested in capturing.
	 * - `trace` - boolean - Whether or not backtraces should be included in
	 *   logged errors/exceptions.
	 * - `log` - boolean - Whether or not you want exceptions logged.
	 * - `exceptionRenderer` - string - The class responsible for rendering
	 *   uncaught exceptions.  If you choose a custom class you should place
	 *   the file for that class in src/Error. This class needs to implement a
	 *   render method.
	 * - `skipLog` - array - List of exceptions to skip for logging. Exceptions that
	 *   extend one of the listed exceptions will also be skipped for logging.
	 *   E.g.:
	 *   `'skipLog' => ['Cake\Http\Exception\NotFoundException', 'Cake\Http\Exception\UnauthorizedException']`
	 * - `extraFatalErrorMemory` - int - The number of megabytes to increase
	 *   the memory limit by when a fatal error is encountered. This allows
	 *   breathing room to complete logging or error handling.
	 */
	'Error' => [
		'errorLevel' => E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED,
		'exceptionRenderer' => 'Cake\Error\ExceptionRenderer',
		'skipLog' => [
			'Cake\Http\Exception\GoneException',
			'Cake\Routing\Exception\MissingControllerException',
		],
		'log' => true,
		'trace' => true,
	],

	/**
	 * Email configuration.
	 *
	 * By defining transports separately from delivery profiles you can easily
	 * re-use transport configuration across multiple profiles.
	 *
	 * You can specify multiple configurations for production, development and
	 * testing.
	 *
	 * Each transport needs a `className`. Valid options are as follows:
	 *
	 *  Mail   - Send using PHP mail function
	 *  Smtp   - Send using SMTP
	 *  Debug  - Do not send the email, just return the result
	 *
	 * You can add custom transports (or override existing transports) by adding the
	 * appropriate file to src/Mailer/Transport.  Transports should be named
	 * 'YourTransport.php', where 'Your' is the name of the transport.
	 */
	'EmailTransport' => [
		'default' => [
			'className' => env('EMAIL_TRANSPORT'),
			'host' => env('SMTP_HOST'),
			'port' => env('SMTP_PORT'),
			'username' => env('SMTP_USERNAME'),
			'password' => env('SMTP_PASSWORD'),
			'timeout' => 30,
			'client' => null,
			'tls' => null,
			'url' => env('EMAIL_TRANSPORT_DEFAULT_URL', null),
		],
		// When the system is in debug mode, all email is written into the 'email' flash message
		// instead of being sent. If you use this, make sure you have
		//		echo $this->Session->flash('email');
		// in your default.ctp layout, and beware that email addresses enclosed in <>
		// will look like invalid HTML to your browser and be hidden unless you view source.
		'debug' => [
			'className' => 'Flash',
			'url' => env('EMAIL_TRANSPORT_FLASH_URL', null),
		],
	],

	/**
	 * Email delivery profiles
	 *
	 * Delivery profiles allow you to predefine various properties about email
	 * messages from your application and give the settings a name. This saves
	 * duplication across your application and makes maintenance and development
	 * easier. Each profile accepts a number of keys. See `Cake\Mailer\Email`
	 * for more information.
	 */
	'Email' => [
		'default' => [
			'transport' => 'default',
			'charset' => 'utf-8',
			'headerCharset' => 'utf-8',
			'from' => env('EMAIL_FROM'),
		],
		'debug' => [
			'transport' => 'debug',
		],
	],

	/**
	 * Connection information used by the ORM to connect
	 * to your application's datastores.
	 * Drivers include Mysql Postgres Sqlite Sqlserver
	 * See vendor\cakephp\cakephp\src\Database\Driver for complete list
	 */
	'Datasources' => [
		'default' => [
			'className' => 'Cake\Database\Connection',
			'driver' => 'Cake\\Database\\Driver\\' . env('SQL_DRIVER'),
			'persistent' => false,
			'host' => env('SQL_HOSTNAME'),
			'port' => env('SQL_PORT'),
			'username' => env('SQL_USERNAME'),
			'password' => env('SQL_PASSWORD'),
			'database' => env('SQL_DATABASE'),
			'encoding' => 'utf8mb4',
			'timezone' => env('TIMEZONE'),
			'flags' => [],
			'cacheMetadata' => true,
			'log' => filter_var(env('DEBUG_SQL_LOG', false), FILTER_VALIDATE_BOOLEAN),

			/**
			 * Set identifier quoting to true if you are using reserved words or
			 * special characters in your table or column names. Enabling this
			 * setting will result in queries built using the Query Builder having
			 * identifiers quoted when creating SQL. It should be noted that this
			 * decreases performance because each query needs to be traversed and
			 * manipulated before being executed.
			 */
			'quoteIdentifiers' => false,

			/**
			 * During development, if using MySQL < 5.6, uncommenting the
			 * following line could boost the speed at which schema metadata is
			 * fetched from the database. It can also be set directly with the
			 * mysql configuration directive 'innodb_stats_on_metadata = 0'
			 * which is the recommended value in production environments
			 */
			// TODOLATER: Users in shared environments may not have access to set this.
			//'init' => ['SET GLOBAL innodb_stats_on_metadata = 0'],

			'url' => env('DATABASE_URL', null),
		],

		/**
		 * The test connection is used during the test suite.
		 */
		'test' => [
			'className' => 'Cake\Database\Connection',
			'driver' => 'Cake\\Database\\Driver\\' . env('SQL_DRIVER'),
			'persistent' => false,
			'host' => env('SQL_TEST_HOSTNAME'),
			'port' => env('SQL_TEST_PORT'),
			'username' => env('SQL_TEST_USERNAME'),
			'password' => env('SQL_TEST_PASSWORD'),
			'database' => env('SQL_TEST_DATABASE'),
			'encoding' => 'utf8mb4',
			'timezone' => env('TIMEZONE'),
			'cacheMetadata' => true,
			'quoteIdentifiers' => false,
			'log' => filter_var(env('DEBUG_SQL_LOG', false), FILTER_VALIDATE_BOOLEAN),
			//'init' => ['SET GLOBAL innodb_stats_on_metadata = 0'],
			'url' => env('DATABASE_TEST_URL', null),
		],

		/**
		 * The debug_kit connection stores DebugKit meta-data.
		 */
		'debug_kit' => [
			'className' => 'Cake\Database\Connection',
			'driver' => 'Cake\\Database\\Driver\\' . env('SQL_DRIVER'),
			'persistent' => false,
			'host' => env('SQL_HOSTNAME'),
			'port' => env('SQL_PORT'),
			'username' => env('SQL_USERNAME'),
			'password' => env('SQL_PASSWORD'),
			'database' => env('SQL_DATABASE'),
			'encoding' => 'utf8mb4',
			'timezone' => env('TIMEZONE'),
			'cacheMetadata' => true,
			'quoteIdentifiers' => false,
			'log' => filter_var(env('DEBUG_SQL_LOG', false), FILTER_VALIDATE_BOOLEAN),
			//'init' => ['SET GLOBAL innodb_stats_on_metadata = 0'],
		],
	],

	/**
	 * Configures logging options
	 */
	'Log' => [
		'debug' => [
			'className' => 'Cake\Log\Engine\FileLog',
			'path' => LOGS,
			'file' => 'debug',
			'scopes' => ['notice', 'info', 'debug'],
			'levels' => ['notice', 'info', 'debug'],
			'url' => env('LOG_DEBUG_URL', null),
		],
		'error' => [
			'className' => 'Cake\Log\Engine\FileLog',
			'path' => LOGS,
			'file' => 'error',
			'levels' => ['warning', 'error', 'critical', 'alert', 'emergency'],
			'url' => env('LOG_ERROR_URL', null),
		],
		'queries' => [
			'className' => 'Cake\Log\Engine\FileLog',
			'path' => LOGS,
			'file' => 'sql',
			'scopes' => ['queriesLog'],
			'url' => env('LOG_QUERIES_URL', null),
		],
		'rules' => [
			'className' => 'Cake\Log\Engine\FileLog',
			'path' => LOGS,
			'file' => 'rules',
			'scopes' => ['rules'],
			'levels' => ['notice', 'info', 'debug'],
			'url' => env('LOG_RULES_URL', null),
		],
	],

	/**
	 * Session configuration.
	 *
	 * Contains an array of settings to use for session configuration. The
	 * `defaults` key is used to define a default preset to use for sessions, any
	 * settings declared here will override the settings of the default config.
	 *
	 * ## Options
	 *
	 * - `cookie` - The name of the cookie to use. Defaults to 'CAKEPHP'.
	 * - `cookiePath` - The url path for which session cookie is set. Maps to the
	 *   `session.cookie_path` php.ini config. Defaults to base path of app.
	 * - `timeout` - The time in minutes the session should be valid for.
	 *    Pass 0 to disable checking timeout.
	 *    Please note that php.ini's session.gc_maxlifetime must be equal to or greater
	 *    than the largest Session['timeout'] in all served websites for it to have the
	 *    desired effect.
	 * - `defaults` - The default configuration set to use as a basis for your session.
	 *    There are four built-in options: php, cake, cache, database.
	 * - `handler` - Can be used to enable a custom session handler. Expects an
	 *    array with at least the `engine` key, being the name of the Session engine
	 *    class to use for managing the session. CakePHP bundles the `CacheSession`
	 *    and `DatabaseSession` engines.
	 * - `ini` - An associative array of additional ini values to set.
	 *
	 * The built-in `defaults` options are:
	 *
	 * - 'php' - Uses settings defined in your php.ini.
	 * - 'cake' - Saves session files in CakePHP's /tmp directory.
	 * - 'database' - Uses CakePHP's database sessions.
	 * - 'cache' - Use the Cache class to save sessions.
	 *
	 * To define a custom session handler, save it at src/Network/Session/<name>.php.
	 * Make sure the class implements PHP's `SessionHandlerInterface` and set
	 * Session.handler to <name>
	 *
	 * To use database sessions, load the SQL file located at config/Schema/sessions.sql
	 */
	'Session' => [
		'defaults' => $session_defaults,
		'cookie' => 'ZuluruSession',
		'timeout' => 120,	// session times out after 2 hours of inactivity
	],

	/**
	 * Configure some I18N strings needed in the UI.
	 */
	'UI' => [
		'field' => $field,
		'field_cap' => Inflector::humanize($field),
		'fields' => Inflector::pluralize($field),
		'fields_cap' => Inflector::humanize(Inflector::pluralize($field)),
	],
];
