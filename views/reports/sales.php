<?php use App\View; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-graph-up"></i> Sales Report</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button onclick="window.print()" class="btn btn-outline-secondary">
            <i class="bi bi-printer"></i> Print
        </button>
    </div>
</div>

<!-- Date Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/reports/sales" class="row g-3">
            <div class="col-md-4">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="<?= $dateFrom ?>">
            </div>
            <div class="col-md-4">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="<?= $dateTo ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Total Revenue</h6>
                <h3 class="card-title text-success"><?= View::formatCurrency($totalRevenue) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Total Orders</h6>
                <h3 class="card-title"><?= $totalOrders ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Fulfilled Orders</h6>
                <h3 class="card-title"><?= $fulfilledOrders ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Average Order Value</h6>
                <h3 class="card-title">
                    <?= $totalOrders > 0 ? View::formatCurrency($totalRevenue / $totalOrders) : 'KES 0.00' ?>
                </h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Orders List -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <strong>Sales Orders (<?= date('M d', strtotime($dateFrom)) ?> - <?= date('M d, Y', strtotime($dateTo)) ?>)</strong>
            </div>
            <div class="card-body p-0">
                <?php if (empty($orders)): ?>
                    <div class="p-3 text-center text-muted">
                        No sales orders found for this period
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Reference</th>
                                    <th>Customer</th>
                                    <th>Status</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                        <td><strong><?= View::e($order['reference']) ?></strong></td>
                                        <td><?= View::e($order['customer_name']) ?></td>
                                        <td>
                                            <span class="badge status-<?= $order['status'] ?>">
                                                <?= ucfirst($order['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= View::formatCurrency($order['total_revenue']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Top Products -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <strong>Top Selling Products</strong>
            </div>
            <div class="card-body p-0">
                <?php if (empty($productsSold)): ?>
                    <div class="p-3 text-center text-muted">
                        No products sold
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach (array_slice($productsSold, 0, 10, true) as $productName => $quantity): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span><?= View::e($productName) ?></span>
                                <span class="badge bg-primary rounded-pill"><?= $quantity ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="text-muted mt-3">
    <small>Report generated on <?= date('F d, Y \a\t H:i') ?></small>
</div>

<style>
@media print {
    .btn-toolbar, .sidebar, .navbar, .card:first-child { display: none !important; }
}
</style>
