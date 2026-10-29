<?php
/** @var array $packs */
$appConfig = ['page' => 'create', 'packs' => $packs];
?>

<section class="panel narrow">
  <h1>Neues Spiel anlegen</h1>
  <p class="muted">Du bekommst danach einen Host-Link – den nicht rausgeben, damit niemand die Runden steuert.</p>

  <form method="post" action="/neu" id="createForm">
    <input type="hidden" name="csrf" value="<?= h($csrf) ?>">

    <label for="title">Titel <span class="muted">(optional)</span></label>
    <input type="text" id="title" name="title" maxlength="80" placeholder="z. B. Montags-All-Hands">

    <label for="terms">Begriffe – einer pro Zeile</label>

    <div class="packs" id="packs">
      <span class="packs-label">Wortlisten</span>
      <?php foreach ($packs as $id => $pack): ?>
        <button type="button" class="chip" data-pack="<?= h($id) ?>"
                title="<?= h($pack['hint']) ?>">
          <?= h($pack['label']) ?>
          <span class="chip-count"><?= count($pack['terms']) ?></span>
        </button>
      <?php endforeach; ?>
      <button type="button" class="chip chip-ghost" id="clearTerms">Leeren</button>
    </div>
    <p class="packs-hint" id="packHint">
      Klick eine Liste an – ihre Begriffe werden eingefügt. Mehrere Listen lassen sich mischen.
    </p>

    <textarea id="terms" name="terms" rows="12" required
      placeholder="Synergieeffekte&#10;Lass uns das mal parken&#10;Ich sehe da keine Blocker&#10;…"></textarea>
    <div class="counter" id="termCounter">0 Begriffe</div>

    <div class="row">
      <div>
        <label for="grid">Raster</label>
        <select id="grid" name="grid">
          <option value="5" selected>5 × 5 (24 Begriffe)</option>
          <option value="4">4 × 4 (15 Begriffe)</option>
          <option value="3">3 × 3 (8 Begriffe)</option>
        </select>
      </div>
      <div class="checkline">
        <label class="check">
          <input type="checkbox" id="free_center" name="free_center" value="1" checked>
          <span>Freies Mittelfeld</span>
        </label>
      </div>
    </div>

    <button class="btn btn-primary btn-big" type="submit">Spiel erzeugen</button>
  </form>
</section>
