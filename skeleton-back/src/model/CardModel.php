<?php

namespace Model;

use PDO;
use PDOException;

class CardModel
{
    private static function connect(){        
        $host = 'localhost';
        $port = 3306;
        $dbName = 'supertrunfodb';
        $username = 'root';
        $password = 'root';

        try {
            $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbName", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        } catch (PDOException $e) {
            die('Erro na conexão com o banco de dados: ' . $e->getMessage());
        }
    }

    public static function createCard($Deck_ID, $name, $image, $Score01, $Score02, $Score03, $Score04, $Score05){
        $sql = "INSERT INTO card (Deck_ID, name, image, Score01, Score02, Score03, Score04, Score05) 
                VALUES (?,?,?, ?, ?, ?, ?, ?)";
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$Deck_ID, $name, $image, $Score01, $Score02, $Score03, $Score04, $Score05]);
            return $pdo->lastInsertId();
        } catch (PDOException $e) {
            die('Erro ao criar card: ' . $e->getMessage());
        }
    }

    public static function getCardByID($id){
        $sql = "SELECT id, Deck_ID, name,image, Score01, Score02, Score03, Score04, Score05 
                FROM card WHERE id = ?";
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Erro ao buscar card por ID: ' . $e->getMessage());
        }
    }

    public static function getCardsByIDsGameInformation(array $ids) {
        if (empty($ids)) {
            return [];
        }    
        $placeholders = implode(',', array_fill(0, count($ids), '?')); // Vai criar um array com '?' separados por ',' na quantidade recebida em count(ids)
        $sql = "SELECT id, Deck_ID, name, image, Score01, Score02, Score03, Score04, Score05 
                FROM card WHERE id IN ($placeholders)";
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);
            $stmt->execute($ids);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException('Erro ao buscar cartas por IDs: ' . $e->getMessage());
        }
    }
    

    public static function getAllCards(){
        $sql = "SELECT id, Deck_ID, name, image, Score01, Score02, Score03, Score04, Score05 
                FROM card";
        try {
            $pdo = self::connect();
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            die('Erro ao buscar todos os cards: ' . $e->getMessage());
        }
    }

    public static function deleteCardByID($id){
        $sql = "DELETE FROM card WHERE id = ?";
        try {            
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);            
            
            return $stmt->rowCount();
        } catch (PDOException $e) {            
            die('Erro ao deletar card: ' . $e->getMessage());
        }
    }

    public static function updateCard($id, array $fieldsToUpdate){
        $setClause = implode(', ', array_map(fn($key) => "$key = ?", array_keys($fieldsToUpdate))); // Vai colocar em uma string os campos percorridos pelo array_map separados por ','
        $sql = "UPDATE card SET $setClause WHERE id = ?";
    
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);
            $values = array_values($fieldsToUpdate);
            $values[] = $id; 
            return $stmt->execute($values);
        } catch (PDOException $e) {
            die('Erro ao atualizar card: ' . $e->getMessage());
        }
    }
    
}
