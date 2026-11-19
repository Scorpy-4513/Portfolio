<?php
ini_set('display_errors', '0');
error_reporting(E_ALL);
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: DENY');
$secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(['httponly' => true, 'secure' => $secure, 'samesite' => 'Lax']);
    ini_set('session.use_strict_mode', '1');
    if (!session_start()) {
        http_response_code(503);
        exit('The website is temporarily unavailable. Please try again later.');
    }
}
try {
    $db = new PDO('mysql:host=' . (getenv('DB_HOST') ?: '127.0.0.1') . ';port=' . (getenv('DB_PORT') ?: '3306') . ';dbname=' . (getenv('DB_NAME') ?: 'portfolio') . ';charset=utf8mb4', getenv('DB_USER') ?: '', getenv('DB_PASSWORD') ?: '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false]);
} catch (PDOException $error) {
    http_response_code(503);
    exit('The website is temporarily unavailable. Please try again later.');
}
function e($value) { return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function check_csrf() {
    if (!isset($_POST['csrf']) || !hash_equals(token(), (string) $_POST['csrf'])) {
        http_response_code(403);
        exit('Your form expired. Please refresh the page and try again.');
    }
}
function csrf_field() { return '<input type="hidden" name="csrf" value="' . e(token()) . '">'; }
function redirect($url) { header('Location: ' . $url); exit; }
function require_admin() {
    if (empty($_SESSION['admin_id']) || empty($_SESSION['last_active']) || time() - $_SESSION['last_active'] > 1800) {
        unset($_SESSION['admin_id'], $_SESSION['last_active']);
        redirect('login.php');
    }
    $_SESSION['last_active'] = time();
}
function require_https() {
    global $secure;
    if (!$secure && getenv('APP_ALLOW_HTTP') !== '1') {
        http_response_code(403);
        exit('HTTPS is required for administration.');
    }
}
function image_url($image) {
    if (preg_match('/^images\/[A-Za-z0-9_.-]+$/', $image)) return $image;
    if (preg_match('/^[a-f0-9]{32}\.jpg$/', $image)) return 'image.php?file=' . rawurlencode($image);
    return '';
}
function upload_image($field) {
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return '';
    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 2 * 1024 * 1024 || $file['size'] < 1 || !is_uploaded_file($file['tmp_name'])) throw new RuntimeException('Choose an image smaller than 2 MB.');
    $info = @getimagesize($file['tmp_name']);
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!$info || !in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true) || $info['mime'] !== $mime || $info[0] > 5000 || $info[1] > 5000 || $info[0] * $info[1] > 8000000) throw new RuntimeException('Use a valid JPEG, PNG, or WebP image up to 5000 pixels wide or high.');
    $source = match ($mime) { 'image/jpeg' => @imagecreatefromjpeg($file['tmp_name']), 'image/png' => @imagecreatefrompng($file['tmp_name']), 'image/webp' => @imagecreatefromwebp($file['tmp_name']) };
    if (!$source) throw new RuntimeException('The image could not be read.');
    $dir = getenv('UPLOAD_DIR');
    if (!$dir || !is_dir($dir) || !is_writable($dir)) throw new RuntimeException('Image storage is not configured.');
    $storage = realpath($dir);
    $webRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    if (!$storage || ($webRoot && ($storage === $webRoot || str_starts_with($storage, $webRoot . DIRECTORY_SEPARATOR)))) throw new RuntimeException('Image storage must be outside the web root.');
    $name = bin2hex(random_bytes(16)) . '.jpg';
    $background = imagecreatetruecolor($info[0], $info[1]);
    $white = imagecolorallocate($background, 255, 255, 255);
    imagefill($background, 0, 0, $white);
    imagecopy($background, $source, 0, 0, 0, 0, $info[0], $info[1]);
    $saved = imagejpeg($background, rtrim($dir, '/') . '/' . $name, 85);
    imagedestroy($source); imagedestroy($background);
    if (!$saved) throw new RuntimeException('The image could not be saved.');
    chmod(rtrim($dir, '/') . '/' . $name, 0640);
    return $name;
}

function remove_unused_image($name) {
    global $db;
    if (!preg_match('/^[a-f0-9]{32}\.jpg$/', $name) || !getenv('UPLOAD_DIR')) return;
    $stmt = $db->prepare('SELECT (SELECT COUNT(*) FROM settings WHERE profile_image = ? OR logo_image = ?) + (SELECT COUNT(*) FROM projects WHERE image = ?) + (SELECT COUNT(*) FROM achievements WHERE image = ?)');
    $stmt->execute([$name, $name, $name, $name]);
    if ((int) $stmt->fetchColumn() === 0) {
        $file = rtrim(getenv('UPLOAD_DIR'), '/') . '/' . $name;
        if (is_file($file)) unlink($file);
    }
}
