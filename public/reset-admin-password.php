<?php
// Temporary script to reset admin password
// DELETE THIS FILE after resetting password!

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/bootstrap.php';

use App\Database;

$resetComplete = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset'])) {
    try {
        $db = Database::connection();

        // Hash the new password
        $newPasswordHash = password_hash('password', PASSWORD_DEFAULT);

        // Update admin user
        $stmt = $db->prepare("UPDATE users SET password_hash = :hash WHERE email = 'admin@example.com'");
        $stmt->execute(['hash' => $newPasswordHash]);

        $resetComplete = true;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Admin Password</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        button { background: #007bff; color: white; border: none; padding: 12px 24px; font-size: 16px; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .success { color: #28a745; padding: 10px; background: #d4edda; border-radius: 4px; margin: 10px 0; }
        .error { color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 4px; margin: 10px 0; }
        .warning { color: #856404; padding: 10px; background: #fff3cd; border-radius: 4px; margin: 10px 0; }
        code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔑 Reset Admin Password</h1>
        <div class="warning"><strong>⚠️ Security Warning:</strong> Delete this file after resetting!</div>

        <?php if ($error): ?>
            <div class="error"><strong>✗ Error:</strong> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($resetComplete): ?>
            <div class="success"><strong>✓ Success!</strong> Admin password has been reset.</div>
            <p><strong>Login credentials:</strong></p>
            <ul>
                <li>Email: <code>admin@example.com</code></li>
                <li>Password: <code>password</code></li>
            </ul>
            <p><a href="/login"><button>Go to Login</button></a></p>
            <div class="warning"><strong>⚠️ Delete public/reset-admin-password.php now!</strong></div>
        <?php else: ?>
            <p>This will reset the admin password to: <code>password</code></p>
            <form method="POST">
                <input type="hidden" name="reset" value="1">
                <button type="submit">Reset Password Now</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
