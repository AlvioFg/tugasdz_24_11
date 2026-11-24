<?php
// DAFTAR KARYAWAN + REKAP KEHADIRAN
require 'config.php';

$sql = "SELECT k.id, k.nama, k.jabatan, k.departemen,
        SUM(CASE WHEN a.status = 'hadir' THEN 1 ELSE 0 END) AS total_hadir,
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
    <title>Daftar Karyawan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h1>Sistem Absensi Karyawan</h1>

<?php if ($pesan === 'tambah'): ?>
    <div class="msg msg-success">Karyawan ditambahkan.</div>
<?php elseif ($pesan === 'edit'): ?>
    <div class="msg msg-success">Data diperbarui.</div>
<?php elseif ($pesan === 'hapus'): ?>
    <div class="msg msg-success">Karyawan dihapus.</div>
<?php elseif ($pesan === 'masih_ada_absensi'): ?>
    <div class="msg msg-error">
        Gagal hapus: karyawan masih punya data absensi.
    </div>
<?php endif; ?>

<p><a href="karyawan-form.php" class="btn">+ Tambah Karyawan</a></p>

<table>
    <tr>
        <th>No</th>
        <th>Nama</th>
        <th>Jabatan</th>
        <th>Departemen</th>
        <th>Total Hadir</th>
        <th>Total Absensi</th>
        <th>Aksi</th>
    </tr>
    <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= htmlspecialchars($row['jabatan']) ?></td>
            <td><?= htmlspecialchars($row['departemen']) ?></td>
            <td><?= (int)$row['total_hadir'] ?></td>
            <td><?= (int)$row['total_absensi'] ?></td>
            <td>
                <a href="absensi.php?karyawan_id=<?= (int)$row['id'] ?>">Absensi</a>
                <a href="karyawan-form.php?id=<?= (int)$row['id'] ?>">Edit</a>
                <a href="karyawan-proses.php?aksi=hapus&id=<?= (int)$row['id'] ?>"
                   onclick="return confirm('Hapus karyawan ini?')"
                   style="color:#b91c1c;">Hapus</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>
</body>
</html>