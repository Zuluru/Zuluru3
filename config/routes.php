<?php
/**
 * Routes configuration.
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * It's loaded within the context of `Application::routes()` method which
 * receives a `RouteBuilder` instance `$routes` as method argument.
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Routing\Route\InflectedRoute;
use Cake\Routing\RouteBuilder;

/*
 * This file is loaded in the context of the `Application` class.
 * So you can use `$this` to reference the application class instance
 * if required.
 */
return static function (RouteBuilder $routes): void {
	/*
	 * The default class to use for all routes
	 *
	 * The following route classes are supplied with CakePHP and are appropriate
	 * to set as the default:
	 *
	 * - Route
	 * - InflectedRoute
	 * - DashedRoute
	 *
	 * If no call is made to `Router::defaultRouteClass()`, the class used is
	 * `Route` (`Cake\Routing\Route\Route`)
	 *
	 * Note that `Route` does not do any inflections on URLs which will result in
	 * inconsistently cased URLs when used with `{plugin}`, `{controller}` and
	 * `{action}` markers.
	 */
    $routes->setRouteClass(InflectedRoute::class);

	$routes->scope('/', function (RouteBuilder $builder): void {
		/*
		 * Make sure CakePHP parses file requests with other known extensions correctly
		 */
		$builder->setExtensions(['csv', 'ics', 'json']);

		/*
		 * Connect the root to the splash page instead
		 */
		$builder->connect('/', ['controller' => 'People', 'action' => 'splash']);

		/*
		 * ...and connect the rest of 'Pages' controller's URLs.
		 */
		$builder->connect('/pages/*', 'Pages::display');

		/*
		 * Connect the help pages
		 */
		$builder->connect('/help/*', 'Help::view');

		/*
		 * Connect most settings URLs to the edit function.
		 */
		$builder->connect('/settings/*', 'Settings::edit');

		/*
		 * Connect tournaments URLs back to the leagues controller, but make sure that leagues
		 * goes there first, so generated league URLs are correct.
		 */
		$builder->connect('/leagues/:action/*', ['controller' => 'Leagues']);
		$builder->connect('/tournaments', ['controller' => 'Leagues', 'action' => 'index', '?' => ['tournaments' => true]]);
		$builder->connect('/tournaments/:action/*', ['controller' => 'Leagues']);

		/*
		 * Connect catchall routes for all controllers.
		 *
		 * The `fallbacks` method is a shortcut for
		 *
		 * ```
		 * $builder->connect('/{controller}', ['action' => 'index']);
		 * $builder->connect('/{controller}/{action}/*', []);
		 * ```
		 *
		 * You can remove these routes once you've connected the
		 * routes you want in your application.
		 */
		$builder->fallbacks();
	});

	/*
	 * If you need a different set of middleware or none at all,
	 * open new scope and define routes there.
	 *
	 * ```
	 * $routes->scope('/api', function (RouteBuilder $builder): void {
	 *     // No $builder->applyMiddleware() here.
	 *
	 *     // Parse specified extensions from URLs
	 *     // $builder->setExtensions(['json', 'xml']);
	 *
	 *     // Connect API actions here.
	 * });
	 * ```
	 */
};
