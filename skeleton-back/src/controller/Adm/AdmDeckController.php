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
                $response->getBody()->write(json_encode($result));
            return $response;            
            }                
            $response->getBody()->write(json_encode($result)); // AJUSTAR ERROS 
            return $response;
        } 
        
        public function getAllDecks(Request $request, Response $response, array $args){
            $result = DeckModel::getAllDecks();
            if ($result === null) {

                $response->getBody()->write(json_encode($result));
                return $response;            
                }                
                $response->getBody()->write(json_encode($result)); // AJUSTAR ERROS
                return $response;
        }

    public function deleteDeckByID(Request $request, Response $response, array $args){
        if (($result = DeckModel::deleteDeckByID($args['id'])) === null) {
            $response->getBody()->write(json_encode($result));
            return $response;            
            }                
            $response->getBody()->write(json_encode($result)); // AJUSTAR ERROS
            return $response;
        } 

        public function updateDeck(PsrRequest $request, PsrResponse $response, $args)
            {                
                $params = json_decode($request->getBody()->getContents(), true) ?? [];

                $Deck_ID = $args['id'] ?? null;

                if (!$Deck_ID) {
                    return Response::error(Response::ERR_INVALID_ID);
                }
                $currentDeck = DeckModel::getDeckById($Deck_ID);

                if (!$currentDeck) {
                    return Response::error(Response::ERR_DECK_NOT_FOUND);
                }
                $keys = ['name', 'qntd_cards', 'disponible'];

                $fieldsToUpdate = [];
                foreach ($keys as $key) {
                    if (isset($params[$key]) && $params[$key] !== $currentDeck[$key]) {
                        $fieldsToUpdate[$key] = $params[$key];
                    }
                }

                if (empty($fieldsToUpdate)) {
                    return Response::ok(Response::NO_FIELDS_UPDATED);
                }

                $result = DeckModel::updateDeck($Deck_ID, $fieldsToUpdate);

                if ($result) {
                    return Response::ok(Response::TRUE);
                } else {
                    return Response::error(Response::ERR_UPDATE_DECK);
                }
            }


}
