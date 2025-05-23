<?php

class EventController {
    private $db;
    
    public function __construct() {
        try {
            $this->db = getDbConnection();
        } catch (Exception $e) {
            error_log("EventController initialization error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function createEvent($name, $words) {
        try {
            if (empty($name)) {
                throw new Exception("Event name is required");
            }
            
            if (empty($words) || !is_array($words)) {
                throw new Exception("Words must be a non-empty array");
            }
            
            if (count($words) < 24) {
                throw new Exception("At least 24 words are required");
            }
            
            $this->db->beginTransaction();
            
            // Generate unique event ID
            $eventId = uniqid('evt_');
            
            // Create event
            $stmt = $this->db->prepare("
                INSERT INTO events (id, name) 
                VALUES (?, ?)
            ");
            $stmt->execute([$eventId, $name]);
            
            // Insert words
            $stmt = $this->db->prepare("
                INSERT INTO words (event_id, word, is_custom) 
                VALUES (?, ?, ?)
            ");
            
            foreach ($words as $word) {
                $isCustom = !in_array($word, [
                    "Synergy", "Leverage", "Bandwidth", "Circle back", "Touch base",
                    "Think outside the box", "Low hanging fruit", "Win-win", "Game changer",
                    "Take it offline", "Deep dive", "Moving forward", "Best practice",
                    "Streamline", "Optimize", "Scalable", "Innovative", "Disruptive",
                    "Agile", "Paradigm shift", "Core competency", "Value proposition",
                    "Stakeholder", "Action item", "Deliverable"
                ]);
                $stmt->execute([$eventId, $word, $isCustom]);
            }
            
            $this->db->commit();
            return ['success' => true, 'eventId' => $eventId];
            
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error creating event: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
} 