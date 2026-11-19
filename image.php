<?php
header('X-Content-Type-Options: nosniff');
$file = $_GET['file'] ?? '';
$dir = getenv('UPLOAD_DIR');
if (!$dir || !preg_match('/^[a-f0-9]{32}\.jpg$/', $file) || !is_file(rtrim($dir, '/') . '/' . $file)) { http_response_code(404); exit; }
header('Content-Type: image/jpeg');
header('Cache-Control: public, max-age=86400');
readfile(rtrim($dir, '/') . '/' . $file);
