<?php
declare(strict_types=1);

namespace App\Models;

class Supplier extends Model
{
    protected static string $table = 'suppliers';
    protected array $fillable = ['name', 'contact_name', 'email', 'phone', 'address'];

    protected function hasTimestamps(): bool
    {
        return false; // suppliers table only has created_at
    }

    public function purchaseOrders(): array
    {
        if (!isset($this->attributes['id'])) {
            return [];
        }

        return PurchaseOrder::where('supplier_id', $this->attributes['id']);
    }
}
