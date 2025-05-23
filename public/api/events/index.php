<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../src/controllers/EventController.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['name']) || !isset($data['words']) || !is_array($data['words'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request data']);
        exit;
    }
    
    $controller = new EventController();
    $result = $controller->createEvent($data['name'], $data['words']);
    
    if ($result['success']) {
        echo json_encode(['eventId' => $result['eventId']]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => $result['error']]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
} 