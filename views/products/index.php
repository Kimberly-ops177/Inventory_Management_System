<?php use App\View; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-box"></i> Products</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/products/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Product
        </a>
    </div>
</div>

<?php if (empty($products)): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> No products found. <a href="/products/create">Create your first product</a>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Cost Price</th>
                    <th>Sale Price</th>
                    <th>Current Stock</th>
                    <th>Reorder Level</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><code><?= View::e($product['sku']) ?></code></td>
                        <td><?= View::e($product['name']) ?></td>
                        <td><?= View::e($product['category_name']) ?></td>
                        <td><?= View::formatCurrency($product['cost_price']) ?></td>
                        <td><?= View::formatCurrency($product['sale_price']) ?></td>
                        <td>
                            <?php if ($product['current_stock'] < $product['reorder_level']): ?>
                                <span class="stock-status-low"><?= $product['current_stock'] ?></span>
                            <?php else: ?>
                                <span class="stock-status-ok"><?= $product['current_stock'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= $product['reorder_level'] ?></td>
                        <td>
                            <?php if ($product['current_stock'] < $product['reorder_level']): ?>
                                <span class="badge bg-danger">Low Stock</span>
                            <?php else: ?>
                                <span class="badge bg-success">In Stock</span>
                            <?php endif; ?>
                        </td>
                        <td class="table-actions">
                            <a href="/products/<?= $product['id'] ?>/edit" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <form method="POST" action="/products/<?= $product['id'] ?>/delete" style="display:inline;">
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Are you sure you want to delete this product?')">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
