<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/bootstrap.php';

use App\Router;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\HealthController;
use App\Controllers\Api\ProductController;
use App\Controllers\Api\CategoryController;
use App\Controllers\Api\SupplierController;
use App\Controllers\Api\PurchaseOrderController;
use App\Controllers\Api\SalesOrderController;
use App\Controllers\Api\StockController;
use App\Controllers\Api\DashboardController as ApiDashboardController;
use App\Auth;

$router = new Router();

// Health Check (no auth required)
$healthController = new HealthController();
$router->get('/health', fn() => $healthController->check()->send());

// Authentication Routes
$authController = new AuthController(new Auth());
$router->get('/login', fn() => $authController->showLoginForm());
$router->post('/login', fn() => $authController->login());
$router->post('/logout', fn() => $authController->logout());

// Simple auth middleware
$authMiddleware = function() {
    if (!isset($_SESSION['user'])) {
        header('Location: /login');
        exit;
    }
};

// Handle method override for PUT/DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method'])) {
    $_SERVER['REQUEST_METHOD'] = strtoupper($_POST['_method']);
}

// Web Routes (with auth)
$dashboardController = new DashboardController();
$router->get('/', fn() => $dashboardController->index(), [$authMiddleware]);

// Web Routes - Products
$productController = new \App\Controllers\ProductController();
$router->get('/products', fn() => $productController->index(), [$authMiddleware]);
$router->get('/products/create', fn() => $productController->create(), [$authMiddleware]);
$router->post('/products', fn() => $productController->store(), [$authMiddleware]);
$router->get('/products/:id/edit', fn($id) => $productController->edit((int)$id), [$authMiddleware]);
$router->put('/products/:id', fn($id) => $productController->update((int)$id), [$authMiddleware]);
$router->post('/products/:id/delete', fn($id) => $productController->destroy((int)$id), [$authMiddleware]);

// Web Routes - Categories
$categoryController = new \App\Controllers\CategoryController();
$router->get('/categories', fn() => $categoryController->index(), [$authMiddleware]);
$router->get('/categories/create', fn() => $categoryController->create(), [$authMiddleware]);
$router->post('/categories', fn() => $categoryController->store(), [$authMiddleware]);
$router->get('/categories/:id/edit', fn($id) => $categoryController->edit((int)$id), [$authMiddleware]);
$router->put('/categories/:id', fn($id) => $categoryController->update((int)$id), [$authMiddleware]);
$router->post('/categories/:id/delete', fn($id) => $categoryController->destroy((int)$id), [$authMiddleware]);

// Web Routes - Suppliers
$supplierController = new \App\Controllers\SupplierController();
$router->get('/suppliers', fn() => $supplierController->index(), [$authMiddleware]);
$router->get('/suppliers/create', fn() => $supplierController->create(), [$authMiddleware]);
$router->post('/suppliers', fn() => $supplierController->store(), [$authMiddleware]);
$router->get('/suppliers/:id/edit', fn($id) => $supplierController->edit((int)$id), [$authMiddleware]);
$router->put('/suppliers/:id', fn($id) => $supplierController->update((int)$id), [$authMiddleware]);
$router->post('/suppliers/:id/delete', fn($id) => $supplierController->destroy((int)$id), [$authMiddleware]);

// Web Routes - Purchase Orders
$purchaseOrderController = new \App\Controllers\PurchaseOrderController();
$router->get('/purchase-orders', fn() => $purchaseOrderController->index(), [$authMiddleware]);
$router->get('/purchase-orders/create', fn() => $purchaseOrderController->create(), [$authMiddleware]);
$router->get('/purchase-orders/:id', fn($id) => $purchaseOrderController->show((int)$id), [$authMiddleware]);

// Web Routes - Sales Orders
$salesOrderController = new \App\Controllers\SalesOrderController();
$router->get('/sales-orders', fn() => $salesOrderController->index(), [$authMiddleware]);
$router->get('/sales-orders/create', fn() => $salesOrderController->create(), [$authMiddleware]);
$router->get('/sales-orders/:id', fn($id) => $salesOrderController->show((int)$id), [$authMiddleware]);

