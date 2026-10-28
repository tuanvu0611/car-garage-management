
<?php

require_once __DIR__ . '/../Services/AuthService.php';

class AuthController
{
    private $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function showLogin()
    {
        require __DIR__ . '/../Views/login.php';
    }

    public function login()
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if(!$this->authService->validateInput($username, $password)) {
            $error = 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.';
            require __DIR__ . '/../Views/login.php';
            return;
        }

        $error = 'Chức năng xác thực tài khoản đang được phát triển.';
        require __DIR__ . '/../Views/login.php';
    }
}
