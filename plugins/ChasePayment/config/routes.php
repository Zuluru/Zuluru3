<?php
use Cake\Routing\Router;

Router::plugin(
    'ChasePayment',
    ['path' => '/chase'],
    function ($routes) {
        $routes->fallbacks('InflectedRoute');
    }
);
