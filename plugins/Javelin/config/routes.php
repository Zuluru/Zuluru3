<?php
use Cake\Routing\Router;

Router::plugin(
    'Javelin',
    ['path' => '/javelin'],
    function ($routes) {
        $routes->fallbacks('InflectedRoute');
    }
);
