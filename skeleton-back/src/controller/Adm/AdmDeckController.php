<?php

namespace Controller\Adm;

use App\Response;
use Model\Adm\DeckModel;

class AdmDeckController
{  
 
    public function createDeck( $request)
        {
            
            $params = $request->getParsedBody() ?? [];
            $allowed = $request->getAttribute('validators');
            Validation::clearParams($params, $allowed);       
            
            
            $result = DeckModel::createDeck($params ['id'], $params ['name'], $params ['qntd_cards'], $params['disponible']);   
            
            if ($result) {          
                return Response::ok(Response::TRUE);
            } else {            
                return Response::error(Response::ERR_CREATE_DECK);
            }
        }
        public function getDeckByID($args){
                if (($result = DeckModel::getDeckByID($args['id'])) === null) {
                    return Response::error(Response::ERR_SERVER);
                }
                
                return Response::ok($result);
        } 
        
        public function getAllDecks(){
                $result = DeckModel::getAll();
                if ($result === null) {
                    return Response::error(Response::ERR_SERVER);
                }                
                return Response::ok($result);
        }

        public function deleteDeckByID($args){
            if (($result = DeckModel::deleteDeckByID($args['id'])) === null) {
                return Response::error(Response::ERR_SERVER);
            }
            
            return Response::ok($result);
        } 

}
