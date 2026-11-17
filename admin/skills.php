<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();
$pdo = db_connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Security check failed. Please try again.');
        redirect('skills.php');
    }

    $action = $_POST['action'] ?? 'save';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete') {
        if ($id > 0) {
            $stmt = $pdo->prepare('DELETE FROM skills WHERE id = :id');
            $stmt->execute([':id' => $id]);
            set_flash('success', 'Skill deleted.');
        }
        redirect('skills.php');
    }

    $name = trim((string) ($_POST['name'] ?? ''));
    if ($name === '') {
        set_flash('error', 'Skill name is required.');
        redirect('skills.php');
    }

    $category = trim((string) ($_POST['category'] ?? 'General')) ?: 'General';
    $proficiency = max(0, min(100, (int) ($_POST['proficiency'] ?? 50)));
    $description = trim((string) ($_POST['description'] ?? ''));
    $sortOrder = (int) ($_POST['sort_order'] ?? 0);

    if ($id > 0) {
        $stmt = $pdo->prepare('UPDATE skills SET name = :name, category = :category, proficiency = :proficiency, description = :description, sort_order = :sort_order WHERE id = :id');
        $stmt->execute([
            ':id' => $id,
            ':name' => $name,
            ':category' => $category,
            ':proficiency' => $proficiency,
            ':description' => $description,
            ':sort_order' => $sortOrder,
        ]);
        set_flash('success', 'Skill updated.');
    } else {
        $stmt = $pdo->prepare('INSERT INTO skills (name, category, proficiency, description, sort_order) VALUES (:name, :category, :proficiency, :description, :sort_order)');
        $stmt->execute([
            ':name' => $name,
            ':category' => $category,
            ':proficiency' => $proficiency,
            ':description' => $description,
            ':sort_order' => $sortOrder,
        ]);
        set_flash('success', 'Skill added.');
    }

    redirect('skills.php');
}

$editingId = (int) ($_GET['edit'] ?? 0);
$editingSkill = null;
if ($editingId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM skills WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $editingId]);
    $editingSkill = $stmt->fetch() ?: null;
}

$skills = $pdo->query('SELECT * FROM skills ORDER BY sort_order ASC, id ASC')->fetchAll() ?: [];
$flashSuccess = get_flash('success');
$flashError = get_flash('error');
$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skills | Dashboard</title>
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
        textarea { min-height:120px; }
        table { width:100%; border-collapse:collapse; margin-top:24px; }
        th,td { border:1px solid #dfe3d9; text-align:left; padding:12px; }
        .actions { display:flex; gap:8px; flex-wrap:wrap; }
    </style>
</head>
<body>
    <main class="wrap">
        <div class="top">
            <h1>Skills</h1>
            <a href="dashboard.php" class="btn secondary">Back to Dashboard</a>
        </div>
        <?php if ($flashSuccess): ?><div class="alert success"><?php echo e($flashSuccess); ?></div><?php endif; ?>
        <?php if ($flashError): ?><div class="alert error"><?php echo e($flashError); ?></div><?php endif; ?>
        <section class="panel">
            <form method="post" action="skills.php">
                <input type="hidden" name="csrf_token" value="<?php echo e($csrf); ?>">
                <?php if ($editingSkill): ?>
                    <input type="hidden" name="id" value="<?php echo e((string) $editingSkill['id']); ?>">
                <?php endif; ?>
                <div class="fields">
                    <div>
                        <label for="name">Skill name</label>
                        <input id="name" name="name" value="<?php echo e((string) ($editingSkill['name'] ?? '')); ?>" required>
                    </div>
                    <div>
                        <label for="category">Category</label>
                        <input id="category" name="category" value="<?php echo e((string) ($editingSkill['category'] ?? 'General')); ?>">
                    </div>
                    <div>
                        <label for="proficiency">Proficiency (0–100)</label>
                        <input id="proficiency" name="proficiency" type="number" min="0" max="100" value="<?php echo e((string) ($editingSkill['proficiency'] ?? 50)); ?>">
                    </div>
                    <div>
                        <label for="sort_order">Order</label>
                        <input id="sort_order" name="sort_order" type="number" value="<?php echo e((string) ($editingSkill['sort_order'] ?? 0)); ?>">
                    </div>
                </div>
                <div>
                    <label for="description">Description</label>
                    <textarea id="description" name="description"><?php echo e((string) ($editingSkill['description'] ?? '')); ?></textarea>
                </div>
                <div class="actions">
                    <button class="btn" type="submit"><?php echo $editingSkill ? 'Update Skill' : 'Add Skill'; ?></button>
                    <?php if ($editingSkill): ?>
                        <a href="skills.php" class="btn secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Proficiency</th>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($skills as $skill): ?>
                        <tr>
                            <td><?php echo e((string) ($skill['name'] ?? '')); ?></td>
                            <td><?php echo e((string) ($skill['category'] ?? 'General')); ?></td>
                            <td><?php echo e((string) ($skill['proficiency'] ?? 0)); ?>%</td>
                            <td><?php echo nl2br(e((string) ($skill['description'] ?? ''))); ?></td>
                            <td>
                                <div class="actions">
                                    <a href="skills.php?edit=<?php echo (int) ($skill['id'] ?? 0); ?>" class="btn secondary">Edit</a>
                                    <form method="post" action="skills.php" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo e($csrf); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo e((string) ($skill['id'] ?? 0)); ?>">
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
