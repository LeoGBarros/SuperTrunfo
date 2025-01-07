<?php

namespace Controller\Player;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Respostas;
use Model\GameModel;

require __DIR__ . '/../../model/GameModel.php';

class PlayerGameController
{   
    public function createGame(Request $request, Response $response): Response
    {
        $headers = $request->getHeader('Authorization');
        $authHeader = $headers[0] ?? null;
    
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            // $error = ['error' => 'Token JWT ausente ou inválido.'];
            // $response->getBody()->write(json_encode($error));
            // return $response->withStatus(401)->withHeader('Content-Type', 'application/json');

            return Respostas::error(Respostas::ERR_CREATE_CARD);
        }
    
        $token = substr($authHeader, 7);
    
        try {
            $ownerId = GameModel::getOwnerId($token);
            // error_log('Owner ID extraído: ' . $ownerId);
    
            $data = json_decode($request->getBody()->getContents(), true);
            $deckSelect = $data['deck_select'] ?? null;
    
            if (!$deckSelect) {
                // $error = ['error' => 'O campo "deck_select" é obrigatório.'];
                // $response->getBody()->write(json_encode($error));
                // return $response->withStatus(400)->withHeader('Content-Type', 'application/json');

                return Respostas::error(Respostas::ERR_CREATE_CARD);
            }
    
            // Cria o jogo e retorna o session_id
            $sessionId = GameModel::createGame($ownerId, $deckSelect);
    
            $responseData = [
                'message' => 'Jogo criado com sucesso.',
                'session_id' => $sessionId
            ];
    
            $response->getBody()->write(json_encode($responseData));
            return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    
        } catch (\Exception $e) {
            $error = ['error' => $e->getMessage()];
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }
    

    public function getCreatedGameByID(Request $request, Response $response, array $args){
        if (($result = GameModel::getCreatedGameByID($args['session_id'])) === null) {
            $response->getBody()->write(json_encode(['error' => 'Cant take select Game']));
            return $response->withStatus(400);            
        }                
        $response->getBody()->write(json_encode($result)); 
        return $response;
    } 
        
    public function getAllCreatedGames(Request $request, Response $response, array $args){
        $result = GameModel::getAllCreatedGames();
        if ($result === null) {

            $response->getBody()->write(json_encode(['error' => 'Cant take all Games']));
            return $response->withStatus(400);           
            }                
            $response->getBody()->write(json_encode($result)); 
            return $response;
    }

    public function joinGame(Request $request, Response $response, array $args)
    {
        // Log da entrada na função
        // error_log('Entered joinGame Controller');

        $session_id = $args['session_id'] ?? null;
        // error_log('Session ID: ' . json_encode($session_id));

        // Validar se o ID da sessão foi fornecido
        if (!$session_id) {
            // error_log('Invalid Session ID');
            $response->getBody()->write(json_encode(['error' => 'Invalid Session ID']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $authorizationHeader = $request->getHeaderLine('Authorization');
        // error_log('Authorization Header: ' . json_encode($authorizationHeader));

        if (!$authorizationHeader) {
            // error_log('Authorization token missing');
            $response->getBody()->write(json_encode(['error' => 'Authorization token missing']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        try {
            $token = str_replace('Bearer ', '', $authorizationHeader);
            // error_log('Token: ' . $token);

            $decodedToken = JWT::decode($token, new Key('a9b1k87YbOpq3h2Mz8aXvP9wLQZ5R4pJ3cLrV5ZJ5DkRt0jQYzZnM+W8X4Lo0yZp', 'HS256'));
            // error_log('Decoded Token: ' . json_encode($decodedToken));

            $user_ID = $decodedToken->user_ID ?? null;
            // error_log('User ID: ' . json_encode($user_ID));

            if (!$user_ID) {
                // error_log('User unauthorized');
                $response->getBody()->write(json_encode(['error' => 'User unauthorized.']));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            // Buscar o jogo no banco de dados
            $currentGame = GameModel::getCreatedGameByID($session_id);
            // error_log('Current Game Data: ' . json_encode($currentGame));

            if (!$currentGame) {
                // error_log('Game not found for Session ID: ' . $session_id);
                $response->getBody()->write(json_encode(['error' => 'Game not found.']));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            $fieldsToUpdate = ['otherPlayer_id' => $user_ID];   
            $result = GameModel::joinGame($session_id, $fieldsToUpdate);
           

            // Verificar o resultado da atualização
            if ($result) {
                $response->getBody()->write(json_encode(['success' => true, 'message' => 'Player joined successfully.']));
                return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
            } else {
                $response->getBody()->write(json_encode(['error' => 'Failed to join game.']));
                return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
            }
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => 'Invalid token: ' . $e->getMessage()]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
    }


    

    // public function deleteCreatedGameID(Request $request, Response $response, array $args){
    //     if (($result = DeckModel::deleteDeckByID($args['id'])) === null) {
    //         $response->getBody()->write(json_encode(['error' => 'Cant delete Deck']));
    //             return $response->withStatus(400);            
    //         }                
    //         $response->getBody()->write(json_encode($result)); 
    //         return $response;
    //     }       


}
