<?php
declare(strict_types=1);

/**
 * Zweisprachige Oberfläche. Default ist Deutsch, umschaltbar über /lang/{de|en}
 * (Cookie, ein Jahr) oder per Accept-Language beim ersten Besuch.
 */
function i18n_dictionary(): array
{
    static $dict = null;
    if ($dict !== null) {
        return $dict;
    }

    $de = [
        // Startseite
        'home.title'          => 'Bullshit Bingo.',
        'home.subtitle'       => 'Aber mit Begriffen, die wirklich fallen.',
        'home.lead'           => 'Spiel anlegen, Begriffszettel reinkopieren, Code oder QR verteilen – jeder bekommt eine eigene Karte. Wer zuerst eine Reihe vollhat, drückt BINGO.',
        'home.cta.create'     => 'Spiel anlegen',
        'home.cta.join'       => 'Ich habe einen Code',
        'home.resume'         => 'Laufendes Spiel:',
        'home.resume.card'    => 'Karte öffnen',
        'home.resume.host'    => 'Host-Ansicht',
        'home.step1.title'    => 'Begriffe rein',
        'home.step1.text'     => 'Eine Liste, ein Begriff pro Zeile – Klassiker wie „Synergie" oder „wir müssen da mal drüber". Mindestens 24 für ein 5×5-Feld, mehr ist besser: dann ist jede Karte anders.',
        'home.step2.title'    => 'Code verteilen',
        'home.step2.text'     => 'Jeder tritt mit sechsstelligem Code oder QR bei und bekommt eine zufällig gemischte Karte aufs Handy.',
        'home.step3.title'    => 'Bingo',
        'home.step3.text'     => 'Gehörtes Feld antippen. Der Server prüft die Reihe und trägt den Sieger live in die Beamer-Ansicht ein.',

        // Spiel anlegen
        'create.title'        => 'Neues Spiel anlegen',
        'create.note'         => 'Du bekommst danach einen Host-Link – den nicht rausgeben, damit niemand die Runden steuert.',
        'create.title_label'  => 'Titel',
        'create.optional'     => '(optional)',
        'create.title_ph'     => 'z. B. Montags-All-Hands',
        'create.terms_label'  => 'Begriffe – einer pro Zeile',
        'create.terms_ph'     => "Synergieeffekte\nLass uns das mal parken\nIch sehe da keine Blocker\n…",
        'create.packs'        => 'Wortlisten',
        'create.clear'        => 'Leeren',
        'create.hint'         => 'Klick eine Liste an – ihre Begriffe werden eingefügt. Mehrere Listen lassen sich mischen.',
        'create.counter'      => '{count} Begriffe · {need} nötig',
        'create.grid'         => 'Raster',
        'create.grid5'        => '5 × 5 (24 Begriffe)',
        'create.grid4'        => '4 × 4 (15 Begriffe)',
        'create.grid3'        => '3 × 3 (8 Begriffe)',
        'create.free_center'  => 'Freies Mittelfeld',
        'create.submit'       => 'Spiel erzeugen',
        'create.pack_added'   => '{label}: {count} Begriffe ergänzt ({total} gesamt).',
        'create.pack_present' => '{label} war komplett schon drin.',
        'create.cleared'      => 'Liste geleert.',

        // Beitreten
        'join.title'          => 'Spiel beitreten',
        'join.code'           => 'Spielcode',
        'join.name'           => 'Dein Name',
        'join.name_ph'        => 'Anna',
        'join.submit'         => 'Karte holen',

        // Host
        'host.round'          => 'Runde',
        'host.code'           => 'Code',
        'host.players_count'  => 'Spieler:innen',
        'host.start'          => 'Spiel starten',
        'host.next'           => 'Neue Runde (Karten neu mischen)',
        'host.finish'         => 'Beenden',
        'host.refresh'        => 'Aktualisieren',
        'host.confirm_finish' => 'Spiel beenden? Niemand kann danach noch mitspielen.',
        'host.players'        => 'Spieler',
        'host.bingos'         => 'Bingos',
        'host.this_round'     => '(diese Runde)',
        'host.col_no'         => '#',
        'host.col_name'       => 'Name',
        'host.col_marks'      => 'Markiert',
        'host.col_best'       => 'Beste Reihe',
        'host.col_bingos'     => 'Bingos',
        'host.empty'          => 'Noch niemand dabei. Code oder QR teilen.',
        'host.no_wins'        => 'Noch kein Bingo.',
        'host.copy_link'      => 'Link kopieren',
        'host.link_copied'    => 'Link kopiert – verteilen!',
        'host.copy_failed'    => 'Kopieren blockiert – Link bitte manuell markieren.',
        'host.flash_kicker'   => 'Bullshit Bingo!',
        'host.win_flash'      => '{name} hat gewonnen!',

        // Status
        'status.lobby'        => 'Warteraum',
        'status.running'      => 'läuft',
        'status.finished'     => 'beendet',

        // Karte
        'card.hint_lobby'     => 'Warte auf den Start. Deine Karte siehst du schon – tippen geht gleich los.',
        'card.hint_running'   => 'Tippe ein Feld an, sobald der Begriff gefallen ist.',
        'card.hint_finished'  => 'Das Spiel ist beendet. Danke fürs Mitspielen!',
        'card.best'           => 'Beste Reihe:',
        'card.marked'         => 'markiert',
        'card.auto_bingo'     => 'Vollständige Reihen werden automatisch als Bullshit Bingo gewertet – kein Knopf nötig.',
        'card.free'           => 'FREI',

        // Fehler
        'err.not_found'       => 'Nicht gefunden',
        'err.not_found_msg'   => 'Diese Seite gibt es nicht.',
        'err.no_terms'        => 'Das ging nicht',
        'err.server'          => 'Serverfehler',
        'err.server_msg'      => 'Da ist etwas schiefgelaufen. Bitte später erneut versuchen.',
        'err.bad_token'       => 'Ungültiges Formular-Token. Bitte Seite neu laden.',
        'err.game_not_found'  => 'Diesen Spielcode gibt es nicht.',
        'err.host_link'       => 'Dieser Host-Link ist ungültig.',
        'err.action_failed'   => 'Aktion fehlgeschlagen.',
        'err.name_required'   => 'Bitte einen Namen angeben.',
        'err.game_finished'   => 'Dieses Spiel ist bereits beendet.',
        'err.game_full'       => 'Dieses Spiel ist voll.',
        'err.too_many_joins'  => 'Zu viele Beitritte in kurzer Zeit. Bitte später erneut versuchen.',
        'err.pool_small'      => 'Der Begriffspool ist zu klein für dieses Spielfeld.',
        'err.too_few_terms'   => 'Zu wenige Begriffe: {have} vorhanden, {need} nötig für {grid}×{grid}{free}.',
        'err.free_suffix'     => ' mit freiem Mittelfeld',
        'err.cell_locked'     => 'Dieses Feld kann nicht markiert werden.',
        'err.not_running'     => 'Das Spiel läuft gerade nicht.',
        'err.not_signed_in'   => 'Nicht für dieses Spiel angemeldet',
        'err.no_free_code'    => 'Kein freier Spielcode gefunden.',
        'err.already_started' => 'Das Spiel wurde bereits gestartet.',
        'err.not_running_round' => 'Nur in einer laufenden Runde möglich.',
        'err.unknown_action'  => 'Unbekannte Aktion.',
        'err.already_counted' => 'Bingo war schon gewertet.',
        'err.no_line'         => 'Noch keine vollständige Reihe – weiter sammeln!',

        // JS
        'js.not_running'      => 'Das Spiel läuft noch nicht.',
        'js.new_round'        => 'Neue Runde – neue Karte!',
        'js.bingo_ok'         => 'Bullshit Bingo! Der Host sieht dich jetzt.',
        'js.bingo_other'      => '{name} hat Bullshit Bingo!',
        'js.session_expired'  => 'Sitzung abgelaufen, bitte neu laden.',
        'js.error'            => 'Da ist etwas schiefgelaufen.',

        // Wortlisten
        'pack.automotive'     => 'Automotive Management',
        'pack.meeting'        => 'Meeting-Bingo',
        'pack.quality'        => 'Qualität & Zulieferer',
        'pack.tech'           => 'Technik (light)',
    ];

    $en = [
        'home.title'          => 'Bullshit Bingo.',
        'home.subtitle'       => 'But with the phrases that actually get said.',
        'home.lead'           => 'Create a game, paste your term list, share the code or QR – everyone gets their own card. First one to fill a line hits BINGO.',
        'home.cta.create'     => 'Create a game',
        'home.cta.join'       => 'I have a code',
        'home.resume'         => 'Active game:',
        'home.resume.card'    => 'Open card',
        'home.resume.host'    => 'Host view',
        'home.step1.title'    => 'Feed the terms',
        'home.step1.text'     => 'One list, one term per line – classics like "synergy" or "let\'s park that". At least 24 for a 5×5 board; more is better, then every card differs.',
        'home.step2.title'    => 'Share the code',
        'home.step2.text'     => 'People join with a six-character code or QR and get a randomly shuffled card on their phone.',
        'home.step3.title'    => 'Bingo',
        'home.step3.text'     => 'Tap a cell when you hear the term. The server validates the line and puts the winner on the big screen.',

        'create.title'        => 'Create a new game',
        'create.note'         => 'You get a host link afterwards – keep it to yourself so nobody else controls the rounds.',
        'create.title_label'  => 'Title',
        'create.optional'     => '(optional)',
        'create.title_ph'     => 'e.g. Monday all-hands',
        'create.terms_label'  => 'Terms – one per line',
        'create.terms_ph'     => "Synergies\nLet's park that\nI don't see any blockers\n…",
        'create.packs'        => 'Word lists',
        'create.clear'        => 'Clear',
        'create.hint'         => 'Click a list to add its terms. You can mix several lists.',
        'create.counter'      => '{count} terms · {need} needed',
        'create.grid'         => 'Grid',
        'create.grid5'        => '5 × 5 (24 terms)',
        'create.grid4'        => '4 × 4 (15 terms)',
        'create.grid3'        => '3 × 3 (8 terms)',
        'create.free_center'  => 'Free centre',
        'create.submit'       => 'Create game',
        'create.pack_added'   => '{label}: {count} terms added ({total} total).',
        'create.pack_present' => '{label} was already complete.',
        'create.cleared'      => 'List cleared.',

        'join.title'          => 'Join a game',
        'join.code'           => 'Game code',
        'join.name'           => 'Your name',
        'join.name_ph'        => 'Anna',
        'join.submit'         => 'Get my card',

        'host.round'          => 'Round',
        'host.code'           => 'Code',
        'host.players_count'  => 'players',
        'host.start'          => 'Start game',
        'host.next'           => 'New round (reshuffle cards)',
        'host.finish'         => 'Finish',
        'host.refresh'        => 'Refresh',
        'host.confirm_finish' => 'Finish the game? Nobody can join afterwards.',
        'host.players'        => 'Players',
        'host.bingos'         => 'Bingos',
        'host.this_round'     => '(this round)',
        'host.col_no'         => '#',
        'host.col_name'       => 'Name',
        'host.col_marks'      => 'Marked',
        'host.col_best'       => 'Best line',
        'host.col_bingos'     => 'Bingos',
        'host.empty'          => 'Nobody here yet. Share the code or QR.',
        'host.no_wins'        => 'No bingo yet.',
        'host.copy_link'      => 'Copy link',
        'host.link_copied'    => 'Link copied – spread it!',
        'host.copy_failed'    => 'Copy blocked – please select the link manually.',
        'host.flash_kicker'   => 'Bullshit Bingo!',
        'host.win_flash'      => '{name} is the winner!',

        'status.lobby'        => 'lobby',
        'status.running'      => 'running',
        'status.finished'     => 'finished',

        'card.hint_lobby'     => 'Waiting for the start. Your card is already here – tapping unlocks soon.',
        'card.hint_running'   => 'Tap a cell as soon as you hear the term.',
        'card.hint_finished'  => 'The game is over. Thanks for playing!',
        'card.best'           => 'Best line:',
        'card.marked'         => 'marked',
        'card.auto_bingo'     => 'Completed lines are scored automatically as Bullshit Bingo – no button needed.',
        'card.free'           => 'FREE',

        'err.not_found'       => 'Not found',
        'err.not_found_msg'   => "This page doesn't exist.",
        'err.no_terms'        => 'That did not work',
        'err.server'          => 'Server error',
        'err.server_msg'      => 'Something went wrong. Please try again later.',
        'err.bad_token'       => 'Invalid form token. Please reload the page.',
        'err.game_not_found'  => 'No game with that code.',
        'err.host_link'       => 'This host link is invalid.',
        'err.action_failed'   => 'Action failed.',
        'err.name_required'   => 'Please enter a name.',
        'err.game_finished'   => 'This game has already finished.',
        'err.game_full'       => 'This game is full.',
        'err.too_many_joins'  => 'Too many join attempts in a short time. Please try again later.',
        'err.pool_small'      => 'The term pool is too small for this board.',
        'err.too_few_terms'   => 'Not enough terms: {have} given, {need} needed for {grid}×{grid}{free}.',
        'err.free_suffix'     => ' with a free centre',
        'err.cell_locked'     => "This cell can't be marked.",
        'err.not_running'     => "The game isn't running right now.",
        'err.not_signed_in'   => 'Not signed in for this game',
        'err.no_free_code'    => 'No free game code available.',
        'err.already_started' => 'The game has already started.',
        'err.not_running_round' => 'Only possible in a running round.',
        'err.unknown_action'  => 'Unknown action.',
        'err.already_counted' => 'Bingo was already counted.',
        'err.no_line'         => 'No complete line yet – keep collecting!',

        'js.not_running'      => "The game hasn't started yet.",
        'js.new_round'        => 'New round – new card!',
        'js.bingo_ok'         => 'Bullshit Bingo! The host sees you now.',
        'js.bingo_other'      => '{name} got Bullshit Bingo!',
        'js.session_expired'  => 'Session expired, please reload.',
        'js.error'            => 'Something went wrong.',

        'pack.automotive'     => 'Automotive management',
        'pack.meeting'        => 'Meeting bingo',
        'pack.quality'        => 'Quality & suppliers',
        'pack.tech'           => 'Tech (light)',
    ];

    return $dict = ['de' => $de, 'en' => $en];
}

