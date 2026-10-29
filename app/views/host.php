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
?>

<section class="host">
  <header class="host-head">
    <div>
      <h1><?= h($game['title']) ?></h1>
      <div class="meta">
        <span class="badge badge-<?= h($game['status']) ?>" id="statusBadge">
          <?= h(t('status.' . $game['status'])) ?>
        </span>
        <span><?= h(t('host.round')) ?> <b id="roundNum"><?= (int) $game['round'] ?></b></span>
        <span><?= h(t('host.code')) ?> <b class="code" id="gameCode"><?= h($game['code']) ?></b></span>
        <span><b id="playerCount"><?= count($progress) ?></b> <?= h(t('host.players_count')) ?></span>
      </div>
    </div>
    <div class="qr-box">
      <div id="qrCode" class="qr" aria-hidden="true"></div>
      <a class="qr-url" id="joinUrl" href="/join/<?= h($game['code']) ?>"></a>
      <button type="button" class="btn btn-small" id="copyLink"><?= h(t('host.copy_link')) ?></button>
    </div>
  </header>

  <div class="host-actions">
    <form method="post" action="/host/<?= $token ?>/aktion">
      <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
      <input type="hidden" name="action" value="start">
      <button class="btn btn-primary" type="submit"><?= h(t('host.start')) ?></button>
    </form>
    <form method="post" action="/host/<?= $token ?>/aktion">
      <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
      <input type="hidden" name="action" value="next">
      <button class="btn" type="submit"><?= h(t('host.next')) ?></button>
    </form>
    <form method="post" action="/host/<?= $token ?>/aktion"
          onsubmit="return confirm(<?= h(json_encode(t('host.confirm_finish'), JSON_UNESCAPED_UNICODE)) ?>);">
      <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
      <input type="hidden" name="action" value="finish">
      <button class="btn btn-ghost" type="submit"><?= h(t('host.finish')) ?></button>
    </form>
    <a class="btn btn-ghost" href="/host/<?= $token ?>"><?= h(t('host.refresh')) ?></a>
  </div>

  <div class="host-grid">
    <section class="panel">
      <h2><?= h(t('host.players')) ?></h2>
      <table class="players">
        <thead>
          <tr>
            <th><?= h(t('host.col_no')) ?></th>
            <th><?= h(t('host.col_name')) ?></th>
            <th><?= h(t('host.col_marks')) ?></th>
            <th><?= h(t('host.col_best')) ?></th>
            <th><?= h(t('host.col_bingos')) ?></th>
          </tr>
        </thead>
        <tbody id="playersBody">
        <?php foreach ($progress as $i => $p): ?>
          <tr>
            <td class="muted"><?= $i + 1 ?></td>
            <td><?= h($p['name']) ?></td>
            <td class="mono-figures"><?= (int) $p['marks'] ?></td>
            <td>
              <span class="bar"><span style="width: <?= (int) round($p['best'] / max(1, $p['total']) * 100) ?>%"></span></span>
              <?= (int) $p['best'] ?>/<?= (int) $p['total'] ?>
            </td>
            <td><?= $p['wins'] > 0 ? '<span class="pill pill-win">' . (int) $p['wins'] . '</span>' : '–' ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <p class="muted" id="emptyHint" <?= $progress ? 'hidden' : '' ?>><?= h(t('host.empty')) ?></p>
    </section>

    <section class="panel">
      <h2><?= h(t('host.bingos')) ?> <span class="muted"><?= h(t('host.this_round')) ?></span></h2>
      <ol class="wins" id="winsList">
        <?php foreach ($wins as $w): ?>
          <?php if ((int) $w['round'] !== (int) $game['round']) { continue; } ?>
          <li><b><?= h($w['name']) ?></b> <span class="muted"><?= h($w['time']) ?> · <?= h($w['type']) ?> <?= (int) $w['index'] + 1 ?></span></li>
        <?php endforeach; ?>
      </ol>
      <p class="muted" id="noWinsHint"><?= h(t('host.no_wins')) ?></p>
    </section>
  </div>

  <div class="flash-overlay" id="winFlash" hidden>
    <div class="flash-inner">
      <span class="flash-kicker"><?= h(t('host.flash_kicker')) ?></span>
      <strong id="winFlashName"></strong>
    </div>
  </div>
</section>
