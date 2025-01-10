<?php

namespace Route;

use App\Middleware\RolesMiddleware;
use Respect\Validation\Validator as v;
use Slim\Routing\RouteCollectorProxy;
use Validators\CardValidation;

class RoutesAdm
{
    public function __construct($app)
    {
        $app->post('/login', \Controller\Adm\AdmUserController::class . ':loginUser');

        $app->group('/adm', function (RouteCollectorProxy $group) {            
           
            $group->group('/cards', function (RouteCollectorProxy $group) {
                $group->post('/', \Controller\Adm\AdmCardController::class . ':createCard');
                $group->get('/', \Controller\Adm\AdmCardController::class . ':getAllCards');
            
                $group->group('/{id:[0-9]+}', function (RouteCollectorProxy $group) {
                    $group->put('', \Controller\Adm\AdmCardController::class . ':updateCard');
                    $group->get('', \Controller\Adm\AdmCardController::class . ':getCardByID');
                    $group->delete('', \Controller\Adm\AdmCardController::class . ':deleteCardByID');
                });
            });
            

            
            $group->group('/deck', function (RouteCollectorProxy $group) {
                $group->post('/', \Controller\Adm\AdmDeckController::class . ':createDeck');
                $group->get('/', \Controller\Adm\AdmDeckController::class . ':getAllDecks');

                $group->group('/{id:[0-9]+}', function (RouteCollectorProxy $group){
                    $group->put('', \Controller\Adm\AdmDeckController::class . ':updateDeck');
                    $group->get('', \Controller\Adm\AdmDeckController::class . ':getDeckByID');
                    $group->delete('', \Controller\Adm\AdmDeckController::class . ':deleteDeckByID');
                });
            });

            
            $group->group('/user', function (RouteCollectorProxy $group) {
                $group->post('/', \Controller\Adm\AdmUserController::class . ':createUser');
                $group->get('/', \Controller\Adm\AdmUserController::class . ':getAllUsers');
                $group->get('/checkAdmin/{id:[0-9]+}', \Controller\Adm\AdmUserController::class . ':checkAdmin');

                $group->group('/{id:[0-9]+}', function (RouteCollectorProxy $group){
                    $group->put('', \Controller\Adm\AdmUserController::class . ':updateUser');
                    $group->get('', \Controller\Adm\AdmUserController::class . ':getUserByID');
                    $group->delete('', \Controller\Adm\AdmUserController::class . ':deleteUserByID');
                });
            });
        })->add(new RolesMiddleware());
    }
}
