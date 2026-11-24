<?php
require_once 'auth.php';

$karyawan_id    = isset($_GET['karyawan_id']) ? (int)$_GET['karyawan_id'] : 0;
$tanggal_dari   = $_GET['tanggal_dari'] ?? '';
$tanggal_sampai = $_GET['tanggal_sampai'] ?? '';
$status_filter  = $_GET['status'] ?? '';

$karyawan_list = [];
$resk = $conn->query("SELECT id, nama FROM karyawan ORDER BY nama ASC");
while ($r = $resk->fetch_assoc()) $karyawan_list[] = $r;

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

$sql = "SELECT a.*, k.nama, k.departemen
        FROM absensi a
        JOIN karyawan k ON a.karyawan_id = k.id";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY a.tanggal DESC, a.id DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$data_absensi = [];
while ($row = $result->fetch_assoc()) $data_absensi[] = $row;
$stmt->close();

$pesan = $_GET['pesan'] ?? '';
$query_string = http_build_query([
        'karyawan_id'    => $karyawan_id,
        'tanggal_dari'   => $tanggal_dari,
        'tanggal_sampai' => $tanggal_sampai,
        'status'         => $status_filter,
]);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Absensi - Sistem Absensi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<h1>Data Absensi Karyawan</h1>

<?php if ($pesan === 'tambah'): ?>
    <div class="msg msg-success">Absensi berhasil dicatat.</div>
<?php elseif ($pesan === 'edit'): ?>
    <div class="msg msg-success">Data absensi diperbarui.</div>
<?php elseif ($pesan === 'hapus'): ?>
    <div class="msg msg-success">Data absensi dihapus.</div>
<?php endif; ?>

<form method="get" action="absensi.php" class="filter-box">
    <div class="filter-grid">
        <div>
            <label>Karyawan</label>
            <select name="karyawan_id">
                <option value="0">-- Semua Karyawan --</option>
                <?php foreach ($karyawan_list as $k): ?>
                    <option value="<?= (int)$k['id'] ?>" <?= $k['id'] == $karyawan_id ? 'selected' : '' ?>>
                        <?= e($k['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>Dari Tanggal</label>
            <input type="date" name="tanggal_dari" value="<?= e($tanggal_dari) ?>">
        </div>
        <div>
            <label>Sampai Tanggal</label>
            <input type="date" name="tanggal_sampai" value="<?= e($tanggal_sampai) ?>">
        </div>
        <div>
            <label>Status</label>
            <select name="status">
                <option value="">-- Semua Status --</option>
                <?php foreach (['hadir', 'izin', 'sakit', 'alpha'] as $s): ?>
                    <option value="<?= $s ?>" <?= $status_filter === $s ? 'selected' : '' ?>>
                        <?= ucfirst($s) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="filter-actions">
        <button type="submit" class="btn">Terapkan Filter</button>
        <a href="absensi.php" class="btn btn-secondary">Reset</a>
    </div>
</form>

<div class="toolbar">
    <a href="absensi-form.php?karyawan_id=<?= $karyawan_id ?>" class="btn">+ Catat Absensi</a>
    <a href="export-csv.php?<?= e($query_string) ?>" class="btn btn-success">Ekspor CSV</a>
</div>

<table>
    <tr>
        <th>No</th>
        <th>Tanggal</th>
        <th>Nama</th>
        <th>Jam Masuk</th>
        <th>Jam Keluar</th>
        <th>Status</th>
        <th>Keterangan</th>
        <th>Aksi</th>
    </tr>
    <?php if (empty($data_absensi)): ?>
        <tr>
            <td colspan="8" class="text-center">Tidak ada data absensi yang sesuai filter.</td>
        </tr>
    <?php endif; ?>
    <?php $no = 1; foreach ($data_absensi as $row): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= e($row['tanggal']) ?></td>
            <td><strong><?= e($row['nama']) ?></strong></td>
            <td><?= e($row['jam_masuk'] ? substr($row['jam_masuk'], 0, 5) : '-') ?></td>
            <td><?= e($row['jam_keluar'] ? substr($row['jam_keluar'], 0, 5) : '-') ?></td>
            <td>
                <span class="badge badge-<?= e($row['status']) ?>">
                    <?= e($row['status']) ?>
                </span>
            </td>
            <td><?= e($row['keterangan'] ?: '-') ?></td>
            <td class="actions">
                <a href="absensi-form.php?id=<?= (int)$row['id'] ?>">Edit</a>
                <form action="absensi-proses.php" method="post" class="inline-form"
                      onsubmit="return confirm('Hapus catatan absensi ini?')">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="aksi" value="hapus">
                    <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                    <button type="submit" class="link-danger">Hapus</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
</body>
</html>