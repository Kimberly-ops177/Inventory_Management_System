<?php use App\View; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-file-earmark-bar-graph"></i> Stock Report</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button onclick="window.print()" class="btn btn-outline-secondary">
            <i class="bi bi-printer"></i> Print
        </button>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Total Products</h6>
                <h3 class="card-title"><?= count($products) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Total Inventory Value</h6>
                <h3 class="card-title"><?= View::formatCurrency($totalValue) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Low Stock Items</h6>
                <h3 class="card-title">
                    <?= $lowStockCount ?>
                    <?php if ($lowStockCount > 0): ?>
                        <span class="badge bg-danger"><?= $lowStockCount ?></span>
                    <?php endif; ?>
                </h3>
            </div>
        </div>
    </div>
</div>

<!-- Stock Details -->
<div class="card">
    <div class="card-header">
        <strong>Current Stock Levels</strong>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Current Stock</th>
                        <th>Reorder Level</th>
                        <th>Unit Cost</th>
                        <th>Stock Value</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr class="<?= $product['current_stock'] < $product['reorder_level'] ? 'table-warning' : '' ?>">
                            <td><code><?= View::e($product['sku']) ?></code></td>
                            <td><strong><?= View::e($product['name']) ?></strong></td>
                            <td><?= View::e($product['category_name'] ?? '-') ?></td>
                            <td>
                                <?php if ($product['current_stock'] < $product['reorder_level']): ?>
                                    <span class="stock-status-low"><?= $product['current_stock'] ?></span>
                                <?php else: ?>
                                    <span class="stock-status-ok"><?= $product['current_stock'] ?></span>
                                <?php endif; ?>
                                <?= View::e($product['unit']) ?>
                            </td>
                            <td><?= $product['reorder_level'] ?></td>
                            <td><?= View::formatCurrency($product['cost_price']) ?></td>
                            <td><?= View::formatCurrency($product['stock_value']) ?></td>
                            <td>
                                <?php if ($product['current_stock'] == 0): ?>
                                    <span class="badge bg-danger">Out of Stock</span>
                                <?php elseif ($product['current_stock'] < $product['reorder_level']): ?>
                                    <span class="badge bg-warning">Low Stock</span>
                                <?php else: ?>
                                    <span class="badge bg-success">In Stock</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-active">
                        <th colspan="6" class="text-end">Total Inventory Value:</th>
                        <th><?= View::formatCurrency($totalValue) ?></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="text-muted mt-3">
    <small>Report generated on <?= date('F d, Y \a\t H:i') ?></small>
</div>

<style>
@media print {
    .btn-toolbar, .sidebar, .navbar { display: none !important; }
}
</style>
