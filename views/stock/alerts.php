<?php use App\View; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-exclamation-triangle"></i> Stock Alerts</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <form method="POST" action="/stock/check-alerts" style="display:inline;">
            <button type="submit" class="btn btn-warning">
                <i class="bi bi-arrow-clockwise"></i> Check All Products
            </button>
        </form>
    </div>
</div>

<?php if (empty($alerts)): ?>
    <div class="alert alert-success">
        <i class="bi bi-check-circle"></i> No low stock alerts! All products are above their reorder levels.
    </div>
<?php else: ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i> <strong><?= count($alerts) ?> product(s)</strong> are low on stock!
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Current Stock</th>
                    <th>Reorder Level</th>
                    <th>Triggered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($alerts as $alert): ?>
                    <tr>
                        <td><strong><?= View::e($alert['product_name']) ?></strong></td>
                        <td><code><?= View::e($alert['sku']) ?></code></td>
                        <td><span class="stock-status-low"><?= $alert['current_stock'] ?></span></td>
                        <td><?= $alert['threshold'] ?></td>
                        <td><?= date('M d, Y H:i', strtotime($alert['triggered_at'])) ?></td>
                        <td>
                            <a href="/products/<?= $alert['product_id'] ?>/edit" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-box"></i> View Product
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
