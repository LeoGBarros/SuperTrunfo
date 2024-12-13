<?php

namespace Model\Adm;

use App\DB;

class  UserModel{


    public static function createUser($username, $password, $Admin, $deck_select)
    {
        return DB::run(
            "INSERT INTO user (username, password, Admin, deck_select) 
            VALUES (?, ?, ?, ?)",
            [$username, $password, $Admin, $deck_select]
        );
    }


    public static function getUserByID($User_ID) {
        return DB::run("SELECT  username, Admin, deck_select; 
        FROM user WHERE id = ?", [$User_ID]);
    }
    
    public static function getAllUsers() {
        return DB::run("SELECT username, Admin, deck_select;
        FROM user");
    } 

    
    public static function getUserDeck($User_ID) {
        return DB::run("SELECT  deck_select; 
        FROM user WHERE id = ?", [$User_ID]);
    }

    public static function deleteUserByID($User_ID) {
        return DB::run("DELETE FROM user WHERE id = ?", [$User_ID]);
    }

    public static function updateUser($username, $password, $Admin, $deck_select)
    {
        return DB::runFR(
            "UPDATE user
                SET username = ?, password = ?, Admin = ?, deck_select = ?
             WHERE id = ?",
            [
                $username, $password, $Admin, $deck_select
            ]
        );
    }
}