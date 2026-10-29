<?php
declare(strict_types=1);

// ---------------------------------------------------------------- Spiele

function game_create(string $title, string $termsRaw, int $grid, bool $freeCenter): array
{
    $title = trim(preg_replace('/\s+/u', ' ', $title) ?? '');
    if ($title === '') {
        $title = 'Bullshit Bingo';
    }
    $title = mb_substr($title, 0, 80);

    $grid = in_array($grid, [3, 4, 5], true) ? $grid : 5;

    $terms = [];
    $seen = [];
    foreach (preg_split('/\R/u', $termsRaw) ?: [] as $line) {
        $term = trim(preg_replace('/\s+/u', ' ', $line) ?? '');
        if ($term === '') {
            continue;
        }
        $term = mb_substr($term, 0, 60);
        $key = mb_strtolower($term);
        if (isset($seen[$key])) {
            continue;
        }
        $seen[$key] = true;
        $terms[] = $term;
    }

    $cells = $grid * $grid;
    $needed = $cells - ($freeCenter ? 1 : 0);

    if (count($terms) < $needed) {
        throw new UserError(t('err.too_few_terms', [
            'have' => count($terms),
            'need' => $needed,
            'grid' => $grid,
            'free' => $freeCenter ? t('err.free_suffix') : '',
        ]));
    }
    if (count($terms) > 300) {
        $terms = array_slice($terms, 0, 300);
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $code = '';
        $check = $pdo->prepare('SELECT 1 FROM games WHERE code = ?');
        for ($try = 0; $try < 10; $try++) {
            $candidate = random_code(6);
            $check->execute([$candidate]);
            if (!$check->fetchColumn()) {
                $code = $candidate;
                break;
            }
        }
        if ($code === '') {
            throw new RuntimeException(t('err.no_free_code'));
        }

        $token = random_token(24);
        $pdo->prepare('INSERT INTO games (code, admin_token, title, grid, free_center, status, round, created_at)
                       VALUES (?, ?, ?, ?, ?, ?, 1, ?)')
            ->execute([$code, $token, $title, $grid, $freeCenter ? 1 : 0, 'lobby', now()]);

        $gameId = (int) $pdo->lastInsertId();
        $ins = $pdo->prepare('INSERT INTO terms (game_id, text, position) VALUES (?, ?, ?)');
        foreach ($terms as $i => $term) {
            $ins->execute([$gameId, $term, $i]);
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }

    return ['id' => $gameId, 'code' => $code, 'admin_token' => $token, 'terms' => count($terms)];
}

function game_by_code(string $code): ?array
{
    $clean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code) ?? '');
    if ($clean === '') {
        return null;
    }
    $stmt = db()->prepare('SELECT * FROM games WHERE code = ?');
    $stmt->execute([$clean]);
    $game = $stmt->fetch();
    return $game ?: null;
}

function game_by_token(string $token): ?array
{
    if ($token === '') {
        return null;
    }
    $stmt = db()->prepare('SELECT * FROM games WHERE admin_token = ?');
    $stmt->execute([$token]);
    $game = $stmt->fetch();
    return $game ?: null;
}

function game_terms(int $gameId): array
{
    $stmt = db()->prepare('SELECT id, text FROM terms WHERE game_id = ? ORDER BY position, id');
    $stmt->execute([$gameId]);
    return $stmt->fetchAll();
}

function terms_map(int $gameId): array
{
    $map = [];
    foreach (game_terms($gameId) as $term) {
        $map[(int) $term['id']] = $term['text'];
    }
    return $map;
}

// --------------------------------------------------------------- Spieler

