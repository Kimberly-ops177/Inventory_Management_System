<?php use App\View; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-cart-check"></i> Sales Orders</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/sales-orders/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> New Sales Order
        </a>
    </div>
</div>

<?php if (empty($orders)): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> No sales orders found. <a href="/sales-orders/create">Create your first sales order</a>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Total Revenue</th>
                    <th>Created</th>
                    <th>Fulfilled</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><strong><?= View::e($order['reference']) ?></strong></td>
                        <td><?= View::e($order['customer_name']) ?></td>
                        <td>
                            <span class="badge status-<?= $order['status'] ?>">
                                <?= ucfirst($order['status']) ?>
                            </span>
                        </td>
                        <td><?= View::formatCurrency($order['total_revenue']) ?></td>
                        <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                        <td><?= $order['fulfilled_at'] ? date('M d, Y', strtotime($order['fulfilled_at'])) : '-' ?></td>
                        <td class="table-actions">
                            <a href="/sales-orders/<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
