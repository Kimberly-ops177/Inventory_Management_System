<?php
use App\Flash;
use App\View;

if (Flash::has()) {
    $messages = Flash::get();
    foreach ($messages as $flash) {
        $type = $flash['type'] ?? 'info';
        $message = $flash['message'] ?? '';

        $alertClass = match($type) {
            'success' => 'alert-success',
            'error' => 'alert-danger',
            'warning' => 'alert-warning',
            default => 'alert-info'
        };

        $icon = match($type) {
            'success' => 'bi-check-circle',
            'error' => 'bi-exclamation-circle',
            'warning' => 'bi-exclamation-triangle',
            default => 'bi-info-circle'
        };
        ?>
        <div class="alert <?= $alertClass ?> alert-dismissible fade show mt-3" role="alert">
            <i class="bi <?= $icon ?>"></i> <?= View::e($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php
    }
}
?>
