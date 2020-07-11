<?php
namespace App;

use Ajax\Middleware\AjaxMiddleware;
use App\Authentication\ActAsIdentity;
use App\Core\UserCache;
use App\Event\FlashTrait;
use App\Exception\ForbiddenRedirectException;
use App\Exception\LockedIdentityException;
use App\Http\Middleware\ActAsMiddleware;
use App\Middleware\AffiliateConfigurationLoader;
use App\Middleware\ConfigurationLoader;
use App\Http\Middleware\CookiePathMiddleware;
use App\Http\Middleware\CsrfProtectionMiddleware;
use App\Policy\TypeResolver;
use Authentication\AuthenticationService;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\Authenticator\UnauthenticatedException;
use Authentication\Identifier\IdentifierInterface;
use Authentication\Middleware\AuthenticationMiddleware;
use Authorization\AuthorizationService;
use Authorization\AuthorizationServiceProviderInterface;
use Authorization\Exception\ForbiddenException;
use Authorization\Exception\MissingIdentityException;
use Authorization\Middleware\AuthorizationMiddleware;
use Authorization\Policy\OrmResolver;
use Authorization\Policy\ResolverCollection;
use App\Middleware\LocalizationMiddleware;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Exception\MissingPluginException;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\BaseApplication;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\Middleware\EncryptedCookieMiddleware;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\I18n\I18n;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\Routing\Router;
use Cake\Utility\Inflector;
use Cake\Utility\Security;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Application setup class.
 *
 * This defines the bootstrapping logic and middleware layers you
 * want to use in your application.
 */
class Application extends BaseApplication implements AuthenticationServiceProviderInterface, AuthorizationServiceProviderInterface {

	use FlashTrait;

	/**
	 * {@inheritDoc}
	 */
	public function bootstrap() {
		if (!defined('DOMAIN_PLUGIN') && !empty(env('DOMAIN_PLUGIN'))) {
			define('DOMAIN_PLUGIN', env('DOMAIN_PLUGIN'));
		}

		// This has to be loaded first, so it's known for getting any local configuration file
		if (defined('DOMAIN_PLUGIN')) {
			$this->addPlugin(DOMAIN_PLUGIN, ['bootstrap' => false, 'routes' => true]);
		}

		// Call parent to load bootstrap from files.
		parent::bootstrap();

		if (array_key_exists('REQUEST_URI', $_SERVER) && strpos($_SERVER['REQUEST_URI'], '/installer/') !== false) {
			Configure::write('Installer.config', ['installer']);
			$this->addPlugin('Installer', ['bootstrap' => true, 'routes' => true]);
		} else {
			if (PHP_SAPI === 'cli') {
				try {
					$this->addPlugin('Bake');
					$this->addPlugin('Transifex');
				} catch (MissingPluginException $e) {
					// Do not halt if the plugin is missing
				}

				$this->addPlugin('Scheduler', ['autoload' => true]);
			}

			/*
			 * Only try to load DebugKit in development mode
			 * Debug Kit should not be installed on a production system
			 */
			if (Configure::read('debug')) {
				$this->addPlugin('DebugKit', ['bootstrap' => true]);
			}

			$this->addPlugin('Authentication');
			$this->addPlugin('Authorization');
			$this->addPlugin('Ajax');
			$this->addPlugin('Josegonzalez/Upload');
			$this->addPlugin('Muffin/Footprint');
			$this->addPlugin('Cors', ['bootstrap' => true, 'routes' => false]);

			$this->addPlugin('ZuluruBootstrap');
			$this->addPlugin('ZuluruJquery');

			if (Configure::read('App.theme') && (!defined('DOMAIN_PLUGIN') || Configure::read('App.theme') != DOMAIN_PLUGIN)) {
				$this->addPlugin(Configure::read('App.theme'), ['bootstrap' => false, 'routes' => false]);
			}
		}

		$this->addPlugin('Bootstrap', ['bootstrap' => true]);
		$this->addPlugin('Migrations');

		foreach (TableRegistry::getTableLocator()->get('Settings')->find()->where(['category' => 'plugin']) as $plugin) {
			if ($plugin->value) {
				$this->addPlugin($plugin->name, ['bootstrap' => true, 'routes' => true]);
			}
		}
	}

	/**
	 * Returns a service provider instance.
	 *
	 * @param \Psr\Http\Message\ServerRequestInterface $request Request
	 * @param \Psr\Http\Message\ResponseInterface $response Response
	 * @return \Authentication\AuthenticationServiceInterface
	 */
	public function getAuthenticationService(ServerRequestInterface $request, ResponseInterface $response) {
		$service = new AuthenticationService();
		$authenticators = Configure::read('Security.authenticators');

		// The fields to use for identification
		$users_table = TableRegistry::getTableLocator()->get(Configure::read('Security.authModel'));
		$fields = [
			IdentifierInterface::CREDENTIAL_USERNAME => $users_table->userField,
			IdentifierInterface::CREDENTIAL_PASSWORD => $users_table->pwdField,
		];

		if (empty($authenticators)) {
			// If Zuluru is managing authentication alone, handle old passwords and migrate them
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

			// Load the session-based authenticator
			$service->loadAuthenticator('Authentication.Session');

			// Add the cookie-based "remember me" authenticator
			$service->loadAuthenticator('Authentication.Cookie', [
				'loginUrl' => Router::url(Configure::read('App.urls.login')),
				'fields' => $fields,
				'cookie' => [
					'name' => 'ZuluruAuth',
					'expire' => new Time('+1 year'),
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
				'userModel' => Configure::read('Security.authModel'),
			],
			'passwordHasher' => $hasher,
		]);

		if ($request->is('json')) {
			// For JSON requests, we allow JWT authentication, as well as form-based login through the token URL
			$service->loadIdentifier('Authentication.JwtSubject', [
				'tokenField' => $users_table->getPrimaryKey(),
				'resolver' => [
					'className' => 'Authentication.Orm',
					'userModel' => Configure::read('Security.authModel'),
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

	public function getAuthorizationService(ServerRequestInterface $request, ResponseInterface $response) {
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
			$dir = opendir(APP . 'Locale');
			if ($dir) {
				while (false !== ($entry = readdir($dir))) {
					if (file_exists(APP . 'Locale' . DS . $entry . DS . 'default.po')) {
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
	 * Setup the middleware queue your application will use.
	 *
	 * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to setup.
	 * @return \Cake\Http\MiddlewareQueue The updated middleware queue.
	 */
	public function middleware($middlewareQueue) {
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

		$cookie_localization = new LocalizationMiddleware($this->getLocales(), null);
		$cookie_localization->setSearchOrder([
			LocalizationMiddleware::FROM_COOKIE,
		]);
		$cookie_localization->setCookieName('ZuluruLocale');
		$cookie_localization->setLocaleCallback(function ($locale) {
			if ($locale) {
				I18n::setLocale($locale);
			}
		});

		$user_localization = new LocalizationMiddleware($this->getLocales(), null);
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
		});
		$user_localization->setLocaleCallback(function ($locale) {
			if ($locale) {
				I18n::setLocale($locale);
			}
		});

		$middlewareQueue
			// Catch any exceptions in the lower layers,
			// and make an error page/response
			->add(ErrorHandlerMiddleware::class)

			// Handle plugin/theme assets like CakePHP normally does.
			->add(new AssetMiddleware([
				'cacheTime' => Configure::read('Asset.cacheTime')
			]))

			->add(ConfigurationLoader::class)

			->add($header_localization)
			->add($cookie_localization)

			// Add routing middleware.
			// Routes collection cache enabled by default, to disable route caching
			// pass null as cacheConfig, example: `new RoutingMiddleware($this)`
			// you might want to disable this cache in case your routing is extremely simple
			->add(new RoutingMiddleware($this))

			// Parse request bodies, allowing for JSON data in authentication
			->add(BodyParserMiddleware::class)

			// Add CSRF protection middleware.
			->add(function (
				ServerRequest $request,
				Response $response,
				callable $next
			) {
				$payment = ($request->getParam('controller') == 'Registrations' && $request->getParam('action') == 'payment');
				if (!$payment && !$request->is('json')) {
					$csrf = new CsrfProtectionMiddleware();

					// This will invoke the CSRF middleware's `__invoke()` handler,
					// just like it would when being registered via `add()`.
					return $csrf($request, $response, $next);
				}

				return $next($request, $response);
			})

			// Add encrypted cookie middleware.
			->add(new EncryptedCookieMiddleware(['ZuluruAuth'], Security::getSalt()))

			// Adjust cookie paths
			->add(CookiePathMiddleware::class)

			// Handle redirects and error messages for Ajax requests
			->add(new AjaxMiddleware(['viewClass' => 'Ajax']))

			// Add authentication
			->add(function (
				ServerRequest $request,
				Response $response,
				callable $next
			) {
				// Do not attempt authentication for the installer
				if ($request && $request->getParam('plugin') != 'Installer') {
					// TODO: Read these from site configuration
					if (Configure::read('feature.authenticate_through') == 'Zuluru') {
						$loginAction = Router::url(Configure::read('App.urls.login'), true);
					} else {
						$loginAction = Router::url(['controller' => 'Leagues', 'action' => 'index'], true);
					}

					$authentication = new AuthenticationMiddleware($this, [
						'unauthenticatedRedirect' => $loginAction,
						'queryParam' => 'redirect',
					]);

					return $authentication($request, $response, $next);
				} else {
					return $next($request, $response);
				}
			})

			// Add unauthorized flash message
			->add(function (
				ServerRequest $request,
				Response $response,
				callable $next
			) {
				try {
					return $next($request, $response);
				} catch (UnauthenticatedException $ex) {
					$this->Flash('error', __('You must login to access full site functionality.'));
					throw $ex;
				}
			})

			// Ensure that the logged in user, if there is one, has a person record
			->add(function (
				ServerRequest $request,
				Response $response,
				callable $next
			) {
				$identity = $request->getAttribute('identity');
				if ($identity) {
					$user = $identity->getOriginalData();

					if (!$user->has('person')) {
						// Immediately post-authentication, the user record might not have person data in it
						$users_table = TableRegistry::getTableLocator()->get(Configure::read('Security.authModel'));
						$users_table->loadInto($user, ['People']);

						if (!$user->has('person')) {
							// Still might not have person data, if it's a brand new user from a third-party system
							$user->person = $users_table->People->createPersonRecord($user);
						}

						// We need to update the identity, so that the new person ID is in the in-memory record
						$result = $request->getAttribute('authentication')->persistIdentity($request, $response, $user);
						$request = $result['request'];
						$response = $result['response'];
					}
				}

				return $next($request, $response);
			})

			->add($user_localization)

			->add(function (
				ServerRequest $request,
				Response $response,
				callable $next
			) {
				// Do not attempt authorization for the installer
				if ($request && $request->getParam('plugin') != 'Installer') {
					// We wrap this in a function, so that by the time the Router::url calls below happen,
					// the router has been initialized by its middleware, and the base path is set.
					$authorization = new AuthorizationMiddleware($this, [
						'identityDecorator' => ActAsIdentity::class,
						'requireAuthorizationCheck' => Configure::read('debug'),
						'unauthorizedHandler' => [
							'className' => 'RedirectFlash',
							'unauthenticatedUrl' => Router::url(Configure::read('App.urls.login')),
							'unauthorizedUrl' => Router::url('/'),
							'exceptions' => [
								MissingIdentityException::class => function($subject, $request, $response, $exception, $options) {
									$subject->Flash('error', __('You must login to access full site functionality.'));
									$url = $subject->getUrl($request, array_merge($options, ['referrer' => true, 'unauthenticated' => true]));

									return $response
										->withHeader('Location', $url)
										->withStatus($options['statusCode']);
								},
								ForbiddenRedirectException::class => function($subject, $request, $response, $exception, $options) {
									$url = $exception->getUrl();
									if (empty($url)) {
										$url = $subject->getUrl($request, array_merge($options, ['referrer' => false]));
									} else if (is_array($url)) {
										$url = Router::url($url);
									}
									if ($exception->getMessage()) {
										$subject->Flash($exception->getClass(), $exception->getMessage(), $exception->getOptions());
									}

									return $response
										->withHeader('Location', $url)
										->withStatus($options['statusCode']);
								},
								LockedIdentityException::class => function($subject, $request, $response, $exception, $options) {
									$subject->Flash('error', __('Your profile is currently {0}, so you can continue to use the site, but may be limited in some areas. To reactivate, {1}.',
										__(UserCache::getInstance()->read('Person.status')),
										__('contact {0}', Configure::read('email.admin_name'))
									));
									$url = $subject->getUrl($request, array_merge($options, ['referrer' => false]));

									return $response
										->withHeader('Location', $url)
										->withStatus($options['statusCode']);
								},
								ForbiddenException::class => function($subject, $request, $response, $exception, $options) {
									$subject->Flash('error', __('You do not have permission to access that page.'));
									$url = $subject->getUrl($request, array_merge($options, ['referrer' => false]));

									return $response
										->withHeader('Location', $url)
										->withStatus($options['statusCode']);
								},
							],
						],
					]);
					return $authorization($request, $response, $next);
				} else {
					return $next($request, $response);
				}
			})

			// Handle "act as" parameters in the URL
			->add(ActAsMiddleware::class)

			->add(AffiliateConfigurationLoader::class)
		;

		if (Configure::read('debug')) {
			// Disable authz for debugkit
			$middlewareQueue->add(function ($req, $res, $next) {
				if ($req->getParam('plugin') === 'DebugKit') {
					$req->getAttribute('authorization')->skipAuthorization();
				}
				return $next($req, $res);
			});
		}

		return $middlewareQueue;
	}
}
