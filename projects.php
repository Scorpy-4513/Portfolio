<?php require __DIR__ . '/includes/header.php'; ?>
<main class="container">
<section class="page-intro">
<p class="eyebrow">Projects</p>
<h1>Things I've built</h1>
</section>
<section class="projects-list"><?php foreach ($db->query('SELECT * FROM projects ORDER BY id DESC') as $row): ?><article class="project-card"><?php if ($row['image'] && image_url($row['image'])): ?><img src="<?= e(image_url($row['image'])) ?>" alt="<?= e($row['title']) ?>"><?php endif; ?><div class="project-body">
<h2><?= e($row['title']) ?></h2>
<p><?= nl2br(e($row['description'])) ?></p>
<ul class="tech-tags"><?php foreach (explode(',', $row['technologies']) as $tag): ?><?php if (trim($tag)): ?><li><?= e(trim($tag)) ?></li><?php endif; ?><?php endforeach; ?></ul>
</div>
</article><?php endforeach; ?></section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
