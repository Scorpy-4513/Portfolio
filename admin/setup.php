<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = db_connect();
$profile = fetch_profile($pdo);
$settings = fetch_settings($pdo);
$successMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $successMessage = 'Security check failed. Please try again.';
    } else {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $subject = trim((string) ($_POST['subject'] ?? ''));
        $message = trim((string) ($_POST['message'] ?? ''));

        if ($name === '' || $email === '' || $subject === '' || $message === '' || !is_valid_email($email)) {
            $successMessage = 'Please fill in all contact fields with a valid email address.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO contact_messages (sender_name, sender_email, subject, message) VALUES (:name, :email, :subject, :message)');
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':subject' => $subject,
                ':message' => $message,
            ]);
            $successMessage = 'Your message has been sent successfully.';
        }
    }
}

$csrf = csrf_token();
$pdo = null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact | <?php echo e($profile['full_name'] ?? 'Ajie C. Javier'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="site-header">
        <nav class="nav">
            <a href="index.php" class="nav-logo"><?php echo e($profile['full_name'] ?? 'Ajie C. Javier'); ?></a>
            <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation menu" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
            <ul class="nav-links" id="navLinks">
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="skills.php">Skills</a></li>
                <li><a href="projects.php">Projects</a></li>
                <li><a href="resume.php">Resume</a></li>
                <li><a href="contact.php" class="active">Contact</a></li>
            </ul>
            <div class="nav-right">
                <a href="admin/login.php" class="theme-toggle" style="display:inline-block; text-align:center;">Admin</a>
            </div>
        </nav>
    </header>

    <main class="container">
        <section class="page-intro">
            <p class="eyebrow">Contact</p>
            <h1>Let's connect</h1>
        </section>

        <section class="contact-layout">
            <div class="contact-info">
                <p>Feel free to send a message if you want to discuss projects, internships, collaborations, or anything related to web development.</p>
                <ul class="contact-list">
                    <?php if (!empty($profile['email'])): ?><li><strong>Email:</strong> <a href="mailto:<?php echo e($profile['email']); ?>"><?php echo e($profile['email']); ?></a></li><?php endif; ?>
                    <?php if (!empty($profile['github_url'])): ?><li><strong>GitHub:</strong> <a href="<?php echo e($profile['github_url']); ?>" target="_blank" rel="noopener"><?php echo e($profile['github_url']); ?></a></li><?php endif; ?>
                    <?php if (!empty($profile['facebook_url'])): ?><li><strong>Facebook:</strong> <a href="<?php echo e($profile['facebook_url']); ?>" target="_blank" rel="noopener"><?php echo e($profile['facebook_url']); ?></a></li><?php endif; ?>
                </ul>
            </div>

            <form class="contact-form" method="post" action="contact.php">
                <input type="hidden" name="csrf_token" value="<?php echo e($csrf); ?>">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input id="name" name="name" type="text" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" required>
                </div>
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input id="subject" name="subject" type="text" required>
                </div>
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" rows="5" required></textarea>
                </div>
                <?php if ($successMessage): ?>
                    <p class="form-success"><?php echo e($successMessage); ?></p>
                <?php endif; ?>
                <button type="submit" class="btn">Send Message</button>
            </form>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container footer-inner">
            <p>&copy; <?php echo date('Y'); ?> <?php echo e($profile['full_name'] ?? 'Ajie C. Javier'); ?>. Built with HTML, CSS &amp; JavaScript.</p>
            <div class="footer-socials">
                <?php if (!empty($profile['github_url'])): ?><a href="<?php echo e($profile['github_url']); ?>" target="_blank" rel="noopener">GitHub</a><?php endif; ?>
                <?php if (!empty($profile['facebook_url'])): ?><a href="<?php echo e($profile['facebook_url']); ?>" target="_blank" rel="noopener">Facebook</a><?php endif; ?>
                <?php if (!empty($profile['instagram_url'])): ?><a href="<?php echo e($profile['instagram_url']); ?>" target="_blank" rel="noopener">Instagram</a><?php endif; ?>
                <?php if (!empty($profile['email'])): ?><a href="mailto:<?php echo e($profile['email']); ?>">Email</a><?php endif; ?>
            </div>
        </div>
    </footer>

    <button class="scroll-top-btn" id="scrollTopBtn" aria-label="Scroll to top">↑</button>
    <script src="js/script.js"></script>
</body>
</html>
