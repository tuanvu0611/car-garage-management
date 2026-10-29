<?php
declare(strict_types=1);
class GarageService {
    public function __construct(private GarageRepository $repository) {}
    public static function text(array $input, string $key, int $max = 255): string {
        $value = $input[$key] ?? '';
        if (!is_string($value) || trim($value) === '' || strlen(trim($value)) > $max)
            throw new DomainException('Thông tin ' . $key . ' bị trống hoặc quá dài.');
        return trim($value);
    }
    public static function number(array $input, string $key, bool $integer = false, float $min = 0): string {
        $value = $input[$key] ?? '';
        if (!is_scalar($value) || !preg_match($integer ? '/^\d+$/' : '/^\d+(\.\d{1,2})?$/', (string)$value)
            || (float)$value < $min || (float)$value > 999999999)
            throw new DomainException('Giá trị ' . $key . ' không hợp lệ.');
        return (string)$value;
    }
    public function save(string $page, array $input): void {
        $r = $this->repository;
        switch ($page) {
            case 'customers':
                $name = self::text($input, 'full_name', 100);
                $phone = self::text($input, 'phone', 20);
                if (!preg_match('/^[+0-9 ()-]{8,20}$/', $phone)) throw new DomainException('Số điện thoại không hợp lệ.');
                $r->query('INSERT INTO customers(full_name,phone) VALUES (?,?)', [$name, $phone]);
                break;
            case 'vehicles':
                $r->query('INSERT INTO vehicles(customer_id,plate,model) VALUES (?,?,?)',
                    [self::number($input, 'customer_id', true, 1), strtoupper(self::text($input, 'plate', 20)), self::text($input, 'model', 100)]);
                break;
            case 'inventory':
                $action = $input['action'] ?? 'create';
                if ($action === 'restock') {
                    $partId = self::number($input, 'part_id', true, 1);
                    $quantity = self::number($input, 'quantity', true, 1);
                    $updated = $r->query('UPDATE parts SET quantity=quantity+? WHERE id=? AND quantity <= 999999999-?',
                        [$quantity, $partId, $quantity]);
                    if ($updated->rowCount() !== 1)
                        throw new DomainException('Phụ tùng không tồn tại hoặc tổng tồn kho vượt giới hạn 999999999.');
                    break;
                }
                if ($action !== 'create') throw new DomainException('Thao tác không hợp lệ.');
                $r->query('INSERT INTO parts(name,quantity,price) VALUES (?,?,?)',
                    [self::text($input, 'name', 100), self::number($input, 'quantity', true), self::number($input, 'price')]);
                break;
            case 'repairs':
                $action = $input['action'] ?? 'create';
                if ($action === 'create') {
                    $r->query('INSERT INTO repairs(vehicle_id,description,labor_cost) VALUES (?,?,?)',
                        [self::number($input, 'vehicle_id', true, 1), self::text($input, 'description', 1000), self::number($input, 'labor_cost')]);
                } elseif ($action === 'complete' || $action === 'use_part') {
                    $this->repairAction($input, $action);
                } else throw new DomainException('Thao tác không hợp lệ.');
                break;
            case 'invoices':
                $this->invoice($input);
                break;
            default: throw new DomainException('Thao tác không hợp lệ.');
        }
    }
    private function repairAction(array $input, string $action): void {
        $r = $this->repository;
        $db = $r->connection();
        $db->beginTransaction();
        try {
            $id = self::number($input, 'repair_id', true, 1);
            $repair = $r->query('SELECT * FROM repairs WHERE id=? FOR UPDATE', [$id])->fetch();
            if (!$repair || $repair['status'] !== 'open') throw new DomainException('Chỉ được sửa phiếu đang mở.');
            if ($action === 'complete') {
                $r->query("UPDATE repairs SET status='completed' WHERE id=?", [$id]);
            } else {
                $partId = self::number($input, 'part_id', true, 1);
                $quantity = self::number($input, 'quantity', true, 1);
                $part = $r->query('SELECT * FROM parts WHERE id=? FOR UPDATE', [$partId])->fetch();
                if (!$part || $part['quantity'] < (int)$quantity) throw new DomainException('Phụ tùng không đủ tồn kho.');
                $r->query('UPDATE parts SET quantity=quantity-? WHERE id=?', [$quantity, $partId]);
                $r->query('INSERT INTO repair_parts(repair_id,part_id,quantity,unit_price) VALUES (?,?,?,?)',
                    [$id, $partId, $quantity, $part['price']]);
            }
            $db->commit();
        } catch (Throwable $error) { $db->rollBack(); throw $error; }
    }
    private function invoice(array $input): void {
        $r = $this->repository;
        $db = $r->connection();
        $db->beginTransaction();
        try {
            $id = self::number($input, 'repair_id', true, 1);
            $repair = $r->query('SELECT * FROM repairs WHERE id=? FOR UPDATE', [$id])->fetch();
            if (!$repair || $repair['status'] !== 'completed') throw new DomainException('Chỉ lập hóa đơn cho phiếu đã hoàn tất.');
            $r->query('INSERT INTO invoices(repair_id,total) SELECT r.id, r.labor_cost + COALESCE(SUM(p.quantity*p.unit_price),0) FROM repairs r LEFT JOIN repair_parts p ON p.repair_id=r.id WHERE r.id=? GROUP BY r.id,r.labor_cost', [$id]);
            $db->commit();
        } catch (Throwable $error) { $db->rollBack(); throw $error; }
    }
}
