<?php use App\View; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-building"></i> Suppliers</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/suppliers/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Supplier
        </a>
    </div>
</div>

<?php if (empty($suppliers)): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> No suppliers found. <a href="/suppliers/create">Create your first supplier</a>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Contact Person</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($suppliers as $supplier): ?>
                    <tr>
                        <td><strong><?= View::e($supplier['name']) ?></strong></td>
                        <td><?= View::e($supplier['contact_name'] ?? '') ?></td>
                        <td><?= View::e($supplier['email'] ?? '') ?></td>
                        <td><?= View::e($supplier['phone'] ?? '') ?></td>
                        <td><?= View::e($supplier['address'] ?? '') ?></td>
                        <td class="table-actions">
                            <a href="/suppliers/<?= $supplier['id'] ?>/edit" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <form method="POST" action="/suppliers/<?= $supplier['id'] ?>/delete" style="display:inline;">
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Are you sure?')">
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
