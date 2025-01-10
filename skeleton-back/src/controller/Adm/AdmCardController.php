<?php

namespace Controller\Adm;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Model\CardModel;

require __DIR__ . '/../../model/CardModel.php';

class AdmCardController
{  

    public function createCard(Request $request, Response $response, array $args){       
        $params = json_decode($request->getBody()->getContents(), true) ?? [];
        
        $requiredFields = ['Deck_ID', 'name', 'image', 'Score01', 'Score02', 'Score03', 'Score04', 'Score05'];
        $missingFields = array_diff($requiredFields, array_keys($params));

        if (!empty($missingFields)) {
            $response->getBody()->write(json_encode([
                'error' => 'Necessario preencher todos os campos:',
                'Campos obrigatórios' => $missingFields
            ]));
            return $response->withStatus(400);
        }

       
        $result = CardModel::createCard(
            $params['Deck_ID'],
            $params['name'],
            $params['image'],
            $params['Score01'],
            $params['Score02'],
            $params['Score03'],
            $params['Score04'],
            $params['Score05']
        );
        
        if ($result === false) {
            $response->getBody()->write(json_encode(['error' => 'Não foi possível criar o cartão']));
            return $response->withStatus(400);
        }
       
        $response->getBody()->write(json_encode($result));
        return $response->withStatus(201);
    }
    public function getCardByID(Request $request, Response $response, array $args){
        if (($result = CardModel::getCardByID($args['id'])) === false) {
            $response->getBody()->write(json_encode(['error' => 'ID invalido']));
            return $response->withStatus(400);           
        }                
        $response->getBody()->write(json_encode($result));  
        return $response;
    } 
        
    public function getAllCards(Request $request, Response $response, array $args){
        $result = CardModel::getAllCards();
        if ($result === false) {
            $response->getBody()->write(json_encode(['error' => 'Não foi possivel pegar todas as cartas']));
            return $response->withStatus(400);            
        }                
        $response->getBody()->write(json_encode($result)); 
        return $response;
    }

    public function deleteCardByID(Request $request, Response $response, array $args){
        $result = CardModel::deleteCardByID($args['id']);           
        if ($result === 0) {
            $response->getBody()->write(json_encode(['error' => 'ID invalido']));
            return $response->withStatus(400);            
        }          
        $response->getBody()->write(json_encode(['success' => 'Carta deletada com Sucesso']));
        return $response->withStatus(200);   
    } 


    public function updateCard(Request $request, Response $response, array $args){
        $params = json_decode($request->getBody()->getContents(), true) ?? [];
        $Card_ID = $args['id'] ?? false;

        if (!$Card_ID) {
            $response->getBody()->write(json_encode(['error' => 'ID invalido']));
            return $response->withStatus(400);
        }

        $currentCard = CardModel::getCardById($Card_ID);

        if (!$currentCard) {
            $response->getBody()->write(json_encode(['error' => 'Carta não encontrada']));
            return $response->withStatus(404);
        }

        $keys = [
            'name', 'image', 'Score01', 'Score02', 'Score03', 'Score04', 'Score05'
        ];

        $fieldsToUpdate = [];
        foreach ($keys as $key) {
            if (isset($params[$key]) && $params[$key] !== $currentCard[$key]) {
                $fieldsToUpdate[$key] = $params[$key];
            }
        }

        if (empty($fieldsToUpdate)) {
            $response->getBody()->write(json_encode(['message' => 'Sem campos para atualizar']));
            return $response->withStatus(200);
        }

        $result = CardModel::updateCard($Card_ID, $fieldsToUpdate);

        if ($result) {
            $response->getBody()->write(json_encode(['success' => true]));
            return $response->withStatus(200);
        } else {
            $response->getBody()->write(json_encode(['error' => 'Falha ao atualizar campos']));
            return $response->withStatus(500);
        }
    }

}
