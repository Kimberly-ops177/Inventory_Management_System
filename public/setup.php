<?php
// Simple setup page - NO bootstrap loading to avoid crashes
// Delete this file after setup!

// Only load when form is submitted
$showForm = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['seed'])) {
    $showForm = false;

    // Now load everything needed
    require __DIR__ . '/../vendor/autoload.php';

    // Load environment manually
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();

    // Start session manually
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Setup</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        button { background: #007bff; color: white; border: none; padding: 12px 24px; font-size: 16px; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .success { color: #28a745; padding: 10px; background: #d4edda; border-radius: 4px; margin: 10px 0; }
        .error { color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 4px; margin: 10px 0; }
        .warning { color: #856404; padding: 10px; background: #fff3cd; border-radius: 4px; margin: 10px 0; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 12px; white-space: pre-wrap; }
        code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Database Setup</h1>
        <div class="warning"><strong>⚠️ Security Warning:</strong> Delete this file after setup!</div>

        <?php if ($showForm): ?>
            <p>This will populate your database with:</p>
            <ul>
                <li>✓ Admin user account</li>
                <li>✓ Sample categories</li>
                <li>✓ Sample suppliers</li>
                <li>✓ Sample products</li>
                <li>✓ Purchase & Sales orders</li>
            </ul>
            <form method="POST">
                <input type="hidden" name="seed" value="1">
                <button type="submit">Seed Database Now</button>
            </form>
        <?php else: ?>
            <h2>Running Database Seed...</h2>
            <?php
            ob_start();
            try {
                include __DIR__ . '/../database/seed.php';
                $output = ob_get_clean();

                echo '<pre>' . htmlspecialchars($output) . '</pre>';
                echo '<div class="success"><strong>✓ Success!</strong> Database seeded.</div>';
                echo '<p><strong>Default Login:</strong></p>';
                echo '<ul><li>Email: <code>admin@example.com</code></li><li>Password: <code>password</code></li></ul>';
                echo '<p><a href="/login"><button>Go to Login</button></a></p>';
                echo '<div class="warning"><strong>⚠️ Delete public/setup.php now!</strong></div>';
            } catch (Exception $e) {
                $output = ob_get_clean();
                echo '<pre>' . htmlspecialchars($output) . '</pre>';
                echo '<div class="error"><strong>✗ Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
                echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
            }
            ?>
        <?php endif; ?>
    </div>
</body>
</html>
