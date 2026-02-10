<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;

class AuditLogger
{
    /**
     * Log an action to the audit log
     */
    public static function log(string $action, array $details = []): void
    {
        $userId = $_SESSION['user']['id'] ?? null;

        if (!$userId) {
            return; // Don't log if no user in session
        }

        AuditLog::log((int)$userId, $action, $details);
    }

    /**
     * Log user login
     */
    public static function logLogin(int $userId, string $email): void
    {
        AuditLog::log($userId, 'user.login', [
            'email' => $email,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    }

    /**
     * Log user logout
     */
    public static function logLogout(int $userId): void
    {
        AuditLog::log($userId, 'user.logout', [
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    }

    /**
     * Log product creation
     */
    public static function logProductCreate(int $productId, array $data): void
    {
        self::log('product.create', [
            'product_id' => $productId,
            'data' => $data
        ]);
    }

    /**
     * Log product update
     */
    public static function logProductUpdate(int $productId, array $oldData, array $newData): void
    {
        self::log('product.update', [
            'product_id' => $productId,
            'old' => $oldData,
            'new' => $newData
        ]);
    }

    /**
     * Log product deletion
     */
    public static function logProductDelete(int $productId, array $data): void
    {
        self::log('product.delete', [
            'product_id' => $productId,
            'data' => $data
        ]);
    }

    /**
     * Log purchase order status change
     */
    public static function logPurchaseOrderStatus(int $orderId, string $oldStatus, string $newStatus): void
    {
        self::log('purchase_order.status_change', [
            'order_id' => $orderId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus
        ]);
    }

    /**
     * Log sales order status change
     */
    public static function logSalesOrderStatus(int $orderId, string $oldStatus, string $newStatus): void
    {
        self::log('sales_order.status_change', [
            'order_id' => $orderId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus
        ]);
    }

    /**
     * Log stock alert trigger
     */
    public static function logStockAlert(int $productId, int $currentStock, int $threshold): void
    {
        self::log('stock_alert.triggered', [
            'product_id' => $productId,
            'current_stock' => $currentStock,
            'threshold' => $threshold
        ]);
    }
}
