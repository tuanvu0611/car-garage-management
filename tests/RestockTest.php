<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;

require_once __DIR__ . '/GarageTestCase.php';

final class RestockTest extends GarageTestCase
{
    private PDO $db;
    private GarageService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $this->db->exec('CREATE TABLE parts(id INTEGER PRIMARY KEY, quantity INTEGER NOT NULL, price NUMERIC NOT NULL)');
        $this->db->exec('INSERT INTO parts VALUES (1, 8, 120000)');
        $this->service = new GarageService(new GarageRepository($this->db));
    }

    public function testRestockIncreasesQuantityWithoutChangingPrice(): void
    {
        $this->restock('5');
        $this->assertInventory(13);
    }

    public static function invalidQuantities(): array
    {
        return [
            'zero' => ['0'], 'negative' => ['-2'], 'fraction' => ['1.5'],
            'array' => [[]], 'above maximum' => ['1000000000'],
            'total overflows maximum' => ['999999999'],
        ];
    }

    #[DataProvider('invalidQuantities')]
    public function testRejectedRestockLeavesInventoryUnchanged(mixed $quantity): void
    {
        $this->expectException(DomainException::class);
        try {
            $this->restock($quantity);
        } finally {
            $this->assertInventory(8);
        }
    }

    public function testMissingPartThrowsDomainException(): void
    {
        $this->expectException(DomainException::class);
        try {
            $this->restock('1', '99');
        } finally {
            $this->assertInventory(8);
        }
    }

    private function restock(mixed $quantity, string $partId = '1'): void
    {
        $this->service->save('inventory', ['action' => 'restock', 'part_id' => $partId, 'quantity' => $quantity]);
    }

    private function assertInventory(int $quantity): void
    {
        $part = $this->db->query('SELECT quantity, price FROM parts WHERE id=1')->fetch(PDO::FETCH_ASSOC);
        self::assertSame($quantity, (int) $part['quantity']);
        self::assertSame(120000, (int) $part['price']);
    }
    public function testRestockAcceptsExactInventoryLimit(): void
    {
        $this->restock('999999991');
        $this->assertInventory(999999999);
    }
}
