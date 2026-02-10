<?php use App\View; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-speedometer2"></i> Dashboard</h1>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card card-stat">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Total Products</h6>
                <h3 class="card-title"><?= View::e($totalProducts) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat <?= $lowStockAlerts > 0 ? 'danger' : '' ?>">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Low Stock Alerts</h6>
                <h3 class="card-title">
                    <?= View::e($lowStockAlerts) ?>
                    <?php if ($lowStockAlerts > 0): ?>
                        <span class="badge bg-danger"><?= $lowStockAlerts ?></span>
                    <?php endif; ?>
                </h3>
                <?php if ($lowStockAlerts > 0): ?>
                    <a href="/stock/alerts" class="btn btn-sm btn-outline-danger">View Alerts</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat success">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Today's Sales</h6>
                <h3 class="card-title"><?= View::e($todaysSalesOrders) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat warning">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Inventory Value</h6>
                <h3 class="card-title"><?= View::formatCurrency($inventoryValue) ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity & Low Stock -->
<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-clock-history"></i> Recent Stock Movements
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentMovements)): ?>
                    <p class="text-muted text-center py-3">No recent movements</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Direction</th>
                                    <th>Quantity</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentMovements as $movement): ?>
                                    <tr>
                                        <td><?= View::e($movement['product_name']) ?></td>
                                        <td>
                                            <?php if ($movement['direction'] === 'in'): ?>
                                                <span class="badge bg-success"><i class="bi bi-arrow-down"></i> In</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning"><i class="bi bi-arrow-up"></i> Out</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= View::e($movement['quantity']) ?></td>
                                        <td><?= date('M d, H:i', strtotime($movement['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-exclamation-triangle"></i> Low Stock Products
            </div>
            <div class="card-body p-0">
                <?php if (empty($lowStockProducts)): ?>
                    <p class="text-muted text-center py-3">No low stock items</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Current</th>
                                    <th>Reorder</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($lowStockProducts, 0, 10) as $product): ?>
                                    <tr>
                                        <td><?= View::e($product['name']) ?></td>
                                        <td>
                                            <span class="stock-status-low"><?= View::e($product['current_stock']) ?></span>
                                        </td>
                                        <td><?= View::e($product['reorder_level']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
