<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

use App\Session;
use App\Response;

/**
 * Middleware responsável por filtrar a disponibilidade das rotas para os diversos níveis de acesso.
 *
 */
class RolesMiddleware
{

    const RolesPassthrough = [
        'admin' => [
            '/^\/?api\/adm\/cards$/' => ['POST'],
            '/^\/?api\/adm\/cards$/' => ['PUT'],
            '/^\/?api\/adm\/cards$/' => ['GET'],            
            '/^\/?api\/adm\/cards\/[0-9]+$/' => ['GET'],
            '/^\/?api\/adm\/cards\/[0-9]+$/' => ['DELETE'],

            
            '/^\/?api\/adm\/deck$/' => ['POST'],
            '/^\/?api\/adm\/deck$/' => ['PUT'],
            '/^\/?api\/adm\/deck$/' => ['GET'],            
            '/^\/?api\/adm\/deck\/[0-9]+$/' => ['GET'],
            '/^\/?api\/adm\/deck\/[0-9]+$/' => ['DELETE'],
            
        ],
        'player' => [
            '/^\/?api\/player\/game$/' => ['POST'],


            
        ],
    ];   



    public function __invoke(PsrRequest $request, RequestHandler $handler): PsrResponse
    {


        $allowed = false;
        foreach (self::RolesPassthrough[Session::getUserType()] as $pattern => $method) {
            if (
                preg_match($pattern, $request->getUri()->getPath()) &&
                in_array($request->getMethod(), $method)
            ) {
                $allowed = true;
            }
        }

        if (!$allowed) {
            return Response::error(Response::ERR_UNAUTHORIZED);
        }

        $response = $handler->handle($request);

        return $response;
    }
}
