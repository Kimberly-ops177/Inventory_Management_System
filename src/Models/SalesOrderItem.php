<?php
declare(strict_types=1);

namespace App\Models;

class SalesOrderItem extends Model
{
    protected static string $table = 'sales_order_items';
    protected array $fillable = [
        'sales_order_id',
        'product_id',
        'quantity',
        'unit_price'
    ];

    protected function hasTimestamps(): bool
    {
        return false; // sales_order_items table has no timestamps
    }

    public function product(): ?array
    {
        if (!isset($this->attributes['product_id'])) {
            return null;
        }

        $productModel = Product::find((int)$this->attributes['product_id']);
        return $productModel?->toArray();
    }

    public function salesOrder(): ?array
    {
        if (!isset($this->attributes['sales_order_id'])) {
            return null;
        }

        $soModel = SalesOrder::find((int)$this->attributes['sales_order_id']);
        return $soModel?->toArray();
    }

    public function getLineTotal(): float
    {
        return (float)($this->attributes['quantity'] ?? 0) * (float)($this->attributes['unit_price'] ?? 0);
    }
}
