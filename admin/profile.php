<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

$pdo = db_connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $introduction = trim($_POST['introduction'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $githubUrl = trim($_POST['github_url'] ?? '');
    $facebookUrl = trim($_POST['facebook_url'] ?? '');
    $instagramUrl = trim($_POST['instagram_url'] ?? '');
    $profileImage = trim($_POST['profile_image'] ?? '');

    $stmt = $pdo->prepare('UPDATE profile SET full_name = :full_name, introduction = :introduction, email = :email, github_url = :github_url, facebook_url = :facebook_url, instagram_url = :instagram_url, profile_image = :profile_image WHERE id = 1');
    $stmt->execute([
        ':full_name' => $fullName,
        ':introduction' => $introduction,
        ':email' => $email,
        ':github_url' => $githubUrl,
        ':facebook_url' => $facebookUrl,
        ':instagram_url' => $instagramUrl,
        ':profile_image' => $profileImage,
    ]);

    set_flash('success', 'Profile updated successfully.');
    redirect('profile.php');
}

$profile = fetch_profile($pdo);
$flashSuccess = get_flash('success');
$flashError = get_flash('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f2ee;
            color: #1b1d1f;
        }
        .wrap {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .panel {
            background: #fff;
            border: 1px solid #dfe3d9;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 8px 20px rgba(21,26,30,0.08);
        }
        h1 {
            margin-top: 0;
        }
        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 10px 16px;
            background: #2d5f4c;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
        }
        .btn.secondary {
            background: #fff;
            color: #1b1d1f;
            border: 1px solid #dfe3d9;
        }
        form {
            display: grid;
            gap: 16px;
        }
        label {
            display: block;
            font-weight: 700;
            margin-bottom: 8px;
        }
        input, textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #dfe3d9;
            border-radius: 8px;
            font-size: 16px;
        }
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        .alert {
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 16px;
        }
        .alert.success {
            background: #ecfdf5;
            border: 1px solid #9fe7c4;
            color: #146c43;
        }
        .alert.error {
            background: #fef3f2;
            border: 1px solid #f5c5c2;
            color: #b42318;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="top">
            <h1>Profile</h1>
            <div>
                <a href="dashboard.php" class="btn secondary">Back to Dashboard</a>
            </div>
        </div>

        <?php if ($flashSuccess): ?>
            <div class="alert success"><?php echo e($flashSuccess); ?></div>
        <?php endif; ?>

        <?php if ($flashError): ?>
            <div class="alert error"><?php echo e($flashError); ?></div>
        <?php endif; ?>

        <div class="panel">
            <form method="post" action="profile.php">
                <div>
                    <label for="full_name">Full Name</label>
                    <input id="full_name" name="full_name" type="text" value="<?php echo e($profile['full_name'] ?? ''); ?>">
                </div>

                <div>
                    <label for="introduction">Introduction</label>
                    <textarea id="introduction" name="introduction"><?php echo e($profile['introduction'] ?? ''); ?></textarea>
                </div>

                <div>
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="<?php echo e($profile['email'] ?? ''); ?>">
                </div>

                <div>
                    <label for="github_url">GitHub URL</label>
                    <input id="github_url" name="github_url" type="url" value="<?php echo e($profile['github_url'] ?? ''); ?>">
                </div>

                <div>
                    <label for="facebook_url">Facebook URL</label>
                    <input id="facebook_url" name="facebook_url" type="url" value="<?php echo e($profile['facebook_url'] ?? ''); ?>">
                </div>

                <div>
                    <label for="instagram_url">Instagram URL</label>
                    <input id="instagram_url" name="instagram_url" type="url" value="<?php echo e($profile['instagram_url'] ?? ''); ?>">
                </div>

                <div>
                    <label for="profile_image">Profile Image</label>
                    <input id="profile_image" name="profile_image" type="text" value="<?php echo e($profile['profile_image'] ?? ''); ?>">
                </div>

                <button type="submit" class="btn">Save Profile</button>
            </form>
        </div>
    </div>
</body>
</html>
