<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../includes/bootstrap.php';
echo "Username: ";
$username = trim(fgets(STDIN));
echo "Password (at least 12 characters): ";
system('stty -echo');
$password = rtrim(fgets(STDIN), "\r\n");
system('stty echo');
echo "\n";
if (!preg_match('/^[A-Za-z0-9_.-]{3,100}$/', $username) || strlen($password) < 12 || strlen($password) > 72) exit("Invalid username or password length.\n");
$stmt = $db->prepare('INSERT INTO admins (username, password_hash) VALUES (?, ?)');
try { $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]); echo "Administrator created.\n"; }
catch (PDOException $error) { exit("Unable to create administrator. The username may already exist.\n"); }
