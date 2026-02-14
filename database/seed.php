<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/bootstrap.php';

use App\Models\Role;
use App\Models\User;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Product;
use App\Services\StockService;

echo "=== Database Seeder ===\n\n";

try {
    $stockService = new StockService();

    // Get or create admin role
    echo "Checking roles...\n";
    $adminRole = Role::where('name', 'admin')->first();
    if (!$adminRole) {
        $adminRole = Role::create(['name' => 'admin']);
        echo "✓ Admin role created (ID: {$adminRole->id})\n";
    } else {
        echo "✓ Admin role already exists (ID: {$adminRole->id})\n";
    }

    // Get or create admin user
    echo "\nChecking admin user...\n";
    $admin = User::where('email', 'admin@example.com')->first();
    if (!$admin) {
        $admin = new User();
        $admin->fill([
            'role_id' => $adminRole->id,
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'is_active' => 1
        ]);
        $admin->setPassword('password');
        $admin->save();
        echo "✓ Admin user created (email: admin@example.com, password: password)\n";
    } else {
        echo "✓ Admin user already exists (email: admin@example.com)\n";
    }

    // Create categories
    echo "\nCreating categories...\n";
    $categories = [
        ['name' => 'Food & Groceries', 'description' => 'Food items and groceries'],
        ['name' => 'Household Items', 'description' => 'Cleaning and household products'],
        ['name' => 'Utilities & Services', 'description' => 'Airtime, gas and other utilities'],
        ['name' => 'Fresh Produce', 'description' => 'Fresh vegetables and produce'],
    ];

    $categoryIds = [];
    foreach ($categories as $catData) {
        $category = Category::create($catData);
        $categoryIds[] = $category->id;
        echo "  - {$catData['name']}\n";
    }

    // Create suppliers
    echo "\nCreating suppliers...\n";
    $suppliers = [
        [
            'name' => 'Nairobi Distributors Ltd',
            'contact_name' => 'James Kamau',
            'email' => 'james@nairobidist.co.ke',
            'phone' => '+254-722-123456',
            'address' => 'Mombasa Road, Industrial Area, Nairobi'
        ],
        [
            'name' => 'Fresh Farms Kenya',
            'contact_name' => 'Mary Wanjiku',
            'email' => 'mary@freshfarms.co.ke',
            'phone' => '+254-733-234567',
            'address' => 'Limuru Road, Kiambu County'
        ],
        [
            'name' => 'Coastal Supplies',
            'contact_name' => 'Hassan Mohammed',
            'email' => 'hassan@coastalsupplies.co.ke',
            'phone' => '+254-711-345678',
            'address' => 'Nyali, Mombasa'
        ],
        [
            'name' => 'K-Gas Distributors',
            'contact_name' => 'Peter Omondi',
            'email' => 'peter@kgas.co.ke',
            'phone' => '+254-700-456789',
            'address' => 'Thika Road, Nairobi'
        ],
    ];

    $supplierIds = [];
    foreach ($suppliers as $suppData) {
        $supplier = Supplier::create($suppData);
        $supplierIds[] = $supplier->id;
        echo "  - {$suppData['name']}\n";
    }

    // Create products - 18 day-to-day Kenyan goods
    echo "\nCreating products...\n";
    $products = [
        // Food & Groceries
        [
            'category_id' => $categoryIds[0],
            'name' => 'Maize Flour (Unga) 2kg',
            'sku' => 'FOOD-001',
            'description' => 'Premium maize flour for ugali',
            'unit' => 'packet',
            'cost_price' => 150.00,
            'sale_price' => 200.00,
            'reorder_level' => 50
        ],
        [
            'category_id' => $categoryIds[0],
            'name' => 'Rice 1kg',
            'sku' => 'FOOD-002',
            'description' => 'Long grain white rice',
            'unit' => 'kg',
            'cost_price' => 120.00,
            'sale_price' => 150.00,
            'reorder_level' => 40
        ],
        [
            'category_id' => $categoryIds[0],
            'name' => 'Cooking Oil 1L',
            'sku' => 'FOOD-003',
            'description' => 'Vegetable cooking oil',
            'unit' => 'bottle',
            'cost_price' => 280.00,
            'sale_price' => 350.00,
            'reorder_level' => 30
        ],
        [
            'category_id' => $categoryIds[0],
            'name' => 'Sugar 1kg',
            'sku' => 'FOOD-004',
            'description' => 'White granulated sugar',
            'unit' => 'packet',
            'cost_price' => 140.00,
            'sale_price' => 180.00,
            'reorder_level' => 45
        ],
        [
            'category_id' => $categoryIds[0],
            'name' => 'Tea Leaves 250g',
            'sku' => 'FOOD-005',
            'description' => 'Kenyan black tea leaves',
            'unit' => 'packet',
            'cost_price' => 90.00,
            'sale_price' => 120.00,
            'reorder_level' => 35
        ],
        [
            'category_id' => $categoryIds[0],
            'name' => 'Milk 500ml',
            'sku' => 'FOOD-006',
            'description' => 'Fresh pasteurized milk',
            'unit' => 'packet',
            'cost_price' => 45.00,
            'sale_price' => 60.00,
            'reorder_level' => 60
        ],
        [
            'category_id' => $categoryIds[0],
            'name' => 'Bread 400g',
            'sku' => 'FOOD-007',
            'description' => 'White sliced bread',
            'unit' => 'loaf',
            'cost_price' => 35.00,
            'sale_price' => 50.00,
            'reorder_level' => 40
        ],
        [
            'category_id' => $categoryIds[0],
            'name' => 'Eggs (Tray of 30)',
            'sku' => 'FOOD-008',
            'description' => 'Fresh chicken eggs',
            'unit' => 'tray',
            'cost_price' => 360.00,
            'sale_price' => 450.00,
            'reorder_level' => 20
        ],
        [
            'category_id' => $categoryIds[0],
            'name' => 'Beans 1kg',
            'sku' => 'FOOD-009',
            'description' => 'Red kidney beans',
            'unit' => 'kg',
            'cost_price' => 90.00,
            'sale_price' => 120.00,
            'reorder_level' => 35
        ],
        [
            'category_id' => $categoryIds[0],
            'name' => 'Salt 500g',
            'sku' => 'FOOD-010',
            'description' => 'Iodized table salt',
            'unit' => 'packet',
            'cost_price' => 20.00,
            'sale_price' => 30.00,
            'reorder_level' => 50
        ],

        // Fresh Produce
        [
            'category_id' => $categoryIds[3],
            'name' => 'Tomatoes 1kg',
            'sku' => 'PROD-001',
            'description' => 'Fresh ripe tomatoes',
            'unit' => 'kg',
            'cost_price' => 60.00,
            'sale_price' => 80.00,
            'reorder_level' => 25
        ],
        [
            'category_id' => $categoryIds[3],
            'name' => 'Onions 1kg',
            'sku' => 'PROD-002',
            'description' => 'Red onions',
            'unit' => 'kg',
            'cost_price' => 75.00,
            'sale_price' => 100.00,
            'reorder_level' => 30
        ],
        [
            'category_id' => $categoryIds[3],
            'name' => 'Potatoes 1kg',
            'sku' => 'PROD-003',
            'description' => 'Irish potatoes',
            'unit' => 'kg',
            'cost_price' => 50.00,
            'sale_price' => 70.00,
            'reorder_level' => 40
        ],

        // Household Items
        [
            'category_id' => $categoryIds[1],
            'name' => 'Laundry Soap Bar',
            'sku' => 'HOME-001',
            'description' => 'Blue washing soap bar',
            'unit' => 'bar',
            'cost_price' => 30.00,
            'sale_price' => 40.00,
            'reorder_level' => 60
        ],
        [
            'category_id' => $categoryIds[1],
            'name' => 'Toilet Paper (4 rolls)',
            'sku' => 'HOME-002',
            'description' => 'Soft tissue toilet paper',
            'unit' => 'pack',
            'cost_price' => 140.00,
            'sale_price' => 180.00,
            'reorder_level' => 30
        ],
        [
            'category_id' => $categoryIds[1],
            'name' => 'Charcoal 4kg',
            'sku' => 'HOME-003',
            'description' => 'Premium cooking charcoal',
            'unit' => 'bag',
            'cost_price' => 150.00,
            'sale_price' => 200.00,
            'reorder_level' => 25
        ],

        // Utilities & Services
        [
            'category_id' => $categoryIds[2],
            'name' => 'Airtime Card KES 100',
            'sku' => 'UTIL-001',
            'description' => 'Mobile airtime voucher',
            'unit' => 'card',
            'cost_price' => 95.00,
            'sale_price' => 100.00,
            'reorder_level' => 100
        ],
        [
            'category_id' => $categoryIds[2],
            'name' => 'LPG Gas 6kg Refill',
            'sku' => 'UTIL-002',
            'description' => 'Cooking gas cylinder refill',
            'unit' => 'refill',
            'cost_price' => 1300.00,
            'sale_price' => 1500.00,
            'reorder_level' => 10
        ],
    ];

    $productIds = [];
    foreach ($products as $prodData) {
        $product = Product::create($prodData);
        $productIds[] = $product->id;
        echo "  - {$prodData['name']} ({$prodData['sku']})\n";
    }

    // Add initial stock
    echo "\nAdding initial stock...\n";
    $initialStock = [
        $productIds[0] => 100,  // Maize Flour
        $productIds[1] => 80,   // Rice
        $productIds[2] => 60,   // Cooking Oil
        $productIds[3] => 90,   // Sugar
        $productIds[4] => 70,   // Tea Leaves
        $productIds[5] => 120,  // Milk
        $productIds[6] => 80,   // Bread
        $productIds[7] => 40,   // Eggs
        $productIds[8] => 75,   // Beans
        $productIds[9] => 100,  // Salt
        $productIds[10] => 50,  // Tomatoes
        $productIds[11] => 60,  // Onions
        $productIds[12] => 85,  // Potatoes
        $productIds[13] => 120, // Soap
        $productIds[14] => 50,  // Toilet Paper
        $productIds[15] => 45,  // Charcoal
        $productIds[16] => 200, // Airtime
        $productIds[17] => 20,  // LPG Gas
    ];

    foreach ($initialStock as $productId => $quantity) {
        $stockService->recordStockMovement(
            $productId,
            'in',
            $quantity,
            'initial_stock',
            null,
            null
        );
        echo "  - Product ID $productId: $quantity units\n";
    }

    echo "\n✓ Seeding completed successfully!\n";
    echo "\nLogin credentials:\n";
    echo "  Email: admin@example.com\n";
    echo "  Password: password\n";

} catch (Exception $e) {
    echo "\n✗ Seeding failed:\n";
    echo "  " . $e->getMessage() . "\n";
    exit(1);
}
