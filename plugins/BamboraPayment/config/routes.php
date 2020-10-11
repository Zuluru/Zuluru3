<?php
use Cake\Routing\Router;

Router::plugin(
    'BamboraPayment',
    ['path' => '/bambora'],
    function ($routes) {
        $routes->fallbacks('InflectedRoute');
    }
);
