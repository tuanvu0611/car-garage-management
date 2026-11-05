<?php
declare(strict_types=1);

class AuthService {
    public function __construct(private GarageRepository $repository) {}

    public function login(string $username, string $password): bool {
        $user = $this->repository
            ->query('SELECT * FROM users WHERE username = ?', [$username])
            ->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        session_regenerate_id(true);

        $_SESSION['user'] = array_intersect_key(
            $user,
            array_flip(['id', 'username', 'full_name', 'role'])
        );

        $_SESSION['csrf'] = bin2hex(random_bytes(32));

        return true;
    }

    public static function can(string $page): bool {
        $permissions = [
            'admin' => ['home', 'customers', 'vehicles', 'repairs', 'inventory', 'invoices'],
            'manager' => ['home', 'customers', 'vehicles', 'repairs', 'inventory', 'invoices'],
            'technician' => ['home', 'customers', 'vehicles', 'repairs'],
            'accountant' => ['home', 'customers', 'vehicles', 'invoices'],
        ];

        $role = $_SESSION['user']['role'] ?? '';

        return in_array($page, $permissions[$role] ?? [], true);
    }
}
