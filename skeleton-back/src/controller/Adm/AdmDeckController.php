<?php

namespace Controller\Adm;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Model\DeckModel;

require __DIR__ . '/../../model/DeckModel.php';

class AdmDeckController
{  
 
    public function createDeck(Request $request, Response $response, array $args)
    {
        $params = json_decode($request->getBody()->getContents(), true) ?? [];
        
        $result = DeckModel::createDeck($params['name'], $params['disponible'],$params['image'], $params['Atributte01'], $params['Atributte02'], $params['Atributte03'], $params['Atributte04'], $params['Atributte05']);

        if ($result === false) {
            $response->getBody()->write(json_encode(['error' => 'Erro ao criar deck']));
            return $response->withStatus(500);
        }

        $response->getBody()->write(json_encode(['id' => $result])); 
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }


    public function getDeckByID(Request $request, Response $response, array $args){
        if (($result = DeckModel::getDeckByID($args['id'])) === false) {
            $response->getBody()->write(json_encode(['error' => 'Não existe o Deck Selecionado']));
            return $response->withStatus(400);            
        }                
        $response->getBody()->write(json_encode($result)); 
        return $response;
    } 
        
    public function getAllDecks(Request $request, Response $response, array $args){
        $result = DeckModel::getAllDecks();
        if ($result === false) {

            $response->getBody()->write(json_encode(['error' => 'Não existe Decks']));
            return $response->withStatus(400);           
            }                
            $response->getBody()->write(json_encode($result)); 
            return $response;
    }

    public function deleteDeckByID(Request $request, Response $response, array $args){
        if (($result = DeckModel::deleteDeckByID($args['id'])) === false) {
            $response->getBody()->write(json_encode(['error' => 'Informe um Deck existente para ser deletado']));
            return $response->withStatus(400);            
        }                
            $response->getBody()->write(json_encode($result)); 
            return $response;
    } 
        
    public function updateDeck(Request $request, Response $response, array $args){
        $params = json_decode($request->getBody()->getContents(), true) ?? [];
        $Deck_ID = $args['id'] ?? false;

        if (!$Deck_ID) {
            $response->getBody()->write(json_encode(['error' => 'Informe um Deck existente para ser atualizado']));
            return $response->withStatus(400);
        }

        $currentDeck = DeckModel::getDeckById($Deck_ID);

        if (!$currentDeck) {
            $response->getBody()->write(json_encode(['error' => 'Deck não encontrado']));
            return $response->withStatus(404);
        }

        $keys = ['name', 'disponible','image', 'Atributte01', 'Atributte02', 'Atributte03', 'Atributte04', 'Atributte05'];

        $fieldsToUpdate = [];
        foreach ($keys as $key) {
            if (isset($params[$key]) && $params[$key] !== $currentDeck[$key]) {
                $fieldsToUpdate[$key] = $params[$key];
            }
        }

        if (empty($fieldsToUpdate)) {
            $response->getBody()->write(json_encode(['message' => 'Sem atualizações de campos']));
            return $response->withStatus(200);
        }

        $result = DeckModel::updateDeck($Deck_ID, $fieldsToUpdate);

        if ($result) {
            $response->getBody()->write(json_encode(['success' => true]));
            return $response->withStatus(200);
        } else {
            $response->getBody()->write(json_encode(['error' => 'Falha ao atualizar deck']));
            return $response->withStatus(500);
        }
    }

}
