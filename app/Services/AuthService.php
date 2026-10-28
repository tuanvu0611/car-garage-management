
<?php

class AuthService
{
    public function validateInput($username, $password)
    {
        if(trim($username) === '' || $password === '') {
            return false;
        }

        return true;
    }

    public function getRoleName($role)
    {
        $roles = [
            'admin' => 'Quản trị viên',
            'manager' => 'Quản lý gara',
            'technician' => 'Kỹ thuật viên',
            'accountant' => 'Nhân viên kế toán'
        ];

        return $roles[$role] ?? 'Chưa xác định';
    }
}
