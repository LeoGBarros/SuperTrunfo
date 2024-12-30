<?php

namespace Route;

use App\Middleware\RolesMiddleware;
use Slim\Routing\RouteCollectorProxy;


class RoutesAdm
{
    public function __construct($app)
    {
        $app->post('/login', \Controller\Adm\AdmUserController::class . ':loginUser');

        $app->group('/adm', function (RouteCollectorProxy $group) {            
           
            $group->group('/cards', function (RouteCollectorProxy $group) {
                $group->post('/', \Controller\Adm\AdmCardController::class . ':createCard');
                $group->put('/{id:[0-9]+}', \Controller\Adm\AdmCardController::class . ':updateCard');
                $group->get('/{id:[0-9]+}', \Controller\Adm\AdmCardController::class . ':getCardByID');
                $group->get('/', \Controller\Adm\AdmCardController::class . ':getAllCards');
                $group->delete('/{id:[0-9]+}', \Controller\Adm\AdmCardController::class . ':deleteCardByID');
            });

            
            $group->group('/deck', function (RouteCollectorProxy $group) {
                $group->post('/', \Controller\Adm\AdmDeckController::class . ':createDeck');
                $group->put('/{id:[0-9]+}', \Controller\Adm\AdmDeckController::class . ':updateDeck');
                $group->get('/{id:[0-9]+}', \Controller\Adm\AdmDeckController::class . ':getDeckByID');
                $group->get('/', \Controller\Adm\AdmDeckController::class . ':getAllDecks');
                $group->delete('/{id:[0-9]+}', \Controller\Adm\AdmDeckController::class . ':deleteDeckByID');
            });

            
            $group->group('/user', function (RouteCollectorProxy $group) {
                $group->post('/', \Controller\Adm\AdmUserController::class . ':createUser');
                $group->put('/{id:[0-9]+}', \Controller\Adm\AdmUserController::class . ':updateUser');
                $group->get('/{id:[0-9]+}', \Controller\Adm\AdmUserController::class . ':getUserByID');
                $group->get('/', \Controller\Adm\AdmUserController::class . ':getAllUsers');
                $group->get('/checkAdmin/{id:[0-9]+}', \Controller\Adm\AdmUserController::class . ':checkAdmin');
                $group->delete('/{id:[0-9]+}', \Controller\Adm\AdmUserController::class . ':deleteUserByID');
            });
        })->add(new RolesMiddleware());
    }
}
