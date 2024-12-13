<?php

namespace Controller\Adm;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
// use App\Response;
use Model\Adm\CardModel;

require __DIR__ . '/../../model/CardModel.php';

class AdmCardController
{  

    public function createCard( $request)
        {
            
            $params = $request->getParsedBody() ?? [];
            $allowed = $request->getAttribute('validators');
            Validation::clearParams($params, $allowed);       
            
            
            $result = CardModel::createCard($params ['id'],  $params ['Deck_ID'],$params['name'], $params ['Atribute01'], $params ['Atribute02'], $params ['Atribute03'], $params ['Atribute04'],
            $params ['Atribute05'], $params ['image'], $params ['Score01'], $params ['Score02'], $params ['Score03'], $params ['Score04'], $params ['Score05']);   
            
            if ($result) {          
                return Response::ok(Response::TRUE);
            } else {            
                return Response::error(Response::ERR_CREATE_CARD);
            }
        }
        public function getCardByID($args){
            if (($result = CardModel::getCardByID($args['id'])) === null) {
                return Response::error(Response::ERR_SERVER);
            }                
            return Response::ok($result);
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

        public function deleteCardByID($args){
            if (($result = CardModel::deleteCardByID($args['id'])) === null) {
                return Response::error(Response::ERR_SERVER);
            }
            
            return Response::ok($result);
        } 


        public function updateCard(PsrRequest $request, PsrResponse $response, $args)
            {

            $params = $request->getParsedBody() ?? [];
            $allowed = $request->getAttribute('validators');
            Validation::clearParams($params, $allowed);

            $card_id = $args['id'] ?? null;

            if (!$card_id) {
                return Response::error(Response::ERR_INVALID_ID);
            }
            $currentCard = CardController::getCardById($card_id);

            if (!$currentCard) {
                return Response::error(Response::ERR_CARD_NOT_FOUND);
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
                return Response::ok(Response::NO_FIELDS_UPDATED);
            }
            $result = CardController::updateCard($card_id, $fieldsToUpdate);

            if ($result) {
                return Response::ok(Response::TRUE);
            } else {
                return Response::error(Response::ERR_UPDATE_CARD);
            }
        }


}
