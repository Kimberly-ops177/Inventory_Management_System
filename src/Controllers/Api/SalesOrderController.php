<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Http\JsonResponse;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Services\StockService;
use App\Services\StockAlertService;
use App\Validator;

class SalesOrderController
{
    private StockService $stockService;
    private StockAlertService $alertService;

    public function __construct()
    {
        $this->stockService = new StockService();
        $this->alertService = new StockAlertService();
    }

    public function index(): JsonResponse
    {
        $orders = SalesOrder::all('created_at', 'DESC');

        // Add totals
        foreach ($orders as &$order) {
            $soModel = SalesOrder::find((int)$order['id']);
            $order['total_revenue'] = $soModel?->getTotalRevenue();
        }

        return JsonResponse::success($orders);
    }

    public function show(int $id): JsonResponse
    {
        $order = SalesOrder::find($id);

        if (!$order) {
            return JsonResponse::error('Sales order not found', [], 404);
        }

        $data = $order->toArray();
        $data['items'] = $order->items();
        $data['total_revenue'] = $order->getTotalRevenue();

        return JsonResponse::success($data);
    }

    public function store(): JsonResponse
    {
        // Read JSON input (API requests) or fall back to $_POST (form submissions)
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $data = [
            'reference' => $input['reference'] ?? 'SO-' . time(),
            'customer_name' => $input['customer_name'] ?? '',
            'status' => 'draft',
            'created_by' => $_SESSION['user']['id'] ?? null,
        ];

        $items = is_string($input['items'] ?? null)
            ? json_decode($input['items'], true)
            : ($input['items'] ?? []);

        $validator = Validator::make($data, [
            'customer_name' => 'required|min:3',
        ]);

        if ($validator->fails()) {
            return JsonResponse::error('Validation failed', $validator->errors(), 422);
        }

        if (empty($items)) {
            return JsonResponse::error('Sales order must have at least one item', [], 422);
        }

        // Validate stock availability
        $stockErrors = $this->stockService->validateStockAvailability($items);
        if (!empty($stockErrors)) {
            return JsonResponse::error('Insufficient stock', ['stock' => $stockErrors], 400);
        }

        // Create sales order
        $order = SalesOrder::create($data);

        // Create order items
        foreach ($items as $item) {
            SalesOrderItem::create([
                'sales_order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
            ]);
        }

        return JsonResponse::success($order->toArray(), 'Sales order created successfully', 201);
    }

    public function update(int $id): JsonResponse
    {
        $order = SalesOrder::find($id);

        if (!$order) {
            return JsonResponse::error('Sales order not found', [], 404);
        }

        if ($order->status === 'fulfilled') {
            return JsonResponse::error('Cannot update fulfilled orders', [], 400);
        }

        // Read JSON input (API requests) or fall back to $_POST (form submissions)
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $data = [
            'customer_name' => $input['customer_name'] ?? $order->customer_name,
            'reference' => $input['reference'] ?? $order->reference,
        ];

        $order->update($data);

        return JsonResponse::success($order->toArray(), 'Sales order updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $order = SalesOrder::find($id);

        if (!$order) {
            return JsonResponse::error('Sales order not found', [], 404);
        }

        if ($order->status === 'fulfilled') {
            return JsonResponse::error('Cannot delete fulfilled orders', [], 400);
        }

        $order->delete();

        return JsonResponse::success(null, 'Sales order deleted successfully');
    }

    public function fulfill(int $id): JsonResponse
    {
        $order = SalesOrder::find($id);

        if (!$order) {
            return JsonResponse::error('Sales order not found', [], 404);
        }

        if ($order->status === 'fulfilled') {
            return JsonResponse::error('Order already fulfilled', [], 400);
        }

        $items = $order->items();

        // Validate stock availability again
        $stockErrors = $this->stockService->validateStockAvailability($items);
        if (!empty($stockErrors)) {
            return JsonResponse::error('Insufficient stock', ['stock' => $stockErrors], 400);
        }

        // Create stock movements (reduce stock)
        foreach ($items as $item) {
            $this->stockService->recordStockMovement(
                (int)$item['product_id'],
                'out',
                (int)$item['quantity'],
                'sales_order',
                $id,
                null
            );

            // Check if low stock alert should be triggered
            $this->alertService->checkProduct((int)$item['product_id']);
        }

        // Update order status
        $order->update([
            'status' => 'fulfilled',
            'fulfilled_at' => date('Y-m-d H:i:s')
        ]);

        return JsonResponse::success($order->toArray(), 'Sales order fulfilled successfully');
    }
}
