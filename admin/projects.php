<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();
$pdo = db_connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Security check failed. Please try again.');
        redirect('projects.php');
    }

    if (($_POST['action'] ?? '') === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare('DELETE FROM projects WHERE id = :id');
            $stmt->execute([':id' => $id]);
            set_flash('success', 'Project deleted.');
        }
        redirect('projects.php');
    }

    $title = trim((string) ($_POST['title'] ?? ''));
    if ($title === '') {
        set_flash('error', 'Project title is required.');
        redirect('projects.php');
    }

    $slugBase = trim((string) preg_replace('/[^a-z0-9]+/i', '-', strtolower($title)), '-');
    $slugBase = $slugBase !== '' ? $slugBase : 'project';
    $slug = $slugBase;
    $suffix = 2;
    $check = $pdo->prepare('SELECT id FROM projects WHERE slug = :slug LIMIT 1');
    while (true) {
        $check->execute([':slug' => $slug]);
        if (!$check->fetch()) {
            break;
        }
        $slug = $slugBase . '-' . $suffix++;
    }

    $stmt = $pdo->prepare('INSERT INTO projects (title, slug, description, image_path, project_url, sort_order, is_published) VALUES (:title, :slug, :description, :image_path, :project_url, :sort_order, :is_published)');
    $stmt->execute([
        ':title' => $title,
        ':slug' => $slug,
        ':description' => trim((string) ($_POST['description'] ?? '')),
        ':image_path' => trim((string) ($_POST['image_path'] ?? '')),
        ':project_url' => trim((string) ($_POST['project_url'] ?? '')),
        ':sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ':is_published' => isset($_POST['is_published']) ? 1 : 0,
    ]);
    set_flash('success', 'Project added.');
    redirect('projects.php');
}

$projects = $pdo->query('SELECT * FROM projects ORDER BY sort_order ASC, id ASC')->fetchAll() ?: [];
$flashSuccess = get_flash('success');
$flashError = get_flash('error');
$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects | Dashboard</title>
    <style>
        body { margin:0; font-family:Arial,sans-serif; background:#f3f2ee; color:#1b1d1f; }
        .wrap { max-width:1100px; margin:40px auto; padding:0 20px; }
        .top { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:20px; }
        .panel { background:#fff; border:1px solid #dfe3d9; border-radius:12px; padding:24px; box-shadow:0 8px 20px rgba(21,26,30,.08); }
        .btn { display:inline-block; padding:10px 16px; background:#2d5f4c; color:#fff; border-radius:8px; text-decoration:none; border:0; cursor:pointer; }
        .secondary { background:#fff; color:#1b1d1f; border:1px solid #dfe3d9; }
        .danger { background:#a1251b; }
        .alert { padding:12px 14px; border-radius:8px; margin-bottom:16px; }
        .success { background:#ecfdf5; border:1px solid #9fe7c4; color:#146c43; }
        .error { background:#fef3f2; border:1px solid #f5c5c2; color:#b42318; }
        form { display:grid; gap:16px; }
        .fields { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:16px; }
        label { display:block; font-weight:700; margin-bottom:8px; }
        input,textarea { width:100%; padding:10px 12px; border:1px solid #dfe3d9; border-radius:8px; font-size:16px; box-sizing:border-box; }
        textarea { min-height:100px; }
        table { width:100%; border-collapse:collapse; margin-top:24px; }
        th,td { border:1px solid #dfe3d9; text-align:left; padding:12px; vertical-align:top; }
    </style>
</head>
<body>
    <main class="wrap">
        <div class="top"><h1>Projects</h1><a href="dashboard.php" class="btn secondary">Back to Dashboard</a></div>
        <?php if ($flashSuccess): ?><div class="alert success"><?php echo e($flashSuccess); ?></div><?php endif; ?>
        <?php if ($flashError): ?><div class="alert error"><?php echo e($flashError); ?></div><?php endif; ?>
        <section class="panel">
            <form method="post" action="projects.php">
                <input type="hidden" name="csrf_token" value="<?php echo e($csrf); ?>">
                <div class="fields">
                    <div><label for="title">Title</label><input id="title" name="title" required></div>
                    <div><label for="image_path">Image path</label><input id="image_path" name="image_path" placeholder="images/project.jpg"></div>
                    <div><label for="project_url">Project URL</label><input id="project_url" name="project_url" type="url"></div>
                    <div><label for="sort_order">Order</label><input id="sort_order" name="sort_order" type="number" value="0"></div>
                </div>
                <div><label for="description">Description</label><textarea id="description" name="description"></textarea></div>
                <label><input type="checkbox" name="is_published" checked> Published</label>
                <button class="btn" type="submit">Add Project</button>
            </form>
            <table><thead><tr><th>Title</th><th>Published</th><th>Order</th><th>Action</th></tr></thead><tbody>
                <?php foreach ($projects as $project): ?><tr>
                    <td><?php echo e((string) $project['title']); ?></td><td><?php echo !empty($project['is_published']) ? 'Yes' : 'No'; ?></td><td><?php echo e((string) $project['sort_order']); ?></td>
                    <td><form method="post" action="projects.php"><input type="hidden" name="csrf_token" value="<?php echo e($csrf); ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo e((string) $project['id']); ?>"><button class="btn danger" type="submit">Delete</button></form></td>
                </tr><?php endforeach; ?>
            </tbody></table>
        </section>
    </main>
</body>
</html>
