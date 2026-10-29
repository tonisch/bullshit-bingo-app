<?php
/** @var array $game */
/** @var array $progress */
/** @var array $wins */

$token = h($game['admin_token']);
$appConfig = [
    'page'  => 'host',
    'code'  => $game['code'],
    'admin' => $game['admin_token'],
    'poll'  => (int) cfg('poll_ms', 2500),
];
$statusLabels = ['lobby' => 'Warteraum', 'running' => 'läuft', 'finished' => 'beendet'];
?>

<section class="host">
  <header class="host-head">
    <div>
      <h1><?= h($game['title']) ?></h1>
      <div class="meta">
        <span class="badge badge-<?= h($game['status']) ?>" id="statusBadge">
          <?= h($statusLabels[$game['status']] ?? $game['status']) ?>
        </span>
        <span>Runde <b id="roundNum"><?= (int) $game['round'] ?></b></span>
        <span>Code <b class="code" id="gameCode"><?= h($game['code']) ?></b></span>
        <span><b id="playerCount"><?= count($progress) ?></b> Spieler:innen</span>
      </div>
    </div>
    <div class="qr-box">
      <div id="qrCode" class="qr" aria-hidden="true"></div>
      <div class="qr-url" id="joinUrl"></div>
    </div>
  </header>

  <div class="host-actions">
    <form method="post" action="/host/<?= $token ?>/aktion">
      <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
      <input type="hidden" name="action" value="start">
      <button class="btn btn-primary" type="submit">Spiel starten</button>
    </form>
    <form method="post" action="/host/<?= $token ?>/aktion">
      <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
      <input type="hidden" name="action" value="next">
      <button class="btn" type="submit">Neue Runde (Karten neu mischen)</button>
    </form>
    <form method="post" action="/host/<?= $token ?>/aktion"
          onsubmit="return confirm('Spiel beenden? Niemand kann danach noch mitspielen.');">
      <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
      <input type="hidden" name="action" value="finish">
      <button class="btn btn-ghost" type="submit">Beenden</button>
    </form>
    <a class="btn btn-ghost" href="/host/<?= $token ?>">Aktualisieren</a>
  </div>

  <div class="host-grid">
    <section class="panel">
      <h2>Spieler</h2>
      <table class="players">
        <thead>
          <tr><th>#</th><th>Name</th><th>Markiert</th><th>Beste Reihe</th><th>Bingos</th></tr>
        </thead>
        <tbody id="playersBody">
        <?php foreach ($progress as $i => $p): ?>
          <tr>
            <td class="muted"><?= $i + 1 ?></td>
            <td><?= h($p['name']) ?></td>
            <td><?= (int) $p['marks'] ?></td>
            <td>
              <span class="bar"><span style="width: <?= (int) round($p['best'] / max(1, $p['total']) * 100) ?>%"></span></span>
              <?= (int) $p['best'] ?>/<?= (int) $p['total'] ?>
            </td>
            <td><?= $p['wins'] > 0 ? '<span class="pill pill-win">' . (int) $p['wins'] . '</span>' : '–' ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <p class="muted" id="emptyHint" <?= $progress ? 'hidden' : '' ?>>Noch niemand dabei. Code oder QR teilen.</p>
    </section>

    <section class="panel">
      <h2>Bingos <span class="muted">(diese Runde)</span></h2>
      <ol class="wins" id="winsList">
        <?php foreach ($wins as $w): ?>
          <?php if ((int) $w['round'] !== (int) $game['round']) { continue; } ?>
          <li><b><?= h($w['name']) ?></b> <span class="muted"><?= h($w['time']) ?> · <?= h($w['type']) ?> <?= (int) $w['index'] + 1 ?></span></li>
        <?php endforeach; ?>
      </ol>
      <p class="muted" id="noWinsHint">Noch kein Bingo.</p>
    </section>
  </div>

  <div class="flash-overlay" id="winFlash" hidden>
    <div class="flash-inner">
      <span class="flash-kicker">BINGO</span>
      <strong id="winFlashName"></strong>
    </div>
  </div>
</section>
