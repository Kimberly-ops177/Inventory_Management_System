<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Http\JsonResponse;
use App\Models\Category;
use App\Validator;

class CategoryController
{
    public function index(): JsonResponse
    {
        $categories = Category::all('name', 'ASC');
        return JsonResponse::success($categories);
    }

    public function show(int $id): JsonResponse
    {
        $category = Category::find($id);

        if (!$category) {
            return JsonResponse::error('Category not found', [], 404);
        }

        $data = $category->toArray();
        $data['products'] = $category->products();

        return JsonResponse::success($data);
    }

    public function store(): JsonResponse
    {
        $data = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
        ];

        $validator = Validator::make($data, [
            'name' => 'required|min:3|unique:categories,name',
        ]);

        if ($validator->fails()) {
            return JsonResponse::error('Validation failed', $validator->errors(), 422);
        }

        $category = Category::create($data);

        return JsonResponse::success($category->toArray(), 'Category created successfully', 201);
    }

    public function update(int $id): JsonResponse
    {
        $category = Category::find($id);

        if (!$category) {
            return JsonResponse::error('Category not found', [], 404);
        }

        $data = [
            'name' => $_POST['name'] ?? $category->name,
            'description' => $_POST['description'] ?? $category->description,
        ];

        $validator = Validator::make($data, [
            'name' => 'required|min:3|unique:categories,name,' . $id,
        ]);

        if ($validator->fails()) {
            return JsonResponse::error('Validation failed', $validator->errors(), 422);
        }

        $category->update($data);

        return JsonResponse::success($category->toArray(), 'Category updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $category = Category::find($id);

        if (!$category) {
            return JsonResponse::error('Category not found', [], 404);
        }

        // Check if category has products
        $products = $category->products();
        if (!empty($products)) {
            return JsonResponse::error('Cannot delete category with associated products', [], 400);
        }

        $category->delete();

        return JsonResponse::success(null, 'Category deleted successfully');
    }
}
