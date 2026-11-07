<?php
declare(strict_types=1);

class GarageService
{
    private const MAX_VALUE = 999999999;
    private const INVALID_ACTION = 'Thao tác không hợp lệ.';

    public function __construct(private GarageRepository $repository) {}

    public static function text(array $input, string $key, int $max = 255): string
    {
        $value = $input[$key] ?? '';
        if (!is_string($value)) {
            throw new DomainException('Thông tin ' . $key . ' bị trống hoặc quá dài.');
        }
        if (!mb_check_encoding($value, 'UTF-8')) {
            throw new DomainException('Thông tin ' . $key . ' không phải UTF-8 hợp lệ.');
        }
        $value = trim($value);
        if ($value === '' || mb_strlen($value, 'UTF-8') > $max) {
            throw new DomainException('Thông tin ' . $key . ' bị trống hoặc quá dài.');
        }
        return $value;
    }

    public static function number(array $input, string $key, bool $integer = false, float $min = 0): string
    {
        $value = $input[$key] ?? '';
        $pattern = $integer ? '/^\d+$/D' : '/^\d+(\.\d{1,2})?$/D';
        if (!(is_string($value) || is_int($value) || is_float($value))
            || !preg_match($pattern, (string)$value)
            || (float)$value < $min
            || (float)$value > self::MAX_VALUE) {
            throw new DomainException('Giá trị ' . $key . ' không hợp lệ.');
        }
        return (string)$value;
    }

    public function save(string $page, array $input): void
    {
        match ($page) {
            'customers' => $this->saveCustomer($input),
            'vehicles'  => $this->saveVehicle($input),
            'inventory' => $this->saveInventory($input),
            'repairs'   => $this->saveRepair($input),
            'invoices'  => $this->createInvoice($input),
            default     => throw new DomainException(self::INVALID_ACTION),
        };
    }


    private function saveCustomer(array $input): void
    {
        $name = self::text($input, 'full_name', 100);
        $phone = self::text($input, 'phone', 20);
        if (!preg_match('/^[+0-9 ()-]{8,20}$/D', $phone)) {
            throw new DomainException('Số điện thoại không hợp lệ.');
        }
        $this->repository->query('INSERT INTO customers(full_name,phone) VALUES (?,?)', [$name, $phone]);
    }


    private function saveVehicle(array $input): void
    {
        $this->repository->query(
            'INSERT INTO vehicles(customer_id,plate,model) VALUES (?,?,?)',
            [
                self::number($input, 'customer_id', true, 1),
                strtoupper(self::text($input, 'plate', 20)),
                self::text($input, 'model', 100),
            ]
        );
    }

    private function saveInventory(array $input): void
    {
        match ($input['action'] ?? 'create') {
            'create'  => $this->createPart($input),
            'restock' => $this->restockPart($input),
            default   => throw new DomainException(self::INVALID_ACTION),
        };
    }

    private function createPart(array $input): void
    {
        $this->repository->query(
            'INSERT INTO parts(name,quantity,price) VALUES (?,?,?)',
            [
                self::text($input, 'name', 100),
                self::number($input, 'quantity', true),
                self::number($input, 'price'),
            ]
        );
    }

    private function restockPart(array $input): void
    {
        $partId = self::number($input, 'part_id', true, 1);
        $quantity = self::number($input, 'quantity', true, 1);
        $updated = $this->repository->query(
            'UPDATE parts SET quantity=quantity+? WHERE id=? AND quantity <= ? - ?',
            [$quantity, $partId, (string)self::MAX_VALUE, $quantity]
        );
        if ($updated->rowCount() !== 1) {
            throw new DomainException('Phụ tùng không tồn tại hoặc tổng tồn kho vượt giới hạn 999999999.');
        }
    }

    private function saveRepair(array $input): void
    {
        match ($input['action'] ?? 'create') {
            'create'   => $this->createRepair($input),
            'complete' => $this->completeRepair($input),
            'use_part' => $this->useRepairPart($input),
            default    => throw new DomainException(self::INVALID_ACTION),
        };
    }

    private function createRepair(array $input): void
    {
        $this->repository->query(
            'INSERT INTO repairs(vehicle_id,description,labor_cost) VALUES (?,?,?)',
            [
                self::number($input, 'vehicle_id', true, 1),
                self::text($input, 'description', 1000),
                self::number($input, 'labor_cost'),
            ]
        );
    }

    private function completeRepair(array $input): void
    {
        $this->withOpenRepair($input, function (string $repairId): void {
            $this->repository->query("UPDATE repairs SET status='completed' WHERE id=?", [$repairId]);
        });
    }

    private function useRepairPart(array $input): void
    {
        $this->withOpenRepair($input, function (string $repairId) use ($input): void {
            $partId = self::number($input, 'part_id', true, 1);
            $quantity = self::number($input, 'quantity', true, 1);
            $part = $this->repository->query('SELECT * FROM parts WHERE id=? FOR UPDATE', [$partId])->fetch();
            if (!$part || $part['quantity'] < (int)$quantity) {
                throw new DomainException('Phụ tùng không đủ tồn kho.');
            }
            $this->repository->query('UPDATE parts SET quantity=quantity-? WHERE id=?', [$quantity, $partId]);
            $this->repository->query(
                'INSERT INTO repair_parts(repair_id,part_id,quantity,unit_price) VALUES (?,?,?,?)',
                [$repairId, $partId, $quantity, $part['price']]
            );
        });
    }

    private function createInvoice(array $input): void
    {
        $this->transaction(function () use ($input): void {
            $repairId = self::number($input, 'repair_id', true, 1);
            $repair = $this->lockRepair($repairId);
            if (!$repair || $repair['status'] !== 'completed') {
                throw new DomainException('Chỉ lập hóa đơn cho phiếu đã hoàn tất.');
            }
            $this->repository->query(
                'INSERT INTO invoices(repair_id,total) '
                . 'SELECT r.id, r.labor_cost + COALESCE(SUM(p.quantity*p.unit_price),0) '
                . 'FROM repairs r LEFT JOIN repair_parts p ON p.repair_id=r.id '
                . 'WHERE r.id=? GROUP BY r.id,r.labor_cost',
                [$repairId]
            );
        });
    }

    private function transaction(callable $work): void
    {
        $connection = $this->repository->connection();
        $connection->beginTransaction();
        try {
            $work();
            $connection->commit();
        } catch (Throwable $error) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }
            throw $error;
        }
    }
    private function withOpenRepair(array $input, callable $work): void
    {
        $this->transaction(function () use ($input, $work): void {
            $repairId = self::number($input, 'repair_id', true, 1);
            $repair = $this->lockRepair($repairId);
            if (!$repair || $repair['status'] !== 'open') {
                throw new DomainException('Chỉ được sửa phiếu đang mở.');
            }
            $work($repairId);
        });
    }

    private function lockRepair(string $repairId): array|false
    {
        return $this->repository->query('SELECT * FROM repairs WHERE id=? FOR UPDATE', [$repairId])->fetch();
    }
}