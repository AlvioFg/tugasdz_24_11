<?php
// DAFTAR ABSENSI + FILTER
require 'config.php';

$karyawan_id   = isset($_GET['karyawan_id']) ? (int)$_GET['karyawan_id'] : 0;
$karyawan_list = [];

$resk = $conn->query("SELECT id, nama FROM karyawan ORDER BY nama ASC");
while ($r = $resk->fetch_assoc()) $karyawan_list[] = $r;

if ($karyawan_id > 0) {
    $stmt = $conn->prepare(
        "SELECT a.*, k.nama
         FROM absensi a
         JOIN karyawan k ON a.karyawan_id = k.id
         WHERE a.karyawan_id = ?
         ORDER BY a.tanggal DESC, a.id DESC"
    );
    $stmt->bind_param('i', $karyawan_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query(
        "SELECT a.*, k.nama
         FROM absensi a
         JOIN karyawan k ON a.karyawan_id = k.id
         ORDER BY a.tanggal DESC, a.id DESC"
    );
}

$data_absensi = [];
while ($row = $result->fetch_assoc()) $data_absensi[] = $row;

$pesan = $_GET['pesan'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Absensi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h1>Data Absensi</h1>
<p><a href="index.php">&laquo; Kembali</a></p>

<?php if ($pesan === 'tambah'): ?>
    <div class="msg msg-success">Absensi dicatat.</div>
<?php elseif ($pesan === 'edit'): ?>
    <div class="msg msg-success">Absensi diperbarui.</div>
<?php elseif ($pesan === 'hapus'): ?>
    <div class="msg msg-success">Absensi dihapus.</div>
<?php endif; ?>

<form method="get" action="absensi.php">
    <label>Filter Karyawan</label>
    <select name="karyawan_id" onchange="this.form.submit()">
        <option value="0">-- Semua Karyawan --</option>
        <?php foreach ($karyawan_list as $k): ?>
            <option value="<?= (int)$k['id'] ?>" <?= $k['id'] == $karyawan_id ? 'selected' : '' ?>>
                <?= htmlspecialchars($k['nama']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<p>
    <a href="absensi-form.php?karyawan_id=<?= $karyawan_id ?>" class="btn">+ Catat Absensi</a>
</p>

<table>
    <tr>
        <th>No</th>
        <th>Tanggal</th>
        <th>Nama</th>
        <th>Jam Masuk</th>
        <th>Jam Keluar</th>
        <th>Status</th>
        <th>Aksi</th>
    </tr>
    <?php $no = 1; foreach ($data_absensi as $row): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['tanggal']) ?></td>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= htmlspecialchars($row['jam_masuk'] ?? '-') ?></td>
            <td><?= htmlspecialchars($row['jam_keluar'] ?? '-') ?></td>
            <td>
                <span class="badge badge-<?= $row['status'] ?>">
                    <?= $row['status'] ?>
                </span>
            </td>
            <td>
                <a href="absensi-form.php?id=<?= (int)$row['id'] ?>">Edit</a>
                <a href="absensi-proses.php?aksi=hapus&id=<?= (int)$row['id'] ?>"
                   onclick="return confirm('Hapus absensi ini?')"
                   style="color:#b91c1c;">Hapus</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
</body>
</html>