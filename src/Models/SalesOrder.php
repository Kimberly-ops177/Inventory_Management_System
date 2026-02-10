<?php
declare(strict_types=1);

namespace App\Models;

class SalesOrder extends Model
{
    protected static string $table = 'sales_orders';
    protected array $fillable = [
        'reference',
        'status',
        'customer_name',
        'created_by',
        'fulfilled_at'
    ];

    public function items(): array
    {
        if (!isset($this->attributes['id'])) {
            return [];
        }

        return SalesOrderItem::where('sales_order_id', $this->attributes['id']);
    }

    public function createdBy(): ?array
    {
        if (!isset($this->attributes['created_by'])) {
            return null;
        }

        $userModel = User::find((int)$this->attributes['created_by']);
        return $userModel?->toArray();
    }

    public function getTotalRevenue(): float
    {
        $items = $this->items();
        $total = 0.0;

        foreach ($items as $item) {
            $total += (float)$item['quantity'] * (float)$item['unit_price'];
        }

        return $total;
    }

    public static function getByDateRange(string $dateFrom, string $dateTo): array
    {
        $db = \App\Database::connection();
        $stmt = $db->prepare("
            SELECT * FROM " . static::$table . "
            WHERE DATE(created_at) BETWEEN ? AND ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$dateFrom, $dateTo]);
        $orders = $stmt->fetchAll();

        // Add items and total_revenue to each order
        foreach ($orders as &$order) {
            $orderModel = static::find((int)$order['id']);
            $order['items'] = $orderModel?->items() ?? [];
            $order['total_revenue'] = $orderModel?->getTotalRevenue() ?? 0;

            // Add product details to items
            foreach ($order['items'] as &$item) {
                $product = Product::find((int)$item['product_id']);
                $item['product'] = $product?->toArray() ?? [];
            }
        }

        return $orders;
    }
}
