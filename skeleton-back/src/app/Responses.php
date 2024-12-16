<?php

namespace App;

class Respostas
{

    // 200
    const TRUE  = ['result' => 'true'];
    const FALSE = ['result' => 'false'];

    // 400
    const ERR_VALIDATION          = ['status' => 400, 'body' => ['code' => 1000, 'message' => 'One or more fields in the request contains invalid data.', 'fields' => []]];
    const ERR_CREATE_CARD         = ['status' => 400, 'body' => ['code' => 1001, 'message' => 'Error create card.']];
    const ERR_CREATE_DECK         = ['status' => 400, 'body' => ['code' => 1002, 'message' => 'Error create deck.']];
    const ERR_CREATE_USER         = ['status' => 400, 'body' => ['code' => 1003, 'message' => 'Error create user.']];
    const ERR_INVALID_ID          = ['status' => 400, 'body' => ['code' => 1004, 'message' => 'Invalid ID provided.']];
    const NO_FIELDS_UPDATED       = ['status' => 400, 'body' => ['code' => 1005, 'message' => 'No fields were updated.']];
    const ERR_UPDATE_DECK         = ['status' => 400, 'body' => ['code' => 1006, 'message' => 'Error update deck.']];
    const ERR_UPDATE_USER         = ['status' => 400, 'body' => ['code' => 1007, 'message' => 'Error update user.']];
    const ERR_UPDATE_CARD         = ['status' => 400, 'body' => ['code' => 1007, 'message' => 'Error update card.']];
    // 401
    const ERR_UNAUTHORIZED        = ['status' => 401, 'body' => null];

    // 404
    const ERR_NOT_FOUND           = ['status' => 404, 'body' => null];
    const ERR_USER_NOT_FOUND      = ['status' => 404, 'body' => ['code' => 4001, 'message' => 'User not found.']];
    const ERR_CARD_NOT_FOUND      = ['status' => 404, 'body' => ['code' => 4001, 'message' => 'User not found.']];
    const ERR_DECK_NOT_FOUND      = ['status' => 404, 'body' => ['code' => 4002, 'message' => 'Deck not found.']];

    // 500
    const ERR_UNKNOWN             = ['status' => 500, 'body' => ['code' => 5000, 'message' => 'Unknown error.']];
    const ERR_SERVER              = ['status' => 500, 'body' => ['code' => 5000, 'message' => 'Unknown server error.']];



    public static function ok(?string $message = null, $data = null){
        if($message){
            return [
                'status' => 'success',
                'message' => $message,
                'data' => $data
            ];
        }
        return $data;
    }
    public static function error(array $error, ?array $errorFields = null):array{
        if($errorFields){
            return [
                'status' => 'error',
                'code' => $error['code'],
                'message' => $error['message'],
                'errors' => $errorFields
            ];
        }
        return [
            'status' => 'error',
            'code' => $error['code'],
            'message' => $error['message']
        ];
    }

}
