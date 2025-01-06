<?php

namespace Validators;

use Respect\Validation\Validator as v;


class CardValidation
{
    /**
     * Validation rules for creating a card.
     */
    public static function cardsValidation()
    {
        return [
            'id' => v::intType()->min(1),
            'Deck_ID' => v::intType()->min(1),
            'name' => v::stringType()->notEmpty(),
            'image' => v::stringType()->notEmpty(),
            'Score01' => v::intType()->between(1, 100),
            'Score02' => v::intType()->between(1, 100),
            'Score03' => v::intType()->between(1, 100),
            'Score04' => v::intType()->between(1, 100),
            'Score05' => v::intType()->between(1, 100),
        ];
    }

    /**
     * Validation rules for updating a card.
     */
    public static function validateUpdateCards()
    {
        return [
            'image' => v::optional(v::stringType()),
            'name' => v::optional(v::stringType()),
            'Score01' => v::optional(v::intType()->between(1, 100)),
            'Score02' => v::optional(v::intType()->between(1, 100)),
            'Score03' => v::optional(v::intType()->between(1, 100)),
            'Score04' => v::optional(v::intType()->between(1, 100)),
            'Score05' => v::optional(v::intType()->between(1, 100)),
        ];
    }
}
