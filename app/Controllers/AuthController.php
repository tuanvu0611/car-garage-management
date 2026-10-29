<?php
declare(strict_types=1);
class AuthController {
    public function login(): void {
        if (time() < ($_SESSION['login_after'] ?? 0)) throw new DomainException('Vui lòng chờ một phút trước khi thử lại.');
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        if (!is_string($username) || !is_string($password) || trim($username) === '' || $password === '')
            throw new DomainException('Vui lòng nhập tên đăng nhập và mật khẩu.');
        if (!(new AuthService())->login(trim($username), $password)) {
            $_SESSION['attempts'] = ($_SESSION['attempts'] ?? 0) + 1;
            if ($_SESSION['attempts'] >= 5) {
                $_SESSION['login_after'] = time() + 60;
                $_SESSION['attempts'] = 0;
            }
            throw new DomainException('Tên đăng nhập hoặc mật khẩu không đúng.');
        }
        unset($_SESSION['attempts'], $_SESSION['login_after']);
        redirect('home');
    }
    public function logout(): never {
        $_SESSION = [];
        session_destroy();
        redirect('login');
    }
}
