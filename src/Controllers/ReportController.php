<?php
declare(strict_types=1);

namespace App\Controllers;

use App\View;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Services\StockService;

class ReportController
{
    private View $view;
    private StockService $stockService;

    public function __construct()
    {
        $this->view = new View();
        $this->stockService = new StockService();
    }

    public function stockReport(): string
    {
        $products = Product::all('name', 'ASC');

        // Add current stock and value for each product
        $totalValue = 0;
        $lowStockCount = 0;

        foreach ($products as &$product) {
            $product['current_stock'] = $this->stockService->getCurrentStock((int)$product['id']);
            $product['stock_value'] = $product['current_stock'] * $product['cost_price'];
            $totalValue += $product['stock_value'];

            if ($product['current_stock'] < $product['reorder_level']) {
                $lowStockCount++;
            }
        }

        $content = $this->view->render('reports/stock', [
            'products' => $products,
            'totalValue' => $totalValue,
            'lowStockCount' => $lowStockCount
        ]);

        return $this->view->layout('app', $content, ['title' => 'Stock Report']);
    }

    public function salesReport(): string
    {
        // Get date filters
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01'); // First day of current month
        $dateTo = $_GET['date_to'] ?? date('Y-m-d'); // Today

        // Get all sales orders in date range
        $orders = SalesOrder::getByDateRange($dateFrom, $dateTo);

        // Calculate statistics
        $totalRevenue = 0;
        $totalOrders = count($orders);
        $fulfilledOrders = 0;
        $productsSold = [];

        foreach ($orders as $order) {
            $totalRevenue += $order['total_revenue'];

            if ($order['status'] === 'fulfilled') {
                $fulfilledOrders++;
            }

            // Count products sold
            if (!empty($order['items'])) {
                foreach ($order['items'] as $item) {
                    $productName = $item['product']['name'] ?? 'Unknown';
                    if (!isset($productsSold[$productName])) {
                        $productsSold[$productName] = 0;
                    }
                    $productsSold[$productName] += $item['quantity'];
                }
            }
        }

        // Sort products by quantity sold
        arsort($productsSold);

        $content = $this->view->render('reports/sales', [
            'orders' => $orders,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'totalRevenue' => $totalRevenue,
            'totalOrders' => $totalOrders,
            'fulfilledOrders' => $fulfilledOrders,
            'productsSold' => $productsSold
        ]);

        return $this->view->layout('app', $content, ['title' => 'Sales Report']);
    }
}
