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
            $sqlCheck = "SELECT COUNT(*) FROM games WHERE owner_id = ? AND status_game = 'dont_started' ";
            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->execute ([$owner_id]);

            if ($stmtCheck->fetchColumn() > 0) {
                throw new \Exception('Já existe uma partida criada e não iniciada para este usuário.');
            }

            $sql = "INSERT INTO games (owner_id, deck_select) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$owner_id, $deck_select]);

            return $pdo->lastInsertId();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function getCreatedGameByID($session_id ){
        $sql = "SELECT session_id, owner_id, otherPlayer_id, deck_select, status_game, whose_turn, cardPlayer1, cardPlayer2, last_round_winner, gameWinner FROM games WHERE session_id  = ?";
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$session_id ]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            die('Erro ao buscar jogos criados por ID: ' . $e->getMessage());
        }
    }

    public static function getActiveGameByOwner($owner_id){
        $sql = "SELECT session_id, owner_id, status_game FROM games WHERE owner_id = ? AND status_game IN ('started', 'dont_started')";

        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$owner_id]);
            return $stmt->fetch(); // Retorna o primeiro jogo ativo encontrado
        } catch (PDOException $e) {
            die('Erro ao buscar jogos ativos por owner_id: ' . $e->getMessage());
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
        $setClause = implode(', ', array_map(fn($key) => "$key = :$key", array_keys($fieldsToUpdate))); // Vai colocar em uma string os campos percorridos pelo array_map separados por ','
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
        
            $midpoint = floor(count($cardIds) / 2); // O floor gante que o primeiro array (cardPlayer1) terá o menor número possível de cartas ao lidar com um número ímpar para o segundo array (cardPlayer2) ter o restante.
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
            // error_log("Cartas brutas encontradas: " . json_encode($cards));

            if (!$cards) {
                throw new PDOException("Não foi possível encontrar as cartas para a sessão.");
            }

            $cardPlayer1 = json_decode($cards['cardPlayer1'], true);
            $cardPlayer2 = json_decode($cards['cardPlayer2'], true);

            // error_log("Cartas decodificadas: Player 1 -> " . json_encode($cardPlayer1) . ", Player 2 -> " . json_encode($cardPlayer2));

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
    
    
    public static function removeFirstCardFromPlayers($session_id) {
        try {
            $pdo = self::connect();
            $query = "SELECT cardPlayer1, cardPlayer2 FROM games WHERE session_id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$session_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$result || !isset($result['cardPlayer1'], $result['cardPlayer2'])) {
                throw new PDOException("Cartas dos jogadores não encontradas ou inválidas.");
            }
            $cardsPlayer1 = json_decode($result['cardPlayer1'], true);
            $cardsPlayer2 = json_decode($result['cardPlayer2'], true);
    
            if (!is_array($cardsPlayer1) || empty($cardsPlayer1)) {
                throw new PDOException("Cartas do jogador 1 estão no formato inválido ou estão vazias.");
            }
            if (!is_array($cardsPlayer2) || empty($cardsPlayer2)) {
                throw new PDOException("Cartas do jogador 2 estão no formato inválido ou estão vazias.");
            }

            $removedCardPlayer1 = array_shift($cardsPlayer1); //Remove a primeira carta do array
            $removedCardPlayer2 = array_shift($cardsPlayer2); //Remove a primeira carta do array
    
            $updateQuery = "UPDATE games SET cardPlayer1 = ?, cardPlayer2 = ? WHERE session_id = ?";
            $updateStmt = $pdo->prepare($updateQuery);
            $updateStmt->execute([json_encode($cardsPlayer1), json_encode($cardsPlayer2), $session_id]);    
            return [
                'removedCardPlayer1' => $removedCardPlayer1,
                'removedCardPlayer2' => $removedCardPlayer2
            ];
        } catch (PDOException $e) {
            throw new PDOException("Erro ao atualizar as cartas dos jogadores: " . $e->getMessage());
        }
    }  

    public static function addCardsToWinner($session_id, $winner, $cards) {
        try {
            if (!is_array($cards) || empty($cards)) {
                throw new \InvalidArgumentException("As cartas fornecidas devem ser um array não vazio.");
            }
            $pdo = self::connect();
            $query = "SELECT $winner FROM games WHERE session_id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$session_id]);
            $game = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$game || !isset($game[$winner])) {
                throw new PDOException("Baralho do vencedor não encontrado.");
            }
            $currentCards = json_decode($game[$winner], true);
    
            if (!is_array($currentCards)) {
                throw new PDOException("Baralho do vencedor está no formato inválido.");
            }
    
            $currentCards = array_merge($currentCards, $cards); // Junta os valores dos arrays em uma sequência.
    
            $updatedCards = json_encode($currentCards);
            $updateQuery = "UPDATE games SET $winner = ? WHERE session_id = ?";
            $updateStmt = $pdo->prepare($updateQuery);
            $updateStmt->execute([$updatedCards, $session_id]);
    
        } catch (PDOException $e) {
            throw new \Exception("Erro ao adicionar cartas ao jogador vencedor: " . $e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception("Erro geral: " . $e->getMessage());
        }
    }   


    public static function updateLastRoundWinner($session_id, $winner_id)   {
        $sql = "UPDATE games SET last_round_winner = ? WHERE session_id = ?";
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$winner_id, $session_id]);
        } catch (PDOException $e) {
            throw new \Exception('Erro ao atualizar o vencedor da última rodada: ' . $e->getMessage());
        }
    }

    public static function setWinner($session_id, $winner_id) {
        $sql = "UPDATE games SET gameWinner = ? WHERE session_id = ?";
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$winner_id, $session_id]);
        } catch (PDOException $e) {
            throw new \Exception('Erro ao definir o vencedor do jogo: ' . $e->getMessage());
        }
    }

    public static function updateGameStatus($session_id) {
        $sql = "UPDATE games SET status_game = 'finished' WHERE session_id = ?";
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$session_id]);
        } catch (PDOException $e) {
            throw new \Exception('Erro ao atualizar o status do jogo para "finished": ' . $e->getMessage());
        }
    }


    public static function getTieCards($session_id){
        $sql = "SELECT tie_cards FROM games WHERE session_id = ?";
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$session_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result && isset($result['tie_cards'])) {
                $tieCards = json_decode($result['tie_cards'], true);                
                return is_array($tieCards) ? $tieCards : [];
            }
            return [];
        } catch (\PDOException $e) {
            throw new \Exception('Erro ao buscar as cartas empatadas: ' . $e->getMessage());
        }
    }
    
    public static function clearTieCards($session_id){
        $sql = "UPDATE games SET tie_cards = JSON_ARRAY() WHERE session_id = ?";
        try {
            $pdo = self::connect();           
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$session_id]);

            return true; // Indica que a operação foi bem-sucedida
        } catch (\PDOException $e) {
            throw new \Exception('Erro ao limpar as cartas empatadas: ' . $e->getMessage());
        }
    }

    public static function addTieCards($session_id, array $newCards){
        $sqlGet = "SELECT tie_cards FROM games WHERE session_id = ?";
        $sqlUpdate = "UPDATE games SET tie_cards = ? WHERE session_id = ?";
        try {            
            $pdo = self::connect();

            
            $stmt = $pdo->prepare($sqlGet);
            $stmt->execute([$session_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $existingTieCards = [];
            if ($result && isset($result['tie_cards'])) {
                $existingTieCards = json_decode($result['tie_cards'], true);
            }

            $updatedTieCards = array_merge($existingTieCards, $newCards);
            
            $stmt = $pdo->prepare($sqlUpdate);
            $stmt->execute([json_encode($updatedTieCards), $session_id]);

            return true; // Indica que a operação foi bem-sucedida
        } catch (\PDOException $e) {
            throw new \Exception('Erro ao adicionar cartas empatadas: ' . $e->getMessage());
        }
    }




}
