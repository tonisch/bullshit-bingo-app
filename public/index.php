<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = '/' . trim($path, '/');
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
if ($method === 'HEAD') {
    $method = 'GET';
}

try {
    dispatch($method, $path);
} catch (UserError $e) {
    if (wants_json()) {
        json_out(['ok' => false, 'error' => $e->getMessage()], 400);
    }
    http_response_code(400);
    view('error', ['title' => 'Das ging nicht', 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    error_log('bingo: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    if (wants_json()) {
        json_out(['ok' => false, 'error' => 'Serverfehler'], 500);
    }
    http_response_code(500);
    view('error', [
        'title'   => 'Serverfehler',
        'message' => cfg('debug') ? $e->getMessage() : 'Da ist etwas schiefgelaufen. Bitte später erneut versuchen.',
    ]);
}

// ------------------------------------------------------------- Dispatcher

function dispatch(string $method, string $path): void
{
    if ($path === '/' && $method === 'GET') {
        view('home', ['title' => 'Bullshit Bingo', 'game' => session_game()]);
        return;
    }

    if ($path === '/neu' && $method === 'GET') {
        view('create', ['title' => 'Neues Spiel anlegen', 'packs' => wordlist_packs()]);
        return;
    }

    if ($path === '/neu' && $method === 'POST') {
        csrf_require($_POST['csrf'] ?? null);
        $result = game_create(
            (string) ($_POST['title'] ?? ''),
            (string) ($_POST['terms'] ?? ''),
            (int) ($_POST['grid'] ?? 5),
            !empty($_POST['free_center'])
        );
        $_SESSION['host'][$result['code']] = $result['admin_token'];
        redirect('/host/' . $result['admin_token']);
    }

    if ($path === '/beitreten' && $method === 'GET') {
        view('join', ['title' => 'Spiel beitreten', 'code' => strtoupper((string) ($_GET['code'] ?? ''))]);
        return;
    }

    if ($path === '/beitreten' && $method === 'POST') {
        csrf_require($_POST['csrf'] ?? null);
        $game = game_by_code((string) ($_POST['code'] ?? ''));
        if (!$game) {
            throw new UserError('Diesen Spielcode gibt es nicht.');
        }
        $player = player_join($game, (string) ($_POST['name'] ?? ''), client_ip());
        $_SESSION['player'] = ['token' => $player['token'], 'game' => $game['code']];
        redirect('/karte');
    }

    if ($path === '/karte' && $method === 'GET') {
        $game = session_game();
        $player = $game ? current_player($game) : null;
        if (!$game || !$player) {
            redirect('/beitreten');
        }
        view('card', [
            'title'  => 'Meine Karte',
            'game'   => $game,
            'player' => $player,
            'terms'  => terms_map((int) $game['id']),
        ]);
        return;
    }

    if (preg_match('#^/host/([a-f0-9]{48})$#', $path, $m) && $method === 'GET') {
        $game = game_by_token($m[1]);
        if (!$game) {
            throw new UserError('Dieser Host-Link ist ungültig.');
        }
        $_SESSION['host'][$game['code']] = $game['admin_token'];
        view('host', [
            'title'    => 'Host: ' . $game['title'],
            'game'     => $game,
            'progress' => players_progress($game),
            'wins'     => game_wins((int) $game['id']),
        ]);
        return;
    }

    if (preg_match('#^/host/([a-f0-9]{48})/aktion$#', $path, $m) && $method === 'POST') {
        csrf_require($_POST['csrf'] ?? null);
        $game = game_by_token($m[1]);
        if (!$game) {
            throw new UserError('Dieser Host-Link ist ungültig.');
        }
        $result = host_action($game, (string) ($_POST['action'] ?? ''));
        if (!$result['ok']) {
            throw new UserError((string) ($result['reason'] ?? 'Aktion fehlgeschlagen.'));
        }
        redirect('/host/' . $m[1]);
    }

    if ($path === '/api/status' && $method === 'GET') {
        $game = game_by_code((string) ($_GET['code'] ?? ''));
        if (!$game) {
            json_out(['ok' => false, 'error' => 'Spiel nicht gefunden'], 404);
        }
        $admin = isset($_GET['admin'])
            && is_string($_GET['admin'])
            && hash_equals((string) $game['admin_token'], $_GET['admin']);

        $player = $admin ? null : current_player($game);
        if (!$admin && !$player) {
            json_out(['ok' => false, 'error' => 'Nicht für dieses Spiel angemeldet'], 403);
        }

        json_out(['ok' => true] + status_payload($game, $player, $admin));
    }

    if ($path === '/api/mark' && $method === 'POST') {
        $payload = request_json();
        header_token_check();

        $game = game_by_code((string) ($payload['code'] ?? ''));
        $player = $game ? current_player($game) : null;
        if (!$game || !$player) {
            json_out(['ok' => false, 'error' => 'Nicht angemeldet'], 403);
        }

        $cell = (int) ($payload['cell'] ?? -1);
        $on = (bool) ($payload['on'] ?? true);
        $marks = set_mark($game, $player, $cell, $on);

        json_out(['ok' => true, 'marks' => $marks]);
    }

    if ($path === '/api/bingo' && $method === 'POST') {
        $payload = request_json();
        header_token_check();

        $game = game_by_code((string) ($payload['code'] ?? ''));
        $player = $game ? current_player($game) : null;
        if (!$game || !$player) {
            json_out(['ok' => false, 'error' => 'Nicht angemeldet'], 403);
        }

        json_out(['ok' => true] + record_bingo($game, $player));
    }

    http_response_code(404);
    view('error', ['title' => 'Nicht gefunden', 'message' => 'Diese Seite gibt es nicht.']);
}

// ---------------------------------------------------------------- Helfer

function wants_json(): bool
{
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
    return strpos($path, '/api/') === 0 || strpos($accept, 'application/json') !== false;
}

function client_ip(): string
{
    return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
}

function header_token_check(): void
{
    $token = $_SERVER['HTTP_X_CSRF'] ?? null;
    if (!csrf_ok($token)) {
        json_out(['ok' => false, 'error' => 'Sitzung abgelaufen, bitte neu laden.'], 419);
    }
}

/**
 * Liefert das Spiel aus der Session, sofern der Spieler noch daran teilnimmt.
 */
function session_game(): ?array
{
    $stored = $_SESSION['player'] ?? null;
    if (!is_array($stored) || empty($stored['game'])) {
        return null;
    }
    return game_by_code((string) $stored['game']);
}

function current_player(?array $game): ?array
{
    if (!$game) {
        return null;
    }
    $stored = $_SESSION['player'] ?? null;
    if (!is_array($stored) || ($stored['game'] ?? '') !== $game['code']) {
        return null;
    }

    $player = player_by_token((string) ($stored['token'] ?? ''));
    if (!$player || (int) $player['game_id'] !== (int) $game['id']) {
        return null;
    }

    return $player;
}
