<?php use App\View; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-arrow-left-right"></i> Stock Movements</h1>
</div>

<?php if (empty($movements)): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> No stock movements found. Create purchase or sales orders to see movements.
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-header">
            <strong>Last 100 Movements</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Product</th>
                            <th>Direction</th>
                            <th>Quantity</th>
                            <th>Source</th>
                            <th>Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movements as $movement): ?>
                            <tr>
                                <td><?= date('M d, Y H:i', strtotime($movement['created_at'])) ?></td>
                                <td>
                                    <strong><?= View::e($movement['product']['name'] ?? 'Unknown') ?></strong>
                                    <br>
                                    <small class="text-muted"><?= View::e($movement['product']['sku'] ?? '') ?></small>
                                </td>
                                <td>
                                    <?php if ($movement['direction'] === 'in'): ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-arrow-down"></i> IN
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">
                                            <i class="bi bi-arrow-up"></i> OUT
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= $movement['quantity'] ?></strong>
                                    <?= View::e($movement['product']['unit'] ?? '') ?>
                                </td>
                                <td>
                                    <?php
                                    $sourceLabels = [
                                        'purchase_order' => 'Purchase Order',
                                        'sales_order' => 'Sales Order',
                                        'adjustment' => 'Adjustment',
                                        'initial_stock' => 'Initial Stock',
                                        'return' => 'Return'
                                    ];
                                    $sourceType = $movement['source'] ?? '';
                                    echo $sourceLabels[$sourceType] ?? ucfirst($sourceType);
                                    ?>
                                </td>
                                <td>
                                    <?= $movement['source_id'] ? '#' . $movement['source_id'] : '-' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="alert alert-info mt-3">
        <i class="bi bi-info-circle"></i> Showing last 100 movements. Stock is calculated in real-time from all movements.
    </div>
<?php endif; ?>