function player_join(array $game, string $name, string $ip): array
{
    $name = trim(preg_replace('/\s+/u', ' ', $name) ?? '');
    $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?? '';
    if ($name === '') {
        throw new UserError(t('err.name_required'));
    }
    $name = mb_substr($name, 0, 24);

    if ($game['status'] === 'finished') {
        throw new UserError(t('err.game_finished'));
    }

    $pdo = db();
    $gameId = (int) $game['id'];

    $count = $pdo->prepare('SELECT COUNT(*) FROM players WHERE game_id = ?');
    $count->execute([$gameId]);
    if ((int) $count->fetchColumn() >= (int) cfg('max_players', 120)) {
        throw new UserError(t('err.game_full'));
    }

    $recent = $pdo->prepare('SELECT COUNT(*) FROM joins WHERE ip = ? AND created_at > ?');
    $recent->execute([$ip, date('Y-m-d H:i:s', time() - 600)]);
    if ((int) $recent->fetchColumn() >= 25) {
        throw new UserError(t('err.too_many_joins'));
    }

    $termIds = [];
    foreach (game_terms($gameId) as $term) {
        $termIds[] = (int) $term['id'];
    }
    $card = build_card($termIds, (int) $game['grid'], (bool) $game['free_center']);

    $token = random_token(24);
    $pdo->prepare('INSERT INTO players (game_id, token, name, card, created_at, last_seen) VALUES (?, ?, ?, ?, ?, ?)')
        ->execute([$gameId, $token, $name, json_encode($card), now(), now()]);
    $playerId = (int) $pdo->lastInsertId();

    $pdo->prepare('INSERT INTO joins (ip, game_id, created_at) VALUES (?, ?, ?)')
        ->execute([$ip, $gameId, now()]);

    return player_by_id($playerId) ?? [];
}

function player_by_id(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM players WHERE id = ?');
    $stmt->execute([$id]);
    $player = $stmt->fetch();
    return $player ?: null;
}

function player_by_token(string $token): ?array
{
    if ($token === '') {
        return null;
    }
    $stmt = db()->prepare('SELECT * FROM players WHERE token = ?');
    $stmt->execute([$token]);
    $player = $stmt->fetch();
    return $player ?: null;
}

function player_card(array $player): array
{
    $card = json_decode((string) $player['card'], true);
    return is_array($card) ? array_map('intval', $card) : [];
}

function marks_of(int $playerId): array
{
    $stmt = db()->prepare('SELECT cell FROM marks WHERE player_id = ? ORDER BY cell');
    $stmt->execute([$playerId]);
    return array_map('intval', array_column($stmt->fetchAll(), 'cell'));
}

function build_card(array $termIds, int $grid, bool $freeCenter): array
{
    $needed = $grid * $grid - ($freeCenter ? 1 : 0);
    if (count($termIds) < $needed) {
        throw new UserError(t('err.pool_small'));
    }
    shuffle($termIds);
    $card = array_slice($termIds, 0, $needed);

    if ($freeCenter) {
        array_splice($card, intdiv($grid * $grid, 2), 0, [0]);
    }

    return array_values($card);
}

function set_mark(array $game, array $player, int $cell, bool $on): array
{
    $card = player_card($player);
    if (!isset($card[$cell]) || $card[$cell] === 0) {
        throw new UserError(t('err.cell_locked'));
    }
    if ($game['status'] !== 'running') {
        throw new UserError(t('err.not_running'));
    }

    $pdo = db();
    if ($on) {
        $pdo->prepare('INSERT OR IGNORE INTO marks (player_id, cell, created_at) VALUES (?, ?, ?)')
            ->execute([(int) $player['id'], $cell, now()]);
    } else {
        $pdo->prepare('DELETE FROM marks WHERE player_id = ? AND cell = ?')
            ->execute([(int) $player['id'], $cell]);
    }

    $pdo->prepare('UPDATE players SET last_seen = ? WHERE id = ?')->execute([now(), (int) $player['id']]);

    return marks_of((int) $player['id']);
}

// ---------------------------------------------------------------- Reihen

