<?php /** @var string $title */ /** @var string $message */ ?>

<section class="panel narrow">
  <h1><?= h($title ?? 'Fehler') ?></h1>
  <p class="lead"><?= h($message ?? 'Unbekannter Fehler.') ?></p>
  <p><a class="btn" href="/">Zur Startseite</a></p>
</section>
