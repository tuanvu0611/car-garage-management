<?php
declare(strict_types=1);
require __DIR__ . '/../config/bootstrap.php';
function verify(bool $condition): void {
    if (!$condition) throw new RuntimeException('Test failed');
}
$db = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$db->exec('CREATE TABLE parts(id INTEGER PRIMARY KEY, quantity INTEGER NOT NULL, price NUMERIC NOT NULL)');
$db->exec('INSERT INTO parts VALUES (1, 8, 120000)');
$service = new GarageService(new GarageRepository($db));
$service->save('inventory', ['action'=>'restock', 'part_id'=>'1', 'quantity'=>'5']);
verify((int)$db->query('SELECT quantity FROM parts')->fetchColumn() === 13);
verify((int)$db->query('SELECT price FROM parts')->fetchColumn() === 120000);
foreach (['0', '-2', '1.5', [], '1000000000', '999999999'] as $quantity) {
    try {
        $service->save('inventory', ['action'=>'restock', 'part_id'=>'1', 'quantity'=>$quantity]);
        throw new RuntimeException('Invalid quantity accepted');
    } catch (DomainException $expected) {}
    verify((int)$db->query('SELECT quantity FROM parts')->fetchColumn() === 13);
}
try {
    $service->save('inventory', ['action'=>'restock', 'part_id'=>'99', 'quantity'=>'1']);
    throw new RuntimeException('Missing part accepted');
} catch (DomainException $expected) {}
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = ['action'=>'restock', 'part_id'=>'1', 'quantity'=>'<script>'];
$_SESSION['csrf'] = 'test';
$page = 'inventory';
$rows = [];
$options = ['part_id'=>[['id'=>1, 'label'=>'Lọc dầu']]];
ob_start();
require __DIR__ . '/../app/Views/records.php';
$html = ob_get_clean();
verify(str_contains($html, 'value="1" selected'));
verify(str_contains($html, 'value="&lt;script&gt;"'));
verify(!str_contains($html, 'value="<script>"'));
verify(substr_count($html, 'value="&lt;script&gt;"') === 1);
echo "Restock, rejected quantities, unchanged price and form recovery: PASS\n";
