<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (preg_match('#^/(includes|database|scripts)(/|$)|/\.#', $path)) {
    http_response_code(403);
    exit;
}
$pages = ['index', 'about', 'skills', 'projects', 'resume', 'contact'];
$name = basename($path, '.html');
if (preg_match('/^\/(index|about|skills|projects|resume|contact)\.html$/', $path) && in_array($name, $pages, true)) {
    require __DIR__ . '/' . $name . '.php';
    return true;
}
return false;
