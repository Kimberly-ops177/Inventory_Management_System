<?php
declare(strict_types=1);

namespace App\Controllers;

use App\View;
use App\Models\Category;
use App\Flash;
use App\Validator;

class CategoryController
{
    private View $view;

    public function __construct()
    {
        $this->view = new View();
    }

    public function index(): string
    {
        $categories = Category::all('name', 'ASC');

        $content = $this->view->render('categories/index', [
            'categories' => $categories
        ]);

        return $this->view->layout('app', $content, ['title' => 'Categories']);
    }

    public function create(): string
    {
        $content = $this->view->render('categories/create');
        return $this->view->layout('app', $content, ['title' => 'Create Category']);
    }

    public function store(): void
    {
        $data = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
        ];

        $validator = Validator::make($data, [
            'name' => 'required|min:3',
        ]);

        if ($validator->fails()) {
            Flash::error('Validation failed');
            header('Location: /categories/create');
            exit;
        }

        Category::create($data);
        Flash::success('Category created successfully');
        header('Location: /categories');
        exit;
    }

    public function edit(int $id): string
    {
        $category = Category::find($id);
        if (!$category) {
            Flash::error('Category not found');
            header('Location: /categories');
            exit;
        }

        $content = $this->view->render('categories/edit', [
            'category' => $category->toArray()
        ]);

        return $this->view->layout('app', $content, ['title' => 'Edit Category']);
    }

    public function update(int $id): void
    {
        $category = Category::find($id);
        if (!$category) {
            Flash::error('Category not found');
            header('Location: /categories');
            exit;
        }

        $data = [
            'name' => $_POST['name'] ?? $category->name,
            'description' => $_POST['description'] ?? $category->description,
        ];

        $category->update($data);
        Flash::success('Category updated successfully');
        header('Location: /categories');
        exit;
    }

    public function destroy(int $id): void
    {
        $category = Category::find($id);
        if (!$category) {
            Flash::error('Category not found');
            header('Location: /categories');
            exit;
        }

        $category->delete();
        Flash::success('Category deleted successfully');
        header('Location: /categories');
        exit;
    }
}
