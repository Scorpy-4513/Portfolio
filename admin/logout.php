<?php
require __DIR__ . '/../includes/bootstrap.php';
require_https();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
check_csrf();
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', ['expires' => time() - 3600, 'path' => $params['path'], 'domain' => $params['domain'], 'secure' => $params['secure'], 'httponly' => true, 'samesite' => 'Lax']);
}
session_destroy();
redirect('login.php');
