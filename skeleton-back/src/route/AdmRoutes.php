<?php

namespace Route;

class RoutesAdm
{
    public function __construct($app)
    {
        $app->group('/adm', function ($group) {
            $group->group('/cards', function ($group) {
                $group->post('/', \Controller\Adm\AdmCardController::class . ':createCard')->add(new \Validation\CardValidation::cardsValidation());
                $group->put('/', \Controller\Adm\AdmCardController::class . ':updateCard')->add(new \Validation\AttitudeValidation::validateUpdateAttitude());
                $group->get('/{card_id:[0-9]+}', \Controller\Adm\AdmCardController::class . ':getCardByID');
                $group->get('/', \Controller\Adm\AdmCardController::class . ':getAll');                
                $group->delete('/{card_id:[0-9]+}', \Controller\Adm\AdmCardController::class . ':deleteCard');
            $group->group('/deck', function ($group) {
                $group->post('/', \Controller\Adm\AdmDeckController::class . ':createDeck')->add(new \Validation\DeckValidation::deckValidation());
                $group->put('/', \Controller\Adm\AdmCardController::class . ':updateDeck')->add(new \Validation\DeckValidation::validateUpdateDeck());
            });
        })->add(new \Middleware\RolesMiddleware())->add(new \Middleware\AdmMiddleware());
    }
}
