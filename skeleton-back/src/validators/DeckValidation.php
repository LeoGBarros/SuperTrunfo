<?php

namespace Validation\Adm;

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
        'qntd_cards' => v::intType()->equals(30),
        'disponible' => v::boolType(),
        ];
    }
    

    public static function validateUpdateDeck() {        
        return [        
          'name' => v::stringType()->length(3, 255),          
          'disponible' => v::boolType(),
        ];
    }
}
