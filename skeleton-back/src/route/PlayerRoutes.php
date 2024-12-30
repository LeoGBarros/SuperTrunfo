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
                $group->put('/{id:[0-9]+}', \Controller\Adm\AdmCardController::class . ':updateCard');
                $group->get('/{id:[0-9]+}', \Controller\Adm\AdmCardController::class . ':getCardByID');
                $group->get('/', \Controller\Adm\AdmCardController::class . ':getAllCards');
                $group->delete('/{id:[0-9]+}', \Controller\Adm\AdmCardController::class . ':deleteCardByID');
            });
        });
    }
}