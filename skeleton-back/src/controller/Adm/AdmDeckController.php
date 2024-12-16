<?php

namespace Controller\Adm;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Respostas;
use Model\Adm\DeckModel;

require __DIR__ . '/../../model/DeckModel.php';

class AdmDeckController
{  
 
    public function createDeck(Request $request, Response $response, array $args)
    {
        $params = json_decode($request->getBody()->getContents(), true) ?? [];
        
        $result = DeckModel::createDeck($params['name'], $params['qntd_cards'], $params['disponible']);

        if ($result === null) {
            $response->getBody()->write(json_encode(['error' => 'Erro ao criar deck']));
            return $response->withStatus(500);
        }

        $response->getBody()->write(json_encode(['id' => $result])); 
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }


        public function getDeckByID(Request $request, Response $response, array $args){
            if (($result = DeckModel::getDeckByID($args['id'])) === null) {
                $response->getBody()->write(json_encode(['error' => 'Cant take select Deck']));
                return $response->withStatus(400);            
            }                
            $response->getBody()->write(json_encode($result)); 
            return $response;
        } 
        
        public function getAllDecks(Request $request, Response $response, array $args){
            $result = DeckModel::getAllDecks();
            if ($result === null) {

                $response->getBody()->write(json_encode(['error' => 'Cant take all Decks']));
                return $response->withStatus(400);           
                }                
                $response->getBody()->write(json_encode($result)); 
                return $response;
        }

    public function deleteDeckByID(Request $request, Response $response, array $args){
        if (($result = DeckModel::deleteDeckByID($args['id'])) === null) {
            $response->getBody()->write(json_encode(['error' => 'Cant delete Deck']));
                return $response->withStatus(400);            
            }                
            $response->getBody()->write(json_encode($result)); 
            return $response;
        } 
    public function updateDeck(Request $request, Response $response, array $args)
        {
            $params = json_decode($request->getBody()->getContents(), true) ?? [];
            $Deck_ID = $args['id'] ?? null;

            if (!$Deck_ID) {
                $response->getBody()->write(json_encode(['error' => 'Invalid ID']));
                return $response->withStatus(400);
            }

            $currentDeck = DeckModel::getDeckById($Deck_ID);

            if (!$currentDeck) {
                $response->getBody()->write(json_encode(['error' => 'Deck not found']));
                return $response->withStatus(404);
            }

            $keys = ['name', 'qntd_cards', 'disponible'];

            $fieldsToUpdate = [];
            foreach ($keys as $key) {
                if (isset($params[$key]) && $params[$key] !== $currentDeck[$key]) {
                    $fieldsToUpdate[$key] = $params[$key];
                }
            }

            if (empty($fieldsToUpdate)) {
                $response->getBody()->write(json_encode(['message' => 'No fields updated']));
                return $response->withStatus(200);
            }

            $result = DeckModel::updateDeck($Deck_ID, $fieldsToUpdate);

            if ($result) {
                $response->getBody()->write(json_encode(['success' => true]));
                return $response->withStatus(200);
            } else {
                $response->getBody()->write(json_encode(['error' => 'Failed to update deck']));
                return $response->withStatus(500);
            }
        }

        


}
