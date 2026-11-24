<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';$user = 'root';
$pass = '';$db   = 'absensi_db';

$conn = new mysqli($host,$user, $pass,$db);
if ($conn->connect_error) {
    die('Koneksi gagal: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

if (empty($_SESSION['csrf_token'])) {$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function e(?string $str): string {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function verify_csrf(string $token): void {
    if (!hash_equals($_SESSION['csrf_token'] ?? '',$token)) {
        http_response_code(403);
        die('Permintaan tidak valid (CSRF Token mismatch).');
    }
}