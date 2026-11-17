<?php
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path, true, 302);
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf_token(?string $token): bool
{
    return is_string($token) && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function set_flash(string $key, string $message): void
{
    $_SESSION['flash'][$key] = $message;
}

function get_flash(string $key): ?string
{
    if (!isset($_SESSION['flash'][$key])) {
        return null;
    }

    $message = (string) $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);

    return $message;
}

function is_valid_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function fetch_profile(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM profile WHERE id = 1 LIMIT 1');
    return $stmt->fetch() ?: [];
}

function fetch_skills(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM skills ORDER BY sort_order ASC, id ASC');
    return $stmt->fetchAll() ?: [];
}

function fetch_projects(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM projects WHERE is_published = 1 ORDER BY sort_order ASC, id ASC');
    $projects = $stmt->fetchAll() ?: [];

    foreach ($projects as &$project) {
        $techStmt = $pdo->prepare('SELECT technology FROM project_technologies WHERE project_id = :project_id ORDER BY sort_order ASC, technology ASC');
        $techStmt->execute([':project_id' => $project['id']]);
        $project['technologies'] = array_column($techStmt->fetchAll() ?: [], 'technology');
    }
    unset($project);

    return $projects;
}

function fetch_education(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM education ORDER BY sort_order ASC, end_date DESC, id DESC');
    return $stmt->fetchAll() ?: [];
}

function fetch_experiences(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM experiences ORDER BY sort_order ASC, start_date DESC, id DESC');
    return $stmt->fetchAll() ?: [];
}

function fetch_settings(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT setting_key, setting_value FROM settings');
    $settings = [];
    foreach ($stmt->fetchAll() ?: [] as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}
