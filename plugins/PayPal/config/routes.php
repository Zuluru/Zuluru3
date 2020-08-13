<?php
use Cake\Routing\Router;

Router::plugin(
    'PayPal',
    ['path' => '/paypal'],
    function ($routes) {
        $routes->fallbacks('InflectedRoute');
    }
);
