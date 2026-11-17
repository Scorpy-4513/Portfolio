<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = db_connect();
$adminCount = (int) $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
$message = null;
$messageIsError = false;

if ($adminCount > 0) {
    http_response_code(403);
    $message = 'Administrator setup is already complete. Please use the login page.';
    $messageIsError = true;
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $message = 'Security check failed. Refresh the page and try again.';
        $messageIsError = true;
    } else {
        $name = trim((string) ($_POST['display_name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');

        if ($name === '' || strlen($name) > 120 || !is_valid_email($email) || strlen($email) > 254 || strlen($password) < 12) {
            $message = 'Enter a display name, valid email, and a password of at least 12 characters.';
            $messageIsError = true;
        } else {
            try {
                // The unique email constraint prevents duplicate accounts. Recheck before insert
                // so a second setup request cannot silently create an additional admin.
                $pdo->beginTransaction();
                $adminCount = (int) $pdo->query('SELECT COUNT(*) FROM admins FOR UPDATE')->fetchColumn();
                if ($adminCount > 0) {
                    $pdo->rollBack();
                    http_response_code(403);
                    $message = 'Administrator setup is already complete. Please use the login page.';
                    $messageIsError = true;
                } else {
                    $stmt = $pdo->prepare('INSERT INTO admins (email, password_hash, display_name) VALUES (:email, :password_hash, :display_name)');
                    $stmt->execute([
                        ':email' => $email,
                        ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
                        ':display_name' => $name,
                    ]);
                    $pdo->commit();
                    $message = 'Administrator account created. Continue to the login page.';
                }
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log('Administrator setup failed: ' . $e->getMessage());
                $message = 'The administrator account could not be created. Check the server log or database setup.';
                $messageIsError = true;
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
    <title>First Admin Setup | Portfolio</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 1rem; background: #f5f3ee; color: #1b1f22; font-family: Arial, sans-serif; }
        main { width: min(100%, 440px); background: white; border: 1px solid #dfe5d9; border-radius: 12px; padding: 2rem; }
        label { display: block; margin: 1rem 0 .4rem; font-weight: 600; }
        input { width: 100%; padding: .8rem; border: 1px solid #dfe5d9; border-radius: 6px; font-size: 1rem; }
        button { width: 100%; margin-top: 1.25rem; padding: .9rem; border: 0; border-radius: 6px; background: #2d5f4c; color: white; font-weight: 700; cursor: pointer; }
        .notice { margin: 1rem 0; padding: .8rem; border-radius: 6px; background: #ecfdf3; color: #17663a; }
        .error { background: #fef3f2; color: #a1251b; }
    </style>
</head>
<body>
<main>
    <h1>Set up administrator</h1>
    <p>This one-time form creates the first dashboard account. Use a unique password and keep it private.</p>
    <?php if ($message !== null): ?>
        <p class="notice<?php echo $messageIsError ? ' error' : ''; ?>"><?php echo e($message); ?></p>
    <?php endif; ?>
    <?php if ($adminCount === 0): ?>
        <form method="post" action="setup.php">
            <input type="hidden" name="csrf_token" value="<?php echo e($csrf); ?>">
            <label for="display_name">Display name</label>
            <input id="display_name" name="display_name" type="text" maxlength="120" required autocomplete="name">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" maxlength="254" required autocomplete="username">
            <label for="password">Password (at least 12 characters)</label>
            <input id="password" name="password" type="password" minlength="12" required autocomplete="new-password">
            <button type="submit">Create administrator</button>
        </form>
    <?php endif; ?>
    <p><a href="login.php">Go to login</a></p>
</main>
</body>
</html>
