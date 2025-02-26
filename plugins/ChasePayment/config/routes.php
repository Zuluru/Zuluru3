<?php
/**
 * @var \Cake\Routing\RouteBuilder $routes
 */

$routes->plugin(
    'ChasePayment',
    ['path' => '/chase'],
    function ($routes) {
        $routes->fallbacks('InflectedRoute');
    }
);
