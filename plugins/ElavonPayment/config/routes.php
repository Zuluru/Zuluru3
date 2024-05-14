<?php
/**
 * @var \Cake\Routing\RouteBuilder $routes
 */

$routes->plugin(
	'ElavonPayment',
	['path' => '/elavon'],
	function ($routes) {
		$routes->fallbacks('InflectedRoute');
	}
);
