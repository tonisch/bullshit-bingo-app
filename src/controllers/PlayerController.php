<?php

class PlayerController {
    private $db;
    
    public function __construct() {
        $this->db = getDbConnection();
    }
    
    public function joinEvent($eventId, $playerName) {
        try {
            // Check if event exists and is active
            $stmt = $this->db->prepare("
                SELECT id FROM events 
                WHERE id = ? AND is_active = TRUE
            ");
            $stmt->execute([$eventId]);
            
            if (!$stmt->fetch()) {
                return [
                    'success' => false,
                    'error' => 'Event not found or not active'
                ];
            }
            
            // Generate unique player ID
            $playerId = uniqid('plr_');
            
            // Add player to event
            $stmt = $this->db->prepare("
                INSERT INTO players (id, event_id, name) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$playerId, $eventId, $playerName]);
            
            return [
                'success' => true,
                'playerId' => $playerId
            ];
            
        } catch (Exception $e) {
            error_log("Error joining event: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to join event'
            ];
        }
    }
} 