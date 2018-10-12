<?php
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
 * @since     3.3.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App;

use App\Http\Middleware\CookiePathMiddleware;
use App\Http\Middleware\CsrfProtectionMiddleware;
use Cake\Core\Configure;
use Cake\Core\Exception\MissingPluginException;
use Cake\Core\Plugin;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\BaseApplication;
use Cake\Http\Middleware\EncryptedCookieMiddleware;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\I18n\Middleware\LocaleSelectorMiddleware;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\Utility\Security;

/**
 * Application setup class.
 *
 * This defines the bootstrapping logic and middleware layers you
 * want to use in your application.
 */
class Application extends BaseApplication {
	/**
	 * {@inheritDoc}
	 */
	public function bootstrap() {
		// Call parent to load bootstrap from files.
		parent::bootstrap();

		if (PHP_SAPI === 'cli') {
			try {
				Plugin::load('Bake');
			} catch (MissingPluginException $e) {
				// Do not halt if the plugin is missing
			}

			Plugin::load('Migrations');
			Plugin::load('Scheduler', ['autoload' => true]);
		} else {
			Configure::write('Installer.config', ['installer']);
			Plugin::load('Installer', ['bootstrap' => true, 'routes' => true]);
		}

		/*
		 * Only try to load DebugKit in development mode
		 * Debug Kit should not be installed on a production system
		 */
		if (Configure::read('debug')) {
			Plugin::load('DebugKit', ['bootstrap' => true]);
		}

		Plugin::load('Ajax');
		Plugin::load('Bootstrap', ['bootstrap' => true]);
		Plugin::load('Josegonzalez/Upload');
		Plugin::load('Muffin/Footprint');
		Plugin::load('ADmad/JwtAuth');
		Plugin::load('Cors', ['bootstrap' => true, 'routes' => false]);

		Plugin::load('ZuluruBootstrap');
		Plugin::load('ZuluruJquery');
	}

	/**
	 * Setup the middleware queue your application will use.
	 *
	 * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to setup.
	 * @return \Cake\Http\MiddlewareQueue The updated middleware queue.
	 */
	public function middleware($middlewareQueue) {
		$middlewareQueue
			// Catch any exceptions in the lower layers,
			// and make an error page/response
			->add(ErrorHandlerMiddleware::class)

			// Handle plugin/theme assets like CakePHP normally does.
			->add(new AssetMiddleware([
				'cacheTime' => Configure::read('Asset.cacheTime')
			]))

			// Set the valid locales
			->add(LocaleSelectorMiddleware::class)

			// Add routing middleware.
			// Routes collection cache enabled by default, to disable route caching
			// pass null as cacheConfig, example: `new RoutingMiddleware($this)`
			// you might want to disable this cache in case your routing is extremely simple
			->add(RoutingMiddleware::class)

			// Add CSRF protection middleware.
			->add(function (
				ServerRequest $request,
				Response $response,
				callable $next
			) {
				$params = $request->getAttribute('params');
				$contentType = $request->getHeader('content-type');
				$json = (array_key_exists('_ext', $params) && $params['_ext'] === 'json') ||
					(!empty($contentType) && $contentType[0] === 'application/json');
				if (!$json) {
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
		;

		return $middlewareQueue;
	}
}
