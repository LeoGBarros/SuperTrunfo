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



    public static function updateAttitude(
        $id,$name,$name_en,$name_es,$name_fr,$public_visible,$score,$who_visible,$tip_name,
        $tip_name_en,$tip_name_es,$tip_name_fr,$tip_type,$tip_url,$tip_url_en,$tip_url_es,$tip_url_fr,$evidence
    ) {
        return DB::runFR(
            "UPDATE attitude_type 
                SET name = ?, name_en = ?, name_es = ?, name_fr = ?, public_visible = ?, score = ?, who_visible = ?, 
                tip_name = ?, tip_name_en = ?, tip_name_es = ?, tip_name_fr = ?, tip_type = ?, tip_url = ?, tip_url_en = ?, 
                tip_url_es = ?, tip_url_fr = ?, evidence = ?
             WHERE id = ?",
            [
                $name,$name_en,$name_es,$name_fr,$public_visible,$score,$who_visible,$tip_name,
                $tip_name_en,$tip_name_es,$tip_name_fr,$tip_type,$tip_url,$tip_url_en,$tip_url_es,$tip_url_fr,$evidence,$id
            ]
        );
    }
}
