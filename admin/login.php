<?php
require __DIR__ . '/../includes/bootstrap.php';
require_https();
if (!empty($_SESSION['admin_id'])) redirect('index.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $keys = [hash('sha256', 'ip:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown')), hash('sha256', 'user:' . mb_strtolower($username))];
    $db->beginTransaction();
    $stmt = $db->prepare('INSERT INTO login_attempts (attempt_key, attempts, last_attempt) VALUES (?, 1, NOW()) ON DUPLICATE KEY UPDATE attempts = IF(last_attempt < DATE_SUB(NOW(), INTERVAL 15 MINUTE), 1, attempts + 1), last_attempt = NOW()');
    foreach ($keys as $key) $stmt->execute([$key]);
    $stmt = $db->prepare('SELECT MAX(attempts) FROM login_attempts WHERE attempt_key IN (?, ?)');
    $stmt->execute($keys);
    $attempts = (int) $stmt->fetchColumn();
    $db->commit();
    if ($attempts > 5) {
        $error = 'Too many attempts. Please wait 15 minutes and try again.';
    } else {
        $stmt = $db->prepare('SELECT * FROM admins WHERE username = ?');
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($admin && strlen($password) <= 72 && password_verify($password, $admin['password_hash'])) {
            if (password_needs_rehash($admin['password_hash'], PASSWORD_DEFAULT)) {
                $stmt = $db->prepare('UPDATE admins SET password_hash = ? WHERE id = ?');
                $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $admin['id']]);
            }
            $stmt = $db->prepare('DELETE FROM login_attempts WHERE attempt_key IN (?, ?)');
            $stmt->execute($keys);
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['last_active'] = time();
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            redirect('index.php');
        }
        $error = 'Incorrect username or password.';
    }
}
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin login</title>
<link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<main class="login-box">
<a href="../index.php">← Back to portfolio</a>
<p class="eyebrow">PORTFOLIO ADMIN</p>
<h1>Welcome back</h1>
<p>Sign in to manage your portfolio.</p><?php if ($error): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?><form method="post"><?= csrf_field() ?><label>Username<input name="username" autocomplete="username" required maxlength="100">
</label>
<label>Password<input name="password" type="password" autocomplete="current-password" required maxlength="72">
</label>
<button type="submit">Sign in</button>
</form>
</main>
</body>
</html>
