<?php
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: absensi.php');
    exit;
}

verify_csrf($_POST['csrf_token'] ?? '');

$aksi = $_POST['aksi'] ?? 'simpan';
$id   = (int)($_POST['id'] ?? 0);

if ($aksi === 'hapus') {
    $stmt = $conn->prepare("SELECT karyawan_id FROM absensi WHERE id = ?");
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

$karyawan_id = (int)($_POST['karyawan_id'] ?? 0);
$tanggal     = trim($_POST['tanggal'] ?? '');
$jam_masuk   = trim($_POST['jam_masuk'] ?? '');
$jam_keluar  = trim($_POST['jam_keluar'] ?? '');
$status      = $_POST['status'] ?? 'hadir';
$keterangan  = trim($_POST['keterangan'] ?? '');

if (!in_array($status, ['hadir', 'izin', 'sakit', 'alpha'], true)) {
    $status = 'hadir';
}

if ($karyawan_id <= 0 || $tanggal === '') {
    $redir = 'absensi-form.php?pesan=lengkapi' . ($id ? '&id=' . $id : '');
    header('Location: ' . $redir);
    exit;
}

if ($status !== 'hadir') {
    $jam_masuk  = null;
    $jam_keluar = null;
} else {
    $jam_masuk  = $jam_masuk !== '' ? $jam_masuk : null;
    $jam_keluar = $jam_keluar !== '' ? $jam_keluar : null;

    if ($jam_masuk && $jam_keluar && $jam_keluar < $jam_masuk) {
        $redir = 'absensi-form.php?pesan=jam_invalid' . ($id ? '&id=' . $id : '');
        header('Location: ' . $redir);
        exit;
    }
}

$keterangan = $keterangan !== '' ? $keterangan : null;

$stmt = $conn->prepare(
    "SELECT id FROM absensi WHERE karyawan_id = ? AND tanggal = ? AND id <> ?"
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

if ($id > 0) {
    $stmt = $conn->prepare(
        "UPDATE absensi
         SET karyawan_id = ?, tanggal = ?, jam_masuk = ?, jam_keluar = ?, status = ?, keterangan = ?
         WHERE id = ?"
    );
    $stmt->bind_param('isssssi', $karyawan_id, $tanggal, $jam_masuk, $jam_keluar, $status, $keterangan, $id);
    $stmt->execute();
    $stmt->close();
    header('Location: absensi.php?karyawan_id=' . $karyawan_id . '&pesan=edit');
} else {
    $stmt = $conn->prepare(
        "INSERT INTO absensi (karyawan_id, tanggal, jam_masuk, jam_keluar, status, keterangan)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param('isssss', $karyawan_id, $tanggal, $jam_masuk, $jam_keluar, $status, $keterangan);
    $stmt->execute();
    $stmt->close();
    header('Location: absensi.php?karyawan_id=' . $karyawan_id . '&pesan=tambah');
}
exit;