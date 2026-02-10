<?php
declare(strict_types=1);

namespace App\Models;

class PurchaseOrder extends Model
{
    protected static string $table = 'purchase_orders';
    protected array $fillable = [
        'supplier_id',
        'reference',
        'status',
        'ordered_at',
        'received_at',
        'created_by'
    ];

    public function supplier(): ?array
    {
        if (!isset($this->attributes['supplier_id'])) {
            return null;
        }

        $supplierModel = Supplier::find((int)$this->attributes['supplier_id']);
        return $supplierModel?->toArray();
    }

    public function items(): array
    {
        if (!isset($this->attributes['id'])) {
            return [];
        }

        return PurchaseOrderItem::where('purchase_order_id', $this->attributes['id']);
    }

    public function createdBy(): ?array
    {
        if (!isset($this->attributes['created_by'])) {
            return null;
        }

        $userModel = User::find((int)$this->attributes['created_by']);
        return $userModel?->toArray();
    }

    public function getTotalCost(): float
    {
        $items = $this->items();
        $total = 0.0;

        foreach ($items as $item) {
            $total += (float)$item['quantity'] * (float)$item['unit_cost'];
        }

        return $total;
    }
}
