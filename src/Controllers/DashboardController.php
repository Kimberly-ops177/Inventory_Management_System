<?php
declare(strict_types=1);

namespace App\Controllers;

use App\View;
use App\Services\StockService;
use App\Models\StockAlert;
use App\Models\Product;
use App\Database;
use PDO;

class DashboardController
{
    private View $view;
    private StockService $stockService;
    private PDO $db;

    public function __construct()
    {
        $this->view = new View();
        $this->stockService = new StockService();
        $this->db = Database::connection();
    }

    public function index(): string
    {
        // Get dashboard stats
        $stmt = $this->db->query("SELECT COUNT(*) FROM products");
        $totalProducts = (int)$stmt->fetchColumn();

        $stmt = $this->db->query("SELECT COUNT(*) FROM stock_alerts WHERE is_resolved = 0");
        $lowStockAlerts = (int)$stmt->fetchColumn();

        $stmt = $this->db->query("SELECT COUNT(*) FROM sales_orders WHERE DATE(created_at) = CURDATE()");
        $todaysSalesOrders = (int)$stmt->fetchColumn();

        $stmt = $this->db->query("SELECT COUNT(*) FROM purchase_orders WHERE status IN ('draft', 'ordered')");
        $pendingPurchaseOrders = (int)$stmt->fetchColumn();

        $inventoryValue = $this->stockService->getStockValue();

        // Recent stock movements
        $stmt = $this->db->query("
            SELECT sm.*, p.name as product_name, p.sku
            FROM stock_movements sm
            JOIN products p ON sm.product_id = p.id
            ORDER BY sm.created_at DESC
            LIMIT 10
        ");
        $recentMovements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Low stock products
        $lowStockProducts = $this->stockService->getLowStockProducts();

        $content = $this->view->render('dashboard/index', [
            'totalProducts' => $totalProducts,
            'lowStockAlerts' => $lowStockAlerts,
            'todaysSalesOrders' => $todaysSalesOrders,
            'pendingPurchaseOrders' => $pendingPurchaseOrders,
            'inventoryValue' => $inventoryValue,
            'recentMovements' => $recentMovements,
            'lowStockProducts' => $lowStockProducts
        ]);

        return $this->view->layout('app', $content, ['title' => 'Dashboard']);
    }
}
