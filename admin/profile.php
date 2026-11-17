<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();
$pdo = db_connect();

$defaults = [
    'id' => 1,
    'full_name' => 'Ajie C. Javier',
    'headline' => '',
    'introduction' => '',
    'biography' => '',
    'email' => '',
    'phone' => '',
    'location' => '',
    'profile_image' => 'images/Ajieprofile.jpeg',
    'github_url' => '',
    'facebook_url' => '',
    'instagram_url' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Security check failed. Please try again.');
        redirect('profile.php');
    }

    $profile = [
        'full_name' => trim((string) ($_POST['full_name'] ?? '')),
        'headline' => trim((string) ($_POST['headline'] ?? '')),
        'introduction' => trim((string) ($_POST['introduction'] ?? '')),
        'biography' => trim((string) ($_POST['biography'] ?? '')),
        'email' => trim((string) ($_POST['email'] ?? '')),
        'phone' => trim((string) ($_POST['phone'] ?? '')),
        'location' => trim((string) ($_POST['location'] ?? '')),
        'profile_image' => trim((string) ($_POST['profile_image'] ?? '')),
        'github_url' => trim((string) ($_POST['github_url'] ?? '')),
        'facebook_url' => trim((string) ($_POST['facebook_url'] ?? '')),
        'instagram_url' => trim((string) ($_POST['instagram_url'] ?? '')),
    ];

    if ($profile['full_name'] === '') {
        set_flash('error', 'Full name is required.');
        redirect('profile.php');
    }
    if ($profile['email'] !== '' && !is_valid_email($profile['email'])) {
        set_flash('error', 'Enter a valid email address or leave the field blank.');
        redirect('profile.php');
    }
    foreach (['github_url', 'facebook_url', 'instagram_url'] as $urlField) {
        if ($profile[$urlField] !== '' && filter_var($profile[$urlField], FILTER_VALIDATE_URL) === false) {
            set_flash('error', 'Enter valid social profile URLs or leave them blank.');
            redirect('profile.php');
        }
    }

    if ($profile['profile_image'] === '') {
        $profile['profile_image'] = 'images/Ajieprofile.jpeg';
    }

    $stmt = $pdo->prepare(
        'INSERT INTO profile (id, full_name, headline, introduction, biography, email, phone, location, profile_image, github_url, facebook_url, instagram_url)
         VALUES (1, :full_name, :headline, :introduction, :biography, :email, :phone, :location, :profile_image, :github_url, :facebook_url, :instagram_url)
         ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), headline = VALUES(headline), introduction = VALUES(introduction), biography = VALUES(biography), email = VALUES(email), phone = VALUES(phone), location = VALUES(location), profile_image = VALUES(profile_image), github_url = VALUES(github_url), facebook_url = VALUES(facebook_url), instagram_url = VALUES(instagram_url)'
    );
    $stmt->execute($profile);

    set_flash('success', 'Profile updated.');
    redirect('profile.php');
}

$profile = array_merge($defaults, fetch_profile($pdo));
$flashSuccess = get_flash('success');
$flashError = get_flash('error');
$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | Dashboard</title>
    <style>
        body { margin:0; font-family:Arial,sans-serif; background:#f3f2ee; color:#1b1d1f; }
        .wrap { max-width:1050px; margin:40px auto; padding:0 20px; }
        .top { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:20px; }
        .panel { background:#fff; border:1px solid #dfe3d9; border-radius:12px; padding:24px; box-shadow:0 8px 20px rgba(21,26,30,.08); }
        .btn { display:inline-block; padding:10px 16px; background:#2d5f4c; color:#fff; border-radius:8px; text-decoration:none; border:0; cursor:pointer; }
        .secondary { background:#fff; color:#1b1d1f; border:1px solid #dfe3d9; }
        .alert { padding:12px 14px; border-radius:8px; margin-bottom:16px; }
        .success { background:#ecfdf5; border:1px solid #9fe7c4; color:#146c43; }
        .error { background:#fef3f2; border:1px solid #f5c5c2; color:#b42318; }
        form { display:grid; gap:16px; }
        .fields { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:16px; }
        label { display:block; font-weight:700; margin-bottom:8px; }
        input, textarea { width:100%; padding:10px 12px; border:1px solid #dfe3d9; border-radius:8px; font-size:16px; box-sizing:border-box; }
        textarea { min-height:110px; }
    </style>
</head>
<body>
    <main class="wrap">
        <div class="top"><h1>Profile</h1><a href="dashboard.php" class="btn secondary">Back to Dashboard</a></div>
        <?php if ($flashSuccess): ?><div class="alert success"><?php echo e($flashSuccess); ?></div><?php endif; ?>
        <?php if ($flashError): ?><div class="alert error"><?php echo e($flashError); ?></div><?php endif; ?>
        <section class="panel">
            <form method="post" action="profile.php">
                <input type="hidden" name="csrf_token" value="<?php echo e($csrf); ?>">
                <div class="fields">
                    <div><label for="full_name">Full name</label><input id="full_name" name="full_name" maxlength="120" value="<?php echo e((string) $profile['full_name']); ?>" required></div>
                    <div><label for="headline">Headline</label><input id="headline" name="headline" maxlength="180" value="<?php echo e((string) $profile['headline']); ?>"></div>
                    <div><label for="email">Email</label><input id="email" name="email" type="email" maxlength="254" value="<?php echo e((string) $profile['email']); ?>"></div>
                    <div><label for="phone">Phone</label><input id="phone" name="phone" maxlength="40" value="<?php echo e((string) $profile['phone']); ?>"></div>
                    <div><label for="location">Location</label><input id="location" name="location" maxlength="160" value="<?php echo e((string) $profile['location']); ?>"></div>
                    <div><label for="profile_image">Profile image path</label><input id="profile_image" name="profile_image" maxlength="255" value="<?php echo e((string) $profile['profile_image']); ?>"></div>
                    <div><label for="github_url">GitHub URL</label><input id="github_url" name="github_url" type="url" maxlength="255" value="<?php echo e((string) $profile['github_url']); ?>"></div>
                    <div><label for="facebook_url">Facebook URL</label><input id="facebook_url" name="facebook_url" type="url" maxlength="255" value="<?php echo e((string) $profile['facebook_url']); ?>"></div>
                    <div><label for="instagram_url">Instagram URL</label><input id="instagram_url" name="instagram_url" type="url" maxlength="255" value="<?php echo e((string) $profile['instagram_url']); ?>"></div>
                </div>
                <div><label for="introduction">Introduction</label><textarea id="introduction" name="introduction"><?php echo e((string) $profile['introduction']); ?></textarea></div>
                <div><label for="biography">Biography</label><textarea id="biography" name="biography"><?php echo e((string) $profile['biography']); ?></textarea></div>
                <button class="btn" type="submit">Save Profile</button>
            </form>
        </section>
    </main>
</body>
</html>
