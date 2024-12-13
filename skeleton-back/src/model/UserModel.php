<?php

namespace Model\Adm;

use PDO;
use PDOException;

class UserModel
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

    public static function createUser($username, $password, $Admin, $deck_select)
    {
        $sql = "INSERT INTO user (username, password, Admin, deck_select) VALUES (?, ?, ?, ?)";
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username, $password, $Admin, $deck_select]);
            return $pdo->lastInsertId();
        } catch (PDOException $e) {
            die('Erro ao criar usuário: ' . $e->getMessage());
        }
    }

    public static function getUserByID($User_ID)
    {
        $sql = "SELECT id, username, Admin, deck_select FROM user WHERE id = ?";
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$User_ID]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            die('Erro ao buscar usuário por ID: ' . $e->getMessage());
        }
    }

    public static function getAllUsers()
    {
        $sql = "SELECT id, username, Admin, deck_select FROM user";
        try {
            $pdo = self::connect();
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            die('Erro ao buscar todos os usuários: ' . $e->getMessage());
        }
    }

    public static function getUserDeck($User_ID)
    {
        $sql = "SELECT deck_select FROM user WHERE id = ?";
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$User_ID]);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            die('Erro ao buscar deck do usuário: ' . $e->getMessage());
        }
    }

    public static function deleteUserByID($User_ID)
    {
        $sql = "DELETE FROM user WHERE id = ?";
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$User_ID]);
        } catch (PDOException $e) {
            die('Erro ao deletar usuário: ' . $e->getMessage());
        }
    }

    public static function updateUser($id, $username, $password, $Admin, $deck_select)
    {
        $sql = "UPDATE user SET username = ?, password = ?, Admin = ?, deck_select = ? WHERE id = ?";
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$username, $password, $Admin, $deck_select, $id]);
        } catch (PDOException $e) {
            die('Erro ao atualizar usuário: ' . $e->getMessage());
        }
    }
}
