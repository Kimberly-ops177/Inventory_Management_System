<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/bootstrap.php';

use App\Models\Supplier;
use App\Models\Product;
use App\Database;

echo "=== Test Orders Seeder ===\n\n";

try {
    $db = Database::connection();

    // Get all suppliers and products
    $suppliers = Supplier::all();
    $products = Product::all();

    if (empty($suppliers) || empty($products)) {
        echo "❌ Error: No suppliers or products found. Run seed-kenyan-products.php first.\n";
        exit(1);
    }

    echo "Found " . count($suppliers) . " suppliers and " . count($products) . " products\n\n";

    // Create 6 Purchase Orders
    echo "Creating 6 Purchase Orders...\n";
    $purchaseOrders = [];

    $poData = [
        [
            'supplier_id' => $suppliers[0]['id'],
            'reference' => 'PO-20260201-1001',
            'items' => [
                ['product_id' => $products[0]['id'], 'quantity' => 50, 'unit_cost' => 150.00],
                ['product_id' => $products[1]['id'], 'quantity' => 40, 'unit_cost' => 120.00],
                ['product_id' => $products[2]['id'], 'quantity' => 30, 'unit_cost' => 280.00],
            ]
        ],
        [
            'supplier_id' => $suppliers[1]['id'],
            'reference' => 'PO-20260202-1002',
            'items' => [
                ['product_id' => $products[10]['id'], 'quantity' => 100, 'unit_cost' => 60.00],
                ['product_id' => $products[11]['id'], 'quantity' => 80, 'unit_cost' => 75.00],
                ['product_id' => $products[12]['id'], 'quantity' => 120, 'unit_cost' => 50.00],
            ]
        ],
        [
            'supplier_id' => $suppliers[2]['id'],
            'reference' => 'PO-20260203-1003',
            'items' => [
                ['product_id' => $products[13]['id'], 'quantity' => 200, 'unit_cost' => 30.00],
                ['product_id' => $products[14]['id'], 'quantity' => 100, 'unit_cost' => 140.00],
            ]
        ],
        [
            'supplier_id' => $suppliers[0]['id'],
            'reference' => 'PO-20260204-1004',
            'items' => [
                ['product_id' => $products[3]['id'], 'quantity' => 60, 'unit_cost' => 140.00],
                ['product_id' => $products[4]['id'], 'quantity' => 80, 'unit_cost' => 90.00],
                ['product_id' => $products[5]['id'], 'quantity' => 100, 'unit_cost' => 45.00],
            ]
        ],
        [
            'supplier_id' => $suppliers[3]['id'],
            'reference' => 'PO-20260205-1005',
            'items' => [
                ['product_id' => $products[16]['id'], 'quantity' => 500, 'unit_cost' => 95.00],
                ['product_id' => $products[17]['id'], 'quantity' => 30, 'unit_cost' => 1300.00],
            ]
        ],
        [
            'supplier_id' => $suppliers[1]['id'],
            'reference' => 'PO-20260206-1006',
            'items' => [
                ['product_id' => $products[6]['id'], 'quantity' => 150, 'unit_cost' => 35.00],
                ['product_id' => $products[7]['id'], 'quantity' => 80, 'unit_cost' => 360.00],
                ['product_id' => $products[8]['id'], 'quantity' => 100, 'unit_cost' => 90.00],
            ]
        ],
    ];

    // Get admin user ID
    $stmt = $db->prepare("SELECT id FROM users WHERE email = 'admin@example.com' LIMIT 1");
    $stmt->execute();
    $adminUser = $stmt->fetch();
    $userId = $adminUser['id'];

    foreach ($poData as $po) {
        // Create PO
        $stmt = $db->prepare("
            INSERT INTO purchase_orders (reference, supplier_id, status, created_by, created_at)
            VALUES (?, ?, 'draft', ?, NOW())
        ");
        $stmt->execute([$po['reference'], $po['supplier_id'], $userId]);
        $poId = (int)$db->lastInsertId();

        $totalCost = 0;
        foreach ($po['items'] as $item) {
            $stmt = $db->prepare("
                INSERT INTO purchase_order_items (purchase_order_id, product_id, quantity, unit_cost, received_quantity)
                VALUES (?, ?, ?, ?, 0)
            ");
            $stmt->execute([$poId, $item['product_id'], $item['quantity'], $item['unit_cost']]);
            $totalCost += $item['quantity'] * $item['unit_cost'];
        }

        $purchaseOrders[] = $poId;
        echo "  ✓ Created PO #{$po['reference']} - KES " . number_format($totalCost, 2) . "\n";
    }

    // Receive all Purchase Orders (add stock)
    echo "\nReceiving all Purchase Orders (adding stock)...\n";
    foreach ($purchaseOrders as $poId) {
        // Get PO items
        $stmt = $db->prepare("SELECT * FROM purchase_order_items WHERE purchase_order_id = ?");
        $stmt->execute([$poId]);
        $items = $stmt->fetchAll();

        foreach ($items as $item) {
            // Update received quantity
            $stmt = $db->prepare("UPDATE purchase_order_items SET received_quantity = quantity WHERE id = ?");
            $stmt->execute([$item['id']]);

            // Add stock movement
            $stmt = $db->prepare("
                INSERT INTO stock_movements (product_id, direction, quantity, source, source_id, created_at)
                VALUES (?, 'in', ?, 'purchase_order', ?, NOW())
            ");
            $stmt->execute([$item['product_id'], $item['quantity'], $poId]);
        }

        // Update PO status
        $stmt = $db->prepare("UPDATE purchase_orders SET status = 'received', ordered_at = NOW(), received_at = NOW() WHERE id = ?");
        $stmt->execute([$poId]);

        echo "  ✓ Received PO #$poId\n";
    }

    // Create 6 Sales Orders
    echo "\nCreating 6 Sales Orders...\n";

    $soData = [
        [
            'customer_name' => 'Jane Wanjiru',
            'reference' => 'SO-20260201-2001',
            'items' => [
                ['product_id' => $products[0]['id'], 'quantity' => 10, 'unit_price' => 200.00],
                ['product_id' => $products[1]['id'], 'quantity' => 5, 'unit_price' => 150.00],
                ['product_id' => $products[5]['id'], 'quantity' => 20, 'unit_price' => 60.00],
            ]
        ],
        [
            'customer_name' => 'John Kariuki',
            'reference' => 'SO-20260202-2002',
            'items' => [
                ['product_id' => $products[10]['id'], 'quantity' => 15, 'unit_price' => 80.00],
                ['product_id' => $products[11]['id'], 'quantity' => 10, 'unit_price' => 100.00],
            ]
        ],
        [
            'customer_name' => 'Mary Njeri',
            'reference' => 'SO-20260203-2003',
            'items' => [
                ['product_id' => $products[6]['id'], 'quantity' => 30, 'unit_price' => 50.00],
                ['product_id' => $products[7]['id'], 'quantity' => 2, 'unit_price' => 450.00],
            ]
        ],
        [
            'customer_name' => 'Peter Ochieng',
            'reference' => 'SO-20260204-2004',
            'items' => [
                ['product_id' => $products[13]['id'], 'quantity' => 50, 'unit_price' => 40.00],
                ['product_id' => $products[14]['id'], 'quantity' => 10, 'unit_price' => 180.00],
            ]
        ],
        [
            'customer_name' => 'Grace Akinyi',
            'reference' => 'SO-20260205-2005',
            'items' => [
                ['product_id' => $products[3]['id'], 'quantity' => 20, 'unit_price' => 180.00],
                ['product_id' => $products[4]['id'], 'quantity' => 15, 'unit_price' => 120.00],
                ['product_id' => $products[16]['id'], 'quantity' => 30, 'unit_price' => 100.00],
            ]
        ],
        [
            'customer_name' => 'David Mwangi',
            'reference' => 'SO-20260206-2006',
            'items' => [
                ['product_id' => $products[2]['id'], 'quantity' => 5, 'unit_price' => 350.00],
                ['product_id' => $products[8]['id'], 'quantity' => 10, 'unit_price' => 120.00],
                ['product_id' => $products[9]['id'], 'quantity' => 25, 'unit_price' => 30.00],
            ]
        ],
    ];

    foreach ($soData as $so) {
        // Create SO
        $stmt = $db->prepare("
            INSERT INTO sales_orders (reference, customer_name, status, created_by, created_at)
            VALUES (?, ?, 'draft', ?, NOW())
        ");
        $stmt->execute([$so['reference'], $so['customer_name'], $userId]);
        $soId = (int)$db->lastInsertId();

        $totalRevenue = 0;
        foreach ($so['items'] as $item) {
            $stmt = $db->prepare("
                INSERT INTO sales_order_items (sales_order_id, product_id, quantity, unit_price)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$soId, $item['product_id'], $item['quantity'], $item['unit_price']]);
            $totalRevenue += $item['quantity'] * $item['unit_price'];

            // Add stock movement (reduce stock)
            $stmt = $db->prepare("
                INSERT INTO stock_movements (product_id, direction, quantity, source, source_id, created_at)
                VALUES (?, 'out', ?, 'sales_order', ?, NOW())
            ");
            $stmt->execute([$item['product_id'], $item['quantity'], $soId]);
        }

        // Mark as fulfilled
        $stmt = $db->prepare("UPDATE sales_orders SET status = 'fulfilled', fulfilled_at = NOW() WHERE id = ?");
        $stmt->execute([$soId]);

        echo "  ✓ Created SO #{$so['reference']} for {$so['customer_name']} - KES " . number_format($totalRevenue, 2) . "\n";
    }

    echo "\n✓ Successfully created 12 test orders!\n\n";
    echo "=== Summary ===\n";
    echo "Purchase Orders: 6 (all received)\n";
    echo "Sales Orders: 6 (all fulfilled)\n";
    echo "\nYou can now:\n";
    echo "- View Stock Movements: http://localhost:8000/stock/movements\n";
    echo "- Check Stock Alerts: http://localhost:8000/stock/alerts\n";
    echo "- View Stock Report: http://localhost:8000/reports/stock\n";
    echo "- View Sales Report: http://localhost:8000/reports/sales\n";

} catch (Exception $e) {
    echo "\n✗ Seeding failed:\n";
    echo "  " . $e->getMessage() . "\n";
    echo "  " . $e->getTraceAsString() . "\n";
    exit(1);
}
