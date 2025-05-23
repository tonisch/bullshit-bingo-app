<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../../src/controllers/GameController.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$gameController = new GameController();

switch ($method) {
    case 'GET':
        // Get game state
        if (isset($_GET['eventId']) && isset($_GET['playerId'])) {
            $result = $gameController->getGameState($_GET['eventId'], $_GET['playerId']);
            echo json_encode($result);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required parameters']);
        }
        break;

    case 'POST':
        // Update game state (mark word)
        $data = json_decode(file_get_contents('php://input'), true);
        if (isset($data['eventId']) && isset($data['playerId']) && isset($data['wordId'])) {
            $result = $gameController->markWord($data['eventId'], $data['playerId'], $data['wordId']);
            echo json_encode($result);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required parameters']);
        }
        break;

    case 'PUT':
        // Start new round
        $data = json_decode(file_get_contents('php://input'), true);
        if (isset($data['eventId'])) {
            $result = $gameController->startNewRound($data['eventId']);
            echo json_encode($result);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Missing event ID']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
} 