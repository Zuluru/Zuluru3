<?php
use Cake\Routing\Router;

Router::plugin(
    'PayPalPayment',
    ['path' => '/paypal'],
    function ($routes) {
        $routes->fallbacks('InflectedRoute');
    }
);
