<?php
use Cake\Routing\Router;

Router::plugin(
    'Chase',
    ['path' => '/chase'],
    function ($routes) {
        $routes->fallbacks('InflectedRoute');
    }
);
