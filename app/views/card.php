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
?>

<section class="card-page" id="cardPage" data-status="<?= h($game['status']) ?>" data-round="<?= (int) $game['round'] ?>">
  <header class="card-head">
    <div>
      <span><?= h($game['title']) ?></span>
      <strong><?= h($player['name']) ?></strong>
    </div>
    <div class="meta">
      <span class="badge badge-<?= h($game['status']) ?>" id="statusBadge">
        <?= h(t('status.' . $game['status'])) ?>
      </span>
      <span><?= h(t('host.round')) ?> <b id="roundNum"><?= (int) $game['round'] ?></b></span>
    </div>
  </header>

  <p class="hint" id="hint"><?= h(t('card.hint_' . $game['status'])) ?></p>

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
        <span class="cell-text"><?= $isFree ? h(t('card.free')) : h($terms[$termId] ?? '?') ?></span>
      </button>
    <?php endforeach; ?>
  </div>

  <div class="card-foot">
    <div class="progress" id="progress">
      <?= h(t('card.best')) ?> <b id="bestNum"><?= (int) $evaluation['best'] ?></b>/<?= $grid ?>
      · <span id="markCount"><?= count($marks) ?></span> <?= h(t('card.marked')) ?>
    </div>
  </div>
  <p class="hint"><?= h(t('card.auto_bingo')) ?></p>

  <div class="toast" id="toast" hidden></div>
</section>
