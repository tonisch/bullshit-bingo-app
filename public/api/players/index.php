<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../src/controllers/PlayerController.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['eventId']) || !isset($data['name'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }
    
    $controller = new PlayerController();
    $result = $controller->joinEvent($data['eventId'], $data['name']);
    
    if ($result['success']) {
        echo json_encode(['playerId' => $result['playerId']]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => $result['error']]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
} 