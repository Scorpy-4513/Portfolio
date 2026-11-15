<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

$pdo = db_connect();

$stmt = $pdo->query('SELECT COUNT(*) AS total FROM projects WHERE is_published = 1');
$projectCount = (int) ($stmt->fetch()['total'] ?? 0);

$stmt = $pdo->query('SELECT COUNT(*) AS total FROM skills');
$skillCount = (int) ($stmt->fetch()['total'] ?? 0);

$stmt = $pdo->query('SELECT COUNT(*) AS total FROM contact_messages');
$messageCount = (int) ($stmt->fetch()['total'] ?? 0);

$stmt = $pdo->query('SELECT COUNT(*) AS total FROM education');
$educationCount = (int) ($stmt->fetch()['total'] ?? 0);

$flashSuccess = get_flash('success');
$flashError = get_flash('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Ajie C. Javier</title>
    <style>
        :root {
            --bg: #f3f2ee;
            --panel: #ffffff;
            --text: #1b1d1f;
            --muted: #586168;
            --accent: #2d5f4c;
            --border: #dfe3d9;
            --shadow: rgba(21,26,30,0.08);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: Arial, sans-serif;
        }
        .topbar {
            background: var(--panel);
            border-bottom: 1px solid var(--border);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .nav {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }
        a { color: inherit; text-decoration: none; }
        .layout {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1.25rem;
            margin-top: 1.5rem;
        }
        .card {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 8px 20px var(--shadow);
            padding: 1.5rem;
        }
        .label {
            color: var(--muted);
            font-size: 0.9rem;
        }
        .value {
            font-size: 2rem;
            font-weight: 700;
            margin-top: 0.5rem;
        }
        .alert {
            padding: 0.8rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .alert.success {
            background: #ecfdf5;
            border: 1px solid #9fe7c4;
            color: #146c43;
        }
        .alert.error {
            background: #fef3f2;
            border: 1px solid #f5c5c2;
            color: #b42318;
        }
        .profile-box {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }
        .btn {
            display: inline-block;
            padding: 0.8rem 1rem;
            border-radius: 8px;
            background: var(--accent);
            color: white;
            font-weight: 700;
        }
        .btn.secondary {
            background: white;
            border: 1px solid var(--border);
            color: var(--text);
        }
    </style>
</head>
<body>
    <header class="topbar">
        <strong>Ajie C. Javier</strong>
        <div class="nav">
            <span>Logged in as <?php echo e(current_admin_name()); ?></span>
            <a href="/admin/logout.php" class="btn secondary">Logout</a>
        </div>
    </header>

    <main class="layout">
        <section class="profile-box">
            <h1>Dashboard</h1>
            <p>Manage the public portfolio and administrator content.</p>
            <div class="actions">
                <a href="/index.php" class="btn secondary">Public Site</a>
                <a href="/admin/login.php" class="btn secondary">Refresh</a>
            </div>
        </section>

        <?php if ($flashSuccess): ?>
            <div class="alert success"><?php echo e($flashSuccess); ?></div>
        <?php endif; ?>

        <?php if ($flashError): ?>
            <div class="alert error"><?php echo e($flashError); ?></div>
        <?php endif; ?>

        <section class="grid">
            <article class="card">
                <div class="label">Published Projects</div>
                <div class="value"><?php echo e((string) $projectCount); ?></div>
            </article>
            <article class="card">
                <div class="label">Skills</div>
                <div class="value"><?php echo e((string) $skillCount); ?></div>
            </article>
            <article class="card">
                <div class="label">Messages</div>
                <div class="value"><?php echo e((string) $messageCount); ?></div>
            </article>
            <article class="card">
                <div class="label">Education Entries</div>
                <div class="value"><?php echo e((string) $educationCount); ?></div>
            </article>
        </section>
    </main>
</body>
</html>
