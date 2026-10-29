<?php
declare(strict_types=1);

/**
 * Fehler, die dem Nutzer im Klartext angezeigt werden dürfen.
 */
class UserError extends RuntimeException
{
}

/**
 * Minimal-Ersatz, falls mbstring fehlt (z. B. in einer kargen CLI-Umgebung).
 */
if (!function_exists('mb_strlen')) {
    function mb_strlen($value, $encoding = null)
    {
        return strlen((string) $value);
    }

    function mb_substr($value, $start, $length = null, $encoding = null)
    {
        return $length === null
            ? substr((string) $value, (int) $start)
            : substr((string) $value, (int) $start, (int) $length);
    }

    function mb_strtolower($value, $encoding = null)
    {
        return strtolower((string) $value);
    }
}

function cfg(string $key, $default = null)
{
    return $GLOBALS['CFG'][$key] ?? $default;
}

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function now(): string
{
    return date('Y-m-d H:i:s');
}

function json_out(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function redirect(string $path): void
{
    header('Location: ' . $path, true, 303);
    exit;
}

function random_code(int $length = 6): string
{
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // ohne 0/O/1/I
    $max = strlen($alphabet) - 1;
    $out = '';
    for ($i = 0; $i < $length; $i++) {
        $out .= $alphabet[random_int(0, $max)];
    }
    return $out;
}

function random_token(int $bytes = 24): string
{
    return bin2hex(random_bytes($bytes));
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = random_token(16);
    }
    return $_SESSION['csrf'];
}

function csrf_ok($token): bool
{
    return is_string($token)
        && !empty($_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $token);
}

function csrf_require($token): void
{
    if (!csrf_ok($token)) {
        throw new UserError('Ungültiges Formular-Token. Bitte Seite neu laden.');
    }
}

function request_json(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function view(string $template, array $vars = []): void
{
    $vars['csrf'] = csrf_token();
    extract($vars, EXTR_SKIP);

    ob_start();
    require APP_ROOT . '/app/views/' . $template . '.php';
    $content = ob_get_clean();

    require APP_ROOT . '/app/views/layout.php';
}
