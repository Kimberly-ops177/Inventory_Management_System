<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/bootstrap.php';

$db = \App\Database::connection();

// Check purchase order items
$stmt = $db->query('SELECT COUNT(*) as count FROM purchase_order_items');
$result = $stmt->fetch();
echo "Purchase Order Items: " . $result['count'] . "\n";

// Check sales order items
$stmt = $db->query('SELECT COUNT(*) as count FROM sales_order_items');
$result = $stmt->fetch();
echo "Sales Order Items: " . $result['count'] . "\n";

// Check a sample PO with items
$stmt = $db->query('SELECT po.id, po.reference, COUNT(poi.id) as item_count, SUM(poi.quantity * poi.unit_cost) as total
                    FROM purchase_orders po
                    LEFT JOIN purchase_order_items poi ON po.id = poi.purchase_order_id
                    GROUP BY po.id
                    LIMIT 3');
echo "\nSample Purchase Orders:\n";
while ($row = $stmt->fetch()) {
    echo "  PO #{$row['reference']}: {$row['item_count']} items, Total: KES " . number_format($row['total'], 2) . "\n";
}

// Check a sample SO with items
$stmt = $db->query('SELECT so.id, so.reference, COUNT(soi.id) as item_count, SUM(soi.quantity * soi.unit_price) as total
                    FROM sales_orders so
                    LEFT JOIN sales_order_items soi ON so.id = soi.sales_order_id
                    GROUP BY so.id
                    LIMIT 3');
echo "\nSample Sales Orders:\n";
while ($row = $stmt->fetch()) {
    echo "  SO #{$row['reference']}: {$row['item_count']} items, Total: KES " . number_format($row['total'], 2) . "\n";
}
