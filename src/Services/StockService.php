<?php
declare(strict_types=1);

namespace App\Services;

use App\Database;
use App\Models\Product;
use App\Models\StockMovement;
use PDO;

class StockService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * Get current stock quantity for a product
     */
    public function getCurrentStock(int $productId): int
    {
        $stmt = $this->db->prepare("
            SELECT COALESCE(
                SUM(CASE WHEN direction = 'in' THEN quantity ELSE -quantity END),
                0
            ) as stock
            FROM stock_movements
            WHERE product_id = ?
        ");
        $stmt->execute([$productId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Record a stock movement (in or out)
     */
    public function recordStockMovement(
        int $productId,
        string $direction,
        int $quantity,
        string $source,
        ?int $sourceId = null,
        ?float $unitCost = null
    ): bool {
        if (!in_array($direction, ['in', 'out'], true)) {
            throw new \InvalidArgumentException("Direction must be 'in' or 'out'");
        }

        if ($quantity <= 0) {
            throw new \InvalidArgumentException("Quantity must be greater than 0");
        }

        StockMovement::create([
            'product_id' => $productId,
            'source' => $source,
            'source_id' => $sourceId,
            'direction' => $direction,
            'quantity' => $quantity,
            'unit_cost' => $unitCost
        ]);

        return true;
    }

    /**
     * Check if a product is below its reorder level
     */
    public function checkReorderLevel(int $productId): bool
    {
        $product = Product::find($productId);
        if (!$product) {
            return false;
        }

        $currentStock = $this->getCurrentStock($productId);
        $reorderLevel = (int)$product->reorder_level;

        return $currentStock < $reorderLevel;
    }

    /**
     * Get all products that are low on stock
     */
    public function getLowStockProducts(): array
    {
        $stmt = $this->db->query("
            SELECT
                p.*,
                COALESCE(
                    SUM(CASE WHEN sm.direction = 'in' THEN sm.quantity ELSE -sm.quantity END),
                    0
                ) as current_stock
            FROM products p
            LEFT JOIN stock_movements sm ON p.id = sm.product_id
            GROUP BY p.id
            HAVING current_stock < p.reorder_level
            ORDER BY current_stock ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get total inventory value (stock * cost_price)
     */
    public function getStockValue(): float
    {
        $stmt = $this->db->query("
            SELECT
                SUM(
                    p.cost_price * COALESCE(
                        (SELECT SUM(CASE WHEN direction = 'in' THEN quantity ELSE -quantity END)
                         FROM stock_movements WHERE product_id = p.id),
                        0
                    )
                ) as total_value
            FROM products p
        ");

        return (float)$stmt->fetchColumn();
    }

    /**
     * Get stock movement history for a product
     */
    public function getProductStockHistory(int $productId, int $days = 30): array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM stock_movements
            WHERE product_id = ?
              AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            ORDER BY created_at DESC
        ");
        $stmt->execute([$productId, $days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get stock levels for all products
     */
    public function getAllStockLevels(): array
    {
        $stmt = $this->db->query("
            SELECT
                p.id,
                p.name,
                p.sku,
                p.unit,
                p.cost_price,
                p.sale_price,
                p.reorder_level,
                COALESCE(
                    SUM(CASE WHEN sm.direction = 'in' THEN sm.quantity ELSE -sm.quantity END),
                    0
                ) as current_stock,
                CASE
                    WHEN COALESCE(
                        SUM(CASE WHEN sm.direction = 'in' THEN sm.quantity ELSE -sm.quantity END),
                        0
                    ) < p.reorder_level THEN 1
                    ELSE 0
                END as is_low_stock
            FROM products p
            LEFT JOIN stock_movements sm ON p.id = sm.product_id
            GROUP BY p.id
            ORDER BY p.name ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Validate if sufficient stock is available for a sales order
     */
    public function validateStockAvailability(array $items): array
    {
        $errors = [];

        foreach ($items as $item) {
            $productId = (int)$item['product_id'];
            $requestedQty = (int)$item['quantity'];

            $currentStock = $this->getCurrentStock($productId);

            if ($currentStock < $requestedQty) {
                $product = Product::find($productId);
                $productName = $product?->name ?? "Product ID $productId";
                $errors[] = "Insufficient stock for $productName. Available: $currentStock, Requested: $requestedQty";
            }
        }

        return $errors;
    }
}
