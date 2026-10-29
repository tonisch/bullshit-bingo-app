<?php /** @var string $title */ /** @var string $content */ /** @var string $csrf */ ?>
<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf" content="<?= h($csrf) ?>">
<title><?= h($title ?? 'Bullshit Bingo') ?></title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='18' fill='%23f5c542'/><g fill='%23151515'><rect x='14' y='14' width='16' height='16' rx='3'/><rect x='42' y='14' width='16' height='16' rx='3'/><rect x='70' y='14' width='16' height='16' rx='3'/><rect x='14' y='42' width='16' height='16' rx='3'/><rect x='42' y='42' width='16' height='16' rx='3'/><rect x='70' y='42' width='16' height='16' rx='3'/><rect x='14' y='70' width='16' height='16' rx='3'/><rect x='70' y='70' width='16' height='16' rx='3'/></g></svg>">
<link rel="stylesheet" href="/assets/style.css?v=1">
</head>
<body data-page="<?= h($page ?? '') ?>" class="<?= h($bodyClass ?? '') ?>">

<header class="topbar">
  <a class="brand" href="/">Bullshit<em>Bingo</em></a>
  <?php if (!empty($navRight)): ?>
    <div class="topbar-right"><?= $navRight ?></div>
  <?php endif; ?>
</header>

<main class="wrap">
  <?= $content ?>
</main>

<footer class="foot">
  <span>Kein PHP-Framework, keine Tracker, eine SQLite-Datei.</span>
</footer>

<script>
window.APP = <?= json_encode($appConfig ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<?php if (($page ?? '') === 'host'): ?>
<script src="/assets/qrcode.js?v=1"></script>
<?php endif; ?>
<script src="/assets/app.js?v=1"></script>
</body>
</html>
