<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

$pdo = db_connect();
$message = null;
$messageIsError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim((string) ($_POST['full_name'] ?? ''));
    $introduction = trim((string) ($_POST['introduction'] ?? ''));
    $profileImage = trim((string) ($_POST['profile_image'] ?? ''));
    $githubUrl = trim((string) ($_POST['github_url'] ?? ''));
    $facebookUrl = trim((string) ($_POST['facebook_url'] ?? ''));
    $instagramUrl = trim((string) ($_POST['instagram_url'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));

    if ($fullName === '' || !is_valid_email($email)) {
        $message = 'Full name and a valid email are required.';
        $messageIsError = true;
    } else {
        try {
            $stmt = $pdo->prepare('SELECT id FROM profile WHERE id = 1 LIMIT 1');
            $stmt->execute();
            $existing = $stmt->fetch();

            if ($existing) {
                $stmt = $pdo->prepare('UPDATE profile SET full_name = :full_name, introduction = :introduction, profile_image = :profile_image, github_url = :github_url, facebook_url = :facebook_url, instagram_url = :instagram_url, email = :email WHERE id = 1');
            } else {
                $stmt = $pdo->prepare('INSERT INTO profile (id, full_name, introduction, profile_image, github_url, facebook_url, instagram_url, email) VALUES (1, :full_name, :introduction, :profile_image, :github_url, :facebook_url, :instagram_url, :email)');
            }

            $stmt->execute([
                ':full_name' => $fullName,
                ':introduction' => $introduction,
                ':profile_image' => $profileImage,
                ':github_url' => $githubUrl,
                ':facebook_url' => $facebookUrl,
                ':instagram_url' => $instagramUrl,
                ':email' => $email,
            ]);

            $message = 'Profile updated successfully.';
            $messageIsError = false;
        } catch (Throwable $e) {
            error_log('Profile update failed: ' . $e->getMessage());
            $message = 'Could not save the profile. Please try again.';
            $messageIsError = true;
        }
    }
}

