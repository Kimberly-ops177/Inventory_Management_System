<?php use App\View; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-cart-plus"></i> Purchase Order #<?= View::e($order['reference']) ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/purchase-orders" class="btn btn-outline-secondary">
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
                        <strong>Supplier:</strong><br>
                        <?= View::e($order['supplier']['name'] ?? 'N/A') ?>
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
                        <strong>Ordered:</strong> <?= $order['ordered_at'] ? date('M d, Y', strtotime($order['ordered_at'])) : 'Not yet' ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Received:</strong> <?= $order['received_at'] ? date('M d, Y', strtotime($order['received_at'])) : 'Not yet' ?>
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
                            <th>Unit Cost</th>
                            <th>Received</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order['items'] as $item): ?>
                            <tr>
                                <td><?= View::e($item['product']['name'] ?? 'Unknown') ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td><?= View::formatCurrency($item['unit_cost']) ?></td>
                                <td><?= $item['received_quantity'] ?></td>
                                <td><?= View::formatCurrency($item['quantity'] * $item['unit_cost']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end">Total:</th>
                            <th><?= View::formatCurrency($order['total_cost']) ?></th>
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
                <?php if ($order['status'] === 'draft' || $order['status'] === 'ordered'): ?>
                    <form id="receiveForm" onsubmit="return confirmReceive(event)">
                        <p class="text-muted small mb-3">
                            Receiving this order will add all items to inventory.
                        </p>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-box-seam"></i> Receive Order
                        </button>
                    </form>
                <?php elseif ($order['status'] === 'received'): ?>
                    <div class="alert alert-success mb-0">
                        <i class="bi bi-check-circle"></i> Order received on <?= date('M d, Y', strtotime($order['received_at'])) ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-secondary mb-0">
                        <i class="bi bi-x-circle"></i> Order cancelled
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <strong>Summary</strong>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Total Items:</span>
                    <strong><?= count($order['items']) ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Total Quantity:</span>
                    <strong><?= array_sum(array_column($order['items'], 'quantity')) ?></strong>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span>Total Cost:</span>
                    <strong class="text-primary"><?= View::formatCurrency($order['total_cost']) ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmReceive(event) {
    event.preventDefault();

    if (!confirm('Are you sure you want to receive this order? This will add all items to inventory.')) {
        return false;
    }

    const orderId = <?= $order['id'] ?>;

    fetch(`/api/purchase-orders/${orderId}/receive`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Order received successfully!');
            window.location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to receive order'));
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });

    return false;
}
</script>
