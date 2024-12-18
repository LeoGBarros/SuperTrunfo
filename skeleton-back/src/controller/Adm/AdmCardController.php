<?php

namespace Controller\Adm;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Respostas;
use Model\Adm\CardModel;

require __DIR__ . '/../../model/CardModel.php';

class AdmCardController
{  

    public function createCard( Request $request, Response $response, array $args)
        {
            
            $params = json_decode($request->getBody()->getContents(), true) ?? [];
            
            $result = CardModel::createCard($params['Deck_ID'],$params['name'], $params['image'], $params['Score01'], $params['Score02'], $params['Score03'], $params['Score04'], $params['Score05']);   
                    
            
            if ($result === null) {

                $response->getBody()->write(json_encode($result));
                return $response;            
                }                
                $response->getBody()->write(json_encode($result)); // AJUSTAR ERROS
                return $response;
        }


        public function getCardByID(Request $request, Response $response, array $args){
            if (($result = CardModel::getCardByID($args['id'])) === null) {
                $response->getBody()->write(json_encode($result));
            return $response;            
            }                
            $response->getBody()->write(json_encode($result)); // AJUSTAR ERROS 
            return $response;
        } 
        
        public function getAllCards(Request $request, Response $response, array $args){
            $result = CardModel::getAllCards();
            if ($result === null) {

            $response->getBody()->write(json_encode($result));
            return $response;            
            }                
            $response->getBody()->write(json_encode($result)); // AJUSTAR ERROS
            return $response;
        }

        public function deleteCardByID(Request $request, Response $response, array $args){
            if (($result = CardModel::deleteCardByID($args['id'])) === null) {
                $response->getBody()->write(json_encode($result));
                return $response;            
                }                
                $response->getBody()->write(json_encode($result)); // AJUSTAR ERROS
                return $response;
        } 


        public function updateCard(Request $request, Response $response, array $args)
        {
            $params = json_decode($request->getBody()->getContents(), true) ?? [];
            $Card_ID = $args['id'] ?? null;

            if (!$Card_ID) {
                $response->getBody()->write(json_encode(['error' => 'Invalid ID']));
                return $response->withStatus(400);
            }

            $currentCard = CardModel::getCardById($Card_ID);

            if (!$currentCard) {
                $response->getBody()->write(json_encode(['error' => 'Card not found']));
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
                $response->getBody()->write(json_encode(['message' => 'No fields updated']));
                return $response->withStatus(200);
            }

            $result = CardModel::updateCard($Card_ID, $fieldsToUpdate);

            if ($result) {
                $response->getBody()->write(json_encode(['success' => true]));
                return $response->withStatus(200);
            } else {
                $response->getBody()->write(json_encode(['error' => 'Failed to update card']));
                return $response->withStatus(500);
            }
        }



}
