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

    public static function createDeck($name, $qntd_cards, $disponible)
    {
        $sql = "INSERT INTO deck (name, qntd_cards, disponible) VALUES (?, ?, ?)";
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $qntd_cards, $disponible]);
            return $pdo->lastInsertId();
        } catch (PDOException $e) {
            die('Erro ao criar deck: ' . $e->getMessage());
        }
    }

    public static function getDeckByID($Deck_ID)
    {
        $sql = "SELECT id, name, qntd_cards, disponible FROM deck WHERE id = ?";
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$Deck_ID]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            die('Erro ao buscar deck por ID: ' . $e->getMessage());
        }
    }

    public static function getAll()
    {
        $sql = "SELECT id, name, qntd_cards, disponible FROM deck";
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

    public static function updateDeck($id, $name, $qntd_cards, $disponible)
    {
        $sql = "UPDATE deck SET name = ?, qntd_cards = ?, disponible = ? WHERE id = ?";
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$name, $qntd_cards, $disponible, $id]);
        } catch (PDOException $e) {
            die('Erro ao atualizar deck: ' . $e->getMessage());
        }
    }
}
