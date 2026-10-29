<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../config/bootstrap.php';
[$script, $username, $fullName, $role] = array_pad($argv, 4, '');
$password = getenv('GARAGE_USER_PASSWORD') ?: '';
if (!preg_match('/^[a-zA-Z0-9_.-]{3,50}$/', $username) || $fullName === '' || strlen($fullName) > 100
    || !in_array($role, ['admin','manager','technician','accountant'], true) || strlen($password) < 12) {
    fwrite(STDERR, "Usage: set GARAGE_USER_PASSWORD (12+ characters), then php database/create-user.php username \"Full Name\" role\n");
    exit(1);
}
try {
    (new GarageRepository(db()))->query('INSERT INTO users(username,password,full_name,role) VALUES (?,?,?,?)',
        [$username, password_hash($password, PASSWORD_DEFAULT), $fullName, $role]);
    echo "User created.\n";
} catch (PDOException $error) { fwrite(STDERR, "Cannot create user. Check database connection or duplicate username.\n"); exit(1); }
