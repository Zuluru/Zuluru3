<?php
use Cake\Routing\Router;

Router::plugin(
    'StripePayment',
    ['path' => '/stripe'],
    function ($routes) {
        $routes->fallbacks('InflectedRoute');
    }
);
