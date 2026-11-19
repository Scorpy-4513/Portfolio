<?php
require __DIR__ . '/includes/bootstrap.php';
$error = '';
$success = !empty($_SESSION['message_sent']);
unset($_SESSION['message_sent']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $sender = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
    if (!empty($_POST['website'])) {
        $_SESSION['message_sent'] = true;
        redirect('contact.php');
    }
    if (mb_strlen($name) < 2 || mb_strlen($name) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190 || mb_strlen($subject) < 2 || mb_strlen($subject) > 200 || mb_strlen($message) < 10 || mb_strlen($message) > 5000) {
        $error = 'Please enter a name, valid email, subject, and a message of at least 10 characters.';
    } else {
        $stmt = $db->prepare('SELECT COUNT(*) FROM messages WHERE sender_key = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)');
        $stmt->execute([$sender]);
        if ($stmt->fetchColumn() > 0) $error = 'Please wait a minute before sending another message.';
        else {
            $stmt = $db->prepare('INSERT INTO messages (name, email, subject, message, sender_key) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$name, $email, $subject, $message, $sender]);
            $_SESSION['message_sent'] = true;
            redirect('contact.php');
        }
    }
}
require __DIR__ . '/includes/header.php';
?><main class="container contact-page">
<section class="page-intro">
<p class="eyebrow">Contact</p>
<h1>Get in touch</h1>
</section>
<div class="contact-layout">
<div class="contact-info">
<p>Send me a message or connect with me through the links below.</p>
<ul class="contact-list">
<li>
<strong>Email:</strong> <a href="mailto:<?= e($settings['email']) ?>"><?= e($settings['email']) ?></a>
</li><?php foreach (['github' => 'GitHub', 'facebook' => 'Facebook', 'instagram' => 'Instagram'] as $field => $label): ?><?php if ($settings[$field]): ?><li>
<strong><?= e($label) ?>:</strong> <a href="<?= e($settings[$field]) ?>" target="_blank" rel="noopener noreferrer"><?= e($label) ?></a>
</li><?php endif; ?><?php endforeach; ?></ul>
</div>
<form method="post" class="contact-form"><?= csrf_field() ?><?php if ($error): ?><p class="error-message" role="alert"><?= e($error) ?></p><?php endif; ?><?php if ($success): ?><p class="form-success" role="status">Thanks! Your message has been sent.</p><?php endif; ?><div class="form-group">
<label for="name">Name</label>
<input id="name" name="name" value="<?= e($_POST['name'] ?? '') ?>" required minlength="2" maxlength="150">
</div>
<div class="form-group">
<label for="email">Email</label>
<input type="email" id="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required maxlength="190">
</div>
<div class="form-group">
<label for="subject">Subject</label>
<input id="subject" name="subject" value="<?= e($_POST['subject'] ?? '') ?>" required minlength="2" maxlength="200">
</div>
<div class="form-group">
<label for="message">Message</label>
<textarea id="message" name="message" rows="5" required minlength="10" maxlength="5000"><?= e($_POST['message'] ?? '') ?></textarea>
</div>
<div class="honeypot" aria-hidden="true">
<label for="website">Website</label>
<input id="website" name="website" tabindex="-1" autocomplete="off">
</div>
<button type="submit" class="btn">Send Message</button>
</form>
</div>
</main><?php require __DIR__ . '/includes/footer.php'; ?>
