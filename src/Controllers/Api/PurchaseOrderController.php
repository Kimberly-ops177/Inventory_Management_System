<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Http\JsonResponse;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Services\StockService;
use App\Services\StockAlertService;
use App\Validator;

class PurchaseOrderController
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
        $orders = PurchaseOrder::all('created_at', 'DESC');

        // Add supplier info and totals
        foreach ($orders as &$order) {
            $poModel = PurchaseOrder::find((int)$order['id']);
            $order['supplier'] = $poModel?->supplier();
            $order['total_cost'] = $poModel?->getTotalCost();
        }

        return JsonResponse::success($orders);
    }

    public function show(int $id): JsonResponse
    {
        $order = PurchaseOrder::find($id);

        if (!$order) {
            return JsonResponse::error('Purchase order not found', [], 404);
        }

        $data = $order->toArray();
        $data['supplier'] = $order->supplier();
        $data['items'] = $order->items();
        $data['total_cost'] = $order->getTotalCost();

        return JsonResponse::success($data);
    }

    public function store(): JsonResponse
    {
        // Read JSON input (API requests) or fall back to $_POST (form submissions)
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $data = [
            'supplier_id' => $input['supplier_id'] ?? null,
            'reference' => $input['reference'] ?? 'PO-' . time(),
            'status' => 'draft',
            'created_by' => $_SESSION['user']['id'] ?? null,
        ];

        $items = is_string($input['items'] ?? null)
            ? json_decode($input['items'], true)
            : ($input['items'] ?? []);

        $validator = Validator::make($data, [
            'supplier_id' => 'required|exists:suppliers,id',
        ]);

        if ($validator->fails()) {
            return JsonResponse::error('Validation failed', $validator->errors(), 422);
        }

        if (empty($items)) {
            return JsonResponse::error('Purchase order must have at least one item', [], 422);
        }

        // Create purchase order
        $order = PurchaseOrder::create($data);

        // Create order items
        foreach ($items as $item) {
            PurchaseOrderItem::create([
                'purchase_order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_cost' => $item['unit_cost'],
                'received_quantity' => 0,
            ]);
        }

        return JsonResponse::success($order->toArray(), 'Purchase order created successfully', 201);
    }

    public function update(int $id): JsonResponse
    {
        $order = PurchaseOrder::find($id);

        if (!$order) {
            return JsonResponse::error('Purchase order not found', [], 404);
        }

        if ($order->status !== 'draft') {
            return JsonResponse::error('Only draft orders can be updated', [], 400);
        }

        // Read JSON input (API requests) or fall back to $_POST (form submissions)
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $data = [
            'supplier_id' => $input['supplier_id'] ?? $order->supplier_id,
            'reference' => $input['reference'] ?? $order->reference,
        ];

        $order->update($data);

        return JsonResponse::success($order->toArray(), 'Purchase order updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $order = PurchaseOrder::find($id);

        if (!$order) {
            return JsonResponse::error('Purchase order not found', [], 404);
        }

        if ($order->status === 'received') {
            return JsonResponse::error('Cannot delete received orders', [], 400);
        }

        $order->delete();

        return JsonResponse::success(null, 'Purchase order deleted successfully');
    }

    public function receive(int $id): JsonResponse
    {
        $order = PurchaseOrder::find($id);

        if (!$order) {
            return JsonResponse::error('Purchase order not found', [], 404);
        }

        if ($order->status === 'received') {
            return JsonResponse::error('Order already received', [], 400);
        }

        // Read JSON input (API requests) or fall back to $_POST (form submissions)
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $receivedItems = is_string($input['items'] ?? null)
            ? json_decode($input['items'], true)
            : ($input['items'] ?? []);

        if (empty($receivedItems)) {
            return JsonResponse::error('No items to receive', [], 422);
        }

        // Update order items and create stock movements
        foreach ($receivedItems as $itemData) {
            $item = PurchaseOrderItem::find((int)$itemData['id']);
            if (!$item) {
                continue;
            }

            $receivedQty = (int)$itemData['received_quantity'];

            // Update received quantity
            $item->update(['received_quantity' => $receivedQty]);

            // Create stock movement
            $this->stockService->recordStockMovement(
                (int)$item->product_id,
                'in',
                $receivedQty,
                'purchase_order',
                $id,
                (float)$item->unit_cost
            );

            // Check if this resolves any alerts
            $this->alertService->checkProduct((int)$item->product_id);
        }

        // Update order status
        $order->update([
            'status' => 'received',
            'received_at' => date('Y-m-d H:i:s')
        ]);

        return JsonResponse::success($order->toArray(), 'Purchase order received successfully');
    }
}
