<?php

declare(strict_types=1);

require_once __DIR__ . '/GarageTestCase.php';

final class RepairInvoiceTest extends GarageTestCase
{
    private PDO $db;
    private GarageService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        $this->db->exec('CREATE TABLE repairs (
            id INTEGER PRIMARY KEY,
            vehicle_id INTEGER NOT NULL,
            description TEXT NOT NULL,
            labor_cost NUMERIC NOT NULL,
            status TEXT NOT NULL
        )');

        $this->db->exec('CREATE TABLE parts (
            id INTEGER PRIMARY KEY,
            name TEXT NOT NULL,
            quantity INTEGER NOT NULL,
            price NUMERIC NOT NULL
        )');

        $this->db->exec('CREATE TABLE repair_parts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            repair_id INTEGER NOT NULL,
            part_id INTEGER NOT NULL,
            quantity INTEGER NOT NULL,
            unit_price NUMERIC NOT NULL
        )');

        $this->db->exec('CREATE TABLE invoices (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            repair_id INTEGER NOT NULL UNIQUE,
            total NUMERIC NOT NULL
        )');

        $this->db->exec("INSERT INTO repairs VALUES
            (1, 1, 'Thay dầu', 500000, 'open'),
            (2, 1, 'Sửa phanh', 300000, 'completed')");

        $this->db->exec("INSERT INTO parts VALUES
            (1, 'Lọc dầu', 10, 120000)");

        $this->service = new GarageService(new GarageRepository($this->db));
    }

    public function testCompleteOpenRepair(): void
    {
        $this->service->save('repairs', [
            'action' => 'complete',
            'repair_id' => '1'
        ]);

        $status = $this->db->query(
            'SELECT status FROM repairs WHERE id=1'
        )->fetchColumn();

        self::assertSame('completed', $status);
    }

    public function testCannotCompleteMissingRepair(): void
    {
        $this->expectException(DomainException::class);

        $this->service->save('repairs', [
            'action' => 'complete',
            'repair_id' => '99'
        ]);
    }

    public function testCannotCompleteAlreadyCompletedRepair(): void
    {
        $this->expectException(DomainException::class);

        $this->service->save('repairs', [
            'action' => 'complete',
            'repair_id' => '2'
        ]);
    }

    public function testUsePartReducesInventory(): void
    {
        $this->service->save('repairs', [
            'action' => 'use_part',
            'repair_id' => '1',
            'part_id' => '1',
            'quantity' => '3'
        ]);

        $quantity = $this->db->query(
            'SELECT quantity FROM parts WHERE id=1'
        )->fetchColumn();

        self::assertSame(7, (int)$quantity);
    }

    public function testUsePartCreatesRepairPartRecord(): void
    {
        $this->service->save('repairs', [
            'action' => 'use_part',
            'repair_id' => '1',
            'part_id' => '1',
            'quantity' => '3'
        ]);

        $record = $this->db->query(
            'SELECT * FROM repair_parts WHERE repair_id=1'
        )->fetch();

        self::assertSame(1, (int)$record['part_id']);
        self::assertSame(3, (int)$record['quantity']);
        self::assertEquals(120000, $record['unit_price']);
    }

    public function testInsufficientInventoryRollsBackTransaction(): void
    {
        $this->expectException(DomainException::class);

        try {
            $this->service->save('repairs', [
                'action' => 'use_part',
                'repair_id' => '1',
                'part_id' => '1',
                'quantity' => '20'
            ]);
        } finally {
            $quantity = $this->db->query(
                'SELECT quantity FROM parts WHERE id=1'
            )->fetchColumn();

            $count = $this->db->query(
                'SELECT COUNT(*) FROM repair_parts'
            )->fetchColumn();

            self::assertSame(10, (int)$quantity);
            self::assertSame(0, (int)$count);
        }
    }

    public function testCannotUsePartForMissingRepair(): void
    {
        $this->expectException(DomainException::class);

        $this->service->save('repairs', [
            'action' => 'use_part',
            'repair_id' => '99',
            'part_id' => '1',
            'quantity' => '1'
        ]);
    }

    public function testCreateInvoiceWithCorrectTotal(): void
    {
        $this->service->save('repairs', [
            'action' => 'use_part',
            'repair_id' => '1',
            'part_id' => '1',
            'quantity' => '2'
        ]);

        $this->service->save('repairs', [
            'action' => 'complete',
            'repair_id' => '1'
        ]);

        $this->service->save('invoices', [
            'repair_id' => '1'
        ]);

        $total = $this->db->query(
            'SELECT total FROM invoices WHERE repair_id=1'
        )->fetchColumn();

        self::assertEquals(740000, $total);
    }

    public function testCannotCreateInvoiceForOpenRepair(): void
    {
        $this->expectException(DomainException::class);

        $this->service->save('invoices', [
            'repair_id' => '1'
        ]);
    }

    public function testCannotCreateInvoiceForMissingRepair(): void
    {
        $this->expectException(DomainException::class);

        $this->service->save('invoices', [
            'repair_id' => '99'
        ]);
    }
}
