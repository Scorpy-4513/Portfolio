<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = db_connect();
$profile = fetch_profile($pdo);
$messageNotice = null;
$messageIsError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $messageNotice = 'Security check failed. Refresh the page and try again.';
        $messageIsError = true;
    } else {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $subject = trim((string) ($_POST['subject'] ?? ''));
        $body = trim((string) ($_POST['message'] ?? ''));

        if ($name === '' || strlen($name) > 120 || !is_valid_email($email) || strlen($email) > 254 || $subject === '' || strlen($subject) > 180 || $body === '') {
            $messageNotice = 'Please complete every field and provide a valid email address.';
            $messageIsError = true;
        } else {
            $stmt = $pdo->prepare('INSERT INTO contact_messages (sender_name, sender_email, subject, message) VALUES (:name, :email, :subject, :message)');
            $stmt->execute([':name' => $name, ':email' => $email, ':subject' => $subject, ':message' => $body]);
            $messageNotice = 'Your message has been sent successfully.';
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
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="site-header"><nav class="nav">
        <a href="index.php" class="nav-logo"><?php echo e($profile['full_name'] ?? 'Ajie C. Javier'); ?></a>
        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation menu" aria-expanded="false"><span></span><span></span><span></span></button>
        <ul class="nav-links" id="navLinks">
            <li><a href="index.php">Home</a></li><li><a href="about.php">About</a></li><li><a href="skills.php">Skills</a></li><li><a href="projects.php">Projects</a></li><li><a href="resume.php">Resume</a></li><li><a href="contact.php" class="active">Contact</a></li>
        </ul><div class="nav-right"><a href="admin/login.php" class="theme-toggle">Admin</a></div>
    </nav></header>
    <main class="container">
        <section class="page-intro"><p class="eyebrow">Contact</p><h1>Let's connect</h1></section>
        <section class="contact-layout">
            <div class="contact-info"><p>Feel free to send a message about projects, internships, collaborations, or web development.</p>
                <?php if (!empty($profile['email'])): ?><p><strong>Email:</strong> <a href="mailto:<?php echo e($profile['email']); ?>"><?php echo e($profile['email']); ?></a></p><?php endif; ?>
                <?php if (!empty($profile['github_url'])): ?><p><strong>GitHub:</strong> <a href="<?php echo e($profile['github_url']); ?>" target="_blank" rel="noopener"><?php echo e($profile['github_url']); ?></a></p><?php endif; ?>
            </div>
            <form class="contact-form" method="post" action="contact.php">
                <input type="hidden" name="csrf_token" value="<?php echo e($csrf); ?>">
                <div class="form-group"><label for="name">Name</label><input id="name" name="name" type="text" maxlength="120" required></div>
                <div class="form-group"><label for="email">Email</label><input id="email" name="email" type="email" maxlength="254" required></div>
                <div class="form-group"><label for="subject">Subject</label><input id="subject" name="subject" type="text" maxlength="180" required></div>
                <div class="form-group"><label for="message">Message</label><textarea id="message" name="message" rows="5" required></textarea></div>
                <?php if ($messageNotice !== null): ?><p class="<?php echo $messageIsError ? 'form-error' : 'form-success'; ?>"><?php echo e($messageNotice); ?></p><?php endif; ?>
                <button type="submit" class="btn">Send Message</button>
            </form>
        </section>
    </main>
    <footer class="site-footer"><div class="container footer-inner"><p>&copy; <?php echo date('Y'); ?> <?php echo e($profile['full_name'] ?? 'Ajie C. Javier'); ?>.</p><div class="footer-socials">
        <?php if (!empty($profile['email'])): ?><a href="mailto:<?php echo e($profile['email']); ?>">Email</a><?php endif; ?>
        <?php if (!empty($profile['github_url'])): ?><a href="<?php echo e($profile['github_url']); ?>" target="_blank" rel="noopener">GitHub</a><?php endif; ?>
    </div></div></footer>
    <button class="scroll-top-btn" id="scrollTopBtn" aria-label="Scroll to top">↑</button><script src="js/script.js"></script>
</body>
</html>
