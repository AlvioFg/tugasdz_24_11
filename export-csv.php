<?php
require_once 'auth.php';

$karyawan_id    = isset($_GET['karyawan_id']) ? (int)$_GET['karyawan_id'] : 0;
$tanggal_dari   = $_GET['tanggal_dari'] ?? '';
$tanggal_sampai = $_GET['tanggal_sampai'] ?? '';
$status_filter  = $_GET['status'] ?? '';

$where  = [];
$params = [];
$types  = '';

if ($karyawan_id > 0) {
    $where[]  = "a.karyawan_id = ?";
    $params[] = $karyawan_id;
    $types   .= 'i';
}
if ($tanggal_dari !== '') {
    $where[]  = "a.tanggal >= ?";
    $params[] = $tanggal_dari;
    $types   .= 's';
}
if ($tanggal_sampai !== '') {
    $where[]  = "a.tanggal <= ?";
    $params[] = $tanggal_sampai;
    $types   .= 's';
}
if (in_array($status_filter, ['hadir', 'izin', 'sakit', 'alpha'], true)) {
    $where[]  = "a.status = ?";
    $params[] = $status_filter;
    $types   .= 's';
}

$sql = "SELECT a.tanggal, k.nama, k.jabatan, k.departemen, a.jam_masuk, a.jam_keluar, a.status, a.keterangan
        FROM absensi a
        JOIN karyawan k ON a.karyawan_id = k.id";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY a.tanggal DESC, k.nama ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$filename = 'laporan_absensi_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

fputcsv($output, ['No', 'Tanggal', 'Nama Karyawan', 'Jabatan', 'Departemen', 'Jam Masuk', 'Jam Keluar', 'Status', 'Keterangan']);

$no = 1;
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $no++,
        $row['tanggal'],
        $row['nama'],
        $row['jabatan'],
        $row['departemen'],
        $row['jam_masuk'] ?? '-',
        $row['jam_keluar'] ?? '-',
        strtoupper($row['status']),
        $row['keterangan'] ?? '-',
    ]);
}

fclose($output);
$stmt->close();
exit;