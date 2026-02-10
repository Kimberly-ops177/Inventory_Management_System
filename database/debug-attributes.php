<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/bootstrap.php';

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;

// Test Purchase Order
echo "=== Testing PurchaseOrder Attributes ===\n";
$po = PurchaseOrder::find(1);
if ($po) {
    echo "PO attributes:\n";
    print_r($po->toArray());

    echo "\nDirect query for items:\n";
    $items = PurchaseOrderItem::where('purchase_order_id', 1);
    echo "Found " . count($items) . " items\n";
    print_r($items);
}
