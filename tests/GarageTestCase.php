<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

abstract class GarageTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }
}
