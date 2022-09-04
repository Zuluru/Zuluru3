<?php
use Cake\Routing\Router;

Router::plugin(
	'ElavonPayment',
	['path' => '/elavon'],
	function ($routes) {
		$routes->fallbacks('InflectedRoute');
	}
);
