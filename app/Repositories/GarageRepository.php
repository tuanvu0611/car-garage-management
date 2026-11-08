<?php
declare(strict_types=1);
class GarageRepository {
    public function __construct(private PDO $db) {}
    public function query(string $sql, array $parameters = []): PDOStatement {
        $statement = $this->db->prepare($sql);
        $statement->execute($parameters);
        return $statement;
    }
    public function connection(): PDO { return $this->db; }
    public function rows(string $page): array {
        $queries = [
            'customers' => 'SELECT id, full_name, phone FROM customers ORDER BY id DESC',
            'vehicles' => 'SELECT v.id, v.plate, v.model, c.full_name AS customer FROM vehicles v JOIN customers c ON c.id=v.customer_id ORDER BY v.id DESC',
            'repairs' => 'SELECT r.id, v.plate, r.description, r.labor_cost, r.status FROM repairs r JOIN vehicles v ON v.id=r.vehicle_id ORDER BY r.id DESC',
            'inventory' => 'SELECT id, name, quantity, price FROM parts ORDER BY id DESC',
            'invoices' => 'SELECT id, repair_id, total, created_at FROM invoices ORDER BY id DESC',
        ];
        return $this->query($queries[$page])->fetchAll();
    }
    public function options(): array {
        return [
            'customer_id' => $this->query('SELECT id, CONCAT(full_name, " - ", phone) AS label FROM customers ORDER BY full_name')->fetchAll(),
            'vehicle_id' => $this->query('SELECT id, plate AS label FROM vehicles ORDER BY plate')->fetchAll(),
            'repair_id' => $this->query('SELECT r.id, CONCAT("#", r.id, " - ", v.plate) AS label FROM repairs r JOIN vehicles v ON v.id=r.vehicle_id ORDER BY r.id DESC')->fetchAll(),
            'part_id' => $this->query('SELECT id, CONCAT(name, " (còn ", quantity, ")") AS label FROM parts ORDER BY name')->fetchAll(),
        ];
    }
}
