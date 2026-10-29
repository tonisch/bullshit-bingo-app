<?php /** @var array|null $game */ ?>

<section class="hero">
  <h1>
    <?= h(t('home.title')) ?>
    <span><?= h(t('home.subtitle')) ?></span>
  </h1>
  <p class="lead"><?= h(t('home.lead')) ?></p>

  <div class="cta">
    <a class="btn btn-primary" href="/neu"><?= h(t('home.cta.create')) ?></a>
    <a class="btn" href="/beitreten"><?= h(t('home.cta.join')) ?></a>
  </div>

  <?php if ($game): ?>
    <div class="resume">
      <div>
        <strong><?= h(t('home.resume')) ?></strong> <?= h($game['title']) ?>
        <span class="muted">· <?= h(t('host.code')) ?> <?= h($game['code']) ?> · <?= h(t('status.' . $game['status'])) ?></span>
      </div>
      <div class="resume-actions">
        <a class="btn btn-small" href="/karte"><?= h(t('home.resume.card')) ?></a>
        <?php if (!empty($_SESSION['host'][$game['code']])): ?>
          <a class="btn btn-small btn-ghost" href="/host/<?= h($_SESSION['host'][$game['code']]) ?>"><?= h(t('home.resume.host')) ?></a>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</section>

<section class="steps">
  <?php for ($step = 1; $step <= 3; $step++): ?>
    <article>
      <span class="num">0<?= $step ?></span>
      <h2><?= h(t('home.step' . $step . '.title')) ?></h2>
      <p><?= h(t('home.step' . $step . '.text')) ?></p>
    </article>
  <?php endfor; ?>
</section>
