<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$pdo = db_connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Security check failed. Please try again.');
        redirect('login.php');
    }

    $email = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $result = login_admin($pdo, $email, $password);

    if ($result['success']) {
        redirect('dashboard.php');
    }

    set_flash('error', $result['message']);
    redirect('login.php');
}

$flashError = get_flash('error');
$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Ajie C. Javier</title>
    <style>
        :root {
            --bg: #f5f3ee;
            --panel: #ffffff;
            --text: #1b1f22;
            --muted: #5c636a;
            --accent: #2d5f4c;
            --border: #dfe5d9;
            --danger: #b42318;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            display: grid;
            place-items: center;
            min-height: 100vh;
        }
        .login-box {
            width: min(92vw, 440px);
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 12px 32px rgba(0,0,0,0.08);
            padding: 2rem;
        }
        h1 { margin: 0 0 0.5rem; font-size: 2rem; }
        .subtitle { color: var(--muted); margin-bottom: 1.5rem; }
        .alert {
            background: #fef3f2;
            border: 1px solid #f5c5c2;
            color: var(--danger);
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        input {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 0.9rem 1rem;
            font-size: 1rem;
            margin-bottom: 1rem;
        }
        button {
            width: 100%;
            border: none;
            background: var(--accent);
            color: white;
            padding: 0.9rem 1rem;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
        }
        .meta { margin-top: 1rem; text-align: center; color: var(--muted); font-size: 0.9rem; }
        a { color: var(--accent); }
    </style>
</head>
<body>
    <main class="login-box">
        <h1>Admin Login</h1>
        <p class="subtitle">Secure portfolio dashboard</p>

        <?php if ($flashError): ?>
            <div class="alert"><?php echo e($flashError); ?></div>
        <?php endif; ?>

        <form method="post" action="login.php">
            <input type="hidden" name="csrf_token" value="<?php echo e($csrf); ?>">

            <div>
                <label for="email">Email</label>
                <input id="email" name="email" type="email" required autocomplete="username">
            </div>

            <div>
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required autocomplete="current-password">
            </div>

            <button type="submit">Login</button>
        </form>

        <div class="meta">
            <a href="../index.php">Return to public site</a>
        </div>
    </main>
</body>
</html>
