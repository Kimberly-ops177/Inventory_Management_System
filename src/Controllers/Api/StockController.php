<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Http\JsonResponse;
use App\Models\StockAlert;
use App\Models\StockMovement;
use App\Services\StockService;
use App\Services\StockAlertService;

class StockController
{
    private StockService $stockService;
    private StockAlertService $alertService;

    public function __construct()
    {
        $this->stockService = new StockService();
        $this->alertService = new StockAlertService();
    }

    public function movements(): JsonResponse
    {
        $movements = StockMovement::all('created_at', 'DESC');

        // Add product info
        foreach ($movements as &$movement) {
            $mvModel = StockMovement::find((int)$movement['id']);
            $movement['product'] = $mvModel?->product();
        }

        return JsonResponse::success($movements);
    }

    public function alerts(): JsonResponse
    {
        $alerts = StockAlert::getActiveAlerts();
        return JsonResponse::success($alerts);
    }

    public function resolveAlert(int $id): JsonResponse
    {
        $alert = StockAlert::find($id);

        if (!$alert) {
            return JsonResponse::error('Alert not found', [], 404);
        }

        if ($alert->isResolved()) {
            return JsonResponse::error('Alert already resolved', [], 400);
        }

        $alert->resolve();

        return JsonResponse::success($alert->toArray(), 'Alert resolved successfully');
    }

    public function checkAlerts(): JsonResponse
    {
        $alertsTriggered = $this->alertService->checkAllProducts();

        return JsonResponse::success([
            'alerts_triggered' => $alertsTriggered,
            'message' => "$alertsTriggered alert(s) triggered"
        ]);
    }
}
