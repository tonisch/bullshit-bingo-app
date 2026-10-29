<?php /** @var array|null $game */ ?>

<section class="hero">
  <h1>Bullshit Bingo.<br><span>Aber mit Begriffen, die wirklich fallen.</span></h1>
  <p class="lead">
    Spiel anlegen, Begriffszettel reinkopieren, Code oder QR verteilen – jeder bekommt eine
    eigene Karte. Wer zuerst eine Reihe vollhat, drückt BINGO.
  </p>

  <div class="cta">
    <a class="btn btn-primary" href="/neu">Spiel anlegen</a>
    <a class="btn" href="/beitreten">Ich habe einen Code</a>
  </div>

  <?php if ($game): ?>
    <div class="resume">
      <div>
        <strong>Laufendes Spiel:</strong> <?= h($game['title']) ?>
        <span class="muted">· Code <?= h($game['code']) ?> · <?= h($game['status']) ?></span>
      </div>
      <div class="resume-actions">
        <a class="btn btn-small" href="/karte">Karte öffnen</a>
        <?php if (!empty($_SESSION['host'][$game['code']])): ?>
          <a class="btn btn-small" href="/host/<?= h($_SESSION['host'][$game['code']]) ?>">Host-Ansicht</a>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</section>

<section class="steps">
  <article>
    <span class="num">1</span>
    <h2>Begriffe rein</h2>
    <p>Eine Liste, ein Begriff pro Zeile – Klassiker wie „Synergie“ oder „wir müssen da mal drüber“. Mindestens 24 für ein 5×5-Feld, mehr ist besser: dann ist jede Karte anders.</p>
  </article>
  <article>
    <span class="num">2</span>
    <h2>Code verteilen</h2>
    <p>Jeder tritt mit sechsstelligem Code oder QR bei und bekommt eine zufällig gemischte Karte aufs Handy.</p>
  </article>
  <article>
    <span class="num">3</span>
    <h2>Bingo</h2>
    <p>Gehörtes Feld antippen. Der Server prüft die Reihe und trägt den Sieger live in die Beamer-Ansicht ein.</p>
  </article>
</section>
