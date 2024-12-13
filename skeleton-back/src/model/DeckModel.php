<?php

namespace Model\Adm;

use App\DB;

class  DeckModel{


    public static function createDeck($name, $qntd_cards, $disponible)
    {
        return DB::run(
            "INSERT INTO deck (name, qntd_cards, disponible) 
            VALUES (?, ?, ?, ?)",
            [$name, $qntd_cards, $disponible]
        );
    }


    public static function getDeckByID($Deck_ID) {
        return DB::run("SELECT  name, qntd_cards, disponible; 
        FROM deck WHERE id = ?", [$Deck_ID]);
    }

    public static function getAll() {
        return DB::run("SELECT name, qntd_cards, disponible;
        FROM deck");
    } 

    public static function deleteDeckByID($Deck_ID) {
        return DB::run("DELETE FROM deck WHERE id = ?", [$Deck_ID]);
    }


    public static function updateDeck($name, $qntd_cards, $disponible)
    {
        return DB::runFR(
            "UPDATE deck
                SET name = ?, qntd_cards = ?, disponible = ?
             WHERE id = ?",
            [
                $name, $qntd_cards, $disponible
            ]
        );
    }
}