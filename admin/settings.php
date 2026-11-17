<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();
$pdo = db_connect();
$editableSettings = [
    'site_title' => 'Site title',
    'site_description' => 'Site description',
    'theme_color' => 'Theme color',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Security check failed. Please try again.');
        redirect('settings.php');
    }

    $values = [
        'site_title' => trim((string) ($_POST['site_title'] ?? '')),
        'site_description' => trim((string) ($_POST['site_description'] ?? '')),
        'theme_color' => trim((string) ($_POST['theme_color'] ?? '')),
    ];
    if ($values['site_title'] === '') {
        set_flash('error', 'Site title is required.');
        redirect('settings.php');
    }
    if ($values['theme_color'] !== '' && !preg_match('/^#[0-9a-fA-F]{6}$/', $values['theme_color'])) {
        set_flash('error', 'Theme color must be a six-digit hex color (for example, #2D5F4C).');
        redirect('settings.php');
    }

    $stmt = $pdo->prepare(
        'INSERT INTO settings (setting_key, setting_value) VALUES (:setting_key, :setting_value)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );
    $pdo->beginTransaction();
    try {
        foreach ($values as $key => $value) {
            $stmt->execute([':setting_key' => $key, ':setting_value' => $value]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Unable to save portfolio settings: ' . $e->getMessage());
        set_flash('error', 'Settings could not be saved. Please try again.');
        redirect('settings.php');
    }

    set_flash('success', 'Settings updated.');
    redirect('settings.php');
}

$settings = fetch_settings($pdo);
$flashSuccess = get_flash('success');
$flashError = get_flash('error');
$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | Dashboard</title>
    <style>
        body { margin:0; font-family:Arial,sans-serif; background:#f3f2ee; color:#1b1d1f; }
        .wrap { max-width:900px; margin:40px auto; padding:0 20px; }
        .top { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:20px; }
        .panel { background:#fff; border:1px solid #dfe3d9; border-radius:12px; padding:24px; box-shadow:0 8px 20px rgba(21,26,30,.08); }
        .btn { display:inline-block; padding:10px 16px; background:#2d5f4c; color:#fff; border-radius:8px; text-decoration:none; border:0; cursor:pointer; }
        .secondary { background:#fff; color:#1b1d1f; border:1px solid #dfe3d9; }
        .alert { padding:12px 14px; border-radius:8px; margin-bottom:16px; }
        .success { background:#ecfdf5; border:1px solid #9fe7c4; color:#146c43; }
        .error { background:#fef3f2; border:1px solid #f5c5c2; color:#b42318; }
        form { display:grid; gap:16px; }
        label { display:block; font-weight:700; margin-bottom:8px; }
        input, textarea { width:100%; padding:10px 12px; border:1px solid #dfe3d9; border-radius:8px; font-size:16px; box-sizing:border-box; }
        textarea { min-height:130px; }
        .help { color:#586168; font-size:.9rem; }
    </style>
</head>
<body>
    <main class="wrap">
        <div class="top"><h1>Site Settings</h1><a href="dashboard.php" class="btn secondary">Back to Dashboard</a></div>
        <?php if ($flashSuccess): ?><div class="alert success"><?php echo e($flashSuccess); ?></div><?php endif; ?>
        <?php if ($flashError): ?><div class="alert error"><?php echo e($flashError); ?></div><?php endif; ?>
        <section class="panel">
            <form method="post" action="settings.php">
                <input type="hidden" name="csrf_token" value="<?php echo e($csrf); ?>">
                <div><label for="site_title">Site title</label><input id="site_title" name="site_title" maxlength="180" value="<?php echo e((string) ($settings['site_title'] ?? 'Ajie C. Javier | Portfolio')); ?>" required></div>
                <div><label for="site_description">Site description</label><textarea id="site_description" name="site_description"><?php echo e((string) ($settings['site_description'] ?? '')); ?></textarea></div>
                <div><label for="theme_color">Theme color</label><input id="theme_color" name="theme_color" type="text" pattern="#[0-9A-Fa-f]{6}" maxlength="7" value="<?php echo e((string) ($settings['theme_color'] ?? '#2D5F4C')); ?>"><p class="help">Use a six-digit hex color, such as #2D5F4C.</p></div>
                <button class="btn" type="submit">Save Settings</button>
            </form>
        </section>
    </main>
</body>
</html>
