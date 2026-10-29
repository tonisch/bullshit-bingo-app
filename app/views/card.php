<?php
/** @var array $game */
/** @var array $player */
/** @var array $terms */

$grid = (int) $game['grid'];
$card = player_card($player);
$marks = marks_of((int) $player['id']);
$marked = array_fill_keys($marks, true);
$evaluation = evaluate_card($card, $marks, $grid);

$appConfig = [
    'page'  => 'card',
    'code'  => $game['code'],
    'grid'  => $grid,
    'poll'  => (int) cfg('poll_ms', 2500),
];
$statusLabels = ['lobby' => 'Warteraum', 'running' => 'läuft', 'finished' => 'beendet'];
?>

<section class="card-page" id="cardPage" data-status="<?= h($game['status']) ?>" data-round="<?= (int) $game['round'] ?>">
  <header class="card-head">
    <div>
      <span class="muted"><?= h($game['title']) ?></span>
      <strong><?= h($player['name']) ?></strong>
    </div>
    <div class="meta">
      <span class="badge badge-<?= h($game['status']) ?>" id="statusBadge">
        <?= h($statusLabels[$game['status']] ?? $game['status']) ?>
      </span>
      <span>Runde <b id="roundNum"><?= (int) $game['round'] ?></b></span>
    </div>
  </header>

  <p class="hint" id="hint">
    <?php if ($game['status'] === 'lobby'): ?>
      Warte auf den Start. Deine Karte siehst du schon – tippen geht gleich los.
    <?php elseif ($game['status'] === 'finished'): ?>
      Das Spiel ist beendet. Danke fürs Mitspielen!
    <?php else: ?>
      Tippe ein Feld an, sobald der Begriff gefallen ist.
    <?php endif; ?>
  </p>

  <div class="board" id="board" style="--grid: <?= $grid ?>">
    <?php foreach ($card as $idx => $termId): ?>
      <?php
        $isFree = $termId === 0;
        $isOn = isset($marked[$idx]);
      ?>
      <button type="button"
              class="cell<?= $isFree ? ' cell-free' : '' ?><?= $isOn ? ' is-marked' : '' ?>"
              data-idx="<?= (int) $idx ?>"
              <?= $isFree ? 'disabled' : '' ?>>
        <span class="cell-text"><?= $isFree ? 'FREI' : h($terms[$termId] ?? '?') ?></span>
      </button>
    <?php endforeach; ?>
  </div>

  <div class="card-foot">
    <div class="progress" id="progress">
      Beste Reihe: <b id="bestNum"><?= (int) $evaluation['best'] ?></b>/<?= $grid ?>
      · <span id="markCount"><?= count($marks) ?></span> markiert
    </div>
    <button class="btn btn-primary btn-bingo" id="bingoBtn" type="button">BINGO!</button>
  </div>

  <div class="toast" id="toast" hidden></div>
</section>
