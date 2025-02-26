<?php
/**
 * @var \Cake\Routing\RouteBuilder $routes
 */

$routes->plugin(
    'StripePayment',
    ['path' => '/stripe'],
    function ($routes) {
        $routes->fallbacks('InflectedRoute');
    }
);
