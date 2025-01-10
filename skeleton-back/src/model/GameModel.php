<?php

namespace Model;

use PDO;
use PDOException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class GameModel
{
    private static function connect()
    {
        $host = 'localhost';
        $dbName = 'supertrunfodb';
        $username = 'root';
        $password = 'root';

        $pdo = new PDO("mysql:host=$host;dbname=$dbName", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $pdo;
    }

    public static function createGame($owner_id, $deck_select)
    {
        try {
            $pdo = self::connect();

            // Verifica se já existe um jogo não iniciado para este owner_id
            $sqlCheck = "SELECT COUNT(*) FROM games WHERE owner_id = ? AND status_game = 'dont_started'";
            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->execute ([$owner_id]);

            if ($stmtCheck->fetchColumn() > 0) {
                throw new \Exception('Já existe uma partida criada e não iniciada para este usuário.');
            }

            // Cria o jogo
            $sql = "INSERT INTO games (owner_id, deck_select) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$owner_id, $deck_select]);

            // Retorna o session_id gerado automaticamente
            return $pdo->lastInsertId();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }



    public static function getOwnerId($token)
    {
        try {
            $secretKey = 'a9b1k87YbOpq3h2Mz8aXvP9wLQZ5R4pJ3cLrV5ZJ5DkRt0jQYzZnM+W8X4Lo0yZp';

            // Decodifica o token JWT
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
            // error_log('Token decodificado: ' . json_encode($decoded));

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
    


    public static function getCreatedGameByID($session_id )
    {
        $sql = "SELECT session_id, owner_id, otherPlayer_id, deck_select, status_game FROM games WHERE session_id  = ?";
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$session_id ]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            die('Erro ao buscar jogos criados por ID: ' . $e->getMessage());
        }
    }

    public static function getAllCreatedGames()
    {
        $sql = "SELECT session_id, owner_id, otherPlayer_id, deck_select, status_game FROM games";
        try {
            $pdo = self::connect();
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            die('Erro ao buscar todos os jogos criados: ' . $e->getMessage());
        }
    }


    public static function joinGame($id, array $fieldsToUpdate)
    {
        // Gerar dinamicamente a cláusula SET
        $setClause = implode(', ', array_map(fn($key) => "$key = :$key", array_keys($fieldsToUpdate)));
        $sql = "UPDATE games SET $setClause WHERE session_id = :session_id";       

        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);

            // Bind values dinamicamente
            foreach ($fieldsToUpdate as $key => $value) {
                $stmt->bindValue(":" . $key, $value);              
            }
            $stmt->bindValue(":session_id", $id, PDO::PARAM_INT);           

            return $stmt->execute();
        } catch (PDOException $e) {
            die('Error updating game: ' . $e->getMessage());
        }
    }


    public static function startGame($Deck_ID, $session_id)
    {
        try {
            $pdo = self::connect();

            // Passo 1: Buscar os IDs das cartas do baralho
            $query = "SELECT id FROM card WHERE Deck_ID = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$Deck_ID]);
            $cardIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Verificar se há cartas suficientes
            if (count($cardIds) < 2) {
                throw new PDOException("Não há cartas suficientes para distribuir.");
            }

            // Passo 2: Embaralhar os IDs
            shuffle($cardIds);

            // Passo 3: Dividir as cartas entre os jogadores
            $midpoint = floor(count($cardIds) / 2);
            $cardPlayer1 = json_encode(array_slice($cardIds, 0, $midpoint));
            $cardPlayer2 = json_encode(array_slice($cardIds, $midpoint));

            // Passo 4: Atualizar o jogo com os dados dos jogadores
            $updateQuery = "UPDATE games SET cardPlayer1 = ?, cardPlayer2 = ? WHERE session_id = ?";
            $updateStmt = $pdo->prepare($updateQuery);

            // Executar a consulta de atualização
            if ($updateStmt->execute([$cardPlayer1, $cardPlayer2, $session_id])) {
                echo "Cartas distribuídas com sucesso para a sessão ID $session_id!";
            } else {
                throw new PDOException("Erro ao atualizar a tabela.");
            }
        } catch (PDOException $e) {
            die('Erro ao iniciar o jogo: ' . $e->getMessage());
        }
    }


    


    // public static function deleteDeckByID($Deck_ID)
    // {
    //     $sql = "DELETE FROM deck WHERE id = ?";
    //     try {
    //         $pdo = self::connect();
    //         $stmt = $pdo->prepare($sql);
    //         return $stmt->execute([$Deck_ID]);
    //     } catch (PDOException $e) {
    //         die('Erro ao deletar deck: ' . $e->getMessage());
    //     }
    // }

   



}
