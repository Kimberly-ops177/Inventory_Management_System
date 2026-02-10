<?php use App\View; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-cart-plus"></i> Purchase Orders</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/purchase-orders/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> New Purchase Order
        </a>
    </div>
</div>

<?php if (empty($orders)): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> No purchase orders found. <a href="/purchase-orders/create">Create your first purchase order</a>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Supplier</th>
                    <th>Status</th>
                    <th>Total Cost</th>
                    <th>Order Date</th>
                    <th>Received Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><strong><?= View::e($order['reference']) ?></strong></td>
                        <td><?= View::e($order['supplier']['name'] ?? 'N/A') ?></td>
                        <td>
                            <span class="badge status-<?= $order['status'] ?>">
                                <?= ucfirst($order['status']) ?>
                            </span>
                        </td>
                        <td><?= View::formatCurrency($order['total_cost']) ?></td>
                        <td><?= $order['ordered_at'] ? date('M d, Y', strtotime($order['ordered_at'])) : '-' ?></td>
                        <td><?= $order['received_at'] ? date('M d, Y', strtotime($order['received_at'])) : '-' ?></td>
                        <td class="table-actions">
                            <a href="/purchase-orders/<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
