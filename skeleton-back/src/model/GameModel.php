<?php

namespace Model;

use PDO;
use PDOException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class GameModel
{
    private static function connect(){
        $host = 'localhost';
        $dbName = 'supertrunfodb';
        $username = 'root';
        $password = 'root';

        $pdo = new PDO("mysql:host=$host;dbname=$dbName", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $pdo;
    }

    public static function createGame($owner_id, $deck_select){
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

    public static function getOwnerId($token){
        try {
            $decoded = JWT::decode($token, new Key('a9b1k87YbOpq3h2Mz8aXvP9wLQZ5R4pJ3cLrV5ZJ5DkRt0jQYzZnM', 'HS256'));
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


    public static function getCreatedGameByID($session_id ){
        $sql = "SELECT session_id, owner_id, otherPlayer_id, deck_select, status_game, whose_turn, cardPlayer1, cardPlayer2 FROM games WHERE session_id  = ?";
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$session_id ]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            die('Erro ao buscar jogos criados por ID: ' . $e->getMessage());
        }
    }

    public static function getAllCreatedGames(){
        $sql = "SELECT session_id, owner_id, otherPlayer_id, deck_select, status_game FROM games";
        try {
            $pdo = self::connect();
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            die('Erro ao buscar todos os jogos criados: ' . $e->getMessage());
        }
    }


    public static function joinGame($id, array $fieldsToUpdate){
        $setClause = implode(', ', array_map(fn($key) => "$key = :$key", array_keys($fieldsToUpdate)));
        $sql = "UPDATE games SET $setClause WHERE session_id = :session_id";       

        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);

            foreach ($fieldsToUpdate as $key => $value) {
                $stmt->bindValue(":" . $key, $value);              
            }
            $stmt->bindValue(":session_id", $id, PDO::PARAM_INT);           

            return $stmt->execute();
        } catch (PDOException $e) {
            die('Error updating game: ' . $e->getMessage());
        }
    }


    public static function startGame($Deck_ID, $session_id){
        try {
            $pdo = self::connect();
        
            $playerQuery = "SELECT owner_id, otherPlayer_id FROM games WHERE session_id = ?";
            $playerStmt = $pdo->prepare($playerQuery);
            $playerStmt->execute([$session_id]);
            $players = $playerStmt->fetch(PDO::FETCH_ASSOC);
        
            if (empty($players['owner_id']) || empty($players['otherPlayer_id'])) {
                throw new PDOException("Não há jogadores suficientes para iniciar o jogo.");
            }
        
            $query = "SELECT id FROM card WHERE Deck_ID = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$Deck_ID]);
            $cardIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
            if (count($cardIds) < 2) {
                throw new PDOException("Não há cartas suficientes para distribuir.");
            }
        
            shuffle($cardIds);
        
            $midpoint = floor(count($cardIds) / 2);
            $cardPlayer1 = json_encode(array_slice($cardIds, 0, $midpoint));
            $cardPlayer2 = json_encode(array_slice($cardIds, $midpoint));
        
            $updateQuery = "UPDATE games SET cardPlayer1 = ?, cardPlayer2 = ?, status_game = 'started' WHERE session_id = ?";
            $updateStmt = $pdo->prepare($updateQuery);
        
            if ($updateStmt->execute([$cardPlayer1, $cardPlayer2, $session_id])) {
            } else {
                throw new PDOException("Erro ao atualizar a tabela.");
            }
        } catch (PDOException $e) {
            die('Erro ao iniciar o jogo: ' . $e->getMessage());
        }
    }

    public static function getFirstCards($session_id) {
        try {
            $pdo = self::connect();
            $query = "SELECT cardPlayer1, cardPlayer2 FROM games WHERE session_id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$session_id]);
            $cards = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log("Cartas brutas encontradas: " . json_encode($cards));

            if (!$cards) {
                throw new PDOException("Não foi possível encontrar as cartas para a sessão.");
            }

            $cardPlayer1 = json_decode($cards['cardPlayer1'], true);
            $cardPlayer2 = json_decode($cards['cardPlayer2'], true);

            error_log("Cartas decodificadas: Player 1 -> " . json_encode($cardPlayer1) . ", Player 2 -> " . json_encode($cardPlayer2));

            return [
                'player1' => $cardPlayer1[0] ?? null,
                'player2' => $cardPlayer2[0] ?? null
            ];
        } catch (PDOException $e) {
            throw new PDOException("Erro ao buscar as primeiras cartas: " . $e->getMessage());
        }
    }

    public static function compareFirstCards($card1Id, $card2Id, $attribute) {
        try {
            $pdo = self::connect();
    
            $deckQuery = "
                SELECT d.Atributte01, d.Atributte02, d.Atributte03, d.Atributte04, d.Atributte05
                FROM card c
                JOIN deck d ON c.Deck_ID = d.id
                WHERE c.id = ?
            ";
            $deckStmt = $pdo->prepare($deckQuery);
            $deckStmt->execute([$card1Id]);
            $deck = $deckStmt->fetch(PDO::FETCH_ASSOC);

            if (!$deck) {
                throw new PDOException("Deck não encontrado para a carta.");
            }
    
            $attributeMap = [
                'Score01' => $deck['Atributte01'],
                'Score02' => $deck['Atributte02'],
                'Score03' => $deck['Atributte03'],
                'Score04' => $deck['Atributte04'],
                'Score05' => $deck['Atributte05']
            ];
    
            if (!isset($attributeMap[$attribute])) {
                throw new PDOException("Atributo não encontrado no deck.");
            }
    
            $attributeName = $attributeMap[$attribute];
    
            $query = "SELECT id, $attribute FROM card WHERE id IN (?, ?)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$card1Id, $card2Id]);
            $cards = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            if (count($cards) !== 2) {
                throw new PDOException("Não foi possível encontrar ambas as cartas para comparação.");
            }
    
            $card1 = $cards[0]['id'] == $card1Id ? $cards[0] : $cards[1];
            $card2 = $cards[0]['id'] == $card2Id ? $cards[0] : $cards[1];
    
            $value1 = $card1[$attribute] ?? null;
            $value2 = $card2[$attribute] ?? null;
    
            if ($value1 === null || $value2 === null) {
                throw new PDOException("Atributo especificado não encontrado nas cartas.");
            }
    
            if ($value1 > $value2) {
                return "Jogador 1 venceu com o atributo $attributeName ($value1 > $value2).";
            } elseif ($value1 < $value2) {
                return "Jogador 2 venceu com o atributo $attributeName ($value2 > $value1).";
            } else {
                return "Empate no atributo $attributeName ($value1 = $value2).";
            }
        } catch (PDOException $e) {
            throw new PDOException("Erro ao comparar as cartas: " . $e->getMessage());
        }
    }
    
    public static function removeCardFromPlayer($session_id, $losingPlayer) {
        try {
            $pdo = self::connect();
    
            $column = ($losingPlayer === 'player1') ? 'cardPlayer1' : 'cardPlayer2';
    
            $query = "SELECT $column FROM games WHERE session_id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$session_id]);
            $cards = $stmt->fetchColumn();
    
            if (!$cards) {
                throw new PDOException("Cartas não encontradas para o jogador perdedor.");
            }
    
            $decodedCards = json_decode($cards, true);
            array_shift($decodedCards);
    
            $updatedCards = json_encode($decodedCards);
            $updateQuery = "UPDATE games SET $column = ? WHERE session_id = ?";
            $updateStmt = $pdo->prepare($updateQuery);
            $updateStmt->execute([$updatedCards, $session_id]);
        } catch (PDOException $e) {
            throw new PDOException("Erro ao atualizar as cartas do jogador perdedor: " . $e->getMessage());
        }
    }

}
