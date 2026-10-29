<?php
/** @var array $packs */
$appConfig = ['page' => 'create', 'packs' => $packs];
?>

<section class="panel narrow">
  <h1><?= h(t('create.title')) ?></h1>
  <p class="muted"><?= h(t('create.note')) ?></p>

  <form method="post" action="/neu" id="createForm">
    <input type="hidden" name="csrf" value="<?= h($csrf) ?>">

    <label for="title"><?= h(t('create.title_label')) ?> <span class="muted"><?= h(t('create.optional')) ?></span></label>
    <input type="text" id="title" name="title" maxlength="80" placeholder="<?= h(t('create.title_ph')) ?>">

    <label for="terms"><?= h(t('create.terms_label')) ?></label>

    <div class="packs" id="packs">
      <span class="packs-label"><?= h(t('create.packs')) ?></span>
      <?php foreach ($packs as $id => $pack): ?>
        <button type="button" class="chip" data-pack="<?= h($id) ?>">
          <?= h($pack['label']) ?>
          <span class="chip-count"><?= count($pack['terms']) ?></span>
        </button>
      <?php endforeach; ?>
      <button type="button" class="chip chip-ghost" id="clearTerms"><?= h(t('create.clear')) ?></button>
    </div>
    <p class="packs-hint" id="packHint"><?= h(t('create.hint')) ?></p>

    <textarea id="terms" name="terms" rows="12" required placeholder="<?= h(t('create.terms_ph')) ?>"></textarea>
    <div class="counter" id="termCounter"><?= h(t('create.counter', ['count' => 0, 'need' => 24])) ?></div>

    <div class="row">
      <div>
        <label for="grid"><?= h(t('create.grid')) ?></label>
        <select id="grid" name="grid">
          <option value="5" selected><?= h(t('create.grid5')) ?></option>
          <option value="4"><?= h(t('create.grid4')) ?></option>
          <option value="3"><?= h(t('create.grid3')) ?></option>
        </select>
      </div>
      <div class="checkline">
        <label class="check">
          <input type="checkbox" id="free_center" name="free_center" value="1" checked>
          <span><?= h(t('create.free_center')) ?></span>
        </label>
      </div>
    </div>

    <button class="btn btn-primary btn-big" type="submit"><?= h(t('create.submit')) ?></button>
  </form>
</section>
