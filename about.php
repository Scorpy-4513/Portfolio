<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = db_connect();
$profile = fetch_profile($pdo);
$settings = fetch_settings($pdo);
$pdo = null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About | <?php echo e($profile['full_name'] ?? 'Ajie C. Javier'); ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="site-header"><nav class="nav">
        <a href="index.php" class="nav-logo"><?php echo e($profile['full_name'] ?? 'Ajie C. Javier'); ?></a>
        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation menu" aria-expanded="false"><span></span><span></span><span></span></button>
        <ul class="nav-links" id="navLinks">
            <li><a href="index.php">Home</a></li><li><a href="about.php" class="active">About</a></li><li><a href="skills.php">Skills</a></li><li><a href="projects.php">Projects</a></li><li><a href="resume.php">Resume</a></li><li><a href="contact.php">Contact</a></li>
        </ul><div class="nav-right"><a href="admin/login.php" class="theme-toggle">Admin</a></div>
    </nav></header>
    <main class="container">
        <section class="page-intro"><p class="eyebrow">About</p><h1>Who I am</h1></section>
        <section class="about-grid">
            <article class="about-block"><h2>Profile</h2><p><?php echo nl2br(e($profile['biography'] ?? 'I am an Information Technology student focused on building modern, practical web experiences.')); ?></p></article>
            <article class="about-block"><h2>Headline</h2><p><?php echo e($profile['headline'] ?? 'Information Technology Student'); ?></p></article>
            <article class="about-block"><h2>Location</h2><p><?php echo e($profile['location'] ?? 'Philippines'); ?></p></article>
            <article class="about-block"><h2>Contact</h2><p><?php echo e($profile['email'] ?? ''); ?><br><?php echo e($profile['phone'] ?? ''); ?></p></article>
        </section>
    </main>
    <footer class="site-footer"><div class="container footer-inner"><p>&copy; <?php echo date('Y'); ?> <?php echo e($profile['full_name'] ?? 'Ajie C. Javier'); ?>.</p><div class="footer-socials">
        <?php if (!empty($profile['github_url'])): ?><a href="<?php echo e($profile['github_url']); ?>" target="_blank" rel="noopener">GitHub</a><?php endif; ?>
        <?php if (!empty($profile['email'])): ?><a href="mailto:<?php echo e($profile['email']); ?>">Email</a><?php endif; ?>
    </div></div></footer>
    <button class="scroll-top-btn" id="scrollTopBtn" aria-label="Scroll to top">↑</button><script src="js/script.js"></script>
</body>
</html>
