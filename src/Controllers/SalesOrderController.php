<?php
declare(strict_types=1);

namespace App\Controllers;

use App\View;
use App\Models\SalesOrder;
use App\Models\Product;
use App\Flash;
use App\Services\StockService;

class SalesOrderController
{
    private View $view;
    private StockService $stockService;

    public function __construct()
    {
        $this->view = new View();
        $this->stockService = new StockService();
    }

    public function index(): string
    {
        $orders = SalesOrder::all('created_at', 'DESC');

        // Add totals
        foreach ($orders as &$order) {
            $soModel = SalesOrder::find((int)$order['id']);
            $order['total_revenue'] = $soModel?->getTotalRevenue();
        }

        $content = $this->view->render('sales-orders/index', [
            'orders' => $orders
        ]);

        return $this->view->layout('app', $content, ['title' => 'Sales Orders']);
    }

    public function create(): string
    {
        $products = Product::all('name', 'ASC');

        $content = $this->view->render('sales-orders/create', [
            'products' => $products
        ]);

        return $this->view->layout('app', $content, ['title' => 'Create Sales Order']);
    }

    public function show(int $id): string
    {
        $order = SalesOrder::find($id);
        if (!$order) {
            Flash::error('Sales order not found');
            header('Location: /sales-orders');
            exit;
        }

        $data = $order->toArray();
        $data['items'] = $order->items();

        // Add product info and current stock to items
        foreach ($data['items'] as &$item) {
            $productModel = Product::find((int)$item['product_id']);
            $item['product'] = $productModel?->toArray();
            $item['product']['current_stock'] = $this->stockService->getCurrentStock((int)$item['product_id']);
        }

        $data['total_revenue'] = $order->getTotalRevenue();

        $content = $this->view->render('sales-orders/show', [
            'order' => $data
        ]);

        return $this->view->layout('app', $content, ['title' => 'Sales Order #' . $order->reference]);
    }
}
