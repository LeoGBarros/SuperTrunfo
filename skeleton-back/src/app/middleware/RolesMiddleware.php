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
            '/^\/?api\/adm\/cards\/[0-9]+\/?$/' => ['GET', 'DELETE'],
            
            '/^\/?api\/adm\/deck\/?$/' => ['POST', 'PUT', 'GET'],
            '/^\/?api\/adm\/deck\/[0-9]+\/?$/' => ['GET', 'DELETE'],

            '/^\/?api\/adm\/user\/?$/' => ['POST', 'PUT', 'GET'],
            '/^\/?api\/adm\/user\/checkAdmin\/[0-9]+$/' => ['GET'],
            '/^\/?api\/adm\/user\/[0-9]+\/?$/' => ['GET', 'DELETE'],
        ],
        'player' => [
            '/^\/?api\/player\/games\/?$/' => ['POST', 'PUT', 'GET'],
        ],
    ]; 



    public function __invoke(PsrRequest $request, RequestHandler $handler): PsrResponse
    {
        try {            
            $authHeader = $request->getHeader('Authorization')[0] ?? null;
            // error_log('Auth Header: ' . json_encode($authHeader));

            if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
                return $this->createErrorResponse(Respostas::ERR_UNAUTHORIZED);
            }
            
            $token = substr($authHeader, 7);
            // error_log('Token: ' . $token);
            
            $isAdmin = UserModel::checkAdmin($token);
            // error_log('Is Admin: ' . ($isAdmin ? 'true' : 'false'));

            // Identifica o papel do usuário
            $role = $isAdmin ? 'admin' : 'player';
            $path = $request->getUri()->getPath();
            $method = $request->getMethod();

            // error_log("Path: $path");
            // error_log("Method: $method");
            // error_log("Role: $role");

            // Recupera as permissões do papel
            $allowedRoutes = self::RolesPassthrough[$role] ?? null;
            if (!$allowedRoutes) {
                // error_log('RolesPassthrough não definido para o papel: ' . $role);
                return $this->createErrorResponse(Respostas::ERR_UNKNOWN);
            }

            // Log das permissões configuradas
            // error_log('Allowed Routes: ' . json_encode($allowedRoutes));

            $allowed = false;
            foreach ($allowedRoutes as $pattern => $methods) {
                if (
                    preg_match($pattern, $path) &&
                    in_array($method, $methods)
                ) {
                    // error_log('Pattern matched: ' . $pattern);
                    $allowed = true;
                    break;
                }
            }

            if (!$allowed) {
                // error_log('Access denied for path: ' . $path . ' with method: ' . $method);
                return $this->createErrorResponse(Respostas::ERR_UNKNOWN);
            }

            // Log de sucesso antes de passar para o próximo middleware/handler
            // error_log('Access granted for path: ' . $path . ' with method: ' . $method);
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
    private function createErrorResponse(array $error): PsrResponse
    {
        $response = new Response();
        $statusCode = $error['status'] ?? 500;
        $body = $error['body'] ? json_encode($error['body']) : '';
    
        $response->getBody()->write($body);
        return $response->withHeader('Content-Type', 'application/json')->withStatus($statusCode);
    }
    

}
