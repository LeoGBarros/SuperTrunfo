<?php

namespace Controller\Player;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Respostas;
use Model\CardModel;
use Model\GameModel;
use PDO;

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

            return Respostas::error(Respostas::ERR_CREATE_GAME);
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

                return Respostas::error(Respostas::ERR_VALIDATION);
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
            $response->getBody()->write(json_encode(['error' => 'ID invalido']));
            return $response->withStatus(400);            
        }                
        $response->getBody()->write(json_encode($result)); 
        return $response;
    } 
        
    public function getAllCreatedGames(Request $request, Response $response, array $args){
        $result = GameModel::getAllCreatedGames();
        if ($result === null) {

            $response->getBody()->write(json_encode(['error' => 'Não foi possivel pegar todos os jogos']));
            return $response->withStatus(400);           
            }                
            $response->getBody()->write(json_encode($result)); 
            return $response;
    }

    public function joinGame(Request $request, Response $response, array $args){
        // Log da entrada na função
        // error_log('Entered joinGame Controller');

        $session_id = $args['session_id'] ?? null;
        // error_log('Session ID: ' . json_encode($session_id));

        if (!$session_id) {
            // error_log('Invalid Session ID');
            $response->getBody()->write(json_encode(['error' => 'Session ID invalido']));
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
                $response->getBody()->write(json_encode(['error' => 'Usuario não autorizado.']));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            $currentGame = GameModel::getCreatedGameByID($session_id);
            // error_log('Current Game Data: ' . json_encode($currentGame));

            if (!$currentGame) {
                // error_log('Game not found for Session ID: ' . $session_id);
                $response->getBody()->write(json_encode(['error' => 'Game não encontrado.']));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            $fieldsToUpdate = ['otherPlayer_id' => $user_ID];   
            $result = GameModel::joinGame($session_id, $fieldsToUpdate);
        

            if ($result) {
                $response->getBody()->write(json_encode(['success' => true, 'message' => 'Player juntou se  com sucesso.']));
                return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
            } else {
                $response->getBody()->write(json_encode(['error' => 'Falha ao junta se ao game.']));
                return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
            }
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => 'Token invalido: ' . $e->getMessage()]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
    }


    public function startGame(Request $request, Response $response, array $args) {
        $session_id = $args['session_id'] ?? null;

        if (!$session_id) {
            $response->getBody()->write(json_encode(['error' => 'session_id não fornecido.']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        try {
            $game = GameModel::getCreatedGameByID($session_id);

            if (!$game) {
                $response->getBody()->write(json_encode(['error' => 'Sessão não encontrada.']));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            $Deck_ID = $game['deck_select'] ?? null;

            if (!$Deck_ID) {
                $response->getBody()->write(json_encode(['error' => 'Deck não encontrado para a sessão fornecida.']));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }

            GameModel::startGame($Deck_ID, $session_id);
            $response->getBody()->write(json_encode(['success' => true, 'message' => 'Jogo iniciado com sucesso.']));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => 'Erro ao iniciar o jogo: ' . $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    public function getFirstCards(Request $request, Response $response, array $args) {
        $session_id = $args['session_id'] ?? null;

        if (!$session_id) {
            $response->getBody()->write(json_encode(['error' => 'session_id não fornecido.']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        try {
            $cards = GameModel::getFirstCards($session_id);
            $response->getBody()->write(json_encode(['success' => true, 'data' => $cards]));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => 'Erro ao buscar as primeiras cartas: ' . $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
    

    public function compareCards(Request $request, Response $response, array $args) {
        $session_id = $args['session_id'] ?? null;
        $rawBody = $request->getBody()->getContents();
        // error_log("Corpo bruto recebido: " . $rawBody);
        $body = json_decode($rawBody, true);
        // error_log("Corpo decodificado: " . json_encode($body));

        $attribute = $body['attribute'] ?? null;
    
        $authorizationHeader = $request->getHeaderLine('Authorization');
        if (!$authorizationHeader) {
            $response->getBody()->write(json_encode(['error' => 'Token de autorização não fornecido.']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
    
        try {
            $token = str_replace('Bearer ', '', $authorizationHeader);
            $decodedToken = JWT::decode($token, new Key('a9b1k87YbOpq3h2Mz8aXvP9wLQZ5R4pJ3cLrV5ZJ5DkRt0jQYzZnM+W8X4Lo0yZp', 'HS256'));
            $user_ID = $decodedToken->user_ID ?? null;
                    
            if (!$user_ID) {
                throw new \Exception('Usuario não encontrado no token.');
            }
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => 'Token inválido: ' . $e->getMessage()]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
    
        if (!$session_id || !$attribute) {
            $response->getBody()->write(json_encode(['error' => 'session_id ou attribute não fornecido.']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    
        try {
            $game = GameModel::getCreatedGameByID($session_id);
            if (!$game) {
                $response->getBody()->write(json_encode(['error' => 'Sessão não encontrada.']));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }
    
            if ($game['whose_turn'] != $user_ID) {
                $response->getBody()->write(json_encode(['error' => 'Não é a vez deste jogador.']));
                return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
            }
    
            $cards = GameModel::getFirstCards($session_id);
            if (!$cards || !isset($cards['player1'], $cards['player2'])) {
                $response->getBody()->write(json_encode(['error' => 'Cartas não encontradas para os jogadores.']));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }
    
            $result = GameModel::compareFirstCards($cards['player1'], $cards['player2'], $attribute);
    
            if (strpos($result, 'Jogador 1 venceu') !== false) {
                GameModel::removeCardFromPlayer($session_id, 'player2');
            } elseif (strpos($result, 'Jogador 2 venceu') !== false) {
                GameModel::removeCardFromPlayer($session_id, 'player1');
            }
    
            $response->getBody()->write(json_encode(['success' => true, 'message' => $result]));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => 'Erro ao comparar as cartas: ' . $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    public function gameInformation(Request $request, Response $response, array $args) {
        $session_id = $args['session_id'] ?? null;

        if (!$session_id) {
            $response->getBody()->write(json_encode(['error' => 'session_id não fornecido.']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        try {
            $game = GameModel::getCreatedGameByID($session_id);

            if (!$game) {
                $response->getBody()->write(json_encode(['error' => 'Sessão não encontrada.']));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }
            $owner_id = $game['owner_id'] ?? null;
            $other_player_id = $game['otherPlayer_id'] ?? null;
            $whose_turn = $game['whose_turn'] ?? null;

            $cardPlayer1 = json_decode($game['cardPlayer1'] ?? '[]', true);
            $cardPlayer2 = json_decode($game['cardPlayer2'] ?? '[]', true);

            // error_log("Raw cardPlayer1: " . $game['cardPlayer1']);
            // error_log("Decoded cardPlayer1: " . json_encode($cardPlayer1));
            // error_log("Raw cardPlayer2: " . $game['cardPlayer2']);
            // error_log("Decoded cardPlayer2: " . json_encode($cardPlayer2));

            $player1Cards = CardModel::getCardsByIDsGameInformation($cardPlayer1);
            $player2Cards = CardModel::getCardsByIDsGameInformation($cardPlayer2);

            $response->getBody()->write(json_encode([
                'Dados' => [
                    'players' => [
                        'owner_id' => $owner_id,
                        'other_player_id' => $other_player_id
                    ],
                    'whose_turn' => $whose_turn,
                    'cards' => [
                        'player1' => $player1Cards,
                        'player2' => $player2Cards
                    ]
                ]
            ]));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => 'Erro ao obter informações do jogo: ' . $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
}
