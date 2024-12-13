<?php

namespace Model\Adm;

use App\DB;

class  CardModel{


    public static function createCard($Deck_ID, $name, $Atribute01, $Atribute02,  $Atribute03,  $Atribute04, $Atribute05,  
    $image,  $Score01, $Score02,  $Score03,  $Score04,  $Score05)
    {
        return DB::run(
            "INSERT INTO card (Deck_ID, name, Atribute01, Atribute02,  Atribute03,  Atribute04, Atribute05,  
        image,  Score01, Score02,  Score03,  Score04,  Score05) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)",
            [$Deck_ID, $name, $Atribute01, $Atribute02,  $Atribute03,  $Atribute04, $Atribute05,  
            $image,  $Score01, $Score02,  $Score03,  $Score04,  $Score05]
        );
    }


    public static function getCardByID($Card_ID) {
        return DB::run("SELECT  id, Deck_ID, name, Atribute01, Atribute02,  Atribute03,  Atribute04, Atribute05,  
        image,  Score01, Score02,  Score03,  Score04,  Score05; 
        FROM card WHERE id = ?", [$Card_ID]);
    }

    public static function getAllCards() {
        return DB::run("SELECT id, Deck_ID, name, 
        FROM card");
    } 

    public static function deleteCardByID($Card_ID) {
        return DB::run("DELETE FROM card WHERE id = ?", [$Card_ID]);
    }



    public static function updateCard(
        $name, $Atribute01, $Atribute02,  $Atribute03,  $Atribute04, $Atribute05,  
        $image,  $Score01, $Score02,  $Score03,  $Score04,  $Score05
    ) {
        return DB::runFR(
            "UPDATE card 
                SET name = ?, Atribute01 = ?, Atribute02 = ?,  Atribute03 = ?,  Atribute04 = ?, Atribute05 = ?,  
        image = ?,  Score01 = ?, Score02 = ?,  Score03 = ?,  Score04 = ?,  Score05 = ?
             WHERE id = ?",
            [
                $name, $Atribute01, $Atribute02,  $Atribute03,  $Atribute04, $Atribute05,  
                $image,  $Score01, $Score02,  $Score03,  $Score04,  $Score05
            ]
        );
    }
}
