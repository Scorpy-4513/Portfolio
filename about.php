<?php require __DIR__ . '/includes/header.php'; ?>
<main class="container about-page">
<section class="page-intro">
<p class="eyebrow">About</p>
<h1>A bit of background</h1>
</section>
<section class="about-grid">
<div class="about-block">
<h2>Background</h2>
<p><?= nl2br(e($settings['biography'])) ?></p>
</div>
<div class="about-block">
<h2>Education</h2><?php foreach ($db->query('SELECT * FROM education ORDER BY id DESC') as $row): ?><p><?= e($row['title']) ?> · <?= e($row['organization']) ?></p><?php endforeach; ?></div>
<div class="about-block">
<h2>Goals</h2>
<p><?= nl2br(e($settings['goals'])) ?></p>
</div>
<div class="about-block">
<h2>Interests</h2>
<p><?= nl2br(e($settings['interests'])) ?></p>
</div>
</section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
