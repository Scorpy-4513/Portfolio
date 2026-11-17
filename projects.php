<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = db_connect();
$profile = fetch_profile($pdo);
$projects = fetch_projects($pdo);
$pdo = null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects | <?php echo e($profile['full_name'] ?? 'Ajie C. Javier'); ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="site-header"><nav class="nav">
        <a href="index.php" class="nav-logo"><?php echo e($profile['full_name'] ?? 'Ajie C. Javier'); ?></a>
        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation menu" aria-expanded="false"><span></span><span></span><span></span></button>
        <ul class="nav-links" id="navLinks">
            <li><a href="index.php">Home</a></li><li><a href="about.php">About</a></li><li><a href="skills.php">Skills</a></li><li><a href="projects.php" class="active">Projects</a></li><li><a href="resume.php">Resume</a></li><li><a href="contact.php">Contact</a></li>
        </ul><div class="nav-right"><a href="admin/login.php" class="theme-toggle">Admin</a></div>
    </nav></header>
    <main class="container">
        <section class="page-intro"><p class="eyebrow">Projects</p><h1>Things I've built</h1></section>
        <section class="projects-list">
            <?php foreach ($projects as $project): ?>
                <article class="project-card">
                    <?php if (!empty($project['image_path'])): ?><img src="<?php echo e($project['image_path']); ?>" alt="Screenshot of <?php echo e($project['title']); ?>"><?php endif; ?>
                    <div class="project-body"><h2><?php echo e($project['title']); ?></h2><p><?php echo nl2br(e($project['description'])); ?></p>
                        <ul class="tech-tags"><?php foreach ($project['technologies'] as $technology): ?><li><?php echo e($technology); ?></li><?php endforeach; ?></ul>
                        <?php if (!empty($project['project_url'])): ?><a href="<?php echo e($project['project_url']); ?>" target="_blank" rel="noopener">View project</a><?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    </main>
    <footer class="site-footer"><div class="container footer-inner"><p>&copy; <?php echo date('Y'); ?> <?php echo e($profile['full_name'] ?? 'Ajie C. Javier'); ?>.</p><div class="footer-socials">
        <?php if (!empty($profile['github_url'])): ?><a href="<?php echo e($profile['github_url']); ?>" target="_blank" rel="noopener">GitHub</a><?php endif; ?>
        <?php if (!empty($profile['email'])): ?><a href="mailto:<?php echo e($profile['email']); ?>">Email</a><?php endif; ?>
    </div></div></footer>
    <button class="scroll-top-btn" id="scrollTopBtn" aria-label="Scroll to top">↑</button><script src="js/script.js"></script>
</body>
</html>
