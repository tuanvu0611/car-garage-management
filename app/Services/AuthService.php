<?php
declare(strict_types=1);
class AuthService {
    public function login(string $username, string $password): bool {
        $user = (new GarageRepository(db()))->query('SELECT * FROM users WHERE username = ?', [$username])->fetch();
        if (!$user || !password_verify($password, $user['password'])) return false;
        session_regenerate_id(true);
        $_SESSION['user'] = array_intersect_key($user, array_flip(['id', 'username', 'full_name', 'role']));
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        return true;
    }
    public static function can(string $page): bool {
        return in_array($page, [
            'admin' => ['home', 'customers', 'vehicles', 'repairs', 'inventory', 'invoices'],
            'manager' => ['home', 'customers', 'vehicles', 'repairs', 'inventory', 'invoices'],
            'technician' => ['home', 'customers', 'vehicles', 'repairs'],
            'accountant' => ['home', 'customers', 'vehicles', 'invoices'],
        ][$_SESSION['user']['role'] ?? ''] ?? [], true);
    }
}