function grid_lines(int $grid): array
{
    $lines = [];

    for ($row = 0; $row < $grid; $row++) {
        $cells = [];
        for ($col = 0; $col < $grid; $col++) {
            $cells[] = $row * $grid + $col;
        }
        $lines[] = ['type' => 'row', 'index' => $row, 'cells' => $cells];
    }

    for ($col = 0; $col < $grid; $col++) {
        $cells = [];
        for ($row = 0; $row < $grid; $row++) {
            $cells[] = $row * $grid + $col;
        }
        $lines[] = ['type' => 'col', 'index' => $col, 'cells' => $cells];
    }

    $diagA = [];
    $diagB = [];
    for ($i = 0; $i < $grid; $i++) {
        $diagA[] = $i * $grid + $i;
        $diagB[] = $i * $grid + ($grid - 1 - $i);
    }
    $lines[] = ['type' => 'diag', 'index' => 0, 'cells' => $diagA];
    $lines[] = ['type' => 'diag', 'index' => 1, 'cells' => $diagB];

    return $lines;
}

function evaluate_card(array $card, array $marks, int $grid): array
{
    $marked = array_fill_keys($marks, true);
    $full = [];
    $best = 0;

    foreach (grid_lines($grid) as $line) {
        $hits = 0;
        foreach ($line['cells'] as $cell) {
            if (($card[$cell] ?? 0) === 0 || isset($marked[$cell])) {
                $hits++;
            }
        }
        if ($hits > $best) {
            $best = $hits;
        }
        if ($hits === $grid) {
            $full[] = $line;
        }
    }

    return ['best' => $best, 'total' => $grid, 'lines' => $full];
}

