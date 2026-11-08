<?php
$schemas = [
    'customers' => ['full_name'=>['Họ tên','text'], 'phone'=>['Điện thoại','tel']],
    'vehicles' => ['customer_id'=>['Khách hàng','select'], 'plate'=>['Biển số','text'], 'model'=>['Hãng / dòng xe','text']],
    'repairs' => ['vehicle_id'=>['Xe','select'], 'description'=>['Nội dung sửa chữa','text'], 'labor_cost'=>['Tiền công (VND)','money']],
    'inventory' => ['name'=>['Tên phụ tùng','text'], 'quantity'=>['Số lượng ban đầu','number'], 'price'=>['Đơn giá (VND)','money']],
    'invoices' => ['repair_id'=>['Phiếu sửa chữa đã hoàn tất','select']],
];
function fields(array $schema, array $options, string $action = 'create'): void {
    $values = ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? 'create') === $action) ? $_POST : [];
    foreach ($schema as $name => [$label, $type]) {
        $value = is_string($values[$name] ?? null) ? $values[$name] : '';
        echo '<label>' . e($label);
        if ($type === 'select') {
            echo '<select name="' . e($name) . '" required><option value="">Chọn...</option>';
            foreach ($options[$name] as $option) echo '<option value="' . e($option['id']) . '"'
                . ((string)$option['id'] === $value ? ' selected' : '') . '>' . e($option['label']) . '</option>';
            echo '</select>';
        } else {
            echo '<input required name="' . e($name) . '" value="' . e($value) . '" type="' . ($type === 'money' ? 'number' : e($type)) . '"'
                . (in_array($type, ['money','number'], true) ? ' min="0" max="999999999" step="' . ($type === 'money' ? '0.01' : '1') . '"' : ' maxlength="' . ($name === 'description' ? '1000' : '100') . '"') . '>';
        }
        echo '</label>';
    }
}
?>
<section class="card">
<h2><?= $page === 'invoices' ? 'Lập hóa đơn' : 'Thêm mới' ?></h2>
<form method="post">
<input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
<?php fields($schemas[$page], $options); ?>
<button>Lưu</button>
</form>
</section>
<?php if ($page === 'inventory'): ?>
<section class="card"><h2>Nhập bổ sung tồn kho</h2>
<p>Chọn phụ tùng đã có và số lượng nhập thêm. Đơn giá hiện tại được giữ nguyên.</p>
<form method="post">
<input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
<input type="hidden" name="action" value="restock">
<?php fields(['part_id'=>['Phụ tùng','select'],'quantity'=>['Số lượng nhập thêm','number']], $options, 'restock'); ?>
<button>Nhập kho</button>
</form></section>
<?php endif; ?>
<?php if ($page === 'repairs'): ?>
<section class="card"><h2>Sử dụng phụ tùng</h2><form method="post">
<input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>"><input type="hidden" name="action" value="use_part">
<?php fields(['repair_id'=>['Phiếu đang mở','select'],'part_id'=>['Phụ tùng','select'],'quantity'=>['Số lượng sử dụng','number']], $options, 'use_part'); ?><button>Xuất kho cho phiếu</button>
</form></section>
<section class="card"><h2>Hoàn tất sửa chữa</h2><form method="post">
<input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>"><input type="hidden" name="action" value="complete">
<?php fields(['repair_id'=>['Phiếu đang mở','select']], $options, 'complete'); ?><button>Đánh dấu hoàn tất</button>
</form></section>
<?php endif; ?>
<h2>Danh sách</h2>
<?php
$labels = ['id'=>'Mã','full_name'=>'Họ tên','phone'=>'Điện thoại','plate'=>'Biển số','model'=>'Dòng xe','customer'=>'Khách hàng','description'=>'Nội dung','labor_cost'=>'Tiền công','status'=>'Trạng thái','name'=>'Tên phụ tùng','quantity'=>'Tồn kho','price'=>'Đơn giá','repair_id'=>'Mã phiếu','total'=>'Tổng tiền (VND)','created_at'=>'Ngày lập'];
?>
<?php if (!$rows): ?><p>Chưa có dữ liệu.</p><?php else: ?>
<div class="table-wrap"><table><thead><tr><?php foreach (array_keys($rows[0]) as $key): ?><th><?= e($labels[$key] ?? $key) ?></th><?php endforeach; ?></tr></thead>
<tbody><?php foreach ($rows as $row): ?><tr><?php foreach ($row as $key => $value): ?><td><?= e($key === 'status' ? ($value === 'open' ? 'Đang mở' : 'Hoàn tất') : $value) ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody></table></div>
<?php endif; ?>
