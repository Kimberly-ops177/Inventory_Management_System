<?php
declare(strict_types=1);

namespace App\Services;

use App\Database;
use PDO;

class ReportService
{
    private PDO $db;
    private StockService $stockService;

    public function __construct()
    {
        $this->db = Database::connection();
        $this->stockService = new StockService();
    }

    /**
     * Generate stock report with current levels and values
     */
    public function stockReport(array $filters = []): array
    {
        $stockLevels = $this->stockService->getAllStockLevels();
        $totalValue = $this->stockService->getStockValue();

        $lowStockItems = array_filter($stockLevels, function($item) {
            return $item['is_low_stock'] == 1;
        });

        return [
            'stock_levels' => $stockLevels,
            'total_inventory_value' => round($totalValue, 2),
            'low_stock_count' => count($lowStockItems),
            'total_products' => count($stockLevels),
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Generate sales report for a date range
     */
    public function salesReport(string $dateFrom, string $dateTo): array
    {
        $stmt = $this->db->prepare("
            SELECT
                so.id,
                so.reference,
                so.customer_name,
                so.created_at,
                so.fulfilled_at,
                SUM(soi.quantity * soi.unit_price) as total_revenue
            FROM sales_orders so
            JOIN sales_order_items soi ON so.id = soi.sales_order_id
            WHERE so.status = 'fulfilled'
              AND so.fulfilled_at BETWEEN ? AND ?
            GROUP BY so.id
            ORDER BY so.fulfilled_at DESC
        ");
        $stmt->execute([$dateFrom, $dateTo]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Top selling products
        $stmt = $this->db->prepare("
            SELECT
                p.id,
                p.name,
                p.sku,
                SUM(soi.quantity) as total_quantity,
                SUM(soi.quantity * soi.unit_price) as total_revenue
            FROM products p
            JOIN sales_order_items soi ON p.id = soi.product_id
            JOIN sales_orders so ON soi.sales_order_id = so.id
            WHERE so.status = 'fulfilled'
              AND so.fulfilled_at BETWEEN ? AND ?
            GROUP BY p.id
            ORDER BY total_quantity DESC
            LIMIT 10
        ");
        $stmt->execute([$dateFrom, $dateTo]);
        $topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalRevenue = array_sum(array_column($orders, 'total_revenue'));

        return [
            'orders' => $orders,
            'top_products' => $topProducts,
            'total_orders' => count($orders),
            'total_revenue' => round($totalRevenue, 2),
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Generate purchase report for a date range
     */
    public function purchaseReport(string $dateFrom, string $dateTo): array
    {
        $stmt = $this->db->prepare("
            SELECT
                po.id,
                po.reference,
                s.name as supplier_name,
                po.created_at,
                po.received_at,
                SUM(poi.quantity * poi.unit_cost) as total_cost
            FROM purchase_orders po
            JOIN suppliers s ON po.supplier_id = s.id
            JOIN purchase_order_items poi ON po.id = poi.purchase_order_id
            WHERE po.status = 'received'
              AND po.received_at BETWEEN ? AND ?
            GROUP BY po.id
            ORDER BY po.received_at DESC
        ");
        $stmt->execute([$dateFrom, $dateTo]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Purchases by supplier
        $stmt = $this->db->prepare("
            SELECT
                s.id,
                s.name,
                COUNT(DISTINCT po.id) as order_count,
                SUM(poi.quantity * poi.unit_cost) as total_spent
            FROM suppliers s
            JOIN purchase_orders po ON s.id = po.supplier_id
            JOIN purchase_order_items poi ON po.id = poi.purchase_order_id
            WHERE po.status = 'received'
              AND po.received_at BETWEEN ? AND ?
            GROUP BY s.id
            ORDER BY total_spent DESC
        ");
        $stmt->execute([$dateFrom, $dateTo]);
        $bySupplier = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalCost = array_sum(array_column($orders, 'total_cost'));

        return [
            'orders' => $orders,
            'by_supplier' => $bySupplier,
            'total_orders' => count($orders),
            'total_cost' => round($totalCost, 2),
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Export data to CSV format
     */
    public function exportToCsv(array $data, array $headers): string
    {
        $output = fopen('php://temp', 'r+');

        // Write headers
        fputcsv($output, $headers);

        // Write data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
