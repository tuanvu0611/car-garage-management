<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;

require_once __DIR__ . '/GarageTestCase.php';

final class ValidationTest extends GarageTestCase
{
    public function testDecimalCostPreservesTwoDecimalPlaces(): void
    {
        self::assertSame('120000.50', GarageService::number(['cost' => '120000.50'], 'cost'));
    }

    public static function invalidNumbers(): array
    {
        return [
            'negative' => ['-1'], 'scientific notation' => ['1e3'],
            'not a number' => ['NaN'], 'excess precision' => ['0.001'],
            'array' => [[]], 'above maximum' => ['1000000000'],
        ];
    }

    #[DataProvider('invalidNumbers')]
    public function testInvalidCostThrowsDomainException(mixed $value): void
    {
        $this->expectException(DomainException::class);
        GarageService::number(['cost' => $value], 'cost');
    }

    #[DataProvider('invalidQuantities')]
    public function testQuantityMustBePositiveInteger(string $value): void
    {
        $this->expectException(DomainException::class);
        GarageService::number(['quantity' => $value], 'quantity', true, 1);
    }

    public static function invalidQuantities(): array
    {
        return ['fraction' => ['1.5'], 'zero' => ['0']];
    }

    #[DataProvider('invalidNames')]
    public function testInvalidNameThrowsDomainException(mixed $value): void
    {
        $this->expectException(DomainException::class);
        GarageService::text(['name' => $value], 'name');
    }

    public static function invalidNames(): array
    {
        return ['array' => [[]], 'blank' => ['  ']];
    }

    public static function roles(): array
    {
        return [
            'admin' => ['admin', true, true, true],
            'manager' => ['manager', true, true, true],
            'technician' => ['technician', false, false, true],
            'accountant' => ['accountant', false, true, false],
            'unknown' => ['unknown', false, false, false],
        ];
    }

    #[DataProvider('roles')]
    public function testRolePermissions(string $role, bool $inventory, bool $invoices, bool $repairs): void
    {
        $_SESSION['user'] = ['role' => $role];
        self::assertSame($inventory, AuthService::can('inventory'));
        self::assertSame($invoices, AuthService::can('invoices'));
        self::assertSame($repairs, AuthService::can('repairs'));
    }

    public function testGuestCannotAccessInventory(): void
    {
        self::assertFalse(AuthService::can('inventory'));
    }

    public function testHtmlSpecialCharactersAreEscaped(): void
    {
        self::assertSame('&lt;script&gt;', e('<script>'));
    }
}
