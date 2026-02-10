<?php use App\View; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-pencil"></i> Edit Supplier</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/suppliers" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="/suppliers/<?= $supplier['id'] ?>">
                    <input type="hidden" name="_method" value="PUT">

                    <div class="mb-3">
                        <label for="name" class="form-label">Supplier Name *</label>
                        <input type="text" class="form-control" id="name" name="name"
                               value="<?= View::e($supplier['name']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="contact_name" class="form-label">Contact Person</label>
                        <input type="text" class="form-control" id="contact_name" name="contact_name"
                               value="<?= View::e($supplier['contact_name'] ?? '') ?>">
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?= View::e($supplier['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone"
                                   value="<?= View::e($supplier['phone'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?= View::e($supplier['address'] ?? '') ?></textarea>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="/suppliers" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Supplier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
