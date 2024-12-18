<?php

namespace Model\Adm;

use PDO;
use PDOException;

class DeckModel
{
    private static function connect()
    {
       
        $host = 'localhost';
        $dbName = 'supertrunfodb';
        $username = 'root';
        $password = 'root';

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbName", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        } catch (PDOException $e) {
            die('Erro na conexão com o banco de dados: ' . $e->getMessage());
        }
    }

    public static function createDeck($name, $disponible, $image, $Atributte01, $Atributte02, $Atributte03, $Atributte04, $Atributte05)
    {
        $sql = "INSERT INTO deck (name, disponible, image, Atributte01,Atributte02,Atributte03,Atributte04,Atributte05) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $disponible, $image, $Atributte01, $Atributte02, $Atributte03, $Atributte04, $Atributte05]);
            return $pdo->lastInsertId(); // Retorna o ID gerado automaticamente
        } catch (PDOException $e) {
            die('Erro ao criar deck: ' . $e->getMessage());
        }
    }


    public static function getDeckByID($Deck_ID)
    {
        $sql = "SELECT id, name, disponible,image, Atributte01,Atributte02,Atributte03,Atributte04,Atributte05 FROM deck WHERE id = ?";
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$Deck_ID]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            die('Erro ao buscar deck por ID: ' . $e->getMessage());
        }
    }

    public static function getAllDecks()
    {
        $sql = "SELECT id, name, disponible, image, Atributte01,Atributte02,Atributte03,Atributte04,Atributte05 FROM deck";
        try {
            $pdo = self::connect();
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            die('Erro ao buscar todos os decks: ' . $e->getMessage());
        }
    }

    public static function deleteDeckByID($Deck_ID)
    {
        $sql = "DELETE FROM deck WHERE id = ?";
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$Deck_ID]);
        } catch (PDOException $e) {
            die('Erro ao deletar deck: ' . $e->getMessage());
        }
    }

    public static function updateDeck($id, array $fieldsToUpdate)
    {
        $setClause = implode(', ', array_map(fn($key) => "$key = ?", array_keys($fieldsToUpdate)));
        $sql = "UPDATE deck SET $setClause WHERE id = ?";
        
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);
            $values = array_values($fieldsToUpdate);
            $values[] = $id; 
            return $stmt->execute($values);
        } catch (PDOException $e) {
            die('Erro ao atualizar deck: ' . $e->getMessage());
        }
    }

}
