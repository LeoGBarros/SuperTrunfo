<?php

namespace Controller\Adm;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Firebase\JWT\JWT;
use Model\UserModel;
use PDOException;


class AdmUserController
{  

    private static $secretKey = "a9b1k87YbOpq3h2Mz8aXvP9wLQZ5R4pJ3cLrV5ZJ5DkRt0jQYzZnM+W8X4Lo0yZp";

    public function createUser( Request $request, Response $response, array $args){        
        $params = json_decode($request->getBody()->getContents(), true) ?? [];            
        
        $result = UserModel::createUser($params['username'], $params['password'], $params['Admin']);   
        
        if ($result === false) {
            $response->getBody()->write(json_encode(['error' => 'Não foi possivel criar usuario']));
            return $response->withStatus(400);            
        }                
        $response->getBody()->write(json_encode(['succes' => 'Usuario criado com sucesso']));
        return $response->withStatus(200);
    }

    public function getUserByID(Request $request, Response $response, array $args){
        if (($result = UserModel::getUserByID($args['id'])) === null) {
            $response->getBody()->write(json_encode(['error' => 'ID invalido']));
            return $response->withStatus(400);           
        }                
        $response->getBody()->write(json_encode($result));
        return $response;
    } 
        
    public function getAllUsers(Request $request, Response $response, array $args){
        $result = UserModel::getAllUsers();
        if ($result === null) {
            $response->getBody()->write(json_encode(['error' => 'Não foi possivel pegar todos os usuarios']));
            return $response->withStatus(400);            
        }                
        $response->getBody()->write(json_encode($result)); // AJUSTAR ERROS
        return $response;
    }        

    public function deleteUserByID(Request $request, Response $response, array $args){
        if (($result = UserModel::deleteUserByID($args['id'])) === null) {
            $response->getBody()->write(json_encode($result));
            return $response;            
        }                
        $response->getBody()->write(json_encode($result)); // AJUSTAR ERROS
        return $response;
    }
        
    public function updateUser(Request $request, Response $response, array $args){
        $params = json_decode($request->getBody()->getContents(), true) ?? [];
        $user_id = $args['id'] ?? null;

        if (!$user_id) {
            $response->getBody()->write(json_encode(['error' => 'ID invalido']));
            return $response->withStatus(400);
        }

        $currentUser = UserModel::getUserById($user_id);

        if (!$currentUser) {
            $response->getBody()->write(json_encode(['error' => 'User not found']));
            return $response->withStatus(404);
        }

        $keys = ['username', 'password', 'Admin'];

        $fieldsToUpdate = [];
        foreach ($keys as $key) {
            if (isset($params[$key]) && $params[$key] !== $currentUser[$key]) {
                $fieldsToUpdate[$key] = $params[$key];
            }
        }
        if (empty($fieldsToUpdate)) {
            $response->getBody()->write(json_encode(['message' => 'No fields updated']));
            return $response->withStatus(200);
        }

        $result = UserModel::updateUser($user_id, $fieldsToUpdate);

        if ($result) {
            $response->getBody()->write(json_encode(['success' => true]));
            return $response->withStatus(200);
        } else {
            $response->getBody()->write(json_encode(['error' => 'Failed to update user']));
            return $response->withStatus(500);
        }
    }


    public function loginUser(Request $request, Response $response, array $args){
        $data = json_decode($request->getBody()->getContents(), true) ?? [];
        $errors = [];
        
        if (empty($data['username'])) {
            $errors[] = 'Username é obrigatório.';
        }
        if (empty($data['password'])) {
            $errors[] = 'Senha é obrigatória.';
        }

        if (!empty($errors)) {
            $response->getBody()->write(json_encode(['error' => $errors]));
            return $response->withStatus(400);
        }

        try {                
            $userData = UserModel::loginUser($data['username'], $data['password']);

            if (!$userData) {
                $response->getBody()->write(json_encode(['message' => 'Usuário ou senha incorretos.']));
                return $response->withStatus(401);
            }

            // Gera o token JWT
            $issuedAt = time();
            $expiration = $issuedAt + 3600; // Token válido por 1hr

            $payload = [
                'iss' => 'supertrunfodb',
                'iat' => $issuedAt,
                'exp' => $expiration,
                'user_ID' => $userData['id'],
                'username' => $userData['username'],
                'admin' => $userData['Admin']
            ];

            $jwt = JWT::encode($payload, self::$secretKey, 'HS256');

            // Retorna o token e os dados do usuário
            $response->getBody()->write(json_encode([
                'token' => $jwt,
                'user' => [
                    'id' => $userData['id'],
                    'username' => $userData['username'],
                    'admin' => $userData['Admin']
                ],
                'token_expiration' => date('Y-m-d H:i:s', $expiration)
            ]));

            return $response->withStatus(200);

        } catch (PDOException $e) {
            $response->getBody()->write(json_encode(['error' => 'Erro interno: ' . $e->getMessage()]));
            return $response->withStatus(500);
        }
    }

    public function checkAdmin(Request $request, Response $response, array $args): Response{
        // error_log('Iniciando checkAdmin');
        
        $headers = $request->getHeader('Authorization');
        $authHeader = $headers[0] ?? null;
        // error_log('Auth Header: ' . var_export($authHeader, true));
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            $error = ['error' => 'Token JWT ausente ou inválido.'];
            // error_log('Erro: ' . json_encode($error));
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
        $token = substr($authHeader, 7);
        // error_log('Token extraído: ' . $token);
        try {
            $isAdmin = UserModel::checkAdmin($token);
            // error_log('Valor de isAdmin no controller: ' . var_export($isAdmin, true));
            // Certifique-se de que o $args['id'] está definido
            $userId = $args['id'] ?? null;
            // error_log('User ID recebido: ' . var_export($userId, true));
            if (!$userId) {
                $error = ['error' => 'ID do usuário não fornecido.'];
                // error_log('Erro: ' . json_encode($error));
                $response->getBody()->write(json_encode($error));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }

            $message = [
                'message' => $isAdmin 
                    ? 'Usuário é administrador.' 
                    : 'Usuário não é administrador.',
                'userId' => $userId,
                'admin' => $isAdmin
            ];
            // error_log('Mensagem de resposta: ' . json_encode($message));
            $response->getBody()->write(json_encode($message));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (\Throwable $e) {
            $error = ['error' => 'Erro inesperado: ' . $e->getMessage()];
            // error_log('Erro inesperado: ' . $e->getMessage());
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }       
        
}
