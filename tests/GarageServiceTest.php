<?php

declare(strict_types=1);

require_once __DIR__ . '/GarageTestCase.php';

final class GarageServiceTest extends GarageTestCase
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

        $this->db->exec('
            CREATE TABLE customers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                full_name TEXT NOT NULL,
                phone TEXT NOT NULL
            )
        ');

        $this->db->exec('
            CREATE TABLE vehicles (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                customer_id INTEGER NOT NULL,
                plate TEXT NOT NULL,
                model TEXT NOT NULL
            )
        ');

        $this->db->exec('
            CREATE TABLE parts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                quantity INTEGER NOT NULL,
                price NUMERIC NOT NULL
            )
        ');

        $this->db->exec('
            CREATE TABLE repairs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                vehicle_id INTEGER NOT NULL,
                description TEXT NOT NULL,
                labor_cost NUMERIC NOT NULL,
                status TEXT DEFAULT "open"
            )
        ');

        $this->service = new GarageService(new GarageRepository($this->db));
    }

    public function testCreateCustomerSuccessfully(): void
    {
        $this->service->save('customers', [
            'full_name' => 'Nguyen Van An',
            'phone' => '0901234567'
        ]);

        $customer = $this->db->query('SELECT * FROM customers WHERE id=1')->fetch();

        self::assertSame('Nguyen Van An', $customer['full_name']);
        self::assertSame('0901234567', $customer['phone']);
    }

    public function testRejectInvalidCustomerPhone(): void
    {
        $this->expectException(DomainException::class);

        $this->service->save('customers', [
            'full_name' => 'Nguyen Van An',
            'phone' => 'abc123'
        ]);
    }

    public function testCreateVehicleConvertsPlateToUppercase(): void
    {
        $this->service->save('vehicles', [
            'customer_id' => '1',
            'plate' => '51a-12345',
            'model' => 'Toyota Vios'
        ]);

        $vehicle = $this->db->query('SELECT * FROM vehicles WHERE id=1')->fetch();

        self::assertSame('51A-12345', $vehicle['plate']);
        self::assertSame('Toyota Vios', $vehicle['model']);
    }

    public function testCreateInventoryPartSuccessfully(): void
    {
        $this->service->save('inventory', [
            'action' => 'create',
            'name' => 'Oil filter',
            'quantity' => '10',
            'price' => '120000'
        ]);

        $part = $this->db->query('SELECT * FROM parts WHERE id=1')->fetch();

        self::assertSame('Oil filter', $part['name']);
        self::assertSame(10, (int)$part['quantity']);
        self::assertEquals(120000, $part['price']);
    }

    public function testCreateRepairSuccessfully(): void
    {
        $this->service->save('repairs', [
            'action' => 'create',
            'vehicle_id' => '1',
            'description' => 'Oil change',
            'labor_cost' => '500000'
        ]);

        $repair = $this->db->query('SELECT * FROM repairs WHERE id=1')->fetch();

        self::assertSame(1, (int)$repair['vehicle_id']);
        self::assertSame('Oil change', $repair['description']);
        self::assertEquals(500000, $repair['labor_cost']);
        self::assertSame('open', $repair['status']);
    }

    public function testRejectInvalidInventoryAction(): void
    {
        $this->expectException(DomainException::class);

        $this->service->save('inventory', [
            'action' => 'delete'
        ]);
    }

    public function testRejectInvalidRepairAction(): void
    {
        $this->expectException(DomainException::class);

        $this->service->save('repairs', [
            'action' => 'delete'
        ]);
    }

    public function testRejectUnknownPage(): void
    {
        $this->expectException(DomainException::class);

        $this->service->save('unknown', []);
    }
}