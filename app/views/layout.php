<?php
/** @var string $title */
/** @var string $content */
/** @var string $csrf */

// Cache-Busting über die Änderungszeit: verhindert, dass Browser alte Assets behalten.
$asset = function (string $path): string {
    $file = APP_ROOT . '/public' . $path;
    return $path . '?v=' . (is_file($file) ? filemtime($file) : '1');
};

$appJson = ($appConfig ?? []) + ['i18n' => i18n_js(), 'lang' => lang_current()];
$langNow = lang_current();
$appPage = $appConfig['page'] ?? '';
?>
<!doctype html>
<html lang="<?= h($langNow) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="color-scheme" content="light">
<meta name="csrf" content="<?= h($csrf) ?>">
<title><?= h($title ?? 'Bullshit Bingo') ?></title>
<link rel="icon" type="image/png" href="/assets/img/favicon-tonisch.png">
<link rel="apple-touch-icon" href="/assets/img/favicon-tonisch.png">
<link rel="preload" href="/assets/fonts/fraunces-latin-full-normal.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/assets/fonts/inter-tight-latin-wght-normal.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?= h($asset('/assets/fonts.css')) ?>">
<link rel="stylesheet" href="<?= h($asset('/assets/style.css')) ?>">
</head>
<body data-page="<?= h($appPage) ?>" class="<?= h($bodyClass ?? '') ?>">

<header class="topbar">
  <a class="brand" href="/" aria-label="Bullshit Bingo – Startseite">
    <img class="brand-mark" src="/assets/img/mark-tonisch.png" width="80" height="64"
         alt="tonisch.de">
    <span class="brand-sep" aria-hidden="true"></span>
    <span class="brand-name">Bullshit<em>Bingo</em></span>
  </a>
  <div class="topbar-right">
    <?php if (!empty($navRight)): ?>
      <span class="nav-extra"><?= $navRight ?></span>
    <?php endif; ?>
    <span class="lang-switch">
      <?php foreach (['de' => 'DE', 'en' => 'EN'] as $code => $label): ?>
        <a class="lang<?= $langNow === $code ? ' is-active' : '' ?>"
           href="/lang/<?= $code ?>" hreflang="<?= $code ?>"
           <?= $langNow === $code ? 'aria-current="true"' : '' ?>><?= $label ?></a>
      <?php endforeach; ?>
    </span>
  </div>
</header>

<main class="wrap">
  <?= $content ?>
</main>

<script>
window.APP = <?= json_encode($appJson, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<?php if ($appPage === 'host'): ?>
<script src="<?= h($asset('/assets/qrcode.js')) ?>"></script>
<?php endif; ?>
<script src="<?= h($asset('/assets/app.js')) ?>"></script>
</body>
</html>
