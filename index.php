<?php
require_once 'auth.php';

$hari_ini = date('Y-m-d');

$stat_sql = "SELECT
    (SELECT COUNT(*) FROM karyawan) AS total_karyawan,
    SUM(CASE WHEN tanggal = ? AND status = 'hadir' THEN 1 ELSE 0 END) AS hadir_hari_ini,
    SUM(CASE WHEN tanggal = ? AND status IN ('izin', 'sakit') THEN 1 ELSE 0 END) AS izin_sakit_hari_ini,
    SUM(CASE WHEN tanggal = ? AND status = 'alpha' THEN 1 ELSE 0 END) AS alpha_hari_ini
    FROM absensi";
$stmt_stat = $conn->prepare($stat_sql);
$stmt_stat->bind_param('sss', $hari_ini, $hari_ini, $hari_ini);
$stmt_stat->execute();
$stats = $stmt_stat->get_result()->fetch_assoc();
$stmt_stat->close();

$sql = "SELECT k.id, k.nama, k.jabatan, k.departemen,
        SUM(CASE WHEN a.status = 'hadir' THEN 1 ELSE 0 END) AS total_hadir,
        SUM(CASE WHEN a.status = 'izin' THEN 1 ELSE 0 END) AS total_izin,
        SUM(CASE WHEN a.status = 'sakit' THEN 1 ELSE 0 END) AS total_sakit,
        SUM(CASE WHEN a.status = 'alpha' THEN 1 ELSE 0 END) AS total_alpha,
        COUNT(a.id) AS total_absensi
        FROM karyawan k
        LEFT JOIN absensi a ON k.id = a.karyawan_id
        GROUP BY k.id
        ORDER BY k.nama ASC";
$result = $conn->query($sql);
$pesan  = $_GET['pesan'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Karyawan - Sistem Absensi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<h1>Dashboard & Daftar Karyawan</h1>

<div class="stats-grid">
    <div class="stat-card">
        <span class="stat-label">Total Karyawan</span>
        <span class="stat-value"><?= (int)$stats['total_karyawan'] ?></span>
    </div>
    <div class="stat-card stat-green">
        <span class="stat-label">Hadir Hari Ini (<?= date('d/m/Y') ?>)</span>
        <span class="stat-value"><?= (int)$stats['hadir_hari_ini'] ?></span>
    </div>
    <div class="stat-card stat-amber">
        <span class="stat-label">Izin / Sakit Hari Ini</span>
        <span class="stat-value"><?= (int)$stats['izin_sakit_hari_ini'] ?></span>
    </div>
    <div class="stat-card stat-red">
        <span class="stat-label">Alpha Hari Ini</span>
        <span class="stat-value"><?= (int)$stats['alpha_hari_ini'] ?></span>
    </div>
</div>

<?php if ($pesan === 'tambah'): ?>
    <div class="msg msg-success">Karyawan berhasil ditambahkan.</div>
<?php elseif ($pesan === 'edit'): ?>
    <div class="msg msg-success">Data karyawan diperbarui.</div>
<?php elseif ($pesan === 'hapus'): ?>
    <div class="msg msg-success">Karyawan dihapus.</div>
<?php elseif ($pesan === 'masih_ada_absensi'): ?>
    <div class="msg msg-error">Gagal hapus: karyawan masih memiliki riwayat data absensi.</div>
<?php endif; ?>

<div class="toolbar">
    <a href="karyawan-form.php" class="btn">+ Tambah Karyawan</a>
    <a href="absensi-form.php" class="btn btn-secondary">+ Catat Absensi Cepat</a>
</div>

<table>
    <tr>
        <th>No</th>
        <th>Nama</th>
        <th>Jabatan</th>
        <th>Departemen</th>
        <th>Hadir</th>
        <th>Izin</th>
        <th>Sakit</th>
        <th>Alpha</th>
        <th>Aksi</th>
    </tr>
    <?php if ($result->num_rows === 0): ?>
        <tr>
            <td colspan="9" class="text-center">Belum ada data karyawan.</td>
        </tr>
    <?php endif; ?>
    <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><strong><?= e($row['nama']) ?></strong></td>
            <td><?= e($row['jabatan']) ?></td>
            <td><?= e($row['departemen']) ?></td>
            <td><span class="badge badge-hadir"><?= (int)$row['total_hadir'] ?></span></td>
            <td><span class="badge badge-izin"><?= (int)$row['total_izin'] ?></span></td>
            <td><span class="badge badge-sakit"><?= (int)$row['total_sakit'] ?></span></td>
            <td><span class="badge badge-alpha"><?= (int)$row['total_alpha'] ?></span></td>
            <td class="actions">
                <a href="absensi.php?karyawan_id=<?= (int)$row['id'] ?>">Absensi</a>
                <a href="karyawan-form.php?id=<?= (int)$row['id'] ?>">Edit</a>
                <form action="karyawan-proses.php" method="post" class="inline-form"
                      onsubmit="return confirm('Hapus karyawan <?= e($row['nama']) ?>?')">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="aksi" value="hapus">
                    <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                    <button type="submit" class="link-danger">Hapus</button>
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
</table>
</body>
</html>