<?php
require_once 'auth.php';

$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('m');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

$nama_bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$sql = "SELECT k.id, k.nama, k.jabatan, k.departemen,
        SUM(CASE WHEN a.status = 'hadir' THEN 1 ELSE 0 END) AS hadir,
        SUM(CASE WHEN a.status = 'izin' THEN 1 ELSE 0 END) AS izin,
        SUM(CASE WHEN a.status = 'sakit' THEN 1 ELSE 0 END) AS sakit,
        SUM(CASE WHEN a.status = 'alpha' THEN 1 ELSE 0 END) AS alpha,
        COUNT(a.id) AS total_catat
        FROM karyawan k
        LEFT JOIN absensi a ON k.id = a.karyawan_id
            AND MONTH(a.tanggal) = ?
            AND YEAR(a.tanggal) = ?
        GROUP BY k.id
        ORDER BY k.nama ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $bulan, $tahun);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Bulanan - Sistem Absensi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<h1>Rekap Absensi Bulanan (<?= $nama_bulan[$bulan] ?? '' ?> <?= $tahun ?>)</h1>

<form method="get" action="rekap-bulanan.php" class="filter-box">
    <div class="filter-grid" style="grid-template-columns: 1fr 1fr auto;">
        <div>
            <label>Bulan</label>
            <select name="bulan">
                <?php foreach ($nama_bulan as $num => $label): ?>
                    <option value="<?= $num ?>" <?= $bulan === $num ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>Tahun</label>
            <select name="tahun">
                <?php for ($y = (int)date('Y') - 2; $y <= (int)date('Y') + 1; $y++): ?>
                    <option value="<?= $y ?>" <?= $tahun === $y ? 'selected' : '' ?>>
                        <?= $y ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <div style="display:flex; align-items:flex-end; padding-bottom: 1.15rem;">
            <button type="submit" class="btn">Tampilkan</button>
        </div>
    </div>
</form>

<table>
    <tr>
        <th>No</th>
        <th>Nama Karyawan</th>
        <th>Departemen</th>
        <th>Hadir</th>
        <th>Izin</th>
        <th>Sakit</th>
        <th>Alpha</th>
        <th>Total Hari</th>
        <th>Persentase Hadir</th>
    </tr>
    <?php $no = 1; while ($row = $result->fetch_assoc()):
        $total = (int)$row['total_catat'];
        $hadir = (int)$row['hadir'];
        $persen = $total > 0 ? round(($hadir / $total) * 100, 1) : 0;
        ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><strong><?= e($row['nama']) ?></strong><br><small style="color:#64748b;"><?= e($row['jabatan']) ?></small></td>
            <td><?= e($row['departemen']) ?></td>
            <td><span class="badge badge-hadir"><?= $hadir ?></span></td>
            <td><span class="badge badge-izin"><?= (int)$row['izin'] ?></span></td>
            <td><span class="badge badge-sakit"><?= (int)$row['sakit'] ?></span></td>
            <td><span class="badge badge-alpha"><?= (int)$row['alpha'] ?></span></td>
            <td><?= $total ?></td>
            <td><strong><?= $persen ?>%</strong></td>
        </tr>
    <?php endwhile; ?>
</table>
</body>
</html>