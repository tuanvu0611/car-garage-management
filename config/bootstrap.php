<?php
declare(strict_types=1);
spl_autoload_register(function (string $class): void {
    foreach (['Controllers', 'Services', 'Repositories'] as $directory) {
        $path = __DIR__ . '/../app/' . $directory . '/' . $class . '.php';
        if (is_file($path)) { require_once $path; return; }
    }
});
if (is_file(__DIR__ . '/../.env')) {
    foreach (file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        if (getenv(trim($key)) === false) putenv(trim($key) . '=' . trim($value));
    }
}
function db(): PDO {
    static $connection;
    return $connection ??= new PDO(sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        getenv('DB_HOST') ?: '127.0.0.1', getenv('DB_PORT') ?: '3306', getenv('DB_DATABASE') ?: 'car_garage'),
        getenv('DB_USERNAME') ?: 'root', getenv('DB_PASSWORD') ?: '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
         PDO::ATTR_EMULATE_PREPARES => false]);
}
function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function redirect(string $page): never { header('Location: ?page=' . urlencode($page)); exit; }
if (PHP_SAPI !== 'cli') {
    session_set_cookie_params(['httponly' => true, 'secure' => getenv('SESSION_SECURE') === '1', 'samesite' => 'Lax']);
    session_start();
    $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}
