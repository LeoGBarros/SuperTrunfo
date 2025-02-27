<?php

namespace Model;

use PDO;
use PDOException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UserModel
{
    private static function connect(){
        
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

    public static function createUser($username, $password, $Admin){
        $sql = "INSERT INTO user (username, password, Admin) VALUES (?, ?, ?)";
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);
            // Criptografa a senha usando password_hash
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            if ($stmt->execute([$username, $hashedPassword, $Admin])) {
                return $pdo->lastInsertId();
            }            
            return false;
        } catch (PDOException $e) {            
            return false;
        }
    }
    

    public static function getUserByID($User_ID){
        $sql = "SELECT id, username, Admin 
        FROM user WHERE id = ?";
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$User_ID]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            die('Erro ao buscar usuário por ID: ' . $e->getMessage());
        }
    }

    public static function getAllUsers(){
        $sql = "SELECT id, username, Admin 
        FROM user";
        try {
            $pdo = self::connect();
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            die('Erro ao buscar todos os usuários: ' . $e->getMessage());
        }
    }        

    public static function getOwnerId($token){
        try {
            $decoded = JWT::decode($token, new Key('a9b1k87YbOpq3h2Mz8aXvP9wLQZ5R4pJ3cLrV5ZJ5DkRt0jQYzZnM+W8X4Lo0yZp', 'HS256'));
            error_log('Estrutura do token JWT: ' . json_encode($decoded));

            // Verifica os campos user_id ou user_ID no token
            if (isset($decoded->user_ID)) {
                return $decoded->user_ID;
            } else {
                throw new \Exception('ID do usuário (user_id ou user_ID) não encontrado no token.');
            }
        } catch (\Firebase\JWT\ExpiredException $e) {
            throw new \Exception('Token expirado.');
        } catch (\Exception $e) {
            throw new \Exception('Erro ao decodificar token.');
        }
    }   

    public static function checkAdmin($token){
        try {
            $secretKey = 'a9b1k87YbOpq3h2Mz8aXvP9wLQZ5R4pJ3cLrV5ZJ5DkRt0jQYzZnM+W8X4Lo0yZp';

            // Decodifica o token JWT
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
            // error_log('Token decodificado: ' . json_encode($decoded));

            // Verifica se o campo "admin" existe e é igual a 0
            $isAdmin = isset($decoded->admin) && intval($decoded->admin) === 1;
            // error_log('Retorno de isAdmin no checkAdmin: ' . var_export($isAdmin, true));

            return $isAdmin;
        } catch (\Exception $e) {
            error_log('Erro ao decodificar token: ' . $e->getMessage());
            return false; // Retorna falso em caso de erro
        }
    }

    public static function deleteUserByID($User_ID){
        $sql = "DELETE FROM user WHERE id = ?";
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$User_ID]);
        } catch (PDOException $e) {
            die('Erro ao deletar usuário: ' . $e->getMessage());
        }
    }

    public static function updateUser($id, array $fieldsToUpdate){
        $setClause = implode(', ', array_map(fn($key) => "$key = ?", array_keys($fieldsToUpdate))); // Vai colocar em uma string os campos percorridos pelo array_map separados por ','
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


    public static function loginUser($username, $password){
        try {
            $db = self::connect();

            // Buscar usuário com base no username
            $query = "SELECT id, username, password, Admin 
                    FROM user WHERE username = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$username]);

            $user = $stmt->fetch();

            // Verifica se o usuário existe e se a senha corresponde
            if (!$user || !password_verify($password, $user['password'])) {
                return null; // Usuário não encontrado ou senha incorreta
            }

            // Remove a senha antes de retornar os dados do usuário
            unset($user['password']);
            return $user;

        } catch (PDOException $e) {
            die('Erro ao tentar login de usuário: ' . $e->getMessage());
        }
    }

}
