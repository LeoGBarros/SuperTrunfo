<?php

namespace Validators;

use Respect\Validation\Validator as v;
use Model\Adm\DeckModel;

/**
 * Validation rules.
 *
 */
class DeckValidation
{

    public static function deckValidation()
    {
        return [
        'id' => v::intType()->min(1),
        'name' => v::stringType()->length(3, 255),
        'Atribute01' => v::stringType()->length(3, 255),
        'Atribute02' => v::stringType()->length(3, 255),
        'Atribute03' => v::stringType()->length(3, 255),
        'Atribute04' => v::stringType()->length(3, 255),
        'Atribute05' => v::stringType()->length(3, 255),
        'disponible' => v::boolType(),
        ];
    }
    

    public static function validateUpdateDeck() {        
        return [        
        'name' => v::stringType()->length(3, 255),           
        'Atribute01' => v::stringType()->length(3, 255),
        'Atribute02' => v::stringType()->length(3, 255),
        'Atribute03' => v::stringType()->length(3, 255),
        'Atribute04' => v::stringType()->length(3, 255),
        'Atribute05' => v::stringType()->length(3, 255),
        'disponible' => v::boolType(),
        ];
    }
}
