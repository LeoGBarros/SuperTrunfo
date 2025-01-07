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
                $group->get('/{session_id:[0-9]+}', \Controller\Player\PlayerGameController::class . ':getCreatedGameByID');
                $group->get('/', \Controller\Player\PlayerGameController::class . ':getAllCreatedGames');
                $group->put('/joinGame/{session_id}', \Controller\Player\PlayerGameController::class . ':joinGame');
            });
        })->add(new RolesMiddleware());
    }
}