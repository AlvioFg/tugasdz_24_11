<?php
// SIMPAN / UPDATE / HAPUS ABSENSI
require 'config.php';

$aksi = $_POST['aksi'] ?? $_GET['aksi'] ?? 'simpan';
$id   = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

/* HAPUS */
if ($aksi === 'hapus') {
    $stmt = $conn->prepare(
        "SELECT karyawan_id FROM absensi WHERE id = ?"
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM absensi WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();

    $redir = 'absensi.php?pesan=hapus';
    if ($row) $redir .= '&karyawan_id=' . (int)$row['karyawan_id'];
    header('Location: ' . $redir);
    exit;
}

/* SIMPAN / UPDATE */
$karyawan_id = (int)($_POST['karyawan_id'] ?? 0);
$tanggal     = trim($_POST['tanggal'] ?? '');
$jam_masuk   = trim($_POST['jam_masuk'] ?? '');
$jam_keluar  = trim($_POST['jam_keluar'] ?? '');
$status      = $_POST['status'] ?? 'hadir';

if ($karyawan_id <= 0 || $tanggal === '') {
    $redir = 'absensi-form.php?pesan=lengkapi' . ($id ? '&id=' . $id : '');
    header('Location: ' . $redir);
    exit;
}

/* Cek duplikat (selain dirinya sendiri kalau sedang edit) */
$stmt = $conn->prepare(
    "SELECT id FROM absensi
     WHERE karyawan_id = ? AND tanggal = ? AND id <> ?"
);
$stmt->bind_param('isi', $karyawan_id, $tanggal, $id);
$stmt->execute();
$dup = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($dup) {
    $redir = 'absensi-form.php?pesan=duplikat' . ($id ? '&id=' . $id : '');
    header('Location: ' . $redir);
    exit;
}

$jam_masuk  = $jam_masuk !== '' ? $jam_masuk : null;
$jam_keluar = $jam_keluar !== '' ? $jam_keluar : null;

if ($id > 0) {
    $stmt = $conn->prepare(
        "UPDATE absensi
         SET karyawan_id = ?, tanggal = ?, jam_masuk = ?, jam_keluar = ?, status = ?
         WHERE id = ?"
    );
    $stmt->bind_param('issssi', $karyawan_id, $tanggal, $jam_masuk, $jam_keluar, $status, $id);
    $stmt->execute();
    $stmt->close();
    header('Location: absensi.php?karyawan_id=' . $karyawan_id . '&pesan=edit');
} else {
    $stmt = $conn->prepare(
        "INSERT INTO absensi (karyawan_id, tanggal, jam_masuk, jam_keluar, status)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param('issss', $karyawan_id, $tanggal, $jam_masuk, $jam_keluar, $status);
    $stmt->execute();
    $stmt->close();
    header('Location: absensi.php?karyawan_id=' . $karyawan_id . '&pesan=tambah');
}
exit;