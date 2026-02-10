<?php
declare(strict_types=1);

namespace App\Models;

class Product extends Model
{
    protected static string $table = 'products';
    protected array $fillable = [
        'category_id',
        'name',
        'sku',
        'description',
        'unit',
        'cost_price',
        'sale_price',
        'reorder_level'
    ];

    public function category(): ?array
    {
        if (!isset($this->attributes['category_id'])) {
            return null;
        }

        $categoryModel = Category::find((int)$this->attributes['category_id']);
        return $categoryModel?->toArray();
    }

    public function getCurrentStock(): int
    {
        if (!isset($this->attributes['id'])) {
            return 0;
        }

        $stmt = $this->db->prepare("
            SELECT
                COALESCE(SUM(CASE WHEN direction = 'in' THEN quantity ELSE -quantity END), 0) as stock
            FROM stock_movements
            WHERE product_id = ?
        ");
        $stmt->execute([$this->attributes['id']]);
        return (int)$stmt->fetchColumn();
    }

    public function isLowStock(): bool
    {
        $currentStock = $this->getCurrentStock();
        $reorderLevel = (int)($this->attributes['reorder_level'] ?? 0);
        return $currentStock < $reorderLevel;
    }

    public function getStockValue(): float
    {
        $currentStock = $this->getCurrentStock();
        $costPrice = (float)($this->attributes['cost_price'] ?? 0);
        return $currentStock * $costPrice;
    }
}
