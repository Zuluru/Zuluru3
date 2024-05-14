<?php
/**
 * @var \Cake\Routing\RouteBuilder $routes
 */

$routes->plugin(
    'PayPalPayment',
    ['path' => '/paypal'],
    function ($routes) {
        $routes->fallbacks('InflectedRoute');
    }
);
