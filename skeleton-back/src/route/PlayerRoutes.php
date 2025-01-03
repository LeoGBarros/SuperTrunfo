<?php

namespace Route;

use App\Middleware\RolesMiddleware;
use Slim\Routing\RouteCollectorProxy;

class RoutesPlayer
{
    public function __construct($app)
    {
        $app->group('/player', function (RouteCollectorProxy $group) {            
           
            $group->group('/games', function (RouteCollectorProxy $group) {
                $group->post('/', \Controller\Player\PlayerGameController::class . ':createGame');
            });
        });
    }
}