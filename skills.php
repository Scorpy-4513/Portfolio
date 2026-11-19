<?php require __DIR__ . '/includes/header.php'; ?>
<main class="container">
<section class="page-intro">
<p class="eyebrow">Skills</p>
<h1>What I work with</h1>
</section>
<section class="skills-grid"><?php foreach ($db->query('SELECT * FROM skills ORDER BY id') as $row): ?><div class="skill-card" tabindex="0">
<h2><?= e($row['name']) ?></h2>
<p class="skill-category"><?= e($row['category']) ?></p>
<div class="skill-bar">
<span data-level="<?= (int) $row['level'] ?>">
</span>
</div>
<div class="skill-info">
<strong><?= (int) $row['level'] ?>%</strong>
<p><?= e($row['description']) ?></p>
</div>
</div><?php endforeach; ?></section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
