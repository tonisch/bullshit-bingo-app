<?php
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
define('VAR_DIR', APP_ROOT . '/var');
define('DB_FILE', getenv('BINGO_DB') ?: (VAR_DIR . '/bingo.sqlite'));

date_default_timezone_set('Europe/Berlin');

$GLOBALS['CFG'] = [
    'app_name'    => 'Bullshit Bingo',
    'max_players' => 120,
    'poll_ms'     => 2500,
    'debug'       => false,
];

require APP_ROOT . '/app/helpers.php';
require APP_ROOT . '/app/db.php';
require APP_ROOT . '/app/game.php';
require APP_ROOT . '/app/wordlists.php';

if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}

if (PHP_SAPI !== 'cli') {
    $port = $_SERVER['SERVER_PORT'] ?? '';
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $port === '443';

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_name('bb');
    session_start();

    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

db_init();
