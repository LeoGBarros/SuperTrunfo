<?php

namespace Route;

require __DIR__ . '/AdmRoutes.php';

use Slim\Routing\RouteCollectorProxy;

class Router{
    public static function Register($app){
        $app->group('/api', function(RouteCollectorProxy $group){
            new RoutesAdm($group);
        });
    }
}


// class RoutesAdm
// {
//     public function __construct($app)
//     {
//         $app->group('/adm', function (RouteCollectorProxy $group) {
//             $group->get('/', \Controller\Adm\AdmCardController::class . ':getAllCards');
//             $group->group('/cards', function (RouteCollectorProxy $group) {
//                 $group->post('/', \Controller\Adm\AdmCardController::class . ':createCard');
//                 $group->put('/', \Controller\Adm\AdmCardController::class . ':updateCard');
//                 $group->get('/{card_id:[0-9]+}', \Controller\Adm\AdmCardController::class . ':getCardByID');
//                 $group->get('/', \Controller\Adm\AdmCardController::class . ':getAll');
//                 $group->delete('/{card_id:[0-9]+}', \Controller\Adm\AdmCardController::class . ':deleteCard');
//             $group->group('/deck', function (RouteCollectorProxy $group) {
//                 $group->post('/', \Controller\Adm\AdmDeckController::class . ':createDeck');
//                 $group->put('/', \Controller\Adm\AdmCardController::class . ':updateDeck');
//                 });
//             }); 
//         });
//     }
// }