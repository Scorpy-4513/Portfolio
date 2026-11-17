<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $message = 'Security check failed. Please try again.';
    } else {
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $displayName = trim((string) ($_POST['display_name'] ?? 'Administrator'));

        if (!is_valid_email($email) || $password === '') {
            $message = 'Please provide a valid email and a non-empty password.';
        } else {
            $pdo = db_connect();
            $stmt = $pdo->prepare('SELECT id FROM admins WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                $message = 'An administrator with that email already exists.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $insert = $pdo->prepare('INSERT INTO admins (email, password_hash, display_name) VALUES (:email, :password_hash, :display_name)');
                $insert->execute([
                    ':email' => $email,
                    ':password_hash' => $hash,
                    ':display_name' => $displayName,
                ]);

                $message = 'Administrator account created successfully. You can now log in from admin/login.php.';
            }
        }
    }
}

$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Setup | Ajie C. Javier</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f3ee; color: #1b1f22; display: grid; place-items: center; min-height: 100vh; margin: 0; }
        .panel { width: min(92vw, 450px); background: #fff; border: 1px solid #dfe5d9; border-radius: 12px; padding: 2rem; }
        input, button { width: 100%; padding: 0.9rem 1rem; border-radius: 8px; border: 1px solid #dfe5d9; box-sizing: border-box; margin-bottom: 1rem; }
        button { background: #2d5f4c; color: #fff; border: none; font-weight: 700; cursor: pointer; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        .message { margin-bottom: 1rem; padding: 0.8rem 1rem; border-radius: 8px; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .message.error { background: #fef2f2; color: #991b1b; border-color: #fecaca; }
    </style>
</head>
<body>
    <div class="panel">
        <h1>Admin Setup</h1>
        <?php if (!empty($message)): ?>
            <div class="message <?php echo str_contains($message, 'successfully') || str_contains($message, 'now log in') ? '' : 'error'; ?>"><?php echo e($message); ?></div>
        <?php endif; ?>
        <form method="post" action="setup.php">
            <input type="hidden" name="csrf_token" value="<?php echo e($csrf); ?>">
            <div>
                <label for="display_name">Display Name</label>
                <input id="display_name" name="display_name" type="text" value="Administrator" required>
            </div>
            <div>
                <label for="email">Email</label>
                <input id="email" name="email" type="email" required>
            </div>
            <div>
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required>
            </div>
            <button type="submit">Create Administrator</button>
        </form>
        <p><a href="login.php">Go to Admin Login</a></p>
    </div>
</body>
</html>
