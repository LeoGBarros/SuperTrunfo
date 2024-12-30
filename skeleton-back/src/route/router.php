<?php

namespace Route;

require __DIR__ . '/AdmRoutes.php';
require __DIR__ . '/PlayerRoutes.php';

use Slim\Routing\RouteCollectorProxy;

class Router{
    public static function Register($app){
        $app->group('/api', function(RouteCollectorProxy $group){
            new RoutesAdm($group);
            new RoutesPlayer($group);
        });
    }
}