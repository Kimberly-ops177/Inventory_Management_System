<?php use App\View; ?>
<main class="form-signin w-100 m-auto">
    <form method="POST" action="/login">
        <div class="text-center mb-4">
            <h1 class="h3 mb-3 fw-normal">
                <i class="bi bi-box-seam"></i> Inventory System
            </h1>
            <p class="text-muted">Please sign in to continue</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle"></i> <?= View::e($error) ?>
            </div>
        <?php endif; ?>

        <div class="form-floating mb-3">
            <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required autofocus>
            <label for="email">Email address</label>
        </div>

        <div class="form-floating mb-3">
            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
            <label for="password">Password</label>
        </div>

        <button class="w-100 btn btn-lg btn-primary" type="submit">
            <i class="bi bi-box-arrow-in-right"></i> Sign in
        </button>

        <p class="mt-5 mb-3 text-muted text-center">&copy; 2024 Inventory System</p>
    </form>
</main>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
