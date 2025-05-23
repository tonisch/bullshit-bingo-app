<?php
require_once __DIR__ . '/../models/Database.php';

class GameController {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getGameState($eventId, $playerId) {
        try {
            // Get current round
            $roundQuery = "SELECT * FROM rounds WHERE event_id = ? ORDER BY created_at DESC LIMIT 1";
            $round = $this->db->query($roundQuery, [$eventId])->fetch();

            if (!$round) {
                return ['error' => 'No active round found'];
            }

            // Get player's board
            $boardQuery = "SELECT pb.*, w.word 
                          FROM player_boards pb 
                          JOIN words w ON pb.word_id = w.id 
                          WHERE pb.round_id = ? AND pb.player_id = ?";
            $board = $this->db->query($boardQuery, [$round['id'], $playerId])->fetchAll();

            // Get marked words
            $markedQuery = "SELECT word_id FROM marked_words 
                           WHERE round_id = ? AND player_id = ?";
            $marked = $this->db->query($markedQuery, [$round['id'], $playerId])->fetchAll(PDO::FETCH_COLUMN);

            // Get winners
            $winnersQuery = "SELECT p.name, w.created_at 
                            FROM winners w 
                            JOIN players p ON w.player_id = p.id 
                            WHERE w.round_id = ?";
            $winners = $this->db->query($winnersQuery, [$round['id']])->fetchAll();

            return [
                'round' => $round,
                'board' => $board,
                'marked' => $marked,
                'winners' => $winners
            ];
        } catch (Exception $e) {
            return ['error' => 'Failed to get game state: ' . $e->getMessage()];
        }
    }

    public function markWord($eventId, $playerId, $wordId) {
        try {
            // Get current round
            $roundQuery = "SELECT * FROM rounds WHERE event_id = ? ORDER BY created_at DESC LIMIT 1";
            $round = $this->db->query($roundQuery, [$eventId])->fetch();

            if (!$round) {
                return ['error' => 'No active round found'];
            }

            // Check if word is already marked
            $checkQuery = "SELECT * FROM marked_words 
                          WHERE round_id = ? AND player_id = ? AND word_id = ?";
            $existing = $this->db->query($checkQuery, [$round['id'], $playerId, $wordId])->fetch();

            if ($existing) {
                // Unmark word
                $deleteQuery = "DELETE FROM marked_words 
                               WHERE round_id = ? AND player_id = ? AND word_id = ?";
                $this->db->query($deleteQuery, [$round['id'], $playerId, $wordId]);
                return ['marked' => false];
            } else {
                // Mark word
                $insertQuery = "INSERT INTO marked_words (round_id, player_id, word_id) 
                               VALUES (?, ?, ?)";
                $this->db->query($insertQuery, [$round['id'], $playerId, $wordId]);

                // Check for win
                if ($this->checkWin($round['id'], $playerId)) {
                    $this->declareWinner($round['id'], $playerId);
                }

                return ['marked' => true];
            }
        } catch (Exception $e) {
            return ['error' => 'Failed to mark word: ' . $e->getMessage()];
        }
    }

    public function startNewRound($eventId) {
        try {
            // Create new round
            $roundQuery = "INSERT INTO rounds (event_id) VALUES (?)";
            $this->db->query($roundQuery, [$eventId]);
            $roundId = $this->db->lastInsertId();

            // Get all players
            $playersQuery = "SELECT id FROM players WHERE event_id = ?";
            $players = $this->db->query($playersQuery, [$eventId])->fetchAll(PDO::FETCH_COLUMN);

            // Get random words for each player
            $wordsQuery = "SELECT id FROM words WHERE event_id = ? ORDER BY RAND() LIMIT 24";
            $words = $this->db->query($wordsQuery, [$eventId])->fetchAll(PDO::FETCH_COLUMN);

            // Create boards for each player
            foreach ($players as $playerId) {
                $boardWords = array_merge(
                    array_slice($words, 0, 12),
                    ['FREE'], // Free space
                    array_slice($words, 12, 12)
                );
                shuffle($boardWords);

                foreach ($boardWords as $position => $wordId) {
                    if ($wordId === 'FREE') {
                        $wordId = null;
                    }
                    $boardQuery = "INSERT INTO player_boards (round_id, player_id, word_id, position) 
                                 VALUES (?, ?, ?, ?)";
                    $this->db->query($boardQuery, [$roundId, $playerId, $wordId, $position]);
                }
            }

            return ['success' => true, 'roundId' => $roundId];
        } catch (Exception $e) {
            return ['error' => 'Failed to start new round: ' . $e->getMessage()];
        }
    }

    private function checkWin($roundId, $playerId) {
        // Get marked words
        $markedQuery = "SELECT position FROM marked_words mw 
                       JOIN player_boards pb ON mw.word_id = pb.word_id 
                       WHERE mw.round_id = ? AND mw.player_id = ?";
        $marked = $this->db->query($markedQuery, [$roundId, $playerId])->fetchAll(PDO::FETCH_COLUMN);

        // Add center position (free space)
        $marked[] = 12;

        // Check rows
        for ($i = 0; $i < 5; $i++) {
            $row = array_filter($marked, function($pos) use ($i) {
                return $pos >= $i * 5 && $pos < ($i + 1) * 5;
            });
            if (count($row) === 5) return true;
        }

        // Check columns
        for ($i = 0; $i < 5; $i++) {
            $col = array_filter($marked, function($pos) use ($i) {
                return $pos % 5 === $i;
            });
            if (count($col) === 5) return true;
        }

        // Check diagonals
        $diag1 = array_filter($marked, function($pos) {
            return $pos % 6 === 0;
        });
        if (count($diag1) === 5) return true;

        $diag2 = array_filter($marked, function($pos) {
            return $pos % 4 === 0 && $pos > 0 && $pos < 24;
        });
        if (count($diag2) === 5) return true;

        return false;
    }

    private function declareWinner($roundId, $playerId) {
        try {
            $query = "INSERT INTO winners (round_id, player_id) VALUES (?, ?)";
            $this->db->query($query, [$roundId, $playerId]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
} 