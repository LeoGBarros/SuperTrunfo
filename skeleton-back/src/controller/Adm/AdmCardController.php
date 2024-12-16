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


            $result = CardModel::createCard($params['id'],  $params['Deck_ID'],$params['name'], $params['Atribute01'], $params['Atribute02'], $params['Atribute03'], $params['Atribute04'],
            $params['Atribute05'], $params['image'], $params['Score01'], $params['Score02'], $params['Score03'], $params['Score04'], $params['Score05']);   
                    
            
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


        public function updateCard(PsrRequest $request, PsrResponse $response, $args)
            {

            $params = json_decode($request->getBody()->getContents(), true) ?? [];

            $id = $args['id'] ?? null;

            if (!$id) {
                return Respostas::error(Respostas::ERR_INVALID_ID);
            }
            $currentCard = CardModel::getCardById($id);

            if (!$currentCard) {
                return Respostas::error(Respostas::ERR_CARD_NOT_FOUND);
            }
            $keys = [
                'name', 'Atribute01', 'Atribute02', 'Atribute03', 
                'Atribute04', 'Atribute05', 'image', 
                'Score01', 'Score02', 'Score03', 'Score04', 'Score05'
            ];
            $fieldsToUpdate = [];
            foreach ($keys as $key) {
                if (isset($params[$key]) && $params[$key] !== $currentCard[$key]) {
                    $fieldsToUpdate[$key] = $params[$key];
                }
            }
            if (empty($fieldsToUpdate)) {
                return Respostas::ok(Respostas::NO_FIELDS_UPDATED);
            }
            $result = CardModel::updateCard($card_id, $fieldsToUpdate);

            if ($result) {
                return Respostas::ok(Respostas::TRUE);
            } else {
                return Respostas::error(Respostas::ERR_UPDATE_CARD);
            }
        }


}
