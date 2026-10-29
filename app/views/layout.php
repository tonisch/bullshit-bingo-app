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
?>
<!doctype html>
<html lang="<?= h($langNow) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="color-scheme" content="light">
<meta name="csrf" content="<?= h($csrf) ?>">
<title><?= h($title ?? 'Bullshit Bingo') ?></title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='16' fill='%23005a9d'/><g fill='%23ffffff'><rect x='15' y='15' width='16' height='16' rx='2'/><rect x='42' y='15' width='16' height='16' rx='2'/><rect x='69' y='15' width='16' height='16' rx='2'/><rect x='15' y='42' width='16' height='16' rx='2'/><rect x='42' y='42' width='16' height='16' rx='2'/><rect x='69' y='42' width='16' height='16' rx='2'/><rect x='15' y='69' width='16' height='16' rx='2'/><rect x='69' y='69' width='16' height='16' rx='2'/></g></svg>">
<link rel="preload" href="/assets/fonts/fraunces-latin-full-normal.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/assets/fonts/inter-tight-latin-wght-normal.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?= h($asset('/assets/fonts.css')) ?>">
<link rel="stylesheet" href="<?= h($asset('/assets/style.css')) ?>">
</head>
<body data-page="<?= h($page ?? '') ?>" class="<?= h($bodyClass ?? '') ?>">

<header class="topbar">
  <a class="brand" href="/">Bullshit<em>Bingo</em></a>
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
<?php if (($page ?? '') === 'host'): ?>
<script src="<?= h($asset('/assets/qrcode.js')) ?>"></script>
<?php endif; ?>
<script src="<?= h($asset('/assets/app.js')) ?>"></script>
</body>
</html>
