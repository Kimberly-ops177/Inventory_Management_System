<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Http\JsonResponse;
use App\Models\Supplier;
use App\Validator;

class SupplierController
{
    public function index(): JsonResponse
    {
        $suppliers = Supplier::all('name', 'ASC');
        return JsonResponse::success($suppliers);
    }

    public function show(int $id): JsonResponse
    {
        $supplier = Supplier::find($id);

        if (!$supplier) {
            return JsonResponse::error('Supplier not found', [], 404);
        }

        $data = $supplier->toArray();
        $data['purchase_orders'] = $supplier->purchaseOrders();

        return JsonResponse::success($data);
    }

    public function store(): JsonResponse
    {
        $data = [
            'name' => $_POST['name'] ?? '',
            'contact_name' => $_POST['contact_name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'address' => $_POST['address'] ?? '',
        ];

        $validator = Validator::make($data, [
            'name' => 'required|min:3',
            'email' => 'email',
        ]);

        if ($validator->fails()) {
            return JsonResponse::error('Validation failed', $validator->errors(), 422);
        }

        $supplier = Supplier::create($data);

        return JsonResponse::success($supplier->toArray(), 'Supplier created successfully', 201);
    }

    public function update(int $id): JsonResponse
    {
        $supplier = Supplier::find($id);

        if (!$supplier) {
            return JsonResponse::error('Supplier not found', [], 404);
        }

        $data = [
            'name' => $_POST['name'] ?? $supplier->name,
            'contact_name' => $_POST['contact_name'] ?? $supplier->contact_name,
            'email' => $_POST['email'] ?? $supplier->email,
            'phone' => $_POST['phone'] ?? $supplier->phone,
            'address' => $_POST['address'] ?? $supplier->address,
        ];

        $validator = Validator::make($data, [
            'name' => 'required|min:3',
            'email' => 'email',
        ]);

        if ($validator->fails()) {
            return JsonResponse::error('Validation failed', $validator->errors(), 422);
        }

        $supplier->update($data);

        return JsonResponse::success($supplier->toArray(), 'Supplier updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $supplier = Supplier::find($id);

        if (!$supplier) {
            return JsonResponse::error('Supplier not found', [], 404);
        }

        $supplier->delete();

        return JsonResponse::success(null, 'Supplier deleted successfully');
    }
}
