<?php use App\View; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-cart-check"></i> Sales Order #<?= View::e($order['reference']) ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/sales-orders" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <strong>Order Details</strong>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Customer:</strong><br>
                        <?= View::e($order['customer_name'] ?? 'N/A') ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong><br>
                        <span class="badge status-<?= $order['status'] ?> fs-6">
                            <?= ucfirst($order['status']) ?>
                        </span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Created:</strong> <?= date('M d, Y', strtotime($order['created_at'])) ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Fulfilled:</strong> <?= $order['fulfilled_at'] ? date('M d, Y', strtotime($order['fulfilled_at'])) : 'Not yet' ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <strong>Items</strong>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order['items'] as $item): ?>
                            <tr>
                                <td>
                                    <strong><?= View::e($item['product']['name'] ?? 'Unknown') ?></strong><br>
                                    <small class="text-muted"><?= View::e($item['product']['sku'] ?? '') ?></small>
                                </td>
                                <td><?= $item['quantity'] ?> <?= View::e($item['product']['unit'] ?? '') ?></td>
                                <td><?= View::formatCurrency($item['unit_price']) ?></td>
                                <td><?= View::formatCurrency($item['quantity'] * $item['unit_price']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total Revenue:</th>
                            <th><?= View::formatCurrency($order['total_revenue']) ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <strong>Actions</strong>
            </div>
            <div class="card-body">
                <?php if ($order['status'] === 'draft' || $order['status'] === 'confirmed'): ?>
                    <form id="fulfillForm" onsubmit="return confirmFulfill(event)">
                        <p class="text-muted small mb-3">
                            Fulfilling this order will reduce stock levels for all items.
                        </p>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle"></i> Fulfill Order
                        </button>
                    </form>
                <?php elseif ($order['status'] === 'fulfilled'): ?>
                    <div class="alert alert-success mb-0">
                        <i class="bi bi-check-circle"></i> Order fulfilled on <?= date('M d, Y', strtotime($order['fulfilled_at'])) ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-secondary mb-0">
                        <i class="bi bi-x-circle"></i> Order cancelled
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($order['status'] === 'draft' || $order['status'] === 'confirmed'): ?>
        <div class="card mt-3">
            <div class="card-header">
                <strong>Stock Check</strong>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-2">Current stock availability:</p>
                <?php foreach ($order['items'] as $item): ?>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-truncate me-2"><?= View::e($item['product']['name'] ?? 'Unknown') ?></span>
                        <span class="badge bg-secondary"><?= $item['product']['current_stock'] ?? 0 ?> available</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function confirmFulfill(event) {
    event.preventDefault();

    if (!confirm('Are you sure you want to fulfill this order? This will reduce stock levels.')) {
        return false;
    }

    const orderId = <?= $order['id'] ?>;

    fetch(`/api/sales-orders/${orderId}/fulfill`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Order fulfilled successfully!');
            window.location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to fulfill order'));
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });

    return false;
}
</script>
