<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();
$pdo = db_connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Security check failed. Please try again.');
        redirect('education.php');
    }

    $action = $_POST['action'] ?? 'save';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete') {
        if ($id > 0) {
            $stmt = $pdo->prepare('DELETE FROM education WHERE id = :id');
            $stmt->execute([':id' => $id]);
            set_flash('success', 'Education entry deleted.');
        }
        redirect('education.php');
    }

    $institution = trim((string) ($_POST['institution'] ?? ''));
    $qualification = trim((string) ($_POST['qualification'] ?? ''));
    if ($institution === '' || $qualification === '') {
        set_flash('error', 'Institution and qualification are required.');
        redirect('education.php');
    }

    $description = trim((string) ($_POST['description'] ?? ''));
    $startDate = ($_POST['start_date'] ?? '') !== '' ? $_POST['start_date'] : null;
    $endDate = ($_POST['end_date'] ?? '') !== '' ? $_POST['end_date'] : null;
    $sortOrder = (int) ($_POST['sort_order'] ?? 0);

    if ($id > 0) {
        $stmt = $pdo->prepare('UPDATE education SET institution = :institution, qualification = :qualification, description = :description, start_date = :start_date, end_date = :end_date, sort_order = :sort_order WHERE id = :id');
        $stmt->execute([
            ':id' => $id,
            ':institution' => $institution,
            ':qualification' => $qualification,
            ':description' => $description,
            ':start_date' => $startDate,
            ':end_date' => $endDate,
            ':sort_order' => $sortOrder,
        ]);
        set_flash('success', 'Education entry updated.');
    } else {
        $stmt = $pdo->prepare('INSERT INTO education (institution, qualification, description, start_date, end_date, sort_order) VALUES (:institution, :qualification, :description, :start_date, :end_date, :sort_order)');
        $stmt->execute([
            ':institution' => $institution,
            ':qualification' => $qualification,
            ':description' => $description,
            ':start_date' => $startDate,
            ':end_date' => $endDate,
            ':sort_order' => $sortOrder,
        ]);
        set_flash('success', 'Education entry added.');
    }

    redirect('education.php');
}

$editingId = (int) ($_GET['edit'] ?? 0);
$editingEducation = null;
if ($editingId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM education WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $editingId]);
    $editingEducation = $stmt->fetch() ?: null;
}

$education = $pdo->query('SELECT * FROM education ORDER BY sort_order ASC, end_date DESC, id DESC')->fetchAll() ?: [];
$flashSuccess = get_flash('success');
$flashError = get_flash('error');
$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Education | Dashboard</title>
    <style>
        body { margin:0; font-family:Arial,sans-serif; background:#f3f2ee; color:#1b1d1f; }
        .wrap { max-width:1000px; margin:40px auto; padding:0 20px; }
        .top { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:20px; }
        .panel { background:#fff; border:1px solid #dfe3d9; border-radius:12px; padding:24px; box-shadow:0 8px 20px rgba(21,26,30,.08); }
        .btn { display:inline-block; padding:10px 16px; background:#2d5f4c; color:#fff; border-radius:8px; text-decoration:none; border:0; cursor:pointer; }
        .secondary { background:#fff; color:#1b1d1f; border:1px solid #dfe3d9; }
        .danger { background:#a1251b; }
        .alert { padding:12px 14px; border-radius:8px; margin-bottom:16px; }
        .success { background:#ecfdf5; border:1px solid #9fe7c4; color:#146c43; }
        .error { background:#fef3f2; border:1px solid #f5c5c2; color:#b42318; }
        form { display:grid; gap:16px; }
        .fields { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:16px; }
        label { display:block; font-weight:700; margin-bottom:8px; }
        input, textarea { width:100%; padding:10px 12px; border:1px solid #dfe3d9; border-radius:8px; font-size:16px; box-sizing:border-box; }
        textarea { min-height:100px; }
        table { width:100%; border-collapse:collapse; margin-top:24px; }
        th,td { border:1px solid #dfe3d9; text-align:left; padding:12px; }
        .actions { display:flex; gap:8px; flex-wrap:wrap; }
    </style>
</head>
<body>
    <main class="wrap">
        <div class="top">
            <h1>Education</h1>
            <a href="dashboard.php" class="btn secondary">Back to Dashboard</a>
        </div>
        <?php if ($flashSuccess): ?><div class="alert success"><?php echo e($flashSuccess); ?></div><?php endif; ?>
        <?php if ($flashError): ?><div class="alert error"><?php echo e($flashError); ?></div><?php endif; ?>
        <section class="panel">
            <form method="post" action="education.php">
                <input type="hidden" name="csrf_token" value="<?php echo e($csrf); ?>">
                <?php if ($editingEducation): ?>
                    <input type="hidden" name="id" value="<?php echo e((string) $editingEducation['id']); ?>">
                <?php endif; ?>
                <div class="fields">
                    <div>
                        <label for="institution">Institution</label>
                        <input id="institution" name="institution" value="<?php echo e((string) ($editingEducation['institution'] ?? '')); ?>" required>
                    </div>
                    <div>
                        <label for="qualification">Qualification</label>
                        <input id="qualification" name="qualification" value="<?php echo e((string) ($editingEducation['qualification'] ?? '')); ?>" required>
                    </div>
                    <div>
                        <label for="start_date">Start date</label>
                        <input id="start_date" name="start_date" type="date" value="<?php echo e((string) ($editingEducation['start_date'] ?? '')); ?>">
                    </div>
                    <div>
                        <label for="end_date">End date</label>
                        <input id="end_date" name="end_date" type="date" value="<?php echo e((string) ($editingEducation['end_date'] ?? '')); ?>">
                    </div>
                    <div>
                        <label for="sort_order">Order</label>
                        <input id="sort_order" name="sort_order" type="number" value="<?php echo e((string) ($editingEducation['sort_order'] ?? 0)); ?>">
                    </div>
                </div>
                <div>
                    <label for="description">Description</label>
                    <textarea id="description" name="description"><?php echo e((string) ($editingEducation['description'] ?? '')); ?></textarea>
                </div>
                <div class="actions">
                    <button class="btn" type="submit"><?php echo $editingEducation ? 'Update Education' : 'Add Education'; ?></button>
                    <?php if ($editingEducation): ?>
                        <a href="education.php" class="btn secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
            <table>
                <thead>
                    <tr>
                        <th>Institution</th>
                        <th>Qualification</th>
                        <th>Dates</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($education as $item): ?>
                        <tr>
                            <td><?php echo e((string) ($item['institution'] ?? '')); ?></td>
                            <td><?php echo e((string) ($item['qualification'] ?? '')); ?></td>
                            <td><?php echo e((string) ($item['start_date'] ?? '')); ?> – <?php echo e((string) ($item['end_date'] ?? '')); ?></td>
                            <td>
                                <div class="actions">
                                    <a href="education.php?edit=<?php echo (int) ($item['id'] ?? 0); ?>" class="btn secondary">Edit</a>
                                    <form method="post" action="education.php" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo e($csrf); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo e((string) ($item['id'] ?? 0)); ?>">
                                        <button type="submit" class="btn danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