$profile = fetch_profile($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings | Dashboard</title>
    <style>
        :root { --bg:#f3f2ee; --panel:#fff; --text:#1b1d1f; --muted:#586168; --accent:#2d5f4c; --border:#dfe3d9; --danger:#b42318; }
        * { box-sizing: border-box; }
        body { margin:0; font-family:Arial,sans-serif; background:var(--bg); color:var(--text); }
        .topbar { background:var(--panel); border-bottom:1px solid var(--border); padding:1rem 2rem; display:flex; justify-content:space-between; align-items:center; }
        .nav { display:flex; gap:1rem; flex-wrap:wrap; align-items:center; }
        .layout { max-width:1100px; margin:0 auto; padding:2rem; }
        .panel { background:var(--panel); border:1px solid var(--border); border-radius:12px; padding:1.5rem; box-shadow:0 8px 20px rgba(21,26,30,0.05); }
        .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:1rem; }
        label { display:block; margin:1rem 0 .5rem; font-weight:600; }
        input, textarea, select { width:100%; border:1px solid var(--border); border-radius:8px; padding:0.8rem; font-size:1rem; }
        textarea { min-height:140px; resize:vertical; }
        .alert { padding:0.8rem 1rem; border-radius:8px; margin-bottom:1rem; }
        .alert.success { background:#ecfdf5; border:1px solid #9fe7c4; color:#146c43; }
        .alert.error { background:#fef3f2; border:1px solid #f5c5c2; color:var(--danger); }
        .btn { display:inline-block; padding:0.8rem 1rem; border-radius:8px; background:var(--accent); color:white; font-weight:700; border:none; cursor:pointer; text-decoration:none; }
        .btn.secondary { background:white; color:var(--text); border:1px solid var(--border); }
        .action-row { display:flex; gap:0.75rem; flex-wrap:wrap; margin-bottom:1rem; }
        .muted { color:var(--muted); }
    </style>
</head>
<body>
    <header class="topbar">
        <strong>Ajie C. Javier</strong>
        <div class="nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="profile.php">Profile</a>
            <a href="skills.php">Skills</a>
            <a href="projects.php">Projects</a>
            <a href="education.php">Education</a>
            <a href="messages.php">Messages</a>
            <a href="logout.php" class="btn secondary">Logout</a>
        </div>
    </header>

    <main class="layout">
        <div class="action-row">
            <a href="dashboard.php" class="btn secondary">Back to dashboard</a>
        </div>

        <?php if ($message !== null): ?>
            <div class="alert <?php echo $messageIsError ? 'error' : 'success'; ?>"><?php echo e($message); ?></div>
        <?php endif; ?>

        <section class="panel">
            <h1>Profile settings</h1>
            <p class="muted">Update the homepage information shown on the public portfolio.</p>

            <form method="post" action="profile.php">
                <div class="grid">
                    <div>
                        <label for="full_name">Full name</label>
                        <input id="full_name" name="full_name" type="text" value="<?php echo e($profile['full_name'] ?? ''); ?>" required>

                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" value="<?php echo e($profile['email'] ?? ''); ?>" required>

                        <label for="profile_image">Profile image path</label>
                        <input id="profile_image" name="profile_image" type="text" value="<?php echo e($profile['profile_image'] ?? 'images/Ajieprofile.jpeg'); ?>">
                    </div>

                    <div>
                        <label for="github_url">GitHub URL</label>
                        <input id="github_url" name="github_url" type="url" value="<?php echo e($profile['github_url'] ?? ''); ?>">

                        <label for="facebook_url">Facebook URL</label>
                        <input id="facebook_url" name="facebook_url" type="url" value="<?php echo e($profile['facebook_url'] ?? ''); ?>">

                        <label for="instagram_url">Instagram URL</label>
                        <input id="instagram_url" name="instagram_url" type="url" value="<?php echo e($profile['instagram_url'] ?? ''); ?>">
                    </div>
                </div>

                <label for="introduction">Introduction</label>
                <textarea id="introduction" name="introduction"><?php echo e($profile['introduction'] ?? ''); ?></textarea>

                <div style="margin-top:1rem;">
                    <button type="submit" class="btn">Save profile</button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
<?php $pdo = null; ?>
























































































































































































































































































































































































































































































































































































































"},{"content":"<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

$pdo = db_connect();
$message = null;
$messageIsError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save';
    $name = trim((string) ($_POST['name'] ?? ''));
    $icon = trim((string) ($_POST['icon'] ?? ''));
    $level = trim((string) ($_POST['level'] ?? ''));
    $sortOrder = (int) ($_POST['sort_order'] ?? 0);

    if ($name === '') {
        $message = 'Skill name is required.';
        $messageIsError = true;
    } else {
        try {
            if (($action === 'delete') && !empty($_POST['id'])) {
                $stmt = $pdo->prepare('DELETE FROM skills WHERE id = :id');
                $stmt->execute([':id' => (int) $_POST['id']]);
                $message = 'Skill deleted.';
            } elseif (!empty($_POST['id'])) {
                $stmt = $pdo->prepare('UPDATE skills SET name = :name, icon = :icon, level = :level, sort_order = :sort_order WHERE id = :id');
                $stmt->execute([
                    ':id' => (int) $_POST['id'],
                    ':name' => $name,
                    ':icon' => $icon,
                    ':level' => $level,
                    ':sort_order' => $sortOrder,
                ]);
                $message = 'Skill updated.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO skills (name, icon, level, sort_order) VALUES (:name, :icon, :level, :sort_order)');
                $stmt->execute([
                    ':name' => $name,
                    ':icon' => $icon,
                    ':level' => $level,
                    ':sort_order' => $sortOrder,
                ]);
                $message = 'Skill added.';
            }
            $messageIsError = false;
        } catch (Throwable $e) {
            error_log('Skill save failed: ' . $e->getMessage());
            $message = 'Could not save the skill.';
            $messageIsError = true;
        }
    }
}

$skills = $pdo->query('SELECT * FROM skills ORDER BY sort_order ASC, id ASC')->fetchAll() ?: [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skills Manager | Dashboard</title>
    <style>
        :root { --bg:#f3f2ee; --panel:#fff; --text:#1b1d1f; --muted:#586168; --accent:#2d5f4c; --border:#dfe3d9; --danger:#b42318; }
        * { box-sizing: border-box; }
        body { margin:0; font-family:Arial,sans-serif; background:var(--bg); color:var(--text); }
        .topbar { background:var(--panel); border-bottom:1px solid var(--border); padding:1rem 2rem; display:flex; justify-content:space-between; align-items:center; }
        .nav { display:flex; gap:1rem; flex-wrap:wrap; align-items:center; }
        .layout { max-width:1100px; margin:0 auto; padding:2rem; }
        .panel { background:var(--panel); border:1px solid var(--border); border-radius:12px; padding:1.5rem; box-shadow:0 8px 20px rgba(21,26,30,0.05); }
        .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:1rem; }
        label { display:block; margin:1rem 0 .5rem; font-weight:600; }
        input, textarea { width:100%; border:1px solid var(--border); border-radius:8px; padding:0.8rem; font-size:1rem; }
        table { width:100%; border-collapse:collapse; margin-top:1rem; }
        th, td { border:1px solid var(--border); text-align:left; padding:0.75rem; }
        .btn { display:inline-block; padding:0.8rem 1rem; border-radius:8px; background:var(--accent); color:white; font-weight:700; border:none; cursor:pointer; text-decoration:none; }
        .btn.secondary { background:white; color:var(--text); border:1px solid var(--border); }
        .btn.danger { background:#a1251b; color:white; }
        .alert { padding:0.8rem 1rem; border-radius:8px; margin-bottom:1rem; }
        .alert.success { background:#ecfdf5; border:1px solid #9fe7c4; color:#146c43; }
        .alert.error { background:#fef3f2; border:1px solid #f5c5c2; color:var(--danger); }
        .action-row { display:flex; gap:0.75rem; flex-wrap:wrap; margin-bottom:1rem; }
        .muted { color:var(--muted); }
    </style>
</head>
<body>
    <header class="topbar">
        <strong>Ajie C. Javier</strong>
        <div class="nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="profile.php">Profile</a>
            <a href="skills.php">Skills</a>
            <a href="projects.php">Projects</a>
            <a href="education.php">Education</a>
            <a href="messages.php">Messages</a>
            <a href="logout.php" class="btn secondary">Logout</a>
        </div>
    </header>

    <main class="layout">
        <div class="action-row">
            <a href="dashboard.php" class="btn secondary">Back to dashboard</a>
        </div>

        <?php if ($message !== null): ?>
            <div class="alert <?php echo $messageIsError ? 'error' : 'success'; ?>"><?php echo e($message); ?></div>
        <?php endif; ?>

        <section class="panel">
            <h1>Skills</h1>
            <p class="muted">Add or update the skills displayed on your public portfolio.</p>

            <form method="post" action="skills.php">
                <div class="grid">
                    <div>
                        <label for="name">Skill name</label>
                        <input id="name" name="name" type="text" required>
                    </div>
                    <div>
                        <label for="icon">Icon or short label</label>
                        <input id="icon" name="icon" type="text" placeholder="e.g. HTML5">
                    </div>
                    <div>
                        <label for="level">Level</label>
                        <input id="level" name="level" type="text" placeholder="e.g. Advanced">
                    </div>
                    <div>
                        <label for="sort_order">Order</label>
                        <input id="sort_order" name="sort_order" type="number" value="0">
                    </div>
                </div>
                <div style="margin-top:1rem;">
                    <button type="submit" class="btn">Add skill</button>
                </div>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Icon</th>
                        <th>Level</th>
                        <th>Order</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($skills as $skill): ?>
                        <tr>
                            <td><?php echo e((string) ($skill['name'] ?? '')); ?></td>
                            <td><?php echo e((string) ($skill['icon'] ?? '')); ?></td>
                            <td><?php echo e((string) ($skill['level'] ?? '')); ?></td>
                            <td><?php echo e((string) ($skill['sort_order'] ?? 0)); ?></td>
                            <td>
                                <form method="post" action="skills.php" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo e((string) ($skill['id'] ?? '')); ?>">
                                    <button type="submit" class="btn danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
<?php $pdo = null; ?>



















































































































































































































































































































"},{"content":"<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

$pdo = db_connect();
$message = null;
$messageIsError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save';
    $title = trim((string) ($_POST['title'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $imageUrl = trim((string) ($_POST['image_url'] ?? ''));
    $projectUrl = trim((string) ($_POST['project_url'] ?? ''));
    $sortOrder = (int) ($_POST['sort_order'] ?? 0);
    $isPublished = isset($_POST['is_published']) ? 1 : 0;

    if ($title === '') {
        $message = 'Project title is required.';
        $messageIsError = true;
    } else {
        try {
            if (($action === 'delete') && !empty($_POST['id'])) {
                $stmt = $pdo->prepare('DELETE FROM projects WHERE id = :id');
                $stmt->execute([':id' => (int) $_POST['id']]);
                $message = 'Project deleted.';
            } elseif (!empty($_POST['id'])) {
                $stmt = $pdo->prepare('UPDATE projects SET title = :title, description = :description, image_url = :image_url, project_url = :project_url, is_published = :is_published, sort_order = :sort_order WHERE id = :id');
                $stmt->execute([
                    ':id' => (int) $_POST['id'],
                    ':title' => $title,
                    ':description' => $description,
                    ':image_url' => $imageUrl,
                    ':project_url' => $projectUrl,
                    ':is_published' => $isPublished,
                    ':sort_order' => $sortOrder,
                ]);
                $message = 'Project updated.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO projects (title, description, image_url, project_url, is_published, sort_order) VALUES (:title, :description, :image_url, :project_url, :is_published, :sort_order)');
                $stmt->execute([
                    ':title' => $title,
                    ':description' => $description,
                    ':image_url' => $imageUrl,
                    ':project_url' => $projectUrl,
                    ':is_published' => $isPublished,
                    ':sort_order' => $sortOrder,
                ]);
                $message = 'Project added.';
            }
            $messageIsError = false;
        } catch (Throwable $e) {
            error_log('Project save failed: ' . $e->getMessage());
            $message = 'Could not save the project.';
            $messageIsError = true;
        }
    }
}

$projects = $pdo->query('SELECT * FROM projects ORDER BY sort_order ASC, id ASC')->fetchAll() ?: [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects Manager | Dashboard</title>
    <style>
        :root { --bg:#f3f2ee; --panel:#fff; --text:#1b1d1f; --muted:#586168; --accent:#2d5f4c; --border:#dfe3d9; --danger:#b42318; }
        * { box-sizing: border-box; }
        body { margin:0; font-family:Arial,sans-serif; background:var(--bg); color:var(--text); }
        .topbar { background:var(--panel); border-bottom:1px solid var(--border); padding:1rem 2rem; display:flex; justify-content:space-between; align-items:center; }
        .nav { display:flex; gap:1rem; flex-wrap:wrap; align-items:center; }
        .layout { max-width:1100px; margin:0 auto; padding:2rem; }
        .panel { background:var(--panel); border:1px solid var(--border); border-radius:12px; padding:1.5rem; box-shadow:0 8px 20px rgba(21,26,30,0.05); }
        .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:1rem; }
        label { display:block; margin:1rem 0 .5rem; font-weight:600; }
        input, textarea, select { width:100%; border:1px solid var(--border); border-radius:8px; padding:0.8rem; font-size:1rem; }
        textarea { min-height:120px; resize:vertical; }
        table { width:100%; border-collapse:collapse; margin-top:1rem; }
        th, td { border:1px solid var(--border); text-align:left; padding:0.75rem; }
        .btn { display:inline-block; padding:0.8rem 1rem; border-radius:8px; background:var(--accent); color:white; font-weight:700; border:none; cursor:pointer; text-decoration:none; }
        .btn.secondary { background:white; color:var(--text); border:1px solid var(--border); }
        .btn.danger { background:#a1251b; color:white; }
        .alert { padding:0.8rem 1rem; border-radius:8px; margin-bottom:1rem; }
        .alert.success { background:#ecfdf5; border:1px solid #9fe7c4; color:#146c43; }
        .alert.error { background:#fef3f2; border:1px solid #f5c5c2; color:var(--danger); }
        .action-row { display:flex; gap:0.75rem; flex-wrap:wrap; margin-bottom:1rem; }
        .muted { color:var(--muted); }
    </style>
</head>
<body>
    <header class="topbar">
        <strong>Ajie C. Javier</strong>
        <div class="nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="profile.php">Profile</a>
            <a href="skills.php">Skills</a>
            <a href="projects.php">Projects</a>
            <a href="education.php">Education</a>
            <a href="messages.php">Messages</a>
            <a href="logout.php" class="btn secondary">Logout</a>
        </div>
    </header>

    <main class="layout">
        <div class="action-row">
            <a href="dashboard.php" class="btn secondary">Back to dashboard</a>
        </div>

        <?php if ($message !== null): ?>
            <div class="alert <?php echo $messageIsError ? 'error' : 'success'; ?>"><?php echo e($message); ?></div>
        <?php endif; ?>

        <section class="panel">
            <h1>Projects</h1>
            <p class="muted">Manage projects shown in the portfolio.</p>

            <form method="post" action="projects.php">
                <div class="grid">
                    <div>
                        <label for="title">Project title</label>
                        <input id="title" name="title" type="text" required>
                    </div>
                    <div>
                        <label for="project_url">Project URL</label>
                        <input id="project_url" name="project_url" type="url">
                    </div>
                    <div>
                        <label for="image_url">Image URL</label>
                        <input id="image_url" name="image_url" type="url">
                    </div>
                    <div>
                        <label for="sort_order">Order</label>
                        <input id="sort_order" name="sort_order" type="number" value="0">
                    </div>
                </div>

                <label for="description">Description</label>
                <textarea id="description" name="description"></textarea>

                <div style="margin-top:1rem;">
                    <label>
                        <input type="checkbox" name="is_published" checked>
                        Published
                    </label>
                </div>

                <div style="margin-top:1rem;">
                    <button type="submit" class="btn">Add project</button>
                </div>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Published</th>
                        <th>Order</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): ?>
                        <tr>
                            <td><?php echo e((string) ($project['title'] ?? '')); ?></td>
                            <td><?php echo !empty($project['is_published']) ? 'Yes' : 'No'; ?></td>
                            <td><?php echo e((string) ($project['sort_order'] ?? 0)); ?></td>
                            <td>
                                <form method="post" action="projects.php" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo e((string) ($project['id'] ?? '')); ?>">
                                    <button type="submit" class="btn danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
<?php $pdo = null; ?>

































































































































"},{"content":"<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

$pdo = db_connect();
$message = null;
$messageIsError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save';
    $school = trim((string) ($_POST['school'] ?? ''));
    $degree = trim((string) ($_POST['degree'] ?? ''));
    $period = trim((string) ($_POST['period'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $sortOrder = (int) ($_POST['sort_order'] ?? 0);

    if ($school === '' || $degree === '') {
        $message = 'School and degree are required.';
        $messageIsError = true;
    } else {
        try {
            if (($action === 'delete') && !empty($_POST['id'])) {
                $stmt = $pdo->prepare('DELETE FROM education WHERE id = :id');
                $stmt->execute([':id' => (int) $_POST['id']]);
                $message = 'Education entry deleted.';
            } elseif (!empty($_POST['id'])) {
                $stmt = $pdo->prepare('UPDATE education SET school = :school, degree = :degree, period = :period, description = :description, sort_order = :sort_order WHERE id = :id');
                $stmt->execute([
                    ':id' => (int) $_POST['id'],
                    ':school' => $school,
                    ':degree' => $degree,
                    ':period' => $period,
                    ':description' => $description,
                    ':sort_order' => $sortOrder,
                ]);
                $message = 'Education entry updated.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO education (school, degree, period, description, sort_order) VALUES (:school, :degree, :period, :description, :sort_order)');
                $stmt->execute([
                    ':school' => $school,
                    ':degree' => $degree,
                    ':period' => $period,
                    ':description' => $description,
                    ':sort_order' => $sortOrder,
                ]);
                $message = 'Education entry added.';
            }
            $messageIsError = false;
        } catch (Throwable $e) {
            error_log('Education save failed: ' . $e->getMessage());
            $message = 'Could not save the education entry.';
            $messageIsError = true;
        }
    }
}

$items = $pdo->query('SELECT * FROM education ORDER BY sort_order ASC, end_date DESC, id DESC')->fetchAll() ?: [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Education Manager | Dashboard</title>
    <style>
        :root { --bg:#f3f2ee; --panel:#fff; --text:#1b1d1f; --muted:#586168; --accent:#2d5f4c; --border:#dfe3d9; --danger:#b42318; }
        * { box-sizing: border-box; }
        body { margin:0; font-family:Arial,sans-serif; background:var(--bg); color:var(--text); }
        .topbar { background:var(--panel); border-bottom:1px solid var(--border); padding:1rem 2rem; display:flex; justify-content:space-between; align-items:center; }
        .nav { display:flex; gap:1rem; flex-wrap:wrap; align-items:center; }
        .layout { max-width:1100px; margin:0 auto; padding:2rem; }
        .panel { background:var(--panel); border:1px solid var(--border); border-radius:12px; padding:1.5rem; box-shadow:0 8px 20px rgba(21,26,30,0.05); }
        .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:1rem; }
        label { display:block; margin:1rem 0 .5rem; font-weight:600; }
        input, textarea { width:100%; border:1px solid var(--border); border-radius:8px; padding:0.8rem; font-size:1rem; }
        textarea { min-height:120px; resize:vertical; }
        table { width:100%; border-collapse:collapse; margin-top:1rem; }
        th, td { border:1px solid var(--border); text-align:left; padding:0.75rem; }
        .btn { display:inline-block; padding:0.8rem 1rem; border-radius:8px; background:var(--accent); color:white; font-weight:700; border:none; cursor:pointer; text-decoration:none; }
        .btn.secondary { background:white; color:var(--text); border:1px solid var(--border); }
        .btn.danger { background:#a1251b; color:white; }
        .alert { padding:0.8rem 1rem; border-radius:8px; margin-bottom:1rem; }
        .alert.success { background:#ecfdf5; border:1px solid #9fe7c4; color:#146c43; }
        .alert.error { background:#fef3f2; border:1px solid #f5c5c2; color:var(--danger); }
        .action-row { display:flex; gap:0.75rem; flex-wrap:wrap; margin-bottom:1rem; }
        .muted { color:var(--muted); }
    </style>
</head>
<body>
    <header class="topbar">
        <strong>Ajie C. Javier</strong>
        <div class="nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="profile.php">Profile</a>
            <a href="skills.php">Skills</a>
            <a href="projects.php">Projects</a>
            <a href="education.php">Education</a>
            <a href="messages.php">Messages</a>
            <a href="logout.php" class="btn secondary">Logout</a>
        </div>
    </header>

    <main class="layout">
        <div class="action-row">
            <a href="dashboard.php" class="btn secondary">Back to dashboard</a>
        </div>

        <?php if ($message !== null): ?>
            <div class="alert <?php echo $messageIsError ? 'error' : 'success'; ?>"><?php echo e($message); ?></div>
        <?php endif; ?>

        <section class="panel">
            <h1>Education</h1>
            <p class="muted">Add academic background entries for the portfolio.</p>

            <form method="post" action="education.php">
                <div class="grid">
                    <div>
                        <label for="school">School / institution</label>
                        <input id="school" name="school" type="text" required>
                    </div>
                    <div>
                        <label for="degree">Degree / course</label>
                        <input id="degree" name="degree" type="text" required>
                    </div>
                    <div>
                        <label for="period">Period</label>
                        <input id="period" name="period" type="text">
                    </div>
                    <div>
                        <label for="sort_order">Order</label>
                        <input id="sort_order" name="sort_order" type="number" value="0">
                    </div>
                </div>

                <label for="description">Description</label>
                <textarea id="description" name="description"></textarea>

                <div style="margin-top:1rem;">
                    <button type="submit" class="btn">Add education</button>
                </div>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>School</th>
                        <th>Degree</th>
                        <th>Period</th>
                        <th>Order</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo e((string) ($item['school'] ?? '')); ?></td>
                            <td><?php echo e((string) ($item['degree'] ?? '')); ?></td>
                            <td><?php echo e((string) ($item['period'] ?? '')); ?></td>
                            <td><?php echo e((string) ($item['sort_order'] ?? 0)); ?></td>
                            <td>
                                <form method="post" action="education.php" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo e((string) ($item['id'] ?? '')); ?>">
                                    <button type="submit" class="btn danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
<?php $pdo = null; ?>


























































































































































































































"},{"content":"<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

$pdo = db_connect();
$message = null;
$messageIsError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        try {
            $stmt = $pdo->prepare('DELETE FROM contact_messages WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $message = 'Message deleted.';
            $messageIsError = false;
        } catch (Throwable $e) {
            error_log('Message delete failed: ' . $e->getMessage());
            $message = 'Could not delete the message.';
            $messageIsError = true;
        }
    }
}

$messages = $pdo->query('SELECT * FROM contact_messages ORDER BY created_at DESC')->fetchAll() ?: [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages | Dashboard</title>
    <style>
        :root { --bg:#f3f2ee; --panel:#fff; --text:#1b1d1f; --muted:#586168; --accent:#2d5f4c; --border:#dfe3d9; --danger:#b42318; }
        * { box-sizing: border-box; }
        body { margin:0; font-family:Arial,sans-serif; background:var(--bg); color:var(--text); }
        .topbar { background:var(--panel); border-bottom:1px solid var(--border); padding:1rem 2rem; display:flex; justify-content:space-between; align-items:center; }
        .nav { display:flex; gap:1rem; flex-wrap:wrap; align-items:center; }
        .layout { max-width:1100px; margin:0 auto; padding:2rem; }
        .panel { background:var(--panel); border:1px solid var(--border); border-radius:12px; padding:1.5rem; box-shadow:0 8px 20px rgba(21,26,30,0.05); }
        .alert { padding:0.8rem 1rem; border-radius:8px; margin-bottom:1rem; }
        .alert.success { background:#ecfdf5; border:1px solid #9fe7c4; color:#146c43; }
        .alert.error { background:#fef3f2; border:1px solid #f5c5c2; color:var(--danger); }
        .btn { display:inline-block; padding:0.8rem 1rem; border-radius:8px; background:var(--accent); color:white; font-weight:700; border:none; cursor:pointer; text-decoration:none; }
        .btn.secondary { background:white; color:var(--text); border:1px solid var(--border); }
        .btn.danger { background:#a1251b; color:white; }
        .action-row { display:flex; gap:0.75rem; flex-wrap:wrap; margin-bottom:1rem; }
        table { width:100%; border-collapse:collapse; margin-top:1rem; }
        th, td { border:1px solid var(--border); text-align:left; padding:0.75rem; vertical-align:top; }
        .muted { color:var(--muted); }
    </style>
</head>
<body>
    <header class="topbar">
        <strong>Ajie C. Javier</strong>
        <div class="nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="profile.php">Profile</a>
            <a href="skills.php">Skills</a>
            <a href="projects.php">Projects</a>
            <a href="education.php">Education</a>
            <a href="messages.php">Messages</a>
            <a href="logout.php" class="btn secondary">Logout</a>
        </div>
    </header>

    <main class="layout">
        <div class="action-row">
            <a href="dashboard.php" class="btn secondary">Back to dashboard</a>
        </div>

        <?php if ($message !== null): ?>
            <div class="alert <?php echo $messageIsError ? 'error' : 'success'; ?>"><?php echo e($message); ?></div>
        <?php endif; ?>

        <section class="panel">
            <h1>Contact messages</h1>
            <p class="muted">Review and delete incoming contact messages.</p>

            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $messageRow): ?>
                        <tr>
                            <td><?php echo e((string) ($messageRow['name'] ?? '')); ?></td>
                            <td><?php echo e((string) ($messageRow['email'] ?? '')); ?></td>
                            <td><?php echo nl2br(e((string) ($messageRow['message'] ?? ''))); ?></td>
                            <td><?php echo e((string) ($messageRow['created_at'] ?? '')); ?></td>
                            <td>
                                <form method="post" action="messages.php">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo e((string) ($messageRow['id'] ?? '')); ?>">
                                    <button type="submit" class="btn danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
<?php $pdo = null; ?>

































































































































































"},{"content":"<?php
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
        body { margin: 0; background: var(--bg); color: var(--text); font-family: Arial, sans-serif; }
        .topbar { background: var(--panel); border-bottom: 1px solid var(--border); padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .nav { display:flex; gap:1rem; align-items:center; flex-wrap:wrap; }
        a { color: inherit; text-decoration:none; }
        .layout { max-width: 1200px; margin:0 auto; padding:2rem; }
        .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:1.25rem; margin-top:1.5rem; }
        .card { background: var(--panel); border: 1px solid var(--border); border-radius:12px; box-shadow:0 8px 20px var(--shadow); padding:1.5rem; }
        .label { color: var(--muted); font-size:0.9rem; }
        .value { font-size:2rem; font-weight:700; margin-top:0.5rem; }
        .alert { padding:0.8rem 1rem; border-radius:8px; margin-bottom:1rem; }
        .alert.success { background:#ecfdf5; border:1px solid #9fe7c4; color:#146c43; }
        .alert.error { background:#fef3f2; border:1px solid #f5c5c2; color:#b42318; }
        .profile-box { background: var(--panel); border:1px solid var(--border); border-radius:12px; padding:1.5rem; margin-bottom:1.5rem; }
        .actions { display:flex; gap:0.75rem; flex-wrap:wrap; margin-top:1rem; }
        .btn { display:inline-block; padding:0.8rem 1rem; border-radius:8px; background:var(--accent); color:white; font-weight:700; }
        .btn.secondary { background:white; border:1px solid var(--border); color:var(--text); }
    </style>
</head>
<body>
    <header class="topbar">
        <strong>Ajie C. Javier</strong>
        <div class="nav">
            <span>Logged in as <?php echo e(current_admin_name()); ?></span>
            <a href="logout.php" class="btn secondary">Logout</a>
        </div>
    </header>

    <main class="layout">
        <section class="profile-box">
            <h1>Dashboard</h1>
            <p>Manage the public portfolio and administrator content.</p>
            <div class="actions">
                <a href="profile.php" class="btn secondary">Profile</a>
                <a href="skills.php" class="btn secondary">Skills</a>
                <a href="projects.php" class="btn secondary">Projects</a>
                <a href="education.php" class="btn secondary">Education</a>
                <a href="messages.php" class="btn secondary">Messages</a>
                <a href="../index.php" class="btn secondary">Public Site</a>
                <a href="dashboard.php" class="btn secondary">Refresh</a>
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
<?php $pdo = null; ?>
