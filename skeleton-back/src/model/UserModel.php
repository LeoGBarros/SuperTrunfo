<?php

namespace Model\Adm;

use PDO;
use PDOException;

class UserModel
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

    public static function createUser($username, $password, $Admin, $deck_select)
    {
        $sql = "INSERT INTO user (username, password, Admin, deck_select) VALUES (?, ?, ?, ?)";
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);
    
            // Criptografa a senha usando password_hash
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
            $stmt->execute([$username, $hashedPassword, $Admin, $deck_select]);
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
        $sql = "
            SELECT d.name AS deck_name 
            FROM user u
            JOIN deck d ON u.deck_select = d.id
            WHERE u.id = ?
        ";

        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$User_ID]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ? $result['deck_name'] : null;
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

    public static function updateUser($id, array $fieldsToUpdate)
    {
        $setClause = implode(', ', array_map(fn($key) => "$key = ?", array_keys($fieldsToUpdate)));
        $sql = "UPDATE user SET $setClause WHERE id = ?";

        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);

            $values = array_values($fieldsToUpdate);
            $values[] = $id;

            return $stmt->execute($values);
        } catch (PDOException $e) {
            die('Erro ao atualizar usuário: ' . $e->getMessage());
        }
    }


    public static function loginUser($username, $password)
    {
        try {
            $db = self::connect();

            // Buscar usuário com base no username
            $query = "SELECT * FROM user WHERE username = :username";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            $user = $stmt->fetch();

            // Verifica se o usuário existe e se a senha corresponde
            if (!$user || !password_verify($password, $user['password'])) {
                return null; // Usuário não encontrado ou senha incorreta
            }

            // Retorna os dados do usuário (removendo a senha)
            unset($user['password']);
            return $user;

        } catch (PDOException $e) {
            die('Erro ao tentar login de usuário: ' . $e->getMessage());
        }
    }


}
