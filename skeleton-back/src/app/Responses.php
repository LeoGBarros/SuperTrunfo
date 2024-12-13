<?php

namespace App;

class Response
{

    // 200
    const TRUE  = ['result' => true];
    const FALSE = ['result' => false];

    // 400
    const ERR_VALIDATION          = ['status' => 400, 'body' => ['code' => 1000, 'message' => 'One or more fields in the request contains invalid data.', 'fields' => []]];
    const ERR_CREATE_CARD = ['status' => 400, 'body' => ['code' => 1001, 'message' => 'Error create card.']];
    const ERR_CREATE_DECK = ['status' => 400, 'body' => ['code' => 1002, 'message' => 'Error create deck.']];

    // 401
    const ERR_UNAUTHORIZED        = ['status' => 401, 'body' => null];

    // 404
    const ERR_NOT_FOUND           = ['status' => 404, 'body' => null];

    // 500
    const ERR_UNKNOWN            = ['status' => 400, 'body' => ['code' => 5000, 'message' => 'Unknown error.']];
    const ERR_SERVER             = ['status' => 500, 'body' => ['code' => 5000, 'message' => 'Unknown server error.']];
    const ERR_CREATE_ATTITUTE    = ['status' => 500, 'body' => ['code' => 5002, 'message' => 'Error create Attitute.']];
    const ERR_UPDATE_ATTITUDE    = ['status' => 500, 'body' => ['code' => 5003, 'message' => 'Error update Attitute.']];

}
