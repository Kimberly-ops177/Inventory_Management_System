<?php
declare(strict_types=1);

namespace App\Controllers;

use App\View;
use App\Models\Product;
use App\Models\Category;
use App\Services\StockService;
use App\Flash;
use App\Validator;

class ProductController
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
        $products = Product::all('name', 'ASC');

        // Add current stock to each product
        foreach ($products as &$product) {
            $product['current_stock'] = $this->stockService->getCurrentStock((int)$product['id']);
            $productModel = Product::find((int)$product['id']);
            $product['category_name'] = $productModel?->category()['name'] ?? 'N/A';
        }

        $content = $this->view->render('products/index', [
            'products' => $products
        ]);

        return $this->view->layout('app', $content, ['title' => 'Products']);
    }

    public function create(): string
    {
        $categories = Category::all('name', 'ASC');

        $content = $this->view->render('products/create', [
            'categories' => $categories
        ]);

        return $this->view->layout('app', $content, ['title' => 'Create Product']);
    }

    public function store(): void
    {
        $data = [
            'category_id' => $_POST['category_id'] ?? null,
            'name' => $_POST['name'] ?? '',
            'sku' => $_POST['sku'] ?? '',
            'description' => $_POST['description'] ?? '',
            'unit' => $_POST['unit'] ?? 'pcs',
            'cost_price' => $_POST['cost_price'] ?? 0,
            'sale_price' => $_POST['sale_price'] ?? 0,
            'reorder_level' => $_POST['reorder_level'] ?? 0,
        ];

        $validator = Validator::make($data, [
            'name' => 'required|min:3',
            'sku' => 'required|unique:products,sku',
            'cost_price' => 'numeric',
            'sale_price' => 'numeric',
        ]);

        if ($validator->fails()) {
            Flash::error('Validation failed: ' . implode(', ', array_map('implode', $validator->errors())));
            header('Location: /products/create');
            exit;
        }

        Product::create($data);
        Flash::success('Product created successfully');
        header('Location: /products');
        exit;
    }

    public function edit(int $id): string
    {
        $product = Product::find($id);
        if (!$product) {
            Flash::error('Product not found');
            header('Location: /products');
            exit;
        }

        $categories = Category::all('name', 'ASC');

        $content = $this->view->render('products/edit', [
            'product' => $product->toArray(),
            'categories' => $categories
        ]);

        return $this->view->layout('app', $content, ['title' => 'Edit Product']);
    }

    public function update(int $id): void
    {
        $product = Product::find($id);
        if (!$product) {
            Flash::error('Product not found');
            header('Location: /products');
            exit;
        }

        $data = [
            'category_id' => $_POST['category_id'] ?? $product->category_id,
            'name' => $_POST['name'] ?? $product->name,
            'sku' => $_POST['sku'] ?? $product->sku,
            'description' => $_POST['description'] ?? $product->description,
            'unit' => $_POST['unit'] ?? $product->unit,
            'cost_price' => $_POST['cost_price'] ?? $product->cost_price,
            'sale_price' => $_POST['sale_price'] ?? $product->sale_price,
            'reorder_level' => $_POST['reorder_level'] ?? $product->reorder_level,
        ];

        $validator = Validator::make($data, [
            'name' => 'required|min:3',
            'sku' => 'required|unique:products,sku,' . $id,
        ]);

        if ($validator->fails()) {
            Flash::error('Validation failed');
            header('Location: /products/' . $id . '/edit');
            exit;
        }

        $product->update($data);
        Flash::success('Product updated successfully');
        header('Location: /products');
        exit;
    }

    public function destroy(int $id): void
    {
        $product = Product::find($id);
        if (!$product) {
            Flash::error('Product not found');
            header('Location: /products');
            exit;
        }

        $product->delete();
        Flash::success('Product deleted successfully');
        header('Location: /products');
        exit;
    }
}
