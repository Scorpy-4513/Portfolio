<footer class="site-footer">
<div class="container footer-inner">
<p>&copy; <?= date('Y') ?> <?= e($settings['name']) ?>.</p>
<div class="footer-socials"><?php foreach (['github' => 'GitHub', 'facebook' => 'Facebook', 'instagram' => 'Instagram'] as $field => $label): ?><?php if ($settings[$field]): ?><a href="<?= e($settings[$field]) ?>" target="_blank" rel="noopener noreferrer"><?= e($label) ?></a><?php endif; ?><?php endforeach; ?><a href="mailto:<?= e($settings['email']) ?>">Email</a>
</div>
</div>
</footer>
<button class="scroll-top-btn" id="scrollTopBtn" aria-label="Scroll to top">↑</button>
<script src="js/script.js">
</script>
</body>
</html>
