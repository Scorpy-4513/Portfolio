<?php
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
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
    return is_string($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
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

    $message = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);

    return $message;
}

function parse_db_error_message(int $code): string
{
    return match ($code) {
        23000 => 'A duplicate record already exists.',
        default => 'The database request could not be completed.',
    };
}

function is_valid_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function safe_int(string $value): int
{
    return max(0, (int) $value);
}
