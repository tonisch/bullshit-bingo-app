<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../src/controllers/EventController.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed. Only POST requests are accepted.');
    }

    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['name']) || !isset($data['words']) || !is_array($data['words'])) {
        throw new Exception('Invalid request data. Name and words array are required.');
    }
    
    $controller = new EventController();
    $result = $controller->createEvent($data['name'], $data['words']);
    
    if ($result['success']) {
        echo json_encode(['eventId' => $result['eventId']]);
    } else {
        throw new Exception($result['error']);
    }
} catch (Exception $e) {
    http_response_code($e->getMessage() === 'Method not allowed' ? 405 : 400);
    echo json_encode(['error' => $e->getMessage()]);
} 