<?php use App\View; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-tags"></i> Categories</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/categories/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Category
        </a>
    </div>
</div>

<?php if (empty($categories)): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> No categories found. <a href="/categories/create">Create your first category</a>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><strong><?= View::e($category['name']) ?></strong></td>
                        <td><?= View::e($category['description'] ?? '') ?></td>
                        <td><?= date('M d, Y', strtotime($category['created_at'])) ?></td>
                        <td class="table-actions">
                            <a href="/categories/<?= $category['id'] ?>/edit" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <form method="POST" action="/categories/<?= $category['id'] ?>/delete" style="display:inline;">
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
