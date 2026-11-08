<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> — Gara Ô tô</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header><strong>Gara Ô tô</strong>
<?php if (isset($_SESSION['user'])): ?>
<span><?= e($_SESSION['user']['full_name']) ?> · <?= e($_SESSION['user']['role']) ?></span>
<form method="post" action="?page=logout"><input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>"><button>Đăng xuất</button></form>
<?php endif; ?>
</header>
<?php if (isset($_SESSION['user'])): ?><nav>
<?php foreach ($pages as $key => $label): if (AuthService::can($key)): ?>
<a href="?page=<?= e($key) ?>" <?= $page === $key ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
<?php endif; endforeach; ?>
</nav><?php endif; ?>
<main>
<h1><?= e($title) ?></h1>
<?php if ($error): ?><p class="error" role="alert"><?= e($error) ?></p><?php endif; ?>
<?php if (isset($_SESSION['flash'])): ?><p class="success" role="status"><?= e($_SESSION['flash']) ?></p><?php unset($_SESSION['flash']); endif; ?>
<?php if ($page === 'login'): require __DIR__ . '/login.php';
elseif ($page === 'home'): ?>
<p>Chọn chức năng trên thanh điều hướng để bắt đầu.</p>
<p>Quy trình: thêm khách hàng → thêm xe → tạo phiếu sửa chữa → dùng phụ tùng → hoàn tất → lập hóa đơn.</p>
<p>Kỹ thuật viên phụ trách sửa chữa; kế toán lập hóa đơn; quản lý và quản trị viên sử dụng toàn bộ chức năng.</p>
<?php elseif ($page === 'missing'): ?><p>Đường dẫn không tồn tại. <a href="?page=home">Về trang chính</a></p>
<?php elseif (isset($options['customer_id'])): require __DIR__ . '/records.php'; endif; ?>
</main>
</body>
</html>
