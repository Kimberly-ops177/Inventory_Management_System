<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/bootstrap.php';

use App\Models\PurchaseOrder;

echo "Step 1: Finding PO\n";
$po = PurchaseOrder::find(1);

echo "Step 2: Calling items()\n";
$items = $po->items();

echo "Step 3: Got items\n";
echo "Number of items: " . count($items) . "\n";

if (!empty($items)) {
    echo "First item:\n";
    print_r($items[0]);

    echo "\nStep 4: Calculating total\n";
    $total = $po->getTotalCost();
    echo "Total: KES " . number_format($total, 2) . "\n";
}

echo "\nDone!\n";
