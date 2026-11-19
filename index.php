<?php require __DIR__ . '/includes/header.php'; ?>
<main class="container">
<section class="hero">
<div class="hero-text">
<p class="eyebrow">Hi, I'm</p>
<h1><?= e($settings['name']) ?></h1>
<p><?= nl2br(e($settings['introduction'])) ?></p>
<a href="projects.php" class="btn">See my work</a>
</div><?php if ($settings['profile_image'] && image_url($settings['profile_image'])): ?><div class="hero-image">
<img src="<?= e(image_url($settings['profile_image'])) ?>" alt="Photo of <?= e($settings['name']) ?>">
</div><?php endif; ?></section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
