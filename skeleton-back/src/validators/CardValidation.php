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
            'Atribute01' => v::stringType()->length(3, 255),
            'Atribute02' => v::stringType()->length(3, 255),
            'Atribute04' => v::stringType()->length(3, 255),
            'Atribute05' => v::stringType()->length(3, 255),
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
            'Atribute01' => v::stringType()->length(3, 255),
            'Atribute02' => v::stringType()->length(3, 255),
            'Atribute04' => v::stringType()->length(3, 255),
            'Atribute05' => v::stringType()->length(3, 255),
            'image' => v::stringType(),
            'Score01' => v::intType()->between(1, 100),
            'Score02' => v::intType()->between(1, 100),
            'Score03' => v::intType()->between(1, 100),
            'Score04' => v::intType()->between(1, 100),
            'Score05' => v::intType()->between(1, 100),
        ];
    }
}
