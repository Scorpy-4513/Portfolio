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

    $action = $_POST['action'] ?? 'save';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete') {
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

    $description = trim((string) ($_POST['description'] ?? ''));
    $imagePath = trim((string) ($_POST['image_path'] ?? ''));
    $projectUrl = trim((string) ($_POST['project_url'] ?? ''));
    $sortOrder = (int) ($_POST['sort_order'] ?? 0);
    $isPublished = isset($_POST['is_published']) ? 1 : 0;

    $slugBase = trim((string) preg_replace('/[^a-z0-9]+/i', '-', strtolower($title)), '-');
    $slugBase = $slugBase !== '' ? $slugBase : 'project';
    $slug = $slugBase;
    $suffix = 2;

    $check = $pdo->prepare('SELECT id FROM projects WHERE slug = :slug AND id != :id LIMIT 1');
    while (true) {
        $check->execute([':slug' => $slug, ':id' => $id]);
        if (!$check->fetch()) {
            break;
        }
        $slug = $slugBase . '-' . $suffix++;
    }

    if ($id > 0) {
        $stmt = $pdo->prepare('UPDATE projects SET title = :title, slug = :slug, description = :description, image_path = :image_path, project_url = :project_url, is_published = :is_published, sort_order = :sort_order WHERE id = :id');
        $stmt->execute([
            ':id' => $id,
            ':title' => $title,
            ':slug' => $slug,
            ':description' => $description,
            ':image_path' => $imagePath,
            ':project_url' => $projectUrl,
            ':is_published' => $isPublished,
            ':sort_order' => $sortOrder,
        ]);
        set_flash('success', 'Project updated.');
    } else {
        $stmt = $pdo->prepare('INSERT INTO projects (title, slug, description, image_path, project_url, sort_order, is_published) VALUES (:title, :slug, :description, :image_path, :project_url, :sort_order, :is_published)');
        $stmt->execute([
            ':title' => $title,
            ':slug' => $slug,
            ':description' => $description,
            ':image_path' => $imagePath,
            ':project_url' => $projectUrl,
            ':sort_order' => $sortOrder,
            ':is_published' => $isPublished,
        ]);
        set_flash('success', 'Project added.');
    }

    redirect('projects.php');
}

$editingId = (int) ($_GET['edit'] ?? 0);
$editingProject = null;
if ($editingId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM projects WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $editingId]);
    $editingProject = $stmt->fetch() ?: null;
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
        input, textarea { width:100%; padding:10px 12px; border:1px solid #dfe3d9; border-radius:8px; font-size:16px; box-sizing:border-box; }
        textarea { min-height:100px; }
        table { width:100%; border-collapse:collapse; margin-top:24px; }
        th,td { border:1px solid #dfe3d9; text-align:left; padding:12px; vertical-align:top; }
        .actions { display:flex; gap:8px; flex-wrap:wrap; }
    </style>
</head>
<body>
    <main class="wrap">
        <div class="top">
            <h1>Projects</h1>
            <a href="dashboard.php" class="btn secondary">Back to Dashboard</a>
        </div>
        <?php if ($flashSuccess): ?><div class="alert success"><?php echo e($flashSuccess); ?></div><?php endif; ?>
        <?php if ($flashError): ?><div class="alert error"><?php echo e($flashError); ?></div><?php endif; ?>
        <section class="panel">
            <form method="post" action="projects.php">
                <input type="hidden" name="csrf_token" value="<?php echo e($csrf); ?>">
                <?php if ($editingProject): ?>
                    <input type="hidden" name="id" value="<?php echo e((string) $editingProject['id']); ?>">
                <?php endif; ?>
                <div class="fields">
                    <div>
                        <label for="title">Title</label>
                        <input id="title" name="title" value="<?php echo e((string) ($editingProject['title'] ?? '')); ?>" required>
                    </div>
                    <div>
                        <label for="image_path">Image path</label>
                        <input id="image_path" name="image_path" value="<?php echo e((string) ($editingProject['image_path'] ?? '')); ?>" placeholder="images/project.jpg">
                    </div>
                    <div>
                        <label for="project_url">Project URL</label>
                        <input id="project_url" name="project_url" type="url" value="<?php echo e((string) ($editingProject['project_url'] ?? '')); ?>">
                    </div>
                    <div>
                        <label for="sort_order">Order</label>
                        <input id="sort_order" name="sort_order" type="number" value="<?php echo e((string) ($editingProject['sort_order'] ?? 0)); ?>">
                    </div>
                </div>
                <div>
                    <label for="description">Description</label>
                    <textarea id="description" name="description"><?php echo e((string) ($editingProject['description'] ?? '')); ?></textarea>
                </div>
                <label>
                    <input type="checkbox" name="is_published" <?php echo !empty($editingProject) ? (!empty($editingProject['is_published']) ? 'checked' : '') : 'checked'; ?>>
                    Published
                </label>
                <div class="actions">
                    <button class="btn" type="submit"><?php echo $editingProject ? 'Update Project' : 'Add Project'; ?></button>
                    <?php if ($editingProject): ?>
                        <a href="projects.php" class="btn secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Published</th>
                        <th>Order</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): ?>
                        <tr>
                            <td><?php echo e((string) ($project['title'] ?? '')); ?></td>
                            <td><?php echo !empty($project['is_published']) ? 'Yes' : 'No'; ?></td>
                            <td><?php echo e((string) ($project['sort_order'] ?? 0)); ?></td>
                            <td>
                                <div class="actions">
                                    <a href="projects.php?edit=<?php echo (int) ($project['id'] ?? 0); ?>" class="btn secondary">Edit</a>
                                    <form method="post" action="projects.php" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo e($csrf); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo e((string) ($project['id'] ?? 0)); ?>">
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
