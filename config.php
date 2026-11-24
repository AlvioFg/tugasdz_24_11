<?php
$host = 'sql201.infinityfree.com';
$user = 'if0_43520271';
$pass = 'frAda2ax3PaH0U7';
$db   = 'if0_43520271_absensi_db';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Koneksi gagal: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');