<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/models/Database.php';

$db = new Database();
if ($db->initialize()) {
    echo "Database initialized successfully!\n";
} else {
    echo "Error initializing database.\n";
} 