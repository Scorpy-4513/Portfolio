<?php
require_once __DIR__ . '/bootstrap.php';
$settings = $db->query('SELECT * FROM settings WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
if (!$settings) { http_response_code(503); exit('The website is not configured yet.'); }
?><!DOCTYPE html>
<html lang="en" data-default-theme="<?= e($settings['appearance']) ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($settings['site_title']) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&amp;family=Inter:wght@400;500;600&amp;display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="site-header">
<nav class="nav">
<a href="index.php" class="nav-logo"><?php if ($settings['logo_image'] && image_url($settings['logo_image'])): ?><img class="site-logo" src="<?= e(image_url($settings['logo_image'])) ?>" alt=""><?php endif; ?><?= e($settings['name']) ?></a>
<button class="nav-toggle" id="navToggle" aria-label="Toggle navigation menu" aria-expanded="false">
<span>
</span>
<span>
</span>
<span>
</span>
</button>
<ul class="nav-links" id="navLinks">
<li>
<a href="index.php">Home</a>
</li>
<li>
<a href="about.php">About</a>
</li>
<li>
<a href="skills.php">Skills</a>
</li>
<li>
<a href="projects.php">Projects</a>
</li>
<li>
<a href="resume.php">Resume</a>
</li>
<li>
<a href="contact.php">Contact</a>
</li>
</ul>
<div class="nav-right">
<button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode">Light|Dark</button>
</div>
</nav>
</header>
