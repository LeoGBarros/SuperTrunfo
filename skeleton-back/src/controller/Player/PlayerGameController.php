<?php

namespace Controller\Player;


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

    // public function deleteCreatedGameID(Request $request, Response $response, array $args){
    //     if (($result = DeckModel::deleteDeckByID($args['id'])) === null) {
    //         $response->getBody()->write(json_encode(['error' => 'Cant delete Deck']));
    //             return $response->withStatus(400);            
    //         }                
    //         $response->getBody()->write(json_encode($result)); 
    //         return $response;
    //     }       


}
