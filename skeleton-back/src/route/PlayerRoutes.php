<?php

namespace Route;

class RoutesPlayer
{
    public function __construct($app)
    {
        $app->group('/player', function ($group) {
            $group->group('/game', function ($group) {
                $group->post('/', \Controller\Adm\AdmCardController::class . ':createCard')->add(new \Validation\AttitudeValidation::notNull());
            });
        })->add(new \Middleware\RolesMiddleware())->add(new \Middleware\AdmMiddleware());
    }
}