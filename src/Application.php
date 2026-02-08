<?php
namespace App;

use App\Authentication\ActAsIdentity;
use App\Http\Middleware\ActAsMiddleware;
use App\Http\Middleware\CookiePathMiddleware;
use App\Middleware\AffiliateConfigurationLoader;
use App\Middleware\AjaxMiddleware;
use App\Middleware\ConfigurationLoader;
use App\Middleware\LocalizationMiddleware;
use App\Middleware\NamedRoutingMiddleware;
use App\Middleware\UnauthorizedHandler\RedirectFlashHandler;
use App\Model\Entity\Plugin;
use App\Policy\MissingIdentityResult;
use App\Policy\RedirectResult;
use App\Policy\TypeResolver;
use Authentication\AuthenticationService;
use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\Authenticator\UnauthenticatedException;
use Authentication\Identifier\IdentifierInterface;
use Authentication\Middleware\AuthenticationMiddleware;
use Authorization\AuthorizationService;
use Authorization\AuthorizationServiceInterface;
use Authorization\AuthorizationServiceProviderInterface;
use Authorization\Exception\Exception;
use Authorization\Exception\ForbiddenException;
use Authorization\Middleware\AuthorizationMiddleware;
use Authorization\Middleware\UnauthorizedHandler\HandlerInterface;
use Authorization\Policy\OrmResolver;
use Authorization\Policy\ResolverCollection;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\BaseApplication;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\Middleware\EncryptedCookieMiddleware;
use Cake\Http\Middleware\SessionCsrfProtectionMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\Http\Response;
use Cake\I18n\FrozenTime;
use Cake\I18n\I18n;
use Cake\ORM\Locator\TableLocator;
use Cake\ORM\TableRegistry;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\Routing\Router;
use Cake\Utility\Inflector;
use Cake\Utility\Security;
use Composer\Autoload\ClassLoader;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Application setup class.
 *
 * This defines the bootstrapping logic and middleware layers you
 * want to use in your application.
 */
class Application extends BaseApplication implements AuthenticationServiceProviderInterface, AuthorizationServiceProviderInterface {

	/**
	 * {@inheritDoc}
	 */
	public function bootstrap(): void {
		if (!defined('DOMAIN_PLUGIN') && !empty(env('DOMAIN_PLUGIN'))) {
			define('DOMAIN_PLUGIN', env('DOMAIN_PLUGIN'));
		}

		// This has to be loaded first, so it's known for getting any local configuration file
		if (defined('DOMAIN_PLUGIN')) {
			$this->addPlugin(DOMAIN_PLUGIN, ['bootstrap' => true, 'routes' => true]);
		}

		// Call parent to load bootstrap from files.
		parent::bootstrap();

		if (array_key_exists('REQUEST_URI', $_SERVER) && strpos($_SERVER['REQUEST_URI'], '/installer/') !== false) {
			Configure::write('Installer.config', ['installer']);
			$this->addPlugin('CakePHPAppInstaller');
		} else {
			if (PHP_SAPI === 'cli') {
				$this->bootstrapCli();
			} else {
				FactoryLocator::add(
					'Table',
					(new TableLocator())->allowFallbackClass(false)
				);
			}

			/*
			 * Only try to load DebugKit in development mode
			 * Debug Kit should not be installed on a production system
			 */
			if (Configure::read('debug')) {
				$this->addOptionalPlugin('DebugKit', ['bootstrap' => true]);
			}

			// Load more plugins here
			$this->addPlugin('Muffin/Footprint');
			$this->addPlugin('Authentication');
			$this->addPlugin('Authorization');
			$this->addPlugin('Ajax', ['bootstrap' => true]);
			$this->addPlugin('Calendar');
			$this->addPlugin('Josegonzalez/Upload');
			$this->addPlugin('Cors', ['bootstrap' => true, 'routes' => false]);

			$this->addPlugin('ZuluruBootstrap');
			$this->addPlugin('ZuluruJquery');

			if (Configure::read('App.theme') && (!defined('DOMAIN_PLUGIN') || Configure::read('App.theme') !== DOMAIN_PLUGIN)) {
				$this->addPlugin(Configure::read('App.theme'), ['bootstrap' => false, 'routes' => false]);
			}

			try {
				foreach (TableRegistry::getTableLocator()->get('Plugins')->find()->where(['enabled' => true])->order('Plugins.name') as $plugin) {
					if ($plugin->path === "plugins/{$plugin->load_name}") {
						$this->addPlugin($plugin->load_name, ['bootstrap' => true, 'routes' => true]);
					} else {
						$this->addPluginToPsr($plugin, ['bootstrap' => true, 'routes' => true]);
					}
				}
			} catch (\Exception $ex) {
				// The plugins table may not exist, if the migration hasn't run.
			}
		}

		$this->addPlugin('BootstrapUI', ['bootstrap' => true]);
	}

	private function addPluginToPsr(Plugin $plugin, array $config): void
	{
		/** @var ClassLoader $loader */
		$loader = require ROOT . '/vendor/autoload.php';
		$loader->addPsr4($plugin->load_name . '\\', ROOT . DS . $plugin->path . DS . 'src');
		$this->addPlugin($plugin->load_name, $config);
	}

	/**
	 * Returns a service provider instance.
	 *
	 * @param \Psr\Http\Message\ServerRequestInterface $request Request
	 * @return \Authentication\AuthenticationServiceInterface
	 */
	public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface {
		// TODO: Read these from site configuration
		if (Configure::read('feature.authenticate_through') == 'Zuluru') {
			// Don't use `true` parameter as base URL may not be set in tests, causing malformed URLs
			$loginAction = Router::url(Configure::read('App.urls.login'));
		} else {
			$loginAction = Router::url(['plugin' => null, 'controller' => 'Leagues', 'action' => 'index'], true);
		}

		$service = new AuthenticationService([
			'unauthenticatedRedirect' => $loginAction,
			'queryParam' => 'redirect',
		]);
		$authenticators = Configure::read('Security.authenticators');

		// The fields to use for identification
		$users_table = TableRegistry::getTableLocator()->get(Configure::read('Security.authPlugin') . Configure::read('Security.authModel'));
		$fields = [
			IdentifierInterface::CREDENTIAL_USERNAME => $users_table->userField,
			IdentifierInterface::CREDENTIAL_PASSWORD => $users_table->pwdField,
		];

		if (empty($authenticators)) {
			// If Zuluru is managing authentication alone, handle old passwords and migrate them
			$hasher = Configure::read('Security.hashers');
			if (empty($hasher)) {
				$hashMethod = Configure::read('Security.hashMethod', 'sha256');
				$hasher = [
					'className' => 'Authentication.Fallback',
					'hashers' => [
						[
							'className' => 'Authentication.Default',
						],
						[
							'className' => 'Authentication.Legacy',
							'hashType' => $hashMethod,
						],
						[
							'className' => 'LegacyNoSalt',
							'hashType' => $hashMethod,
						],
					],
				];
			}

			// Load the session-based authenticator
			$service->loadAuthenticator('Authentication.Session');

			// Add the cookie-based "remember me" authenticator
			$service->loadAuthenticator('Authentication.Cookie', [
				'loginUrl' => Router::url(Configure::read('App.urls.login')),
				'fields' => $fields,
				'cookie' => [
					'name' => 'ZuluruAuth',
					'expires' => new FrozenTime('+1 year'),
					'path' => '/' . trim($request->getAttribute('webroot'), '/'),
				],
			]);
		} else {
			// Load third-party authenticators. We don't load the session authenticator
			// directly here, but it may be used by these internally.
			foreach ($authenticators as $authenticator => $authenticator_config) {
				if (is_numeric($authenticator)) {
					$authenticator = $authenticator_config;
					$authenticator_config = [];
				}
				$authenticator_obj = $service->loadAuthenticator($authenticator, array_merge($authenticator_config, ['service' => $service]));
				if (property_exists($authenticator_obj, 'hasher')) {
					$hasher = $authenticator_obj->hasher;
				}
			}
		}

		// Add the password-based identifier, using configuration from above
		$service->loadIdentifier('Authentication.Password', [
			'fields' => $fields,
			'resolver' => [
				'className' => 'Authentication.Orm',
				'userModel' => Configure::read('Security.authPlugin') . Configure::read('Security.authModel'),
			],
			'passwordHasher' => $hasher,
		]);

		if ($request->is('json')) {
			// For JSON requests, we allow JWT authentication, as well as form-based login through the token URL
			$service->loadIdentifier('Authentication.JwtSubject', [
				'tokenField' => $users_table->getPrimaryKey(),
				'resolver' => [
					'className' => 'Authentication.Orm',
					'userModel' => Configure::read('Security.authPlugin') . Configure::read('Security.authModel'),
				],
			]);

			$service->loadAuthenticator('Authentication.Form', [
				'fields' => $fields,
				'loginUrl' => Router::url(['controller' => 'Users', 'action' => 'token', '_ext' => 'json']),
			]);

			$service->loadAuthenticator('Authentication.Jwt', [
				'returnPayload' => false
			]);
		} else if (empty($authenticators)) {
			// For non-JSON requests, we allow form-based login through the standard login URL
			$service->loadAuthenticator('Authentication.Form', [
				'fields' => $fields,
				'loginUrl' => Router::url(Configure::read('App.urls.login')),
			]);
		}

		return $service;
	}

