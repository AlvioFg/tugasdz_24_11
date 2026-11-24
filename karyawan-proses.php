<?php
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

verify_csrf($_POST['csrf_token'] ?? '');

$aksi = $_POST['aksi'] ?? 'simpan';
$id   = (int)($_POST['id'] ?? 0);

if ($aksi === 'hapus') {
    $stmt = $conn->prepare("SELECT COUNT(*) AS jml FROM absensi WHERE karyawan_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $cek = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ((int)$cek['jml'] > 0) {
        header('Location: index.php?pesan=masih_ada_absensi');
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM karyawan WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();

    header('Location: index.php?pesan=hapus');
    exit;
}

$nama       = trim($_POST['nama'] ?? '');
$jabatan    = trim($_POST['jabatan'] ?? '');
$departemen = trim($_POST['departemen'] ?? '');

if ($nama === '' || $jabatan === '' || $departemen === '') {
    $redir = 'karyawan-form.php?pesan=wajib_lengkap' . ($id ? '&id=' . $id : '');
    header('Location: ' . $redir);
    exit;
}

if ($id > 0) {
    $stmt = $conn->prepare(
        "UPDATE karyawan SET nama = ?, jabatan = ?, departemen = ? WHERE id = ?"
    );
    $stmt->bind_param('sssi', $nama, $jabatan, $departemen, $id);
    $stmt->execute();
    $stmt->close();
    header('Location: index.php?pesan=edit');
} else {
    $stmt = $conn->prepare(
        "INSERT INTO karyawan (nama, jabatan, departemen) VALUES (?, ?, ?)"
    );
    $stmt->bind_param('sss', $nama, $jabatan, $departemen);
    $stmt->execute();
    $stmt->close();
    header('Location: index.php?pesan=tambah');
}
exit;