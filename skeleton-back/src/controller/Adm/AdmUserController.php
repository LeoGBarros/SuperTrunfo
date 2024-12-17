<?php

namespace Controller\Adm;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// use App\Response;
use Model\Adm\UserModel;

require __DIR__ . '/../../model/UserModel.php';

class AdmUserController
{  

    public function createUser( Request $request, Response $response, array $args) //Transformar o valor de Admin(Boolean) para String 
    {
            
            $params = json_decode($request->getBody()->getContents(), true) ?? [];    
            
            
            $result = UserModel::createUser($params['username'], $params['password'], $params['Admin'], $params['deck_select']);   
            
            if ($result === null) {

                $response->getBody()->write(json_encode($result));
                return $response;            
                }                
                $response->getBody()->write(json_encode($result)); // AJUSTAR ERROS
                return $response;
    }

        public function getUserByID(Request $request, Response $response, array $args){
            if (($result = UserModel::getUserByID($args['id'])) === null) {
                $response->getBody()->write(json_encode($result));
            return $response;            
            }                
            $response->getBody()->write(json_encode($result)); // AJUSTAR ERROS 
            return $response;
        } 
        
        public function getAllUsers(Request $request, Response $response, array $args){
            $result = UserModel::getAllUsers();
            if ($result === null) {

                $response->getBody()->write(json_encode($result));
                return $response;            
                }                
                $response->getBody()->write(json_encode($result)); // AJUSTAR ERROS
                return $response;
        }

        public function getUserDeck(Request $request, Response $response, array $args)
        {
            $deckName = UserModel::getUserDeck($args['id']);

            if ($deckName === null) {
                $response->getBody()->write(json_encode(['error' => 'Deck not found for this user']));
                return $response->withStatus(404);
            }

            $response->getBody()->write(json_encode(['deck_name' => $deckName]));
            return $response->withStatus(200);
        }

        

        public function deleteUserByID(Request $request, Response $response, array $args){
            if (($result = UserModel::deleteUserByID($args['id'])) === null) {
                $response->getBody()->write(json_encode($result));
                return $response;            
                }                
                $response->getBody()->write(json_encode($result)); // AJUSTAR ERROS
                return $response;
        }
        
        public function updateUser(Request $request, Response $response, array $args)
        {
            $params = json_decode($request->getBody()->getContents(), true) ?? [];
            $user_id = $args['id'] ?? null;

            if (!$user_id) {
                $response->getBody()->write(json_encode(['error' => 'Invalid ID']));
                return $response->withStatus(400);
            }

            $currentUser = UserModel::getUserById($user_id);

            if (!$currentUser) {
                $response->getBody()->write(json_encode(['error' => 'User not found']));
                return $response->withStatus(404);
            }

            $keys = ['username', 'password', 'Admin', 'deck_select'];

            $fieldsToUpdate = [];
            foreach ($keys as $key) {
                if (isset($params[$key]) && $params[$key] !== $currentUser[$key]) {
                    $fieldsToUpdate[$key] = $params[$key];
                }
            }
            if (empty($fieldsToUpdate)) {
                $response->getBody()->write(json_encode(['message' => 'No fields updated']));
                return $response->withStatus(200);
            }

            $result = UserModel::updateUser($user_id, $fieldsToUpdate);

            if ($result) {
                $response->getBody()->write(json_encode(['success' => true]));
                return $response->withStatus(200);
            } else {
                $response->getBody()->write(json_encode(['error' => 'Failed to update user']));
                return $response->withStatus(500);
            }
        }


}
