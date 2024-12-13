<?php

namespace Controller\Adm;

use App\Response;
use Model\Adm\CardModel;

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
        
        public function getAllCards(){
            $result = CardModel::getAllCards();
            if ($result === null) {
                return Response::error(Response::ERR_SERVER);
            }                
            return Response::ok($result);
        }

        public function deleteCardByID($args){
            if (($result = CardModel::deleteCardByID($args['id'])) === null) {
                return Response::error(Response::ERR_SERVER);
            }
            
            return Response::ok($result);
        } 

}
