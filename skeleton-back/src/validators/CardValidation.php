<?php

namespace Validation\Adm;

use Respect\Validation\Validator as v;
use Model\Adm\CardModel;

/**
 * Validation rules for Cards.
 */
class CardValidation
{
    public static function cardsValidation()
    {
        return [
            'id' => v::intType()->min(1),
            'Deck_ID' => v::intType()->min(1),            
            'name' => v::stringType(), 
            'image' => v::stringType(), 
            'Score01' => v::intType()->between(1, 100),
            'Score02' => v::intType()->between(1, 100),
            'Score03' => v::intType()->between(1, 100),
            'Score04' => v::intType()->between(1, 100),
            'Score05' => v::intType()->between(1, 100),
        ];
    }

    public static function validateUpdateCards()
    {
        return [            
            'image' => v::stringType(),
            'name' => v::stringType(),
            'Score01' => v::intType()->between(1, 100),
            'Score02' => v::intType()->between(1, 100),
            'Score03' => v::intType()->between(1, 100),
            'Score04' => v::intType()->between(1, 100),
            'Score05' => v::intType()->between(1, 100),
        ];
    }
}
