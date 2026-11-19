<?php
require __DIR__ . '/../includes/bootstrap.php';
require_https();
require_admin();
$modules = [
    'skills' => ['label' => 'Skills', 'fields' => ['name' => 'Skill name', 'category' => 'Category', 'level' => 'Level (0–100)', 'description' => 'Description']],
    'projects' => ['label' => 'Projects', 'fields' => ['title' => 'Title', 'description' => 'Description', 'technologies' => 'Technologies (comma separated)', 'image' => 'Image']],
    'education' => ['label' => 'Education', 'fields' => ['title' => 'Title', 'organization' => 'School', 'period' => 'Period', 'description' => 'Description']],
    'experience' => ['label' => 'Experience', 'fields' => ['title' => 'Title', 'organization' => 'Organization', 'period' => 'Period', 'description' => 'Description']],
    'achievements' => ['label' => 'Achievements', 'fields' => ['title' => 'Title', 'organization' => 'Organization', 'period' => 'Period', 'description' => 'Description', 'image' => 'Image']]
];
$page = $_GET['page'] ?? 'dashboard';
if (!in_array($page, array_merge(['dashboard', 'settings', 'messages', 'account'], array_keys($modules)), true)) { http_response_code(404); exit('Page not found.'); }
$error = '';
$notice = $_SESSION['notice'] ?? '';
unset($_SESSION['notice']);
$settings = $db->query('SELECT * FROM settings WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$record = [];
if (isset($modules[$page]) && $id) {
    $stmt = $db->prepare("SELECT * FROM $page WHERE id = ?");
    $stmt->execute([$id]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$record) { http_response_code(404); exit('Record not found.'); }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    try {
        if ($page === 'settings') {
            $fields = ['site_title', 'name', 'introduction', 'biography', 'goals', 'interests', 'email', 'github', 'facebook', 'instagram', 'appearance'];
            $values = [];
            foreach ($fields as $field) $values[$field] = trim($_POST[$field] ?? '');
            foreach (['site_title', 'name', 'introduction', 'biography', 'email'] as $field) if ($values[$field] === '') throw new RuntimeException('Please fill in all required fields.');
            if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL) || strlen($values['email']) > 190) throw new RuntimeException('Enter a valid email address.');
            foreach (['github', 'facebook', 'instagram'] as $field) if ($values[$field] !== '' && (!filter_var($values[$field], FILTER_VALIDATE_URL) || !preg_match('/^https?:\/\//i', $values[$field]) || strlen($values[$field]) > 255)) throw new RuntimeException('Social links must be valid HTTP or HTTPS URLs.');
            if (!in_array($values['appearance'], ['light', 'dark'], true)) throw new RuntimeException('Choose a valid appearance.');
            foreach ($values as $field => $value) if (mb_strlen($value) > (in_array($field, ['introduction', 'biography', 'goals', 'interests']) ? 5000 : 150) && !in_array($field, ['email', 'github', 'facebook', 'instagram'])) throw new RuntimeException('A field is too long.');
            $values['profile_image'] = upload_image('profile_image') ?: $settings['profile_image'];
            $values['logo_image'] = upload_image('logo_image') ?: $settings['logo_image'];
            $assignments = [];
            foreach (array_keys($values) as $field) $assignments[] = "$field = ?";
            $stmt = $db->prepare('UPDATE settings SET ' . implode(', ', $assignments) . ' WHERE id = 1');
            $stmt->execute(array_values($values));
            remove_unused_image($settings['profile_image']);
            remove_unused_image($settings['logo_image']);
        } elseif (isset($modules[$page])) {
            $action = $_POST['action'] ?? '';
            $postedId = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
            if ($action === 'delete') {
                $oldImage = '';
                if (isset($modules[$page]['fields']['image'])) {
                    $stmt = $db->prepare("SELECT image FROM $page WHERE id = ?");
                    $stmt->execute([$postedId]);
                    $oldImage = $stmt->fetchColumn() ?: '';
                }
                $stmt = $db->prepare("DELETE FROM $page WHERE id = ?");
                $stmt->execute([$postedId]);
                remove_unused_image($oldImage);
            } elseif ($action === 'save') {
                $existing = [];
                if ($postedId) {
                    $stmt = $db->prepare("SELECT * FROM $page WHERE id = ?");
                    $stmt->execute([$postedId]);
                    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (!$existing) throw new RuntimeException('Record not found.');
                }
                $values = [];
                foreach ($modules[$page]['fields'] as $field => $label) {
                    if ($field === 'image') continue;
                    $value = trim($_POST[$field] ?? '');
                    $limit = $field === 'description' ? 5000 : ($field === 'technologies' ? 255 : ($field === 'period' || $field === 'category' ? 100 : ($field === 'name' ? 150 : 200)));
                    if (($value === '' && !in_array($field, ['description', 'organization', 'period', 'technologies'], true)) || mb_strlen($value) > $limit) throw new RuntimeException('Check the required fields and their lengths.');
                    if ($field === 'level' && (filter_var($value, FILTER_VALIDATE_INT) === false || (int) $value < 0 || (int) $value > 100)) throw new RuntimeException('Skill level must be a number from 0 to 100.');
                    $values[$field] = $value;
                }
                if (isset($modules[$page]['fields']['image'])) $values['image'] = upload_image('image') ?: ($existing['image'] ?? '');
                $columns = array_keys($values);
                if ($postedId) {
                    $assignments = array_map(fn($column) => "$column = ?", $columns);
                    $stmt = $db->prepare("UPDATE $page SET " . implode(', ', $assignments) . ' WHERE id = ?');
                    $stmt->execute([...array_values($values), $postedId]);
                    if (isset($existing['image'])) remove_unused_image($existing['image']);
                } else {
                    $stmt = $db->prepare("INSERT INTO $page (" . implode(', ', $columns) . ') VALUES (' . implode(', ', array_fill(0, count($columns), '?')) . ')');
                    $stmt->execute(array_values($values));
                }
            } else throw new RuntimeException('Invalid action.');
        } elseif ($page === 'messages') {
            $messageId = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
            $action = $_POST['action'] ?? '';
            if ($action === 'delete') $stmt = $db->prepare('DELETE FROM messages WHERE id = ?');
            elseif ($action === 'toggle') $stmt = $db->prepare('UPDATE messages SET is_read = 1 - is_read WHERE id = ?');
            else throw new RuntimeException('Invalid action.');
            $stmt->execute([$messageId]);
        } elseif ($page === 'account') {
            $stmt = $db->prepare('SELECT password_hash FROM admins WHERE id = ?');
            $stmt->execute([$_SESSION['admin_id']]);
            $hash = $stmt->fetchColumn();
            $new = $_POST['new_password'] ?? '';
            if (!$hash || !password_verify($_POST['current_password'] ?? '', $hash)) throw new RuntimeException('Current password is incorrect.');
            if (strlen($new) < 12 || strlen($new) > 72 || $new !== ($_POST['confirm_password'] ?? '')) throw new RuntimeException('New passwords must match and be 12 to 72 characters long.');
            $stmt = $db->prepare('UPDATE admins SET password_hash = ? WHERE id = ?');
            $stmt->execute([password_hash($new, PASSWORD_DEFAULT), $_SESSION['admin_id']]);
            session_regenerate_id(true);
        } else throw new RuntimeException('Invalid action.');
        $_SESSION['notice'] = 'Changes saved.';
        redirect('index.php?page=' . $page);
    } catch (PDOException $exception) {
        $error = 'Changes could not be saved. Please try again later.';
    } catch (RuntimeException $exception) {
        $error = $exception->getMessage();
    }
}
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Portfolio dashboard</title>
<link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<div class="admin-layout">
<aside class="sidebar">
<a class="brand" href="index.php">Portfolio <span>admin</span>
</a>
<nav>
<a href="index.php">Overview</a>
<a href="index.php?page=settings">Personal info &amp; settings</a><?php foreach ($modules as $key => $module): ?><a href="index.php?page=<?= e($key) ?>"><?= e($module['label']) ?></a><?php endforeach; ?><a href="index.php?page=messages">Messages</a>
<a href="index.php?page=account">Account</a>
<a href="../index.php">View website ↗</a>
</nav>
<form method="post" action="logout.php"><?= csrf_field() ?><button class="logout" type="submit">Log out</button>
</form>
</aside>
<main class="content">
<header class="top">
<div>
<p class="eyebrow">PORTFOLIO ADMIN</p>
<h1><?= e($modules[$page]['label'] ?? ['dashboard' => 'Dashboard', 'settings' => 'Personal info & settings', 'messages' => 'Messages', 'account' => 'Account'][$page]) ?></h1>
</div>
<span>Manage your website</span>
</header><?php if ($notice): ?><p class="notice" role="status"><?= e($notice) ?></p><?php endif; ?><?php if ($error): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?>
<?php if ($page === 'dashboard'): ?><p class="lead">Welcome back. Here is a quick look at your portfolio.</p>
<div class="stats"><?php foreach (['projects' => 'Projects', 'skills' => 'Skills', 'messages' => 'Messages', 'experience' => 'Experience', 'achievements' => 'Achievements'] as $table => $label): ?><a class="stat" href="index.php?page=<?= e($table) ?>">
<span><?= e($label) ?></span>
<strong><?= (int) $db->query("SELECT COUNT(*) FROM $table")->fetchColumn() ?></strong>
<small>Manage →</small>
</a><?php endforeach; ?></div>
<?php elseif ($page === 'settings'): ?><form method="post" enctype="multipart/form-data" class="panel form-grid"><?= csrf_field() ?><?php foreach (['site_title' => 'Website title', 'name' => 'Name', 'introduction' => 'Introduction', 'biography' => 'Biography', 'goals' => 'Goals', 'interests' => 'Interests', 'email' => 'Email', 'github' => 'GitHub URL', 'facebook' => 'Facebook URL', 'instagram' => 'Instagram URL'] as $field => $label): ?><label><?= e($label) ?><?php if (in_array($field, ['introduction','biography','goals','interests'])): ?><textarea name="<?= e($field) ?>" rows="4" maxlength="5000"><?= e($settings[$field]) ?></textarea><?php else: ?><input name="<?= e($field) ?>" value="<?= e($settings[$field]) ?>" maxlength="<?= in_array($field,['github','facebook','instagram']) ? 255 : ($field === 'email' ? 190 : 150) ?>"><?php endif; ?></label><?php endforeach; ?><label>Default appearance<select name="appearance">
<option value="light" <?= $settings['appearance'] === 'light' ? 'selected' : '' ?>>Light</option>
<option value="dark" <?= $settings['appearance'] === 'dark' ? 'selected' : '' ?>>Dark</option>
</select>
</label>
<label>Profile image<input type="file" name="profile_image" accept="image/jpeg,image/png,image/webp">
<small>JPEG, PNG or WebP, up to 2 MB.</small>
</label>
<label>Logo image<input type="file" name="logo_image" accept="image/jpeg,image/png,image/webp">
<small>JPEG, PNG or WebP, up to 2 MB.</small>
</label>
<button>Save settings</button>
</form>
<?php elseif (isset($modules[$page])): ?><div class="columns">
<section class="panel">
<h2><?= $id ? 'Edit' : 'Add' ?> <?= e(rtrim($modules[$page]['label'], 's')) ?></h2>
<form method="post" enctype="multipart/form-data"><?= csrf_field() ?><input type="hidden" name="action" value="save">
<input type="hidden" name="id" value="<?= (int) $id ?>"><?php foreach ($modules[$page]['fields'] as $field => $label): ?><label><?= e($label) ?><?php if ($field === 'description'): ?><textarea name="<?= e($field) ?>" rows="4" maxlength="5000"><?= e($_POST[$field] ?? $record[$field] ?? '') ?></textarea><?php elseif ($field === 'image'): ?><input type="file" name="image" accept="image/jpeg,image/png,image/webp">
<small>JPEG, PNG or WebP, up to 2 MB.</small><?php else: ?><input name="<?= e($field) ?>" value="<?= e($_POST[$field] ?? $record[$field] ?? '') ?>" <?= $field === 'level' ? 'type="number" min="0" max="100"' : '' ?> <?= in_array($field, ['name','title','category','level']) ? 'required' : '' ?>><?php endif; ?></label><?php endforeach; ?><button>Save</button><?php if ($id): ?> <a href="index.php?page=<?= e($page) ?>">Cancel</a><?php endif; ?></form>
</section>
<section class="panel">
<h2>All <?= e(strtolower($modules[$page]['label'])) ?></h2><?php $rows = $db->query("SELECT * FROM $page ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC); if (!$rows): ?><p>No records yet.</p><?php endif; ?><?php foreach ($rows as $row): ?><div class="item">
<div>
<strong><?= e($row['name'] ?? $row['title']) ?></strong>
<small><?= e($row['category'] ?? $row['period'] ?? '') ?></small>
</div>
<div class="actions">
<a href="index.php?page=<?= e($page) ?>&amp;id=<?= (int) $row['id'] ?>">Edit</a>
<form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
<input type="hidden" name="action" value="delete">
<button class="text-button" onclick="return confirm('Delete this record?')">Delete</button>
</form>
</div>
</div><?php endforeach; ?></section>
</div>
<?php elseif ($page === 'messages'): ?><?php $rows = $db->query('SELECT * FROM messages ORDER BY created_at DESC, id DESC')->fetchAll(PDO::FETCH_ASSOC); if (!$rows): ?><div class="panel">No messages yet.</div><?php endif; ?><?php foreach ($rows as $row): ?><article class="panel message">
<div class="item">
<div>
<span class="badge"><?= $row['is_read'] ? 'Read' : 'Unread' ?></span>
<h2><?= e($row['subject']) ?></h2>
<p><?= e($row['name']) ?> · <?= e($row['email']) ?> · <?= e($row['created_at']) ?></p>
</div>
</div>
<p class="message-body"><?= nl2br(e($row['message'])) ?></p>
<div class="actions"><?php foreach (['toggle' => $row['is_read'] ? 'Mark unread' : 'Mark read', 'delete' => 'Delete'] as $action => $label): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
<input type="hidden" name="action" value="<?= e($action) ?>">
<button class="text-button" <?= $action === 'delete' ? 'onclick="return confirm(\'Delete this message?\')"' : '' ?>><?= e($label) ?></button>
</form><?php endforeach; ?></div>
</article><?php endforeach; ?>
<?php else: ?><form method="post" class="panel narrow"><?= csrf_field() ?><h2>Change password</h2>
<label>Current password<input name="current_password" type="password" required autocomplete="current-password">
</label>
<label>New password<input name="new_password" type="password" required minlength="12" maxlength="72" autocomplete="new-password">
</label>
<label>Confirm new password<input name="confirm_password" type="password" required minlength="12" maxlength="72" autocomplete="new-password">
</label>
<button>Update password</button>
</form><?php endif; ?></main>
</div>
</body>
</html>
