<?php

namespace Controller\Player;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Respostas;
use Model\CardModel;
use Model\DeckModel;
use Model\GameModel;
use Model\UserModel;
use PDO;

require __DIR__ . '/../../model/GameModel.php';

class PlayerGameController
{   
    public function createGame(Request $request, Response $response): Response{
        $headers = $request->getHeader('Authorization');
        $authHeader = $headers[0] ?? null;

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return Respostas::error(Respostas::ERR_CREATE_GAME);
        }

        $token = substr($authHeader, 7);

        try {
            $ownerId = UserModel::getOwnerId($token);

            $data = json_decode($request->getBody()->getContents(), true);
            $deckSelect = $data['deck_select'] ?? null;


            $decodedToken = JWT::decode($token, new Key('a9b1k87YbOpq3h2Mz8aXvP9wLQZ5R4pJ3cLrV5ZJ5DkRt0jQYzZnM+W8X4Lo0yZp', 'HS256'));
            $user_ID = $decodedToken->user_ID ?? null;            

            //verifica se participa de mais jogos
            $allCurrentGames = GameModel::getAllCreatedGames();
            $isInAnotherGame = false;   

            foreach ($allCurrentGames as $game) {
                $player2 = $game['otherPlayer_id'] ?? null;
                $owner_id = $game['owner_id'] ?? null;

                if ($user_ID === $owner_id || $user_ID === $player2) {
                    $isInAnotherGame = true;
                    break;
                }
            }

            if ($isInAnotherGame) {
                $response->getBody()->write(json_encode(['error' => 'Jogador já está dentro de outra partida.']));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }

            if (!$deckSelect) {
                $error = ['error' => 'O campo Deck Select é obrigatório.'];
                $response->getBody()->write(json_encode($error));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }

            if (!is_numeric($deckSelect)) {
                $error = ['error' => 'O campo Deck Select deve ser um número válido.'];
                $response->getBody()->write(json_encode($error));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }
            $existingGame = GameModel::getActiveGameByOwner($ownerId);

            if ($existingGame && in_array($existingGame['status_game'], ['started', 'dont_started'])) {
                $error = ['error' => 'Você já possui um jogo ativo. Não é possível criar outro jogo.'];
                $response->getBody()->write(json_encode($error));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }
            $deck = DeckModel::getDeckById($deckSelect);

            if (!$deck) {
                $error = ['error' => 'Deck não encontrado.'];
                $response->getBody()->write(json_encode($error));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            if ($deck['disponible'] === 0) {
                $error = ['error' => 'Deck selecionado não está disponível.'];
                $response->getBody()->write(json_encode($error));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }            

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
        if (($result = GameModel::getCreatedGameByID($args['session_id'])) === false) {
            $response->getBody()->write(json_encode(['error' => 'ID não existente']));
            return $response->withStatus(400);            
        }                
        if(isset($result) && !intval($result)){
            $response->getBody()->write(json_encode(['error' => 'O ID é obrigatoriamente um numero.']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        $response->getBody()->write(json_encode($result)); 
        return $response;
    } 
        
    public function getAllCreatedGames(Request $request, Response $response, array $args){
        $result = GameModel::getAllCreatedGames();
        if ($result === false) {
            $response->getBody()->write(json_encode(['error' => 'Não foi possivel pegar todos os jogos']));
            return $response->withStatus(400);           
        }                
        $response->getBody()->write(json_encode($result)); 
        return $response;
    }

    public function joinGame(Request $request, Response $response, array $args){
        $session_id = $args['session_id'] ?? null;
    
        $authorizationHeader = $request->getHeaderLine('Authorization');
    
        if (!$authorizationHeader) {
            $response->getBody()->write(json_encode(['error' => 'Authorization token missing']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
    
        try {
            $token = str_replace('Bearer ', '', $authorizationHeader);
            $decodedToken = JWT::decode($token, new Key('a9b1k87YbOpq3h2Mz8aXvP9wLQZ5R4pJ3cLrV5ZJ5DkRt0jQYzZnM+W8X4Lo0yZp', 'HS256'));
            $user_ID = $decodedToken->user_ID ?? null;
    
            if (!$user_ID) {
                $response->getBody()->write(json_encode(['error' => 'Usuario não autorizado.']));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }
    
            $currentGame = GameModel::getCreatedGameByID($session_id);
    
            if (!$currentGame) {
                $response->getBody()->write(json_encode(['error' => 'Game não encontrado.']));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }
    
            if ($currentGame['status_game'] === 'started') {
                $response->getBody()->write(json_encode(['error' => 'A partida já foi iniciada.']));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }

            if ($currentGame['status_game'] === 'finished') {
                $response->getBody()->write(json_encode(['error' => 'A partida já foi finalizada.']));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }

            $player2 = $currentGame['otherPlayer_id'] ?? null;
            $owner_id = $currentGame['owner_id'] ?? null;
    
            if ($user_ID === $owner_id) {
                $response->getBody()->write(json_encode(['error' => 'Jogador já está dentro da partida.']));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }
    
            if ($player2) {
                $response->getBody()->write(json_encode(['error' => 'O jogo já possui dois jogadores.']));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }

            //verifica se participa de mais jogos
            $allCurrentGames = GameModel::getAllCreatedGames();
            $isInAnotherGame = false;   

            foreach ($allCurrentGames as $game) {
                $player2 = $game['otherPlayer_id'] ?? null;
                $owner_id = $game['owner_id'] ?? null;

                if ($user_ID === $owner_id || $user_ID === $player2) {
                    $isInAnotherGame = true;
                    break;
                }
            }

            if ($isInAnotherGame) {
                $response->getBody()->write(json_encode(['error' => 'Jogador já está dentro de outra partida.']));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }
    
            $fieldsToUpdate = ['otherPlayer_id' => $user_ID];
            $result = GameModel::joinGame($session_id, $fieldsToUpdate);
    
            if ($result) {
                $response->getBody()->write(json_encode(['success' => true, 'message' => 'Jogador juntou-se com sucesso.']));
                return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
            } else {
                $response->getBody()->write(json_encode(['error' => 'Falha ao juntar-se ao jogo.']));
                return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
            }
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => 'Token inválido: ' . $e->getMessage()]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
    } 


    public function startGame(Request $request, Response $response, array $args) {
        $session_id = $args['session_id'] ?? null;

        if (!$session_id) {
            $response->getBody()->write(json_encode(['error' => 'session id não fornecido.']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        try {
            $game = GameModel::getCreatedGameByID($session_id);

            $status_game = $game['status_game'] ?? null;

            if($status_game === 'started'){
                $response->getBody()->write(json_encode(['error' => 'Partida já esta iniciada.']));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            if($status_game === 'finished'){
                $response->getBody()->write(json_encode(['error' => 'Partida já esta finalizada.']));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            if (!$game) {
                $response->getBody()->write(json_encode(['error' => 'Sessão não encontrada.']));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            $Deck_ID = $game['deck_select'] ?? null;

            if (!$Deck_ID) {
                $response->getBody()->write(json_encode(['error' => 'Deck não encontrado para a sessão fornecida.']));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }

            $otherPlayer_id= $game['otherPlayer_id'] ?? null;

            if (!isset($otherPlayer_id)) {
                $response->getBody()->write(json_encode(['error' => 'Não tem jogadores suficientes na partida.']));
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

        $current_game = GameModel::getCreatedGameByID($session_id);
        $status_game = $current_game['status_game'] ?? null;

        if (!$current_game) {
            $response->getBody()->write(json_encode(['error' => 'Jogo não encontrado.']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }


        if ($status_game !== 'started') {
            $response->getBody()->write(json_encode(['error' => 'Jogo ainda não foi iniciado.']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        if (!$session_id) {
            $response->getBody()->write(json_encode(['error' => 'session id não fornecido.']));
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
        $body = json_decode($rawBody, true);
    
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
                $removedCards = GameModel::removeFirstCardFromPlayers($session_id);
                $tieCards = GameModel::getTieCards($session_id);
                $allCards = array_merge($tieCards, [$removedCards['removedCardPlayer1'], $removedCards['removedCardPlayer2']]);
                GameModel::addCardsToWinner($session_id, 'cardPlayer1', $allCards);
                GameModel::updateLastRoundWinner($session_id, $game['owner_id']);
                GameModel::clearTieCards($session_id);
            } elseif (strpos($result, 'Jogador 2 venceu') !== false) {
                $removedCards = GameModel::removeFirstCardFromPlayers($session_id);
                $tieCards = GameModel::getTieCards($session_id);
                $allCards = array_merge($tieCards, [$removedCards['removedCardPlayer1'], $removedCards['removedCardPlayer2']]);
                GameModel::addCardsToWinner($session_id, 'cardPlayer2', $allCards);
                GameModel::updateLastRoundWinner($session_id, $game['otherPlayer_id']);
                GameModel::clearTieCards($session_id);
            } else {
                $removedCards = GameModel::removeFirstCardFromPlayers($session_id);
                GameModel::addTieCards($session_id, [$removedCards['removedCardPlayer1'], $removedCards['removedCardPlayer2']]);
            }    
            // Verifica se algum jogador ficou sem cartas
            $cards = GameModel::getFirstCards($session_id);
    
            if (!isset($cards['player1']) || empty($cards['player1'])) {
                GameModel::updateGameStatus($session_id, 'finished');
                GameModel::setWinner($session_id, $game['otherPlayer_id']);
                $response->getBody()->write(json_encode(['success' => true, 'message' => 'Jogador 2 venceu a partida.']));
                return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
            }
    
            if (!isset($cards['player2']) || empty($cards['player2'])) {
                GameModel::updateGameStatus($session_id, 'finished');
                GameModel::setWinner($session_id, $game['owner_id']);
                $response->getBody()->write(json_encode(['success' => true, 'message' => 'Jogador 1 venceu a partida.']));
                return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
            }
    
            $response->getBody()->write(json_encode(['success' => true, 'message' => $result]));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => 'Erro ao comparar as cartas: ' . $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
    

    public function gameInformation(Request $request, Response $response, array $args): Response {
        $session_id = $args['session_id'] ?? null;

        if (!$session_id) {
            // error_log('Erro: session_id não fornecido.');
            $response->getBody()->write(json_encode(['error' => 'session_id não fornecido.']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $authHeader = $request->getHeaderLine('Authorization');
        // error_log('Authorization Header: ' . $authHeader);

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            // error_log('Erro: Token JWT ausente ou inválido.');
            $response->getBody()->write(json_encode(['error' => 'Token JWT ausente ou inválido.']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $token = substr($authHeader, 7);
        // error_log('Token extraído: ' . $token);

        try {
            $decodedToken = JWT::decode($token, new Key('a9b1k87YbOpq3h2Mz8aXvP9wLQZ5R4pJ3cLrV5ZJ5DkRt0jQYzZnM+W8X4Lo0yZp', 'HS256'));
            $user_ID = $decodedToken->user_ID ?? null;

            // error_log('Token decodificado com sucesso. user_ID: ' . $user_ID);

            if (!$user_ID) {
                // error_log('Erro: user_ID ausente no token.');
                $response->getBody()->write(json_encode(['error' => 'Token inválido ou usuário não autorizado.']));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            // Obtém informações do jogo
            $game = GameModel::getCreatedGameByID($session_id);
            // error_log('Dados do jogo: ' . json_encode($game));

            if (!$game) {
                // error_log('Erro: Sessão não encontrada para session_id: ' . $session_id);
                $response->getBody()->write(json_encode(['error' => 'Sessão não encontrada.']));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            // Verifica se o usuário é o owner_id ou o otherPlayer_id
            $owner_id = $game['owner_id'] ?? null;
            $other_player_id = $game['otherPlayer_id'] ?? null;

            // error_log("Owner ID: $owner_id, Other Player ID: $other_player_id");

            if ($user_ID !== $owner_id && $user_ID !== $other_player_id) {
                // error_log('Erro: Usuário não autorizado. user_ID: ' . $user_ID);
                $response->getBody()->write(json_encode(['error' => 'Usuário não autorizado a acessar esta sessão.']));
                return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
            }

            // Define as cartas que o usuário pode acessar
            $cardPlayer1 = json_decode($game['cardPlayer1'] ?? '[]', true);
            $cardPlayer2 = json_decode($game['cardPlayer2'] ?? '[]', true);

            // error_log('Cartas do Player 1: ' . json_encode($cardPlayer1));
            // error_log('Cartas do Player 2: ' . json_encode($cardPlayer2));

            $accessibleCards = [];
            if ($user_ID === $owner_id) {
                $accessibleCards = CardModel::getCardsByIDsGameInformation($cardPlayer1);
            } elseif ($user_ID === $other_player_id) {
                $accessibleCards = CardModel::getCardsByIDsGameInformation($cardPlayer2);
            }

            $response->getBody()->write(json_encode([
                'Dados da partida' => [
                    'Seu ID' => $user_ID,
                    'Status da partida' => $game['status_game'] ?? null,
                    'Quem vai jogar é o jogador com ID' => $game['whose_turn'] ?? null,
                    'Suas cartas' => $accessibleCards,
                    'Vencedor da última rodada' => $game['last_round_winner'] ?? null,
                    'Vencedor da partida' => $game['gameWinner'] ?? null
                ]
            ]));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => 'Erro ao obter informações do jogo: ' . $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

}
