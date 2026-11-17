<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();
$pdo = db_connect();

$projectCount = (int) $pdo->query('SELECT COUNT(*) FROM projects WHERE is_published = 1')->fetchColumn();
$skillCount = (int) $pdo->query('SELECT COUNT(*) FROM skills')->fetchColumn();
$messageCount = (int) $pdo->query('SELECT COUNT(*) FROM contact_messages')->fetchColumn();
$educationCount = (int) $pdo->query('SELECT COUNT(*) FROM education')->fetchColumn();
$experienceCount = (int) $pdo->query('SELECT COUNT(*) FROM experiences')->fetchColumn();

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
        :root { --bg:#f3f2ee; --panel:#fff; --text:#1b1d1f; --muted:#586168; --accent:#2d5f4c; --border:#dfe3d9; --shadow:rgba(21,26,30,.08); }
        * { box-sizing:border-box; }
        body { margin:0; background:var(--bg); color:var(--text); font-family:Arial,sans-serif; }
        .topbar { background:var(--panel); border-bottom:1px solid var(--border); padding:1rem 2rem; display:flex; justify-content:space-between; align-items:center; gap:1rem; }
        .nav,.actions { display:flex; gap:1rem; align-items:center; flex-wrap:wrap; }
        a { color:inherit; text-decoration:none; }
        .layout { max-width:1200px; margin:0 auto; padding:2rem; }
        .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:1.25rem; margin-top:1.5rem; }
        .card,.profile-box { background:var(--panel); border:1px solid var(--border); border-radius:12px; box-shadow:0 8px 20px var(--shadow); padding:1.5rem; }
        .profile-box { margin-bottom:1.5rem; }
        .label { color:var(--muted); font-size:.9rem; }
        .value { font-size:2rem; font-weight:700; margin-top:.5rem; }
        .actions { margin-top:1rem; }
        .btn { display:inline-block; padding:.8rem 1rem; border-radius:8px; background:var(--accent); color:#fff; font-weight:700; }
        .btn.secondary { background:#fff; border:1px solid var(--border); color:var(--text); }
        .alert { padding:.8rem 1rem; border-radius:8px; margin-bottom:1rem; }
        .alert.success { background:#ecfdf5; border:1px solid #9fe7c4; color:#146c43; }
        .alert.error { background:#fef3f2; border:1px solid #f5c5c2; color:#b42318; }
    </style>
</head>
<body>
    <header class="topbar">
        <strong>Ajie C. Javier</strong>
        <div class="nav"><span>Logged in as <?php echo e(current_admin_name()); ?></span><a href="logout.php" class="btn secondary">Logout</a></div>
    </header>
    <main class="layout">
        <section class="profile-box">
            <h1>Dashboard</h1>
            <p>Manage the public portfolio and administrator content.</p>
            <nav class="actions" aria-label="Admin pages">
                <a href="profile.php" class="btn secondary">Profile</a>
                <a href="skills.php" class="btn secondary">Skills</a>
                <a href="projects.php" class="btn secondary">Projects</a>
                <a href="education.php" class="btn secondary">Education</a>
                <a href="experience.php" class="btn secondary">Experience</a>
                <a href="settings.php" class="btn secondary">Settings</a>
                <a href="messages.php" class="btn secondary">Messages</a>
                <a href="../index.php" class="btn secondary">Public Site</a>
                <a href="dashboard.php" class="btn secondary">Refresh</a>
            </nav>
        </section>
        <?php if ($flashSuccess): ?><div class="alert success"><?php echo e($flashSuccess); ?></div><?php endif; ?>
        <?php if ($flashError): ?><div class="alert error"><?php echo e($flashError); ?></div><?php endif; ?>
        <section class="grid">
            <article class="card"><div class="label">Published Projects</div><div class="value"><?php echo e((string) $projectCount); ?></div></article>
            <article class="card"><div class="label">Skills</div><div class="value"><?php echo e((string) $skillCount); ?></div></article>
            <article class="card"><div class="label">Messages</div><div class="value"><?php echo e((string) $messageCount); ?></div></article>
            <article class="card"><div class="label">Education Entries</div><div class="value"><?php echo e((string) $educationCount); ?></div></article>
            <article class="card"><div class="label">Experience Entries</div><div class="value"><?php echo e((string) $experienceCount); ?></div></article>
        </section>
    </main>
</body>
</html>
