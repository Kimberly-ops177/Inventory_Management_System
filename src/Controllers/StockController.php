<?php
declare(strict_types=1);

namespace App\Controllers;

use App\View;
use App\Models\StockMovement;
use App\Models\StockAlert;
use App\Services\StockAlertService;
use App\Flash;

class StockController
{
    private View $view;
    private StockAlertService $alertService;

    public function __construct()
    {
        $this->view = new View();
        $this->alertService = new StockAlertService();
    }

    public function movements(): string
    {
        $movements = StockMovement::all('created_at', 'DESC');

        // Add product info
        foreach ($movements as &$movement) {
            $mvModel = StockMovement::find((int)$movement['id']);
            $movement['product'] = $mvModel?->product();
        }

        // Limit to last 100
        $movements = array_slice($movements, 0, 100);

        $content = $this->view->render('stock/movements', [
            'movements' => $movements
        ]);

        return $this->view->layout('app', $content, ['title' => 'Stock Movements']);
    }

    public function alerts(): string
    {
        $alerts = StockAlert::getActiveAlerts();

        $content = $this->view->render('stock/alerts', [
            'alerts' => $alerts
        ]);

        return $this->view->layout('app', $content, ['title' => 'Stock Alerts']);
    }

    public function checkAlerts(): void
    {
        $count = $this->alertService->checkAllProducts();
        Flash::success("Checked all products. $count alert(s) triggered.");
        header('Location: /stock/alerts');
        exit;
    }
}
