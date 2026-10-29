<?php
declare(strict_types=1);
require __DIR__ . '/../config/bootstrap.php';
function check(bool $condition): void { if (!$condition) throw new RuntimeException('Assertion failed'); }
function rejects(callable $callback): void {
    try { $callback(); } catch (DomainException $error) { return; }
    throw new RuntimeException('Expected validation rejection');
}
check(GarageService::number(['cost'=>'120000.50'], 'cost') === '120000.50');
foreach (['-1','1e3','NaN','0.001',[], '1000000000'] as $invalid)
    rejects(fn() => GarageService::number(['cost'=>$invalid], 'cost'));
rejects(fn() => GarageService::number(['quantity'=>'1.5'], 'quantity', true));
rejects(fn() => GarageService::number(['quantity'=>'0'], 'quantity', true, 1));
rejects(fn() => GarageService::text(['name'=>[]], 'name'));
rejects(fn() => GarageService::text(['name'=>'  '], 'name'));
foreach (['admin','manager','technician','accountant','unknown'] as $role) {
    $_SESSION['user']['role'] = $role;
    check(AuthService::can('inventory') === in_array($role, ['admin','manager'], true));
    check(AuthService::can('invoices') === in_array($role, ['admin','manager','accountant'], true));
    check(AuthService::can('repairs') === in_array($role, ['admin','manager','technician'], true));
}
check(e('<script>') === '&lt;script&gt;');
echo "Validation, role permissions and escaping: PASS\n";