	public function getAuthorizationService(ServerRequestInterface $request): AuthorizationServiceInterface {
		$resolver = new ResolverCollection([
			new OrmResolver(),
			new TypeResolver([
				'Controller' => function ($name) {
					if (substr($name, -10) != 'Controller') {
						return false;
					}
					return Inflector::singularize(substr($name, 0, -10));
				},
				'Model\\Table' => function ($name) {
					if (substr($name, -5) != 'Table') {
						return false;
					}
					return Inflector::singularize(substr($name, 0, -5));
				},
				'Model\\Entity' => false,
				'Authorization' => function ($name, $resource, $resolver) {
					return $resource->getResolver($resolver);
				},
			]),
		]);

		return new AuthorizationService($resolver);
	}

	public static function getLocales() {
		$translations = Cache::read('available_translations');
		$translation_strings = Cache::read('available_translation_strings');
		if (!$translations || !$translation_strings) {
			$translations = ['en' => 'English'];
			$translation_strings = ["en: 'English'"];
			$dir = opendir(ROOT . DS . 'resources' . DS . 'locales');
			if ($dir) {
				while (false !== ($entry = readdir($dir))) {
					if (file_exists(ROOT . DS . 'resources' . DS . 'locales' . DS . $entry . DS . 'default.po')) {
						$name = \Locale::getDisplayName($entry, $entry);
						if ($name != $entry) {
							$translations[$entry] = $name;
							$translation_strings[] = "$entry: '$name'";
						}
					}
				}
			}
			Cache::write('available_translations', $translations);
			Cache::write('available_translation_strings', $translation_strings);
		}
		Configure::write('available_translations', $translations);
		Configure::write('available_translation_strings', implode(', ', $translation_strings));

		return array_keys($translations);
	}