function lang_current(): string
{
    static $lang = null;
    if ($lang !== null) {
        return $lang;
    }

    $cookie = $_COOKIE['bb_lang'] ?? '';
    if ($cookie === 'de' || $cookie === 'en') {
        return $lang = $cookie;
    }

    $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''));
    if ($accept !== '' && strpos($accept, 'en') === 0) {
        return $lang = 'en';
    }

    return $lang = 'de';
}

function lang_set(string $lang): void
{
    if ($lang !== 'de' && $lang !== 'en') {
        return;
    }
    $GLOBALS['BB_LANG'] = $lang;
    if (PHP_SAPI !== 'cli' && !headers_sent()) {
        setcookie('bb_lang', $lang, [
            'expires'  => time() + 365 * 86400,
            'path'     => '/',
            'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'httponly' => false,
            'samesite' => 'Lax',
        ]);
    }
}

function t(string $key, array $vars = []): string
{
    $dict = i18n_dictionary();
    $lang = lang_current();
    $text = $dict[$lang][$key] ?? $dict['de'][$key] ?? $key;

    foreach ($vars as $name => $value) {
        $text = str_replace('{' . $name . '}', (string) $value, $text);
    }

    return $text;
}

/**
 * Nur die Strings, die das Frontend braucht.
 */
function i18n_js(): array
{
    $dict = i18n_dictionary();
    return $dict[lang_current()];
}
