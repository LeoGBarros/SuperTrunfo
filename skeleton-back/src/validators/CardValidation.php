<?php

namespace Validation\Adm;

use Respect\Validation\Validator as v;
use Model\Adm\CardModel;

/**
 * Validation rules.
 *
 */
class CardValidation
{

    public static function notNull()
    {
        return [
            'evidence' => v::stringType()->length(1, 45),
            'name' => v::stringType()->length(1, 128),
            'name_en' => v::stringType()->length(1, 128),
            'name_es' => v::stringType()->length(1, 128),
            'name_fr' => v::stringType()->length(1, 128),
            'score' => v::stringType()->length(1, 255),
            'who_visible' => v::boolType()
        ];
    }
    

    public static function validateUpdateAttitude() {        
        return [
            'name' => v::stringType()->length(1, 128),
            'name_en' =>v::stringType()->length(1, 128),
            'name_es' =>v::stringType()->length(1, 128),
            'name_fr' =>v::stringType()->length(1, 128),
            'public_visible' => v::intType(),
            'score' => v::intType()->between(0, 45),
            'who_visible' => v::boolType(),
            'tip_name' =>v::stringType()->length(0, 255),
            'tip_name_en' =>v::stringType()->length(0, 255),
            'tip_name_es' =>v::stringType()->length(0, 255),
            'tip_name_fr' =>v::stringType()->length(0, 255),
            'tip_type' =>v::stringType()->length(0, 32),
            'tip_url' =>v::url(),
            'tip_url_en' =>v::url(),
            'tip_url_es' =>v::url(),
            'tip_url_fr' =>v::url(),
            'evidence' => v::optional(v::stringType()->length(0, 45))
        ];
    }
}
