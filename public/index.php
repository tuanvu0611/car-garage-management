<?php
declare(strict_types=1);
require __DIR__ . '/../config/bootstrap.php';
header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
$page = $_GET['page'] ?? 'home';
$pages = ['home'=>'Tổng quan','customers'=>'Khách hàng','vehicles'=>'Xe','repairs'=>'Sửa chữa','inventory'=>'Kho phụ tùng','invoices'=>'Hóa đơn','login'=>'Đăng nhập','logout'=>'Đăng xuất'];
$error = null;
$rows = [];
$options = [];
if (!is_string($page) || !isset($pages[$page])) {
    http_response_code(404); $page = 'missing'; $title = 'Không tìm thấy trang';
} else {
    $title = $pages[$page];
    if ($page !== 'login' && !isset($_SESSION['user'])) redirect('login');
    if ($page === 'login' && isset($_SESSION['user'])) redirect('home');
    if ($page !== 'login' && $page !== 'logout' && !AuthService::can($page)) {
        http_response_code(403); $error = 'Bạn không có quyền truy cập trang này.'; $page = 'denied';
    }
    try {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf'] ?? '';
            if (!is_string($token) || !hash_equals($_SESSION['csrf'], $token)) {
                http_response_code(403); throw new DomainException('Phiên biểu mẫu không hợp lệ. Hãy tải lại trang.');
            }
            if ($page === 'login') (new AuthController())->login();
            elseif ($page === 'logout') (new AuthController())->logout();
            elseif (AuthService::can($page) && $page !== 'home') {
                (new GarageService(new GarageRepository(db())))->save($page, $_POST);
                $_SESSION['flash'] = 'Đã lưu thành công.';
                redirect($page);
            }
        }
        if ($page === 'logout') { http_response_code(405); header('Allow: POST'); $error = 'Đăng xuất cần gửi bằng biểu mẫu.'; }
    } catch (DomainException $exception) {
        $error = $exception->getMessage();
    } catch (PDOException $exception) {
        error_log((string)$exception);
        $error = $exception->getCode() === '23000'
            ? 'Dữ liệu bị trùng hoặc bản ghi liên quan không tồn tại.'
            : 'Không thể truy cập dữ liệu. Hãy kiểm tra cấu hình và nhật ký máy chủ.';
        http_response_code($exception->getCode() === '23000' ? 422 : 503);
    }
    if (in_array($page, ['customers','vehicles','repairs','inventory','invoices'], true)) {
        try {
            $repository = new GarageRepository(db());
            $rows = $repository->rows($page);
            $options = $repository->options();
        } catch (PDOException $exception) {
            error_log((string)$exception);
            $error = 'Không thể tải dữ liệu. Hãy kiểm tra kết nối và import database/create_table.sql.';
            http_response_code(503);
        }
    }
}
require __DIR__ . '/../app/Views/layout.php';
