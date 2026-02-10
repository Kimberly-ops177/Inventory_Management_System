<?php use App\View; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-pencil"></i> Edit Product</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/products" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Products
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="/products/<?= $product['id'] ?>" class="needs-validation" novalidate>
                    <input type="hidden" name="_method" value="PUT">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Product Name *</label>
                            <input type="text" class="form-control" id="name" name="name"
                                   value="<?= View::e($product['name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="sku" class="form-label">SKU *</label>
                            <input type="text" class="form-control" id="sku" name="sku"
                                   value="<?= View::e($product['sku']) ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>"
                                        <?= $product['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                    <?= View::e($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= View::e($product['description'] ?? '') ?></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="unit" class="form-label">Unit</label>
                            <select class="form-select" id="unit" name="unit">
                                <option value="pcs" <?= $product['unit'] == 'pcs' ? 'selected' : '' ?>>Pieces (pcs)</option>
                                <option value="box" <?= $product['unit'] == 'box' ? 'selected' : '' ?>>Box</option>
                                <option value="kg" <?= $product['unit'] == 'kg' ? 'selected' : '' ?>>Kilogram (kg)</option>
                                <option value="liter" <?= $product['unit'] == 'liter' ? 'selected' : '' ?>>Liter</option>
                                <option value="meter" <?= $product['unit'] == 'meter' ? 'selected' : '' ?>>Meter</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="cost_price" class="form-label">Cost Price *</label>
                            <input type="number" step="0.01" class="form-control" id="cost_price" name="cost_price"
                                   value="<?= $product['cost_price'] ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="sale_price" class="form-label">Sale Price *</label>
                            <input type="number" step="0.01" class="form-control" id="sale_price" name="sale_price"
                                   value="<?= $product['sale_price'] ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="reorder_level" class="form-label">Reorder Level</label>
                        <input type="number" class="form-control" id="reorder_level" name="reorder_level"
                               value="<?= $product['reorder_level'] ?>">
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="/products" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
