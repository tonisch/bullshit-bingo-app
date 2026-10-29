# Bullshit Bingo

Party-Bingo mit eigenen Begriffen: Host legt ein Spiel an, verteilt Code oder QR, alle
spielen auf dem Handy. Wer zuerst eine Reihe vollhat, drückt BINGO – der Server prüft.

Läuft auf dem All-Inkl-Webspace unter <https://bingo.tonisch.de>.
Kein Framework, kein Build, keine externen Requests. PHP 8 + SQLite + Vanilla JS.

## Wie es funktioniert

1. **Spiel anlegen** (`/neu`): Titel, Begriffe (eine pro Zeile), Raster 3×3 / 4×4 / 5×5,
   optional freies Mittelfeld. Ergebnis: Spielcode + Host-Link mit Admin-Token.
2. **Beitreten** (`/beitreten`): Code + Name. Jede:r bekommt eine eigene, gemischte Karte
   (bei mehr Begriffen als Feldern ist jede Karte anders).
3. **Spielen** (`/karte`): Feld antippen, wenn der Begriff gefallen ist.
4. **BINGO**: Server prüft Reihen/Spalten/Diagonalen, trägt den Sieg mit Zeitstempel ein.
5. **Host-Ansicht** (`/host/<token>`): Beamer-taugliche Liste mit Fortschritt pro Person,
   Bingos der laufenden Runde, Vollbild-Flash bei Sieg. Buttons: Start, Neue Runde, Beenden.

„Neue Runde" mischt alle Karten neu und löscht die Markierungen; Siege bleiben in der
Historie erhalten.

## Sprachen

Die Oberfläche ist zweisprachig (Deutsch/Englisch). Umgeschaltet wird über die Links
`DE` / `EN` oben rechts (`/lang/de`, `/lang/en`), gespeichert in einem Cookie für ein Jahr.
Ohne Cookie entscheidet `Accept-Language`, Default ist Deutsch. Alle Texte stehen in
`app/i18n.php`; das Frontend bekommt die Strings als `window.APP.i18n` und greift über
`T('key')` darauf zu.

## Wortlisten

`app/wordlists.php` enthält vier englische Presets, die auf `/neu` als Chips erscheinen:

| ID | Liste | Begriffe | Inhalt |
|----|-------|----------|--------|
| `automotive` | Automotive management | 40 | ASPICE, ISO 26262, SOP, steering committee, change board |
| `meeting` | Meeting bingo | 30 | Klassiker aus jedem Jour fixe |
| `quality` | Quality & suppliers | 28 | 8D report, PPAP, field return, Cpk |
| `tech` | Tech (light) | 24 | Over-the-air, DTC, AUTOSAR, test bench |

Ein Klick ergänzt die Begriffe im Textfeld, Dubletten werden übersprungen; mehrere
Listen lassen sich kombinieren. Beschriftungen kommen aus der i18n-Schicht, die Begriffe
selbst sind sprachneutral englisch gehalten.

## Gestaltung

Typografie und Palette folgen Alfred (dort ADR-0005): **Fraunces Variable** für
Schlagzeilen, **Inter Tight Variable** für UI-Elemente, **Inter Variable** für Fließtext.
Alle drei Familien liegen selbst gehostet in `public/assets/fonts/` – keine Requests zu
einem Font-CDN. Akzentfarbe `#005a9d`, Linien in Slate `#556677`, heller Grund,
Signatur-Band oben (1 px Slate / 2 px Blau / 1 px Slate).

## Realtime

Kein WebSocket – Shared Hosting kann keine Daemons halten. Der Client pollt
`/api/status` alle 2,5 s (`poll_ms` in `app/bootstrap.php`). Das trägt bei 15–30
Spieler:innen problemlos; bei deutlich mehr Teilnehmenden das Intervall erhöhen.

## Aufbau

```
app/
  bootstrap.php   Konfiguration, Session, Fehlerbehandlung
  helpers.php     Ausgabe-Escaping, CSRF, Zufallscodes, View-Rendering
  i18n.php        Wörterbuch DE/EN, Sprachwahl, t()
  db.php          PDO-SQLite + Schema (CREATE TABLE IF NOT EXISTS)
  game.php        Spiellogik: anlegen, beitreten, markieren, Bingo prüfen, Runden
  wordlists.php   Preset-Wortlisten für /neu
  views/          Layout + Seiten
public/           Document Root (index.php = Front-Controller, assets/)
  assets/fonts/   Fraunces, Inter Tight, Inter (woff2, self-hosted)
bin/
  test.php        End-to-End-Test der Spiellogik ohne Webserver
  console.php     Spiele auflisten / löschen / aufräumen
var/bingo.sqlite  Datenbank (außerhalb des Document Root)
```

Assets werden mit `?v=<filemtime>` ausgeliefert – nach einem Deployment sehen Browser
automatisch die neue Version, ohne Hard-Reload.

## Datenmodell

| Tabelle   | Inhalt |
|-----------|--------|
| `games`   | Spielcode, Admin-Token, Titel, Raster, Status (`lobby`/`running`/`finished`), Runde |
| `terms`   | Begriffe eines Spiels |
| `players` | Name, Karte als JSON-Array von Term-IDs (`0` = freies Feld) |
| `marks`   | markierte Felder pro Spieler:in (Zellindex) |
| `wins`    | gewertete Linien (Typ row/col/diag + Index), eindeutig pro Runde |

## Sicherheit

- Admin-Zugriff nur über 48-stelliges Token in der URL (nicht ratbar, nicht im Code).
- CSRF-Token für alle Formulare und JSON-POSTs (`X-CSRF`-Header).
- Beitritts-Limit: max. 120 Spieler:innen pro Spiel, 25 Beitritte pro IP / 10 Minuten.
- Bingo wird serverseitig gegen die eigene Karte geprüft; doppelte Linien zählen einmal.
- SQLite-Datei liegt in `var/`, außerhalb von `public/`.

## Entwicklung

```bash
php bin/test.php                  # Spiellogik durchtesten (eigene Wegwerf-DB)
php -S localhost:8000 -t public   # lokal spielen
```

Für lokale Tests eine andere DB nutzen: `BINGO_DB=/tmp/bingo.sqlite php -S …`

## Deployment

```bash
rsync -az --exclude 'var/' ./ tonisch.de:'~/www/htdocs/w01a1fd3/bingo.tonisch.de/'
```

Document Root der Domain ist `bingo.tonisch.de/public`. PHP läuft als Account-User,
`var/` braucht also keine Sonderrechte.

## Verwaltung

```bash
php bin/console.php list
php bin/console.php delete ABC123
php bin/console.php cleanup 30
```

Backup: einfach `var/bingo.sqlite` kopieren (im WAL-Modus zusätzlich `-wal`/`-shm`
oder vorher `PRAGMA wal_checkpoint(TRUNCATE)`).

## Lizenz

MIT, siehe `LICENSE`.
