<?php

namespace Validation\Adm;

use Respect\Validation\Validator as v;
use Model\Adm\UserModel;

/**
 * Validation rules.
 *
 */
class UserValidation
{

    public static function userValidation()
    {
        return [
        'id' => v::intType()->min(1),
        'username' => v::stringType()->length(1, 255),
        'password' => v::stringType()->length(1, 255),
        'deck_select' => v::stringType(),
        'Admin' => v::boolType(),
        ];
    }
    

    public static function validateUpdateUser() {        
        return [        
        'username' => v::stringType()->length(1, 255),
        'password' => v::stringType()->length(1, 255),
        'deck_select' => v::stringType(),
        ];
    }
}
