<?php

namespace Controller;

class UserController
{  

    public function createDeck(PsrRequest $request, PsrResponse $response, $args)
    {
        
        $params = $request->getParsedBody() ?? [];
        $allowed = $request->getAttribute('validators');
        Validation::clearParams($params, $allowed);        
         
        
        $result = MODALCONTROLER::createUser($params['id'], $params['username'], $params['password'], $params['admin']);   
        
        if ($result) {          
            return Response::ok(Response::TRUE);
        } else {            
            return Response::error(Response::ERR_CREATE_ATTITUTE);
        }
    }

    public function updateDeck(PsrRequest $request, PsrResponse $response, $args)
    {
        
        $params = $request->getParsedBody() ?? [];
        $allowed = $request->getAttribute('validators');
        Validation::clearParams($params, $allowed);

        
        $attitude_type_id = $args['id'] ?? null;

        $currentAttitude = AttitudeController::getType($request, $args, $response);

        $fieldsToUpdate = [];

        $keys = ['name','name_en','name_es','name_fr','public_visible','score','who_visible',
        'tip_name','tip_name_en','tip_name_es','tip_name_fr','tip_type','tip_url','tip_url_en',
        'tip_url_es','tip_url_fr','evidence'
        ];

        foreach ($keys as $key) {
            if (isset($params[$key]) && $params[$key] !== $currentAttitude[$key]) {
                $fieldsToUpdate[$key] = $params[$key];
            }
        }
        $result = AttitudeController::updateAttitude($attitude_type_id, $fieldsToUpdate);
        
    }

    public function getDeckByID(PsrRequest $request, PsrResponse $response, $args)
    {
        if (($result = Attitude::getTypeAll($args['id'])) === null) return Response::error
        (Response::ERR_UNKNOWN_ATTITUDE_TYPE);
        return Response::ok($result);
    }

    public function getAllDeck(PsrRequest $request, PsrResponse $response, $args)
    {
        if (($result = Attitude::getTypeAll($args['id'])) === null) return Response::
        (Response::ERR_UNKNOWN_ATTITUDE_TYPE);
        return Response::ok($result);
    }
}
