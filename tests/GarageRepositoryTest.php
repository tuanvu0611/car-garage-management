<?php

declare(strict_types=1);

require_once __DIR__ . '/GarageTestCase.php';

final class GarageRepositoryTest extends GarageTestCase
{
    private PDO $db;
    private GarageRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        $this->db->sqliteCreateFunction('CONCAT', function (...$values) {
            return implode('', $values);
        });

        $this->db->exec('
            CREATE TABLE customers (
                id INTEGER PRIMARY KEY,
                full_name TEXT NOT NULL,
                phone TEXT NOT NULL
            )
        ');

        $this->db->exec('
            CREATE TABLE vehicles (
                id INTEGER PRIMARY KEY,
                plate TEXT NOT NULL,
                model TEXT NOT NULL,
                customer_id INTEGER NOT NULL
            )
        ');

        $this->db->exec('
            CREATE TABLE repairs (
                id INTEGER PRIMARY KEY,
                vehicle_id INTEGER NOT NULL,
                description TEXT NOT NULL,
                labor_cost NUMERIC NOT NULL,
                status TEXT NOT NULL
            )
        ');

        $this->db->exec('
            CREATE TABLE parts (
                id INTEGER PRIMARY KEY,
                name TEXT NOT NULL,
                quantity INTEGER NOT NULL,
                price NUMERIC NOT NULL
            )
        ');

        $this->db->exec('
            CREATE TABLE invoices (
                id INTEGER PRIMARY KEY,
                repair_id INTEGER NOT NULL,
                total NUMERIC NOT NULL,
                created_at TEXT
            )
        ');

        $this->db->exec("
            INSERT INTO customers VALUES (1, 'Nguyen Van An', '0901234567');
            INSERT INTO vehicles VALUES (1, '51A-12345', 'Toyota Vios', 1);
            INSERT INTO repairs VALUES (1, 1, 'Thay dau', 500000, 'completed');
            INSERT INTO parts VALUES (1, 'Loc dau', 10, 120000);
            INSERT INTO invoices VALUES (1, 1, 740000, '2026-11-07');
        ");

        $this->repository = new GarageRepository($this->db);
    }

    public function testConnectionReturnsSamePDO(): void
    {
        self::assertSame($this->db, $this->repository->connection());
    }

    public function testQueryExecutesSqlWithParameters(): void
    {
        $result = $this->repository->query(
            'SELECT full_name FROM customers WHERE id=?',
            [1]
        )->fetchColumn();

        self::assertSame('Nguyen Van An', $result);
    }

    public function testRowsReturnsCustomers(): void
    {
        $rows = $this->repository->rows('customers');

        self::assertCount(1, $rows);
        self::assertSame('Nguyen Van An', $rows[0]['full_name']);
    }

    public function testRowsReturnsVehicles(): void
    {
        $rows = $this->repository->rows('vehicles');

        self::assertCount(1, $rows);
        self::assertSame('51A-12345', $rows[0]['plate']);
        self::assertSame('Nguyen Van An', $rows[0]['customer']);
    }

    public function testRowsReturnsRepairs(): void
    {
        $rows = $this->repository->rows('repairs');

        self::assertCount(1, $rows);
        self::assertSame('completed', $rows[0]['status']);
    }

    public function testRowsReturnsInventory(): void
    {
        $rows = $this->repository->rows('inventory');

        self::assertCount(1, $rows);
        self::assertSame('Loc dau', $rows[0]['name']);
        self::assertSame(10, (int)$rows[0]['quantity']);
    }

    public function testRowsReturnsInvoices(): void
    {
        $rows = $this->repository->rows('invoices');

        self::assertCount(1, $rows);
        self::assertEquals(740000, $rows[0]['total']);
    }

    public function testOptionsReturnsAllSelectionGroups(): void
    {
        $options = $this->repository->options();

        self::assertArrayHasKey('customer_id', $options);
        self::assertArrayHasKey('vehicle_id', $options);
        self::assertArrayHasKey('repair_id', $options);
        self::assertArrayHasKey('part_id', $options);

        self::assertCount(1, $options['customer_id']);
        self::assertCount(1, $options['vehicle_id']);
        self::assertCount(1, $options['repair_id']);
        self::assertCount(1, $options['part_id']);
    }

    public function testOptionsReturnsCorrectLabels(): void
    {
        $options = $this->repository->options();

        self::assertSame(
            'Nguyen Van An - 0901234567',
            $options['customer_id'][0]['label']
        );

        self::assertSame(
            '51A-12345',
            $options['vehicle_id'][0]['label']
        );

        self::assertSame(
            '#1 - 51A-12345',
            $options['repair_id'][0]['label']
        );

        self::assertSame(
            'Loc dau (còn 10)',
            $options['part_id'][0]['label']
        );
    }
}