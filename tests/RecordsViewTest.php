<?php

declare(strict_types=1);

require_once __DIR__ . '/GarageTestCase.php';

final class RecordsViewTest extends GarageTestCase
{
    public function testRestockFormRetainsSelectedPartAndEscapesInvalidQuantity(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['action' => 'restock', 'part_id' => '1', 'quantity' => '<script>'];
        $_SESSION['csrf'] = 'test';
        $page = 'inventory';
        $rows = [];
        $options = ['part_id' => [['id' => 1, 'label' => 'Oil filter']]];
        ob_start();
        try {
            require __DIR__ . '/../app/Views/records.php';
            $html = ob_get_contents();
        } finally {
            ob_end_clean();
        }
        self::assertStringContainsString('value="1" selected', $html);
        self::assertStringNotContainsString('value="<script>"', $html);
        self::assertSame(1, substr_count($html, 'value="&lt;script&gt;"'));
    }
}
