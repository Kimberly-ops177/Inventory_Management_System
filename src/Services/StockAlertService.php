<?php
declare(strict_types=1);

namespace App\Services;

use App\Database;
use App\Models\Product;
use App\Models\StockAlert;
use PDO;

class StockAlertService
{
    private PDO $db;
    private StockService $stockService;

    public function __construct()
    {
        $this->db = Database::connection();
        $this->stockService = new StockService();
    }

    /**
     * Check all products and trigger alerts for low stock
     */
    public function checkAllProducts(): int
    {
        $products = Product::all();
        $alertsTriggered = 0;

        foreach ($products as $productData) {
            $product = Product::find((int)$productData['id']);
            if ($product && $this->checkProduct((int)$product->id)) {
                $alertsTriggered++;
            }
        }

        return $alertsTriggered;
    }

    /**
     * Check a specific product and trigger alert if below reorder level
     */
    public function checkProduct(int $productId): bool
    {
        $product = Product::find($productId);
        if (!$product) {
            return false;
        }

        $currentStock = $this->stockService->getCurrentStock($productId);
        $reorderLevel = (int)$product->reorder_level;

        // If stock is below reorder level
        if ($currentStock < $reorderLevel) {
            // Check if there's already an active alert
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM stock_alerts
                WHERE product_id = ? AND is_resolved = 0
            ");
            $stmt->execute([$productId]);
            $hasActiveAlert = (int)$stmt->fetchColumn() > 0;

            if (!$hasActiveAlert) {
                // Create new alert
                StockAlert::create([
                    'product_id' => $productId,
                    'triggered_at' => date('Y-m-d H:i:s'),
                    'threshold' => $reorderLevel,
                    'current_stock' => $currentStock,
                    'is_resolved' => 0
                ]);

                return true;
            }
        } else {
            // Stock is above reorder level, auto-resolve any active alerts
            $stmt = $this->db->prepare("
                UPDATE stock_alerts
                SET is_resolved = 1
                WHERE product_id = ? AND is_resolved = 0
            ");
            $stmt->execute([$productId]);
        }

        return false;
    }

    /**
     * Get all active alerts
     */
    public function getActiveAlerts(): array
    {
        return StockAlert::getActiveAlerts();
    }

    /**
     * Resolve a specific alert
     */
    public function resolveAlert(int $alertId): bool
    {
        $alert = StockAlert::find($alertId);
        if (!$alert) {
            return false;
        }

        return $alert->resolve();
    }
}
