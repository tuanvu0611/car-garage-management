<form method="post" action="?page=login" class="card">
<input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
<label>Tên đăng nhập <input name="username" required maxlength="50" autocomplete="username" value="<?= e(is_string($_POST['username'] ?? null) ? $_POST['username'] : '') ?>"></label>
<label>Mật khẩu <input type="password" name="password" required autocomplete="current-password"></label>
<button>Đăng nhập</button>
</form>