function record_bingo(array $game, array $player): array
{
    if ($game['status'] !== 'running') {
        return ['ok' => false, 'reason' => t('err.not_running')];
    }

    $grid = (int) $game['grid'];
    $evaluation = evaluate_card(player_card($player), marks_of((int) $player['id']), $grid);

    if (!$evaluation['lines']) {
        return [
            'ok'       => false,
            'reason'   => t('err.no_line'),
            'best'     => $evaluation['best'],
            'total'    => $grid,
        ];
    }

    $pdo = db();
    $insert = $pdo->prepare('INSERT OR IGNORE INTO wins (game_id, player_id, round, line_type, line_index, created_at)
                             VALUES (?, ?, ?, ?, ?, ?)');
    $new = 0;
    foreach ($evaluation['lines'] as $line) {
        $insert->execute([
            (int) $game['id'],
            (int) $player['id'],
            (int) $game['round'],
            $line['type'],
            $line['index'],
            now(),
        ]);
        $new += $insert->rowCount();
    }

    $pdo->prepare('UPDATE players SET wins = (SELECT COUNT(*) FROM wins WHERE player_id = ?) WHERE id = ?')
        ->execute([(int) $player['id'], (int) $player['id']]);

    return [
        'ok'    => true,
        'new'   => $new,
        'lines' => $evaluation['lines'],
        'total' => $grid,
    ];
}

// ------------------------------------------------------------------ Host

function host_action(array $game, string $action): array
{
    $pdo = db();
    $gameId = (int) $game['id'];

    if ($action === 'start') {
        if ($game['status'] !== 'lobby') {
            return ['ok' => false, 'reason' => t('err.already_started')];
        }
        $pdo->prepare("UPDATE games SET status = 'running', started_at = ? WHERE id = ?")
            ->execute([now(), $gameId]);
        return ['ok' => true];
    }

    if ($action === 'next') {
        if ($game['status'] !== 'running') {
            return ['ok' => false, 'reason' => t('err.not_running_round')];
        }
        $termIds = [];
        foreach (game_terms($gameId) as $term) {
            $termIds[] = (int) $term['id'];
        }

        $players = $pdo->prepare('SELECT id FROM players WHERE game_id = ?');
        $players->execute([$gameId]);
        $update = $pdo->prepare('UPDATE players SET card = ? WHERE id = ?');
        $clear = $pdo->prepare('DELETE FROM marks WHERE player_id = ?');

        $pdo->beginTransaction();
        try {
            foreach ($players->fetchAll() as $row) {
                $card = build_card($termIds, (int) $game['grid'], (bool) $game['free_center']);
                $update->execute([json_encode($card), (int) $row['id']]);
                $clear->execute([(int) $row['id']]);
            }
            $pdo->prepare('UPDATE games SET round = round + 1 WHERE id = ?')->execute([$gameId]);
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
        return ['ok' => true];
    }

    if ($action === 'finish') {
        $pdo->prepare("UPDATE games SET status = 'finished', finished_at = ? WHERE id = ?")
            ->execute([now(), $gameId]);
        return ['ok' => true];
    }

    return ['ok' => false, 'reason' => t('err.unknown_action')];
}

function game_wins(int $gameId, ?int $round = null): array
{
    $sql = 'SELECT w.round, w.line_type, w.line_index, w.created_at, p.name
            FROM wins w JOIN players p ON p.id = w.player_id
            WHERE w.game_id = ?';
    $params = [$gameId];
    if ($round !== null) {
        $sql .= ' AND w.round = ?';
        $params[] = $round;
    }
    $sql .= ' ORDER BY w.created_at, w.id';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    $out = [];
    foreach ($stmt->fetchAll() as $row) {
        $out[] = [
            'name'   => $row['name'],
            'round'  => (int) $row['round'],
            'type'   => $row['line_type'],
            'index'  => (int) $row['line_index'],
            'at'     => $row['created_at'],
            'time'   => substr($row['created_at'], 11, 5),
        ];
    }
    return $out;
}

function players_progress(array $game): array
{
    $pdo = db();
    $gameId = (int) $game['id'];
    $grid = (int) $game['grid'];

    $players = $pdo->prepare('SELECT id, name, card, wins, created_at FROM players WHERE game_id = ? ORDER BY created_at, id');
    $players->execute([$gameId]);
    $rows = $players->fetchAll();

    $marksByPlayer = [];
    $marks = $pdo->prepare('SELECT m.player_id, m.cell FROM marks m JOIN players p ON p.id = m.player_id WHERE p.game_id = ?');
    $marks->execute([$gameId]);
    foreach ($marks->fetchAll() as $row) {
        $marksByPlayer[(int) $row['player_id']][] = (int) $row['cell'];
    }

    $out = [];
    foreach ($rows as $row) {
        $playerId = (int) $row['id'];
        $playerMarks = $marksByPlayer[$playerId] ?? [];
        $evaluation = evaluate_card(player_card($row), $playerMarks, $grid);

        $out[] = [
            'id'     => $playerId,
            'name'   => $row['name'],
            'marks'  => count($playerMarks),
            'best'   => $evaluation['best'],
            'total'  => $grid,
            'lines'  => count($evaluation['lines']),
            'wins'   => (int) $row['wins'],
            'joined' => substr($row['created_at'], 11, 5),
        ];
    }
    return $out;
}

// ---------------------------------------------------------------- Status

function status_payload(array $game, ?array $player = null, bool $admin = false): array
{
    $progress = players_progress($game);

    $out = [
        'game' => [
            'code'       => $game['code'],
            'title'      => $game['title'],
            'status'     => $game['status'],
            'round'      => (int) $game['round'],
            'grid'       => (int) $game['grid'],
            'freeCenter' => (bool) $game['free_center'],
            'players'    => count($progress),
        ],
        'terms' => terms_map((int) $game['id']),
        'wins'  => game_wins((int) $game['id'], (int) $game['round']),
        'time'  => now(),
    ];

    if ($player !== null) {
        $marks = marks_of((int) $player['id']);
        $card = player_card($player);
        $evaluation = evaluate_card($card, $marks, (int) $game['grid']);
        $out['me'] = [
            'id'    => (int) $player['id'],
            'name'  => $player['name'],
            'card'  => $card,
            'marks' => $marks,
            'best'  => $evaluation['best'],
            'total' => (int) $game['grid'],
            'wins'  => (int) $player['wins'],
        ];
    }

    if ($admin) {
        $out['players'] = $progress;
    }

    return $out;
}
