<?php
require_once __DIR__ . '/db.php';

function is_logged_in(): bool
{
    return !empty($_SESSION['admin_id']) && !empty($_SESSION['admin_email']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('error', 'Please log in to access the administration dashboard.');
        redirect('login.php');
    }
}

function account_locked_for_identifier(PDO $pdo, string $identifier): ?string
{
    $hash = hash('sha256', strtolower(trim($identifier)));
    $stmt = $pdo->prepare('SELECT locked_until FROM login_attempts WHERE identifier_hash = :identifier_hash LIMIT 1');
    $stmt->execute([':identifier_hash' => $hash]);
    $row = $stmt->fetch();

    if (!$row) {
        return null;
    }

    if (!empty($row['locked_until']) && new DateTime($row['locked_until']) > new DateTime()) {
        return $row['locked_until'];
    }

    return null;
}

function login_admin(PDO $pdo, string $email, string $password): array
{
    $email = strtolower(trim($email));

    if (!is_valid_email($email)) {
        return ['success' => false, 'message' => 'Please provide a valid administrator email address.'];
    }

    $lockedUntil = account_locked_for_identifier($pdo, $email);
    if ($lockedUntil) {
        return ['success' => false, 'message' => 'This account is temporarily locked due to repeated failed login attempts. Please try again later.'];
    }

    $stmt = $pdo->prepare('SELECT * FROM admins WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        $hash = hash('sha256', $email);
        $attemptStmt = $pdo->prepare(
            'INSERT INTO login_attempts (identifier_hash, attempt_count, first_attempt_at, locked_until)
             VALUES (:hash, 1, NOW(), NULL)
             ON DUPLICATE KEY UPDATE
                attempt_count = attempt_count + 1,
                first_attempt_at = IF(first_attempt_at IS NULL, NOW(), first_attempt_at),
                locked_until = CASE
                    WHEN attempt_count + 1 >= 5 THEN DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                    ELSE NULL
                END'
        );
        $attemptStmt->execute([':hash' => $hash]);

        return ['success' => false, 'message' => 'Invalid email or password.'];
    }

    $pdo->prepare('DELETE FROM login_attempts WHERE identifier_hash = :hash')->execute([':hash' => hash('sha256', $email)]);

    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $admin['id'];
    $_SESSION['admin_email'] = $admin['email'];
    $_SESSION['admin_name'] = $admin['display_name'];

    $pdo->prepare('UPDATE admins SET last_login_at = NOW() WHERE id = :id')->execute([':id' => $admin['id']]);

    return ['success' => true, 'message' => 'Welcome back.'];
}

function logout_admin(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}

function current_admin_name(): string
{
    return $_SESSION['admin_name'] ?? 'Administrator';
}
