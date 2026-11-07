<?php

declare(strict_types=1);

require_once __DIR__ . '/GarageTestCase.php';

final class AuthTest extends GarageTestCase
{
    private PDO $db;
    private AuthService $authService;

    protected function setUp(): void{
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        parent::setUp();

        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->db->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE,
                password TEXT,
                full_name TEXT,
                role TEXT
            )
        ");

        $repository = new GarageRepository($this->db);
        $this->authService = new AuthService($repository);
    }

    private function createUser(string $username, string $password): void
    {
        $statement = $this->db->prepare("
            INSERT INTO users (username, password, full_name, role)
            VALUES (?, ?, ?, ?)
        ");

        $statement->execute([
            $username,
            password_hash($password, PASSWORD_DEFAULT),
            'Test User',
            'admin'
        ]);
    }

    public function testLoginWithCorrectCredentials(): void
    {
        $this->createUser('testuser', '123456');

        self::assertTrue($this->authService->login('testuser', '123456'));
        self::assertSame('testuser', $_SESSION['user']['username']);
        self::assertSame('admin', $_SESSION['user']['role']);
    }

    public function testLoginWithWrongPassword(): void
    {
        $this->createUser('testuser', '123456');

        self::assertFalse($this->authService->login('testuser', 'wrongpass'));
        self::assertArrayNotHasKey('user', $_SESSION);
    }

    public function testLoginWithUnknownUsername(): void
    {
        self::assertFalse($this->authService->login('unknown', '123456'));
        self::assertArrayNotHasKey('user', $_SESSION);
    }

    public function testPasswordIsNotStoredInSession(): void
    {
        $this->createUser('testuser', '123456');

        $this->authService->login('testuser', '123456');

        self::assertArrayNotHasKey('password', $_SESSION['user']);
    }
}
