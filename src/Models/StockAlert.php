<?php
declare(strict_types=1);

namespace App\Models;

class StockAlert extends Model
{
    protected static string $table = 'stock_alerts';
    protected array $fillable = [
        'product_id',
        'triggered_at',
        'threshold',
        'current_stock',
        'is_resolved'
    ];

    protected function hasTimestamps(): bool
    {
        return false; // stock_alerts table has no timestamps
    }

    public function product(): ?array
    {
        if (!isset($this->attributes['product_id'])) {
            return null;
        }

        $productModel = Product::find((int)$this->attributes['product_id']);
        return $productModel?->toArray();
    }

    public function isResolved(): bool
    {
        return ($this->attributes['is_resolved'] ?? 0) == 1;
    }

    public function resolve(): bool
    {
        $this->attributes['is_resolved'] = true;
        return $this->save();
    }

    public static function getActiveAlerts(): array
    {
        $instance = new static();
        $stmt = $instance->db->query(
            "SELECT sa.*, p.name as product_name, p.sku
             FROM stock_alerts sa
             JOIN products p ON sa.product_id = p.id
             WHERE sa.is_resolved = FALSE
             ORDER BY sa.triggered_at DESC"
        );
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
