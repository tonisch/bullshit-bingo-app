<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    if (!is_dir(VAR_DIR)) {
        @mkdir(VAR_DIR, 0775, true);
    }

    $pdo = new PDO('sqlite:' . DB_FILE, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT            => 5,
    ]);

    try {
        $pdo->exec('PRAGMA journal_mode = WAL');
    } catch (Throwable $e) {
        // Shared Storage ohne Shared-Memory: dann klassisches Rollback-Journal.
    }
    $pdo->exec('PRAGMA busy_timeout = 5000');
    $pdo->exec('PRAGMA foreign_keys = ON');

    return $pdo;
}

function db_init(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    db()->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS games (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    code        TEXT    NOT NULL UNIQUE,
    admin_token TEXT    NOT NULL UNIQUE,
    title       TEXT    NOT NULL,
    grid        INTEGER NOT NULL DEFAULT 5,
    free_center INTEGER NOT NULL DEFAULT 1,
    status      TEXT    NOT NULL DEFAULT 'lobby',
    round       INTEGER NOT NULL DEFAULT 1,
    created_at  TEXT    NOT NULL,
    started_at  TEXT,
    finished_at TEXT
);

CREATE TABLE IF NOT EXISTS terms (
    id       INTEGER PRIMARY KEY AUTOINCREMENT,
    game_id  INTEGER NOT NULL REFERENCES games(id) ON DELETE CASCADE,
    text     TEXT    NOT NULL,
    position INTEGER NOT NULL DEFAULT 0
);

CREATE INDEX IF NOT EXISTS idx_terms_game ON terms(game_id);

CREATE TABLE IF NOT EXISTS players (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    game_id    INTEGER NOT NULL REFERENCES games(id) ON DELETE CASCADE,
    token      TEXT    NOT NULL UNIQUE,
    name       TEXT    NOT NULL,
    card       TEXT    NOT NULL,
    wins       INTEGER NOT NULL DEFAULT 0,
    created_at TEXT    NOT NULL,
    last_seen  TEXT
);

CREATE INDEX IF NOT EXISTS idx_players_game ON players(game_id);

CREATE TABLE IF NOT EXISTS marks (
    player_id  INTEGER NOT NULL REFERENCES players(id) ON DELETE CASCADE,
    cell       INTEGER NOT NULL,
    created_at TEXT    NOT NULL,
    PRIMARY KEY (player_id, cell)
);

CREATE TABLE IF NOT EXISTS wins (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    game_id    INTEGER NOT NULL REFERENCES games(id) ON DELETE CASCADE,
    player_id  INTEGER NOT NULL REFERENCES players(id) ON DELETE CASCADE,
    round      INTEGER NOT NULL,
    line_type  TEXT    NOT NULL,
    line_index INTEGER NOT NULL,
    created_at TEXT    NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_wins_game ON wins(game_id, round);

CREATE UNIQUE INDEX IF NOT EXISTS uniq_wins ON wins(player_id, round, line_type, line_index);

CREATE TABLE IF NOT EXISTS joins (
    ip         TEXT    NOT NULL,
    game_id    INTEGER NOT NULL,
    created_at TEXT    NOT NULL
);
SQL
    );
}
