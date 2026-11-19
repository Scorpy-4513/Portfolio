<?php require __DIR__ . '/includes/header.php'; ?>
<main class="container">
<section class="page-intro">
<p class="eyebrow">Resume</p>
<h1>Education &amp; experience</h1>
</section><?php foreach (['education' => 'Education', 'experience' => 'Experience', 'achievements' => 'Training & Certifications'] as $table => $heading): ?><section class="resume-section">
<h2 class="resume-heading"><?= e($heading) ?></h2>
<div class="timeline"><?php foreach ($db->query("SELECT * FROM $table ORDER BY id DESC") as $row): ?><div class="timeline-item">
<span class="timeline-date"><?= e($row['period']) ?></span>
<h3><?= e($row['title']) ?></h3>
<p><?= e($row['organization']) ?></p>
<p><?= nl2br(e($row['description'])) ?></p><?php if (!empty($row['image']) && image_url($row['image'])): ?><img class="achievement-image" src="<?= e(image_url($row['image'])) ?>" alt="<?= e($row['title']) ?>"><?php endif; ?></div><?php endforeach; ?></div>
</section><?php endforeach; ?></main>
<?php require __DIR__ . '/includes/footer.php'; ?>
