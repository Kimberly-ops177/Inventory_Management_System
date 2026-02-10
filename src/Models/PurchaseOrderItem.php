<?php
declare(strict_types=1);

namespace App\Models;

class PurchaseOrderItem extends Model
{
    protected static string $table = 'purchase_order_items';
    protected array $fillable = [
        'purchase_order_id',
        'product_id',
        'quantity',
        'unit_cost',
        'received_quantity'
    ];

    protected function hasTimestamps(): bool
    {
        return false; // purchase_order_items table has no timestamps
    }

    public function product(): ?array
    {
        if (!isset($this->attributes['product_id'])) {
            return null;
        }

        $productModel = Product::find((int)$this->attributes['product_id']);
        return $productModel?->toArray();
    }

    public function purchaseOrder(): ?array
    {
        if (!isset($this->attributes['purchase_order_id'])) {
            return null;
        }

        $poModel = PurchaseOrder::find((int)$this->attributes['purchase_order_id']);
        return $poModel?->toArray();
    }

    public function getLineTotal(): float
    {
        return (float)($this->attributes['quantity'] ?? 0) * (float)($this->attributes['unit_cost'] ?? 0);
    }
}
