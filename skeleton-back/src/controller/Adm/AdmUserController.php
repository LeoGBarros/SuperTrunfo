<?php

namespace Controller\Adm;

use App\Response;
use Model\Adm\UserModel;

class AdmUserController
{  

    public function createUser( $request) //Transformar o valor de Admin(Boolean) para String 
        {
            
            $params = $request->getParsedBody() ?? [];
            $allowed = $request->getAttribute('validators');
            Validation::clearParams($params, $allowed);       
            
            
            $result = UserModel::createUser($params['username'], $params['password'], $params['Admin'], $params['deck_select']);   
            
            if ($result) {          
                return Response::ok(Response::TRUE);
            } else {            
                return Response::error(Response::ERR_CREATE_CARD);
            }
        }
        public function getUserByID($args){
            if (($result = UserModel::getUserByID($args['id'])) === null) {
                return Response::error(Response::ERR_SERVER);
            }                
            return Response::ok($result);
        } 
        
        public function getAllUsers(){
            $result = UserModel::getAllUsers();
            if ($result === null) {
                return Response::error(Response::ERR_SERVER);
            }                
            return Response::ok($result);
        }

        public function getUserDeck($args){
            if (($result = UserModel::getUserDeck($args['id'])) === null) {
                return Response::error(Response::ERR_SERVER);
            }                
            return Response::ok($result);
        } 
        

        public function deleteUserByID($args){
            if (($result = UserModel::deleteUserByID($args['id'])) === null) {
                return Response::error(Response::ERR_SERVER);
            }
            
            return Response::ok($result);
        } 

}
