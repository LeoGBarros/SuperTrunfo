<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

use Slim\Psr7\Response;
use App\Respostas;
use Model\UserModel;
use PDOException;


/**
 * Middleware responsável por filtrar a disponibilidade das rotas para os diversos níveis de acesso.
 *
 */
class RolesMiddleware
{

    const RolesPassthrough = [
        'admin' => [
            '/^\/?api\/adm\/cards\/?$/' => ['POST', 'PUT', 'GET'],
            '/^\/?api\/adm\/cards\/[0-9]+\/?$/' => ['GET', 'DELETE','PUT'],
            
            '/^\/?api\/adm\/deck\/?$/' => ['POST', 'PUT', 'GET'],
            '/^\/?api\/adm\/deck\/[0-9]+\/?$/' => ['GET', 'DELETE','PUT'],

            '/^\/?api\/adm\/user\/?$/' => ['POST', 'PUT', 'GET'],
            '/^\/?api\/adm\/user\/checkAdmin\/[0-9]+$/' => ['GET'],
            '/^\/?api\/adm\/user\/[0-9]+\/?$/' => ['GET', 'DELETE','PUT'],

            '/^\/?api\/player\/games\/?$/' => ['POST', 'PUT', 'GET'],
            '/^\/?api\/player\/games\/[0-9]+\/?$/' => ['GET', 'DELETE'],
            '/^\/?api\/player\/games\/joinGame\/[0-9]+\/?$/' => ['PUT'],
            '/^\/?api\/player\/games\/startGame\/[0-9]+\/?$/' => ['PUT'],
            '/^\/?api\/player\/games\/getFirstCards\/[0-9]+\/?$/' => ['GET'],
            '/^\/?api\/player\/games\/compareCards\/[0-9]+\/?$/' => ['POST'],
            '/^\/?api\/player\/games\/gameInformation\/[0-9]+\/?$/' => ['GET'],
        ],
        'player' => [
            '/^\/?api\/player\/games\/?$/' => ['POST', 'PUT', 'GET'],
            '/^\/?api\/player\/games\/[0-9]+\/?$/' => ['GET', 'DELETE'],
            '/^\/?api\/player\/games\/joinGame\/[0-9]+\/?$/' => ['PUT'],
            '/^\/?api\/player\/games\/startGame\/[0-9]+\/?$/' => ['PUT'],
            '/^\/?api\/player\/games\/getFirstCards\/[0-9]+\/?$/' => ['GET'],
            '/^\/?api\/player\/games\/compareCards\/[0-9]+\/?$/' => ['POST'],
            '/^\/?api\/player\/games\/gameInformation\/[0-9]+\/?$/' => ['GET'],
            
        ],
    ]; 



    public function __invoke(PsrRequest $request, RequestHandler $handler): PsrResponse{
        try {           
            $path = $request->getUri()->getPath();
            $method = $request->getMethod();
            if ($path === '/api/adm/user/' && strtoupper($method) === 'POST') {
                return $handler->handle($request);
            }            
            $authHeader = $request->getHeader('Authorization')[0] ?? null;
            if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
                return $this->createErrorResponse(Respostas::ERR_UNAUTHORIZED);
            }
            
            $token = substr($authHeader, 7);       
            $isAdmin = UserModel::checkAdmin($token);
            $role = $isAdmin ? 'admin' : 'player';
            $path = $request->getUri()->getPath();
            $method = $request->getMethod();
            $allowedRoutes = self::RolesPassthrough[$role] ?? null;
            if (!$allowedRoutes) {
                return $this->createErrorResponse(Respostas::ERR_UNAUTHORIZED);
            }
            $allowed = false;
            foreach ($allowedRoutes as $pattern => $methods) {
                if (
                    preg_match($pattern, $path) &&
                    in_array($method, $methods)
                ) {
                    $allowed = true;
                    break;
                }
            }

            if (!$allowed) {
                return $this->createErrorResponse(Respostas::ERR_UNAUTHORIZED);
            }
            return $handler->handle($request);

        } catch (\PDOException $e) {            
            return $this->createErrorResponse(Respostas::ERR_SERVER);
        } catch (\Exception $e) {            
            return $this->createErrorResponse(Respostas::ERR_SERVER);
        }
    }
    
    /**
     * Método auxiliar para criar respostas de erro.
     */
    private function createErrorResponse(array $error): PsrResponse{       
        $response = new Response();
        $statusCode = $error['status'] ?? 500;  //Define um status padrão
        $body = isset($error['body']) ? json_encode($error['body']) : json_encode(['error' => 'Unknown error']);

        $response->getBody()->write($body);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }   

}
