<?php
declare(strict_types=1);

namespace App\Controllers;

use App\View;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Product;
use App\Flash;

class PurchaseOrderController
{
    private View $view;

    public function __construct()
    {
        $this->view = new View();
    }

    public function index(): string
    {
        $orders = PurchaseOrder::all('created_at', 'DESC');

        // Add supplier info
        foreach ($orders as &$order) {
            $poModel = PurchaseOrder::find((int)$order['id']);
            $order['supplier'] = $poModel?->supplier();
            $order['total_cost'] = $poModel?->getTotalCost();
        }

        $content = $this->view->render('purchase-orders/index', [
            'orders' => $orders
        ]);

        return $this->view->layout('app', $content, ['title' => 'Purchase Orders']);
    }

    public function create(): string
    {
        $suppliers = Supplier::all('name', 'ASC');
        $products = Product::all('name', 'ASC');

        $content = $this->view->render('purchase-orders/create', [
            'suppliers' => $suppliers,
            'products' => $products
        ]);

        return $this->view->layout('app', $content, ['title' => 'Create Purchase Order']);
    }

    public function show(int $id): string
    {
        $order = PurchaseOrder::find($id);
        if (!$order) {
            Flash::error('Purchase order not found');
            header('Location: /purchase-orders');
            exit;
        }

        $data = $order->toArray();
        $data['supplier'] = $order->supplier();
        $data['items'] = $order->items();

        // Add product info to items
        foreach ($data['items'] as &$item) {
            $productModel = Product::find((int)$item['product_id']);
            $item['product'] = $productModel?->toArray();
        }

        $data['total_cost'] = $order->getTotalCost();

        $content = $this->view->render('purchase-orders/show', [
            'order' => $data
        ]);

        return $this->view->layout('app', $content, ['title' => 'Purchase Order #' . $order->reference]);
    }
}
