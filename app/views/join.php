<?php /** @var string $code */ $appConfig = ['page' => 'join']; ?>

<section class="panel narrow">
  <h1><?= h(t('join.title')) ?></h1>

  <form method="post" action="/beitreten" autocomplete="off">
    <input type="hidden" name="csrf" value="<?= h($csrf) ?>">

    <label for="code"><?= h(t('join.code')) ?></label>
    <input type="text" id="code" name="code" value="<?= h($code) ?>" maxlength="10"
           inputmode="latin" autocapitalize="characters" spellcheck="false"
           class="code-input" placeholder="ABC123" required>

    <label for="name"><?= h(t('join.name')) ?></label>
    <input type="text" id="name" name="name" maxlength="24" placeholder="<?= h(t('join.name_ph')) ?>" required>

    <button class="btn btn-primary btn-big" type="submit"><?= h(t('join.submit')) ?></button>
  </form>
</section>
