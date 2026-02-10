<?php
declare(strict_types=1);

namespace App\Models;

class StockMovement extends Model
{
    protected static string $table = 'stock_movements';
    protected array $fillable = [
        'product_id',
        'source',
        'source_id',
        'direction',
        'quantity',
        'unit_cost'
    ];

    protected function hasTimestamps(): bool
    {
        return false; // stock_movements table only has created_at
    }

    public function product(): ?array
    {
        if (!isset($this->attributes['product_id'])) {
            return null;
        }

        $productModel = Product::find((int)$this->attributes['product_id']);
        return $productModel?->toArray();
    }

    public function getSignedQuantity(): int
    {
        $quantity = (int)($this->attributes['quantity'] ?? 0);
        $direction = $this->attributes['direction'] ?? 'in';

        return $direction === 'in' ? $quantity : -$quantity;
    }
}
