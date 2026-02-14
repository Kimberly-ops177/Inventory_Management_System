<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Http\JsonResponse;
use App\Models\Product;
use App\Models\StockAlert;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\StockMovement;
use App\Services\StockService;
use App\Database;
use PDO;

class DashboardController
{
    private StockService $stockService;
    private PDO $db;

    public function __construct()
    {
        $this->stockService = new StockService();
        $this->db = Database::connection();
    }

    public function stats(): JsonResponse
    {
        // Total products count
        $stmt = $this->db->query("SELECT COUNT(*) FROM products");
        $totalProducts = (int)$stmt->fetchColumn();

        // Low stock alerts count
        $stmt = $this->db->query("SELECT COUNT(*) FROM stock_alerts WHERE is_resolved = FALSE");
        $lowStockAlerts = (int)$stmt->fetchColumn();

        // Today's sales orders
        $stmt = $this->db->query("SELECT COUNT(*) FROM sales_orders WHERE DATE(created_at) = CURRENT_DATE");
        $todaysSalesOrders = (int)$stmt->fetchColumn();

        // Pending purchase orders
        $stmt = $this->db->query("SELECT COUNT(*) FROM purchase_orders WHERE status IN ('draft', 'ordered')");
        $pendingPurchaseOrders = (int)$stmt->fetchColumn();

        // Total inventory value
        $inventoryValue = $this->stockService->getStockValue();

        // Recent stock movements (last 10)
        $stmt = $this->db->query("
            SELECT sm.*, p.name as product_name, p.sku
            FROM stock_movements sm
            JOIN products p ON sm.product_id = p.id
            ORDER BY sm.created_at DESC
            LIMIT 10
        ");
        $recentMovements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Top selling products (by quantity)
        $stmt = $this->db->query("
            SELECT p.id, p.name, p.sku, SUM(soi.quantity) as total_sold
            FROM products p
            JOIN sales_order_items soi ON p.id = soi.product_id
            JOIN sales_orders so ON soi.sales_order_id = so.id
            WHERE so.status = 'fulfilled'
            GROUP BY p.id
            ORDER BY total_sold DESC
            LIMIT 5
        ");
        $topSellingProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Stock level by category
        $stmt = $this->db->query("
            SELECT
                c.name as category_name,
                COUNT(DISTINCT p.id) as product_count,
                COALESCE(SUM(
                    CASE WHEN sm.direction = 'in' THEN sm.quantity ELSE -sm.quantity END
                ), 0) as total_stock
            FROM categories c
            LEFT JOIN products p ON c.id = p.category_id
            LEFT JOIN stock_movements sm ON p.id = sm.product_id
            GROUP BY c.id
            ORDER BY c.name
        ");
        $stockByCategory = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return JsonResponse::success([
            'total_products' => $totalProducts,
            'low_stock_alerts' => $lowStockAlerts,
            'todays_sales_orders' => $todaysSalesOrders,
            'pending_purchase_orders' => $pendingPurchaseOrders,
            'inventory_value' => round($inventoryValue, 2),
            'recent_movements' => $recentMovements,
            'top_selling_products' => $topSellingProducts,
            'stock_by_category' => $stockByCategory,
        ]);
    }
}
