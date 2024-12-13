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

        public function updateDeck(PsrRequest $request, PsrResponse $response, $args)
            {                
                $params = $request->getParsedBody() ?? [];
                $allowed = $request->getAttribute('validators');
                Validation::clearParams($params, $allowed);

                $deck_id = $args['id'] ?? null;

                if (!$deck_id) {
                    return Response::error(Response::ERR_INVALID_ID);
                }
                $currentDeck = DeckController::getDeckById($deck_id);

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

                $result = DeckController::updateDeck($deck_id, $fieldsToUpdate);

                if ($result) {
                    return Response::ok(Response::TRUE);
                } else {
                    return Response::error(Response::ERR_UPDATE_DECK);
                }
            }


}
