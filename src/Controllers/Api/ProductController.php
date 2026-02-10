<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Http\JsonResponse;
use App\Models\Product;
use App\Services\StockService;
use App\Validator;

class ProductController
{
    private StockService $stockService;

    public function __construct()
    {
        $this->stockService = new StockService();
    }

    public function index(): JsonResponse
    {
        $products = Product::all('name', 'ASC');

        // Add current stock to each product
        foreach ($products as &$product) {
            $product['current_stock'] = $this->stockService->getCurrentStock((int)$product['id']);
        }

        return JsonResponse::success($products);
    }

    public function show(int $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return JsonResponse::error('Product not found', [], 404);
        }

        $data = $product->toArray();
        $data['current_stock'] = $this->stockService->getCurrentStock($id);
        $data['category'] = $product->category();

        return JsonResponse::success($data);
    }

    public function store(): JsonResponse
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
            'reorder_level' => 'integer',
        ]);

        if ($validator->fails()) {
            return JsonResponse::error('Validation failed', $validator->errors(), 422);
        }

        $product = Product::create($data);

        return JsonResponse::success($product->toArray(), 'Product created successfully', 201);
    }

    public function update(int $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return JsonResponse::error('Product not found', [], 404);
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
            'cost_price' => 'numeric',
            'sale_price' => 'numeric',
            'reorder_level' => 'integer',
        ]);

        if ($validator->fails()) {
            return JsonResponse::error('Validation failed', $validator->errors(), 422);
        }

        $product->update($data);

        return JsonResponse::success($product->toArray(), 'Product updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return JsonResponse::error('Product not found', [], 404);
        }

        $product->delete();

        return JsonResponse::success(null, 'Product deleted successfully');
    }

    public function stock(int $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return JsonResponse::error('Product not found', [], 404);
        }

        $currentStock = $this->stockService->getCurrentStock($id);
        $history = $this->stockService->getProductStockHistory($id);

        return JsonResponse::success([
            'product' => $product->toArray(),
            'current_stock' => $currentStock,
            'history' => $history
        ]);
    }
}
