<?php
declare(strict_types=1);

namespace App\Controllers;

use App\View;
use App\Models\Supplier;
use App\Flash;
use App\Validator;

class SupplierController
{
    private View $view;

    public function __construct()
    {
        $this->view = new View();
    }

    public function index(): string
    {
        $suppliers = Supplier::all('name', 'ASC');

        $content = $this->view->render('suppliers/index', [
            'suppliers' => $suppliers
        ]);

        return $this->view->layout('app', $content, ['title' => 'Suppliers']);
    }

    public function create(): string
    {
        $content = $this->view->render('suppliers/create');
        return $this->view->layout('app', $content, ['title' => 'Create Supplier']);
    }

    public function store(): void
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
            Flash::error('Validation failed');
            header('Location: /suppliers/create');
            exit;
        }

        Supplier::create($data);
        Flash::success('Supplier created successfully');
        header('Location: /suppliers');
        exit;
    }

    public function edit(int $id): string
    {
        $supplier = Supplier::find($id);
        if (!$supplier) {
            Flash::error('Supplier not found');
            header('Location: /suppliers');
            exit;
        }

        $content = $this->view->render('suppliers/edit', [
            'supplier' => $supplier->toArray()
        ]);

        return $this->view->layout('app', $content, ['title' => 'Edit Supplier']);
    }

    public function update(int $id): void
    {
        $supplier = Supplier::find($id);
        if (!$supplier) {
            Flash::error('Supplier not found');
            header('Location: /suppliers');
            exit;
        }

        $data = [
            'name' => $_POST['name'] ?? $supplier->name,
            'contact_name' => $_POST['contact_name'] ?? $supplier->contact_name,
            'email' => $_POST['email'] ?? $supplier->email,
            'phone' => $_POST['phone'] ?? $supplier->phone,
            'address' => $_POST['address'] ?? $supplier->address,
        ];

        $supplier->update($data);
        Flash::success('Supplier updated successfully');
        header('Location: /suppliers');
        exit;
    }

    public function destroy(int $id): void
    {
        $supplier = Supplier::find($id);
        if (!$supplier) {
            Flash::error('Supplier not found');
            header('Location: /suppliers');
            exit;
        }

        $supplier->delete();
        Flash::success('Supplier deleted successfully');
        header('Location: /suppliers');
        exit;
    }
}
