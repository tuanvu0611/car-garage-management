
<?php

class AuthController
{
    public function showLogin()
    {
        require __DIR__ . '/../Views/login.php';
    }

    public function login()
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if($username === '' || $password === '') {
            $error = 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.';
            require __DIR__ . '/../Views/login.php';
            return;
        }

        $error = 'Chức năng xác thực đang được phát triển.';
        require __DIR__ . '/../Views/login.php';
    }
}
