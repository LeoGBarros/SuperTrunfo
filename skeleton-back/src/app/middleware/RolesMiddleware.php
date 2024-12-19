<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

use App\Session;
use App\Respostas;
use Model\Adm\UserModel;
use PDOException;

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
        try {
            $authHeader = $request->getHeader('Authorization')[0] ?? null;
    
            if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
                return Respostas::error(Respostas::ERR_UNAUTHORIZED);
            }
    
            $token = substr($authHeader, 7);
            $isAdmin = UserModel::checkAdmin($token);
    
            // Lógica de autorização baseada em status de administrador
            $allowed = false;
            foreach (self::RolesPassthrough[$isAdmin ? 'admin' : 'player'] as $pattern => $method) {
                if (
                    preg_match($pattern, $request->getUri()->getPath()) &&
                    in_array($request->getMethod(), $method)
                ) {
                    $allowed = true;
                    break;
                }
            }
    
            if (!$allowed) {
                return Respostas::error(Respostas::ERR_UNKNOWN);
            }
    
            // Continua para o próximo middleware ou handler
            return $handler->handle($request);
        } catch (PDOException $e) {
            return Respostas::error(Respostas::ERR_UNKNOWN);
        }
    }
    

}
