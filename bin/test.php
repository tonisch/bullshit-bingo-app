<?php
/**
 * End-to-End-Test ohne Webserver: spielt eine komplette Runde durch.
 *
 *   php bin/test.php
 */
declare(strict_types=1);

putenv('BINGO_DB=' . sys_get_temp_dir() . '/bingo-test-' . getmypid() . '.sqlite');
require dirname(__DIR__) . '/app/bootstrap.php';

$failures = 0;

function check(string $label, bool $condition, string $detail = ''): void
{
    global $failures;
    if ($condition) {
        echo "  ok    {$label}\n";
        return;
    }
    $failures++;
    echo "  FAIL  {$label}" . ($detail !== '' ? " ({$detail})" : '') . "\n";
}

$terms = "Synergie\nAgile Transformation\nLass uns das parken\nWir müssen da mal drüber\nQuick Win\n"
    . "Ende-zu-Ende\nBest Practice\nLow hanging fruits\nIch sehe da keine Blocker\nSingle Source of Truth\n"
    . "Kundennutzen\nSkalierung\nOnboarding\nRoadmap\nTouchpoint\nJourney\nStakeholder\nAlignment\n"
    . "Deep Dive\nNorth Star\nKPI\nRetrospektive\nSilo\nMehrwert\nOwnership\nEnablement\n";

echo "Spiel anlegen\n";
$created = game_create('Test-Bingo', $terms, 5, true);
check('Spielcode hat 6 Zeichen', strlen($created['code']) === 6, $created['code']);
check('26 Begriffe gespeichert', $created['terms'] === 26, (string) $created['terms']);

$game = game_by_code($created['code']);
check('Spiel per Code auffindbar', is_array($game));
check('Startstatus ist lobby', $game['status'] === 'lobby', $game['status']);
check('Admin-Token funktioniert', (game_by_token($created['admin_token'])['id'] ?? 0) === $game['id']);

echo "Zu wenige Begriffe\n";
try {
    game_create('Zu klein', "eins\nzwei\ndrei", 5, true);
    check('UserError bei zu wenig Begriffen', false);
} catch (UserError $e) {
    check('UserError bei zu wenig Begriffen', true);
}

echo "Spieler treten bei\n";
$players = [];
foreach (['Anna', 'Bert', 'Clara'] as $name) {
    $players[$name] = player_join($game, $name, '127.0.0.1');
    check("{$name} hat 25 Felder", count(player_card($players[$name])) === 25);
    check("{$name} hat freies Mittelfeld", player_card($players[$name])[12] === 0);
}

$cards = array_map('player_card', $players);
$uniqueCards = count(array_unique(array_map('json_encode', $cards)));
check('Karten sind unterschiedlich', $uniqueCards === 3, $uniqueCards . ' verschieden');
check('Keine Doppelten Begriffe auf einer Karte', count(array_unique(array_filter($cards['Anna']))) === 24);

echo "Markieren im Warteraum\n";
$blocked = false;
try {
    set_mark($game, $players['Anna'], 0, true);
} catch (UserError $e) {
    $blocked = true;
}
check('Markieren vor Start blockiert', $blocked);

echo "Spiel starten\n";
$result = host_action($game, 'start');
check('Start akzeptiert', $result['ok'] === true);
$game = game_by_code($created['code']);
check('Status ist running', $game['status'] === 'running', $game['status']);

echo "Bingo ohne vollständige Reihe\n";
$marks = [];
foreach ([0, 1, 2, 3] as $cell) {
    set_mark($game, $players['Anna'], $cell, true);
    $marks[] = $cell;
}
$early = record_bingo($game, $players['Anna']);
check('Unvollständige Reihe wird abgelehnt', $early['ok'] === false, json_encode($early));
check('Beste Reihe ist 4/5', $early['best'] === 4, (string) $early['best']);

echo "Reihe vollmachen\n";
set_mark($game, $players['Anna'], 4, true);
$bingo = record_bingo($game, $players['Anna']);
check('Bingo akzeptiert', $bingo['ok'] === true, json_encode($bingo));
check('Genau eine neue Linie', $bingo['new'] === 1, (string) $bingo['new']);

$again = record_bingo($game, $players['Anna']);
check('Doppeltes Bingo zählt nicht neu', $again['ok'] === true && $again['new'] === 0);

echo "Zweiter Sieger\n";
foreach ([5, 6, 7, 8, 9] as $cell) {
    set_mark($game, $players['Bert'], $cell, true);
}
$second = record_bingo($game, $players['Bert']);
check('Bert gewinnt mit zweiter Reihe', $second['ok'] === true && $second['new'] === 1);

$wins = game_wins((int) $game['id']);
check('Zwei Siege protokolliert', count($wins) === 2, (string) count($wins));

echo "Neue Runde\n";
$next = host_action($game, 'next');
check('Runde 2 gestartet', $next['ok'] === true);
$game = game_by_code($created['code']);
check('Rundenzähler ist 2', (int) $game['round'] === 2, (string) $game['round']);

$bert = player_by_id((int) $players['Bert']['id']);
check('Karte wurde neu gemischt', json_encode(player_card($bert)) !== json_encode($cards['Bert']));
check('Markierungen wurden geleert', count(marks_of((int) $bert['id'])) === 0);
check('Siege bleiben erhalten', count(game_wins((int) $game['id'])) === 2);

echo "Anti-Cheat\n";
$blockedBad = false;
try {
    set_mark($game, $bert, 999, true);
} catch (UserError $e) {
    $blockedBad = true;
}
check('Feld außerhalb der Karte wird abgelehnt', $blockedBad);

$blockedFree = false;
try {
    set_mark($game, $bert, 12, true);
} catch (UserError $e) {
    $blockedFree = true;
}
check('Freies Mittelfeld ist nicht markierbar', $blockedFree);

echo "Status-Payload\n";
$status = status_payload($game, $bert, true);
check('Host sieht Spielerliste', isset($status['players']) && count($status['players']) === 3);
check('Spieler sieht eigene Karte', isset($status['me']['card']) && count($status['me']['card']) === 25);
check('Terme sind gemappt', count($status['terms']) === 26);

echo "Spiel beenden\n";
host_action($game, 'finish');
$game = game_by_code($created['code']);
check('Status ist finished', $game['status'] === 'finished', $game['status']);
check('Beitritt danach blockiert', (function () use ($game) {
    try {
        player_join($game, 'Zuspät', '127.0.0.1');
        return false;
    } catch (UserError $e) {
        return true;
    }
})());

echo "Wortlisten\n";
foreach (wordlist_packs() as $packId => $pack) {
    $unique = array_unique(array_map('mb_strtolower', $pack['terms']));
    check($packId . ' hat genug Begriffe', count($unique) >= 24, count($unique) . ' eindeutige');

    $tooLong = array_filter($pack['terms'], function ($term) {
        return mb_strlen($term) > 60;
    });
    check($packId . ' ohne überlange Begriffe', count($tooLong) === 0, implode(', ', $tooLong));

    $created = game_create('Pack ' . $packId, implode("\n", $pack['terms']), 5, true);
    check($packId . ' lässt sich als 5x5 anlegen', $created['terms'] === count($unique), (string) $created['terms']);
}

@unlink(getenv('BINGO_DB'));

echo "\n" . ($failures === 0 ? "Alle Tests bestanden.\n" : "{$failures} Test(s) fehlgeschlagen.\n");
exit($failures === 0 ? 0 : 1);
