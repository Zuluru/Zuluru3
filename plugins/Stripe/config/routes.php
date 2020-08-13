<?php
use Cake\Routing\Router;

Router::plugin(
    'Stripe',
    ['path' => '/stripe'],
    function ($routes) {
        $routes->fallbacks('InflectedRoute');
    }
);