	/**
	 * Set up the middleware queue your application will use.
	 *
	 * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to setup.
	 * @return \Cake\Http\MiddlewareQueue The updated middleware queue.
	 */
	public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue {
		// We use three copies of the localization middleware.
		// The first will set it based on the header, but won't update the cookie. It defaults to
		// English. This is what will be used for anyone who is not logged in.
		// TODO: Set the default with a callback, so it can be set based on site configuration?
		// The second will set it based on the cookie, if found, which can only be set based on a
		// personal preference in the third copy, or through the ULS selector (if enabled).
		// With these two, the locale will be set as best as we can for any other processes that run
		// up to and including authentication: anyone logged in will have their preference honoured
		// through the previously set cookie, others will use the header, with English fallback.
		// The third will check for personal preference for the logged-in user, and set the cookie
		// only if that preference is found, but the presence of the cookie will short-circuit that
		// to eliminate the database query.
		$header_localization = new LocalizationMiddleware($this->getLocales(), 'en');
		$header_localization->setSearchOrder([
			LocalizationMiddleware::FROM_HEADER,
		]);
		$header_localization->setLocaleCallback(function ($locale) {
			if ($locale) {
				I18n::setLocale($locale);
			}
		});

		$cookie_localization = new LocalizationMiddleware($this->getLocales(), 'en');
		$cookie_localization->setSearchOrder([
			LocalizationMiddleware::FROM_COOKIE,
		]);
		$cookie_localization->setCookieName('ZuluruLocale');
		$cookie_localization->setLocaleCallback(function ($locale) {
			if ($locale) {
				I18n::setLocale($locale);
			}
		});

		$user_localization = new LocalizationMiddleware($this->getLocales(), 'en');
		$user_localization->setSearchOrder([
			LocalizationMiddleware::FROM_COOKIE,
			LocalizationMiddleware::FROM_CALLBACK,
		]);
		$user_localization->setCookieName('ZuluruLocale');
		$user_localization->setSearchCallback(function (ServerRequestInterface $request) {
			$identity = $request->getAttribute('identity');
			if ($identity) {
				$preference = TableRegistry::getTableLocator()->get('Settings')->find()
					->where(['person_id' => $identity->person->id, 'name' => 'language'])
					->first();
				if ($preference && $preference->value) {
					return $preference->value;
				}
			}

			return 'en';
		});
		$user_localization->setLocaleCallback(function ($locale) {
			if ($locale) {
				I18n::setLocale($locale);
			}
		});

		$middlewareQueue
			// Catch any exceptions in the lower layers,
			// and make an error page/response
			->add(new ErrorHandlerMiddleware(Configure::read('Error')))

			// Handle plugin/theme assets like CakePHP normally does.
			->add(new AssetMiddleware([
				'cacheTime' => Configure::read('Asset.cacheTime')
			]))

			->add(ConfigurationLoader::class)

			->add($header_localization)
			->add($cookie_localization)

			// Add routing middleware.
			// If you have a large number of routes connected, turning on routes
			// caching in production could improve performance. For that when
			// creating the middleware instance specify the cache config name by
			// using it's second constructor argument:
			// `new RoutingMiddleware($this, '_cake_routes_')`
			->add(new RoutingMiddleware($this))

			// Backward compatibility with old CakePHP-style named URLs
			->add(new NamedRoutingMiddleware())

			// Parse various types of encoded request bodies so that they are
			// available as array through $request->getData()
			// https://book.cakephp.org/4/en/controllers/middleware.html#body-parser-middleware
			->add(new BodyParserMiddleware())

			// Cross Site Request Forgery (CSRF) Protection Middleware
			// https://book.cakephp.org/4/en/controllers/middleware.html#cross-site-request-forgery-csrf-middleware
			->add((new SessionCsrfProtectionMiddleware())
				->skipCheckCallback(function (ServerRequestInterface $request) {
					$payment = ($request->getParam('controller') === 'Payment' && $request->getParam('action') === 'index');
					return $payment || $request->is('json');
				})
			)

			// Add encrypted cookie middleware.
			->add(function (
				ServerRequestInterface $request,
				RequestHandlerInterface $handler
			): ResponseInterface {
				// Do not attempt cookie encryption for the installer
				if ($request->getParam('plugin') !== 'CakePHPAppInstaller') {
					return (new EncryptedCookieMiddleware(['ZuluruAuth'], Security::getSalt()))->process($request, $handler);
				}

				return $handler->handle($request);
			})

			// Adjust cookie paths
			->add(CookiePathMiddleware::class)

			// Handle redirects and error messages for Ajax requests
			->add(new AjaxMiddleware(['viewClass' => 'Ajax']))

			// Add authentication
			->add(function (
				ServerRequestInterface $request,
				RequestHandlerInterface $handler
			): ResponseInterface {
				// Do not attempt authentication for the installer
				if ($request->getParam('plugin') !== 'CakePHPAppInstaller') {
					return (new AuthenticationMiddleware($this))->process($request, $handler);
				}

				return $handler->handle($request);
			})

			// Add Footprint middleware
			->add('Muffin/Footprint.Footprint')

			// Add unauthorized flash message
			->add(function (
				ServerRequestInterface $request,
				RequestHandlerInterface $handler
			): ResponseInterface {
				try {
					return $handler->handle($request);
				} catch (UnauthenticatedException $ex) {
					$request->getFlash()->error(__('You must login to access full site functionality.'));
					throw $ex;
				}
			})

			// Ensure that the logged in user, if there is one, has a person record
			->add(function (
				ServerRequestInterface $request,
				RequestHandlerInterface $handler
			): ResponseInterface {
				$identity = $request->getAttribute('identity');
				if ($identity) {
					$user = $identity->getOriginalData();

					if (!$user->has('person')) {
						// Immediately post-authentication, the user record might not have person data in it
						$users_table = TableRegistry::getTableLocator()->get(Configure::read('Security.authPlugin') . Configure::read('Security.authModel'));
						$users_table->loadInto($user, ['People']);

						if (!$user->has('person')) {
							// Still might not have person data, if it's a brand new user from a third-party system
							$user->person = $users_table->createPersonRecord($user);
						}

						// We need to update the identity, so that the new person ID is in the in-memory record
						// The Response object is not used; the Authentication middleware takes care of adding anything
						// that's needed in there.
						$result = $request->getAttribute('authentication')->persistIdentity($request, new Response(), $user);
						$request = $result['request'];
					}
				}

				return $handler->handle($request);
			})

			->add($user_localization)

			->add(function (
				ServerRequestInterface $request,
				RequestHandlerInterface $handler
			): ResponseInterface {
				// Do not attempt authorization for the installer
				if ($request->getParam('plugin') !== 'CakePHPAppInstaller') {
					// We wrap this in a function, so that by the time the Router::url calls below happen,
					// the router has been initialized by its middleware, and the base path is set.
					$authorization = new AuthorizationMiddleware($this, [
						'identityDecorator' => ActAsIdentity::class,
						'requireAuthorizationCheck' => Configure::read('debug'),
						'unauthorizedHandler' => [
							'className' => RedirectFlashHandler::class,
							'unauthenticatedUrl' => Router::url(Configure::read('App.urls.login')),
							'unauthorizedUrl' => Router::url('/'),
							'exceptions' => [
								ForbiddenException::class => function(HandlerInterface $subject, ServerRequestInterface $request, Exception $exception, array $options) {
									$result = $exception->getResult();
									$url = [];

									if ($result instanceof MissingIdentityResult) {
										$url = $subject->getUrl($request, array_merge($options, ['referrer' => true, 'unauthenticated' => true]));
										$request->getFlash()->error(__('You must login to access full site functionality.'));
									} else if ($result instanceof RedirectResult) {
										$url = $result->getUrl();

										if ($result->getReason()) {
											$flashOptions = $result->getOptions();
											$flashOptions['element'] = $result->getElement();
											$request->getFlash()->set($result->getReason(), $flashOptions);
										}
									} else {
										$request->getFlash()->error(__('You do not have permission to access that page.'));
									}

									if (empty($url)) {
										$url = $subject->getUrl($request, array_merge($options, ['referrer' => false]));
									} else if (is_array($url)) {
										$url = Router::url($url);
									}

									$response = new Response();

									return $response
										->withHeader('Location', $url)
										->withStatus($options['statusCode']);
								},
							],
						],
					]);
					return $authorization->process($request, $handler);
				}

				return $handler->handle($request);
			})

			// Handle "act as" parameters in the URL
			->add(ActAsMiddleware::class)

			->add(AffiliateConfigurationLoader::class)
		;

		return $middlewareQueue;
	}

	/**
	 * Register application container services.
	 *
	 * @param \Cake\Core\ContainerInterface $container The Container to update.
	 * @return void
	 * @link https://book.cakephp.org/4/en/development/dependency-injection.html#dependency-injection
	 */
	public function services(ContainerInterface $container): void
	{
	}

	/**
	 * Bootstrapping for CLI application.
	 *
	 * That is when running commands.
	 *
	 * @return void
	 */
	protected function bootstrapCli(): void
	{
		$this->addOptionalPlugin('Cake/Repl');
		$this->addOptionalPlugin('Bake');

		if (Configure::read('debug')) {
			$this->addOptionalPlugin('CakephpFixtureFactories');
		}

		$this->addPlugin('Migrations');

		// Load more plugins here
		$this->addOptionalPlugin('Transifex');
		$this->addPlugin('Scheduler', ['autoload' => true]);
	}
}
