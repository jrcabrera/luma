<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function requireAdmin(): void
{
    if (empty($_SESSION['admin_logged'])) {
        header('Location: login.php');
        exit;
    }
}

function requirePost(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        http_response_code(405);
        exit('Metodo no permitido.');
    }
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['csrf_token'];
}

function verifyCsrfOrFail(): void
{
    $token = (string) ($_POST['csrf_token'] ?? '');
    $sessionToken = (string) ($_SESSION['csrf_token'] ?? '');

    if ($token === '' || $sessionToken === '' || !hash_equals($sessionToken, $token)) {
        http_response_code(403);
        exit('Token CSRF invalido.');
    }
}

function isLoginRateLimited(int $maxAttempts = 5, int $windowSeconds = 900): bool
{
    $attempts = $_SESSION['login_attempts'] ?? [];
    if (!is_array($attempts)) {
        $_SESSION['login_attempts'] = [];
        return false;
    }

    $now = time();
    $attempts = array_values(array_filter($attempts, static function ($timestamp) use ($now, $windowSeconds): bool {
        return is_int($timestamp) && ($now - $timestamp) < $windowSeconds;
    }));

    $_SESSION['login_attempts'] = $attempts;

    return count($attempts) >= $maxAttempts;
}

function recordLoginFailure(): void
{
    $attempts = $_SESSION['login_attempts'] ?? [];
    if (!is_array($attempts)) {
        $attempts = [];
    }

    $attempts[] = time();
    $_SESSION['login_attempts'] = $attempts;
}

function clearLoginFailures(): void
{
    unset($_SESSION['login_attempts']);
}
