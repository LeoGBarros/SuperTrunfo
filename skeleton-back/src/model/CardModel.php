<?php

namespace Model\Adm;

use PDO;
use PDOException;

class CardModel
{
    private static function connect()
    {
        
        $host = 'localhost';
        $dbName = 'nome_do_banco';
        $username = 'usuario';
        $password = 'senha';

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbName", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        } catch (PDOException $e) {
            die('Erro na conexão com o banco de dados: ' . $e->getMessage());
        }
    }

    public static function createCard(
        $Deck_ID, $name, $Atribute01, $Atribute02, $Atribute03, $Atribute04, $Atribute05,
        $image, $Score01, $Score02, $Score03, $Score04, $Score05
    ) {
        $sql = "INSERT INTO card (Deck_ID, name, Atribute01, Atribute02, Atribute03, Atribute04, Atribute05, 
                image, Score01, Score02, Score03, Score04, Score05) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $Deck_ID, $name, $Atribute01, $Atribute02, $Atribute03, $Atribute04, $Atribute05,
                $image, $Score01, $Score02, $Score03, $Score04, $Score05
            ]);
            return $pdo->lastInsertId();
        } catch (PDOException $e) {
            die('Erro ao criar card: ' . $e->getMessage());
        }
    }

    public static function getCardByID($Card_ID)
    {
        $sql = "SELECT id, Deck_ID, name, Atribute01, Atribute02, Atribute03, Atribute04, Atribute05, 
                image, Score01, Score02, Score03, Score04, Score05 
                FROM card WHERE id = ?";
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$Card_ID]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            die('Erro ao buscar card por ID: ' . $e->getMessage());
        }
    }

    public static function getAllCards()
    {
        $sql = "SELECT id, Deck_ID, name, Atribute01, Atribute02, Atribute03, Atribute04, Atribute05, 
                image, Score01, Score02, Score03, Score04, Score05 
                FROM card";
        try {
            $pdo = self::connect();
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            die('Erro ao buscar todos os cards: ' . $e->getMessage());
        }
    }

    public static function deleteCardByID($Card_ID)
    {
        $sql = "DELETE FROM card WHERE id = ?";
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$Card_ID]);
        } catch (PDOException $e) {
            die('Erro ao deletar card: ' . $e->getMessage());
        }
    }

    public static function updateCard(
        $id, $name, $Atribute01, $Atribute02, $Atribute03, $Atribute04, $Atribute05,
        $image, $Score01, $Score02, $Score03, $Score04, $Score05
    ) {
        $sql = "UPDATE card 
                SET name = ?, Atribute01 = ?, Atribute02 = ?, Atribute03 = ?, Atribute04 = ?, Atribute05 = ?, 
                    image = ?, Score01 = ?, Score02 = ?, Score03 = ?, Score04 = ?, Score05 = ? 
                WHERE id = ?";
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                $name, $Atribute01, $Atribute02, $Atribute03, $Atribute04, $Atribute05,
                $image, $Score01, $Score02, $Score03, $Score04, $Score05, $id
            ]);
        } catch (PDOException $e) {
            die('Erro ao atualizar card: ' . $e->getMessage());
        }
    }
}
