<?php
/**
 * Kleines Verwaltungs-CLI für die Spieldatenbank.
 *
 *   php bin/console.php list
 *   php bin/console.php delete ABC123
 *   php bin/console.php cleanup 3      (löscht Spiele, die älter als 3 Tage sind)
 */
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

$cmd = $argv[1] ?? 'list';
$pdo = db();

if ($cmd === 'list') {
    $rows = $pdo->query('SELECT g.code, g.title, g.status, g.round, g.created_at,
                                (SELECT COUNT(*) FROM players p WHERE p.game_id = g.id) AS players,
                                (SELECT COUNT(*) FROM wins w WHERE w.game_id = g.id) AS wins
                         FROM games g ORDER BY g.created_at DESC')->fetchAll();

    if (!$rows) {
        echo "Keine Spiele vorhanden.\n";
        exit(0);
    }

    printf("%-8s %-28s %-9s %6s %8s %6s  %s\n", 'Code', 'Titel', 'Status', 'Runde', 'Spieler', 'Bingos', 'Angelegt');
    foreach ($rows as $row) {
        printf(
            "%-8s %-28s %-9s %6d %8d %6d  %s\n",
            $row['code'],
            mb_substr($row['title'], 0, 28),
            $row['status'],
            (int) $row['round'],
            (int) $row['players'],
            (int) $row['wins'],
            $row['created_at']
        );
    }
    exit(0);
}

if ($cmd === 'delete') {
    $code = strtoupper($argv[2] ?? '');
    $game = game_by_code($code);
    if (!$game) {
        fwrite(STDERR, "Kein Spiel mit Code {$code}.\n");
        exit(1);
    }
    $pdo->prepare('DELETE FROM games WHERE id = ?')->execute([(int) $game['id']]);
    echo "Spiel {$code} gelöscht.\n";
    exit(0);
}

if ($cmd === 'cleanup') {
    $days = max(1, (int) ($argv[2] ?? 3));
    $deleted = games_cleanup($days);
    printf("%d Spiele älter als %d Tage gelöscht.\n", $deleted, $days);
    exit(0);
}

fwrite(STDERR, "Unbekannter Befehl: {$cmd}\nBekannt: list, delete <CODE>, cleanup <TAGE>\n");
exit(1);
