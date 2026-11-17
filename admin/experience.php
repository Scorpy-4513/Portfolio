<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();
$pdo = db_connect();

function valid_optional_date(string $value): bool
{
    if ($value === '') {
        return true;
    }
    $date = DateTime::createFromFormat('!Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Security check failed. Please try again.');
        redirect('experience.php');
    }

    $action = (string) ($_POST['action'] ?? 'save');
    $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
    if ($action === 'delete') {
        if ($id > 0) {
            $stmt = $pdo->prepare('DELETE FROM experiences WHERE id = :id');
            $stmt->execute([':id' => $id]);
            set_flash('success', 'Experience entry deleted.');
        }
        redirect('experience.php');
    }

    $organization = trim((string) ($_POST['organization'] ?? ''));
    $position = trim((string) ($_POST['position'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $startDate = trim((string) ($_POST['start_date'] ?? ''));
    $isCurrent = isset($_POST['is_current']) ? 1 : 0;
    $endDate = $isCurrent ? '' : trim((string) ($_POST['end_date'] ?? ''));
    $sortOrder = filter_var($_POST['sort_order'] ?? 0, FILTER_VALIDATE_INT);

    if ($organization === '' || $position === '') {
        set_flash('error', 'Organization and position are required.');
        redirect('experience.php');
    }
    if (!valid_optional_date($startDate) || !valid_optional_date($endDate)) {
        set_flash('error', 'Enter valid start and end dates.');
        redirect('experience.php');
    }
    if ($startDate !== '' && $endDate !== '' && $endDate < $startDate) {
        set_flash('error', 'End date cannot be before the start date.');
        redirect('experience.php');
    }
    if ($sortOrder === false) {
        set_flash('error', 'Sort order must be a whole number.');
        redirect('experience.php');
    }

    $params = [
        ':organization' => $organization,
        ':position' => $position,
        ':description' => $description,
        ':start_date' => $startDate !== '' ? $startDate : null,
        ':end_date' => $endDate !== '' ? $endDate : null,
        ':is_current' => $isCurrent,
        ':sort_order' => $sortOrder,
    ];
    if ($id > 0) {
        $params[':id'] = $id;
        $stmt = $pdo->prepare('UPDATE experiences SET organization = :organization, position = :position, description = :description, start_date = :start_date, end_date = :end_date, is_current = :is_current, sort_order = :sort_order WHERE id = :id');
        $stmt->execute($params);
        set_flash('success', 'Experience entry updated.');
    } else {
        $stmt = $pdo->prepare('INSERT INTO experiences (organization, position, description, start_date, end_date, is_current, sort_order) VALUES (:organization, :position, :description, :start_date, :end_date, :is_current, :sort_order)');
        $stmt->execute($params);
        set_flash('success', 'Experience entry added.');
    }
    redirect('experience.php');
}

$editingId = filter_var($_GET['edit'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
$editingExperience = null;
if ($editingId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM experiences WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $editingId]);
    $editingExperience = $stmt->fetch() ?: null;
}
$experiences = $pdo->query('SELECT * FROM experiences ORDER BY sort_order ASC, start_date DESC, id DESC')->fetchAll() ?: [];
$flashSuccess = get_flash('success');
$flashError = get_flash('error');
$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Experience | Dashboard</title>
    <style>
        body { margin:0; font-family:Arial,sans-serif; background:#f3f2ee; color:#1b1d1f; }
        .wrap { max-width:1050px; margin:40px auto; padding:0 20px; }
        .top { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:20px; }
        .panel { background:#fff; border:1px solid #dfe3d9; border-radius:12px; padding:24px; box-shadow:0 8px 20px rgba(21,26,30,.08); overflow-x:auto; }
        .btn { display:inline-block; padding:10px 16px; background:#2d5f4c; color:#fff; border-radius:8px; text-decoration:none; border:0; cursor:pointer; }
        .secondary { background:#fff; color:#1b1d1f; border:1px solid #dfe3d9; }
        .danger { background:#a1251b; }
        .alert { padding:12px 14px; border-radius:8px; margin-bottom:16px; }
        .success { background:#ecfdf5; border:1px solid #9fe7c4; color:#146c43; }
        .error { background:#fef3f2; border:1px solid #f5c5c2; color:#b42318; }
        .fields { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:16px; }
        label { display:block; font-weight:700; margin-bottom:8px; }
        input, textarea { width:100%; padding:10px 12px; border:1px solid #dfe3d9; border-radius:8px; font-size:16px; box-sizing:border-box; }
        textarea { min-height:100px; }
        .editor { display:grid; gap:16px; }
        table { width:100%; border-collapse:collapse; margin-top:24px; }
        th,td { border:1px solid #dfe3d9; text-align:left; padding:12px; vertical-align:top; }
        .actions { display:flex; gap:8px; flex-wrap:wrap; }
        .actions form { margin:0; }
    </style>
</head>
<body>
    <main class="wrap">
        <div class="top"><h1>Experience</h1><a href="dashboard.php" class="btn secondary">Back to Dashboard</a></div>
        <?php if ($flashSuccess): ?><div class="alert success"><?php echo e($flashSuccess); ?></div><?php endif; ?>
        <?php if ($flashError): ?><div class="alert error"><?php echo e($flashError); ?></div><?php endif; ?>
        <section class="panel">
            <form class="editor" method="post" action="experience.php">
                <input type="hidden" name="csrf_token" value="<?php echo e($csrf); ?>">
                <?php if ($editingExperience): ?><input type="hidden" name="id" value="<?php echo e((string) $editingExperience['id']); ?>"><?php endif; ?>
                <div class="fields">
                    <div><label for="organization">Organization</label><input id="organization" name="organization" maxlength="180" value="<?php echo e((string) ($editingExperience['organization'] ?? '')); ?>" required></div>
                    <div><label for="position">Position</label><input id="position" name="position" maxlength="180" value="<?php echo e((string) ($editingExperience['position'] ?? '')); ?>" required></div>
                    <div><label for="start_date">Start date</label><input id="start_date" name="start_date" type="date" value="<?php echo e((string) ($editingExperience['start_date'] ?? '')); ?>"></div>
                    <div><label for="end_date">End date</label><input id="end_date" name="end_date" type="date" value="<?php echo e((string) ($editingExperience['end_date'] ?? '')); ?>"></div>
                    <div><label for="sort_order">Order</label><input id="sort_order" name="sort_order" type="number" value="<?php echo e((string) ($editingExperience['sort_order'] ?? 0)); ?>"></div>
                </div>
                <div><label for="description">Description</label><textarea id="description" name="description"><?php echo e((string) ($editingExperience['description'] ?? '')); ?></textarea></div>
                <label><input type="checkbox" name="is_current" value="1" <?php echo !empty($editingExperience['is_current']) ? 'checked' : ''; ?>> Current position (end date will be cleared)</label>
                <div class="actions">
                    <button class="btn" type="submit"><?php echo $editingExperience ? 'Update Experience' : 'Add Experience'; ?></button>
                    <?php if ($editingExperience): ?><a href="experience.php" class="btn secondary">Cancel</a><?php endif; ?>
                </div>
            </form>
            <table>
                <thead><tr><th>Organization</th><th>Position</th><th>Dates</th><th>Description</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($experiences as $experience): ?>
                        <tr>
                            <td><?php echo e((string) $experience['organization']); ?></td>
                            <td><?php echo e((string) $experience['position']); ?></td>
                            <td><?php echo e((string) ($experience['start_date'] ?? '')); ?> – <?php echo !empty($experience['is_current']) ? 'Present' : e((string) ($experience['end_date'] ?? '')); ?></td>
                            <td><?php echo nl2br(e((string) ($experience['description'] ?? ''))); ?></td>
                            <td><div class="actions">
                                <a href="experience.php?edit=<?php echo (int) $experience['id']; ?>" class="btn secondary">Edit</a>
                                <form method="post" action="experience.php" onsubmit="return confirm('Delete this experience entry?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo e($csrf); ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo e((string) $experience['id']); ?>">
                                    <button type="submit" class="btn danger">Delete</button>
                                </form>
                            </div></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
