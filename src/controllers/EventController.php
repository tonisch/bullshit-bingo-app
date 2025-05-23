<?php

class EventController {
    private $db;
    
    public function __construct() {
        $this->db = getDbConnection();
    }
    
    public function createEvent($name, $words) {
        try {
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
            $this->db->rollBack();
            error_log("Error creating event: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to create event'];
        }
    }
} 