<?php

namespace App;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Route\Router;
use Route\RoutesAdm;
use Route\RoutesPlayer;
use Slim\Routing\RouteCollectorProxy;

class App
{
    /**
     * Inicia a execução do framework, declara os middlewares, handlers, tipo de acesso e chama o registro de rotas.
     */
    public static function run()
    {
        
        $app = AppFactory::create();         
        
        
       Router::Register($app);

       new RoutesAdm($app);
       new RoutesPlayer($app);
        $app->run();
    }
}