<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/bootstrap.php';

use App\Models\PurchaseOrder;
use App\Models\SalesOrder;

// Test Purchase Order
echo "=== Testing Purchase Order Model ===\n";
$po = PurchaseOrder::find(1);
if ($po) {
    echo "Found PO: " . $po->reference . "\n";
    $items = $po->items();
    echo "Number of items: " . count($items) . "\n";
    echo "Items data:\n";
    print_r($items);
    echo "Total Cost: KES " . number_format($po->getTotalCost(), 2) . "\n";
} else {
    echo "PO not found\n";
}

echo "\n=== Testing Sales Order Model ===\n";
$so = SalesOrder::find(1);
if ($so) {
    echo "Found SO: " . $so->reference . "\n";
    $items = $so->items();
    echo "Number of items: " . count($items) . "\n";
    echo "Items data:\n";
    print_r($items);
    echo "Total Revenue: KES " . number_format($so->getTotalRevenue(), 2) . "\n";
} else {
    echo "SO not found\n";
}
