<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/bootstrap.php';

use App\Models\PurchaseOrder;

echo "Step 1: Finding PO\n";
$po = PurchaseOrder::find(1);

echo "Step 2: Got PO\n";
echo "Reference: " . ($po ? $po->reference : 'null') . "\n";

echo "Step 3: Getting attributes\n";
$attrs = $po->toArray();
echo "Has ID: " . (isset($attrs['id']) ? 'YES' : 'NO') . "\n";

echo "Step 4: Done\n";