// Web Routes - Stock
$stockController = new \App\Controllers\StockController();
$router->get('/stock/movements', fn() => $stockController->movements(), [$authMiddleware]);
$router->get('/stock/alerts', fn() => $stockController->alerts(), [$authMiddleware]);
$router->post('/stock/check-alerts', fn() => $stockController->checkAlerts(), [$authMiddleware]);

// Web Routes - Reports
$reportController = new \App\Controllers\ReportController();
$router->get('/reports/stock', fn() => $reportController->stockReport(), [$authMiddleware]);
$router->get('/reports/sales', fn() => $reportController->salesReport(), [$authMiddleware]);

// API Routes - Products
$productApi = new ProductController();
$router->get('/api/products', fn() => $productApi->index()->send());
$router->get('/api/products/:id', fn($id) => $productApi->show((int)$id)->send());
$router->post('/api/products', fn() => $productApi->store()->send());
$router->put('/api/products/:id', fn($id) => $productApi->update((int)$id)->send());
$router->delete('/api/products/:id', fn($id) => $productApi->destroy((int)$id)->send());
$router->get('/api/products/:id/stock', fn($id) => $productApi->stock((int)$id)->send());

// API Routes - Categories
$categoryApi = new CategoryController();
$router->get('/api/categories', fn() => $categoryApi->index()->send());
$router->get('/api/categories/:id', fn($id) => $categoryApi->show((int)$id)->send());
$router->post('/api/categories', fn() => $categoryApi->store()->send());
$router->put('/api/categories/:id', fn($id) => $categoryApi->update((int)$id)->send());
$router->delete('/api/categories/:id', fn($id) => $categoryApi->destroy((int)$id)->send());

// API Routes - Suppliers
$supplierApi = new SupplierController();
$router->get('/api/suppliers', fn() => $supplierApi->index()->send());
$router->get('/api/suppliers/:id', fn($id) => $supplierApi->show((int)$id)->send());
$router->post('/api/suppliers', fn() => $supplierApi->store()->send());
$router->put('/api/suppliers/:id', fn($id) => $supplierApi->update((int)$id)->send());
$router->delete('/api/suppliers/:id', fn($id) => $supplierApi->destroy((int)$id)->send());

// API Routes - Purchase Orders
$purchaseOrderApi = new PurchaseOrderController();
$router->get('/api/purchase-orders', fn() => $purchaseOrderApi->index()->send());
$router->get('/api/purchase-orders/:id', fn($id) => $purchaseOrderApi->show((int)$id)->send());
$router->post('/api/purchase-orders', fn() => $purchaseOrderApi->store()->send());
$router->put('/api/purchase-orders/:id', fn($id) => $purchaseOrderApi->update((int)$id)->send());
$router->delete('/api/purchase-orders/:id', fn($id) => $purchaseOrderApi->destroy((int)$id)->send());
$router->post('/api/purchase-orders/:id/receive', fn($id) => $purchaseOrderApi->receive((int)$id)->send());

// API Routes - Sales Orders
$salesOrderApi = new SalesOrderController();
$router->get('/api/sales-orders', fn() => $salesOrderApi->index()->send());
$router->get('/api/sales-orders/:id', fn($id) => $salesOrderApi->show((int)$id)->send());
$router->post('/api/sales-orders', fn() => $salesOrderApi->store()->send());
$router->put('/api/sales-orders/:id', fn($id) => $salesOrderApi->update((int)$id)->send());
$router->delete('/api/sales-orders/:id', fn($id) => $salesOrderApi->destroy((int)$id)->send());
$router->post('/api/sales-orders/:id/fulfill', fn($id) => $salesOrderApi->fulfill((int)$id)->send());

// API Routes - Stock
$stockApi = new StockController();
$router->get('/api/stock/movements', fn() => $stockApi->movements()->send());
$router->get('/api/stock/alerts', fn() => $stockApi->alerts()->send());
$router->post('/api/stock/alerts/:id/resolve', fn($id) => $stockApi->resolveAlert((int)$id)->send());
$router->post('/api/stock/check-alerts', fn() => $stockApi->checkAlerts()->send());

// API Routes - Dashboard
$dashboardApi = new ApiDashboardController();
$router->get('/api/dashboard/stats', fn() => $dashboardApi->stats()->send());

// Dispatch
$response = $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');

echo $response;
