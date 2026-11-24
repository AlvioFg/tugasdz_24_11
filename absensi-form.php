<?php
// FORM TAMBAH / EDIT ABSENSI
require 'config.php';

$id          = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$karyawan_id = isset($_GET['karyawan_id']) ? (int)$_GET['karyawan_id'] : 0;

$data = [
    'karyawan_id' => $karyawan_id,
    'tanggal'     => date('Y-m-d'),
    'jam_masuk'   => '',
    'jam_keluar'  => '',
    'status'      => 'hadir',
];

if ($id > 0) {
    $stmt = $conn->prepare(
        "SELECT karyawan_id, tanggal, jam_masuk, jam_keluar, status
         FROM absensi WHERE id = ?"
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row) $data = $row;
    $stmt->close();
}

$karyawan_list = [];
$resk = $conn->query("SELECT id, nama FROM karyawan ORDER BY nama ASC");
while ($r = $resk->fetch_assoc()) $karyawan_list[] = $r;

$pesan = $_GET['pesan'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $id ? 'Edit' : 'Catat' ?> Absensi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h1><?= $id ? 'Edit' : 'Catat' ?> Absensi</h1>

<?php if ($pesan === 'lengkapi'): ?>
    <div class="msg msg-error">
        Karyawan dan tanggal wajib dipilih.
    </div>
<?php elseif ($pesan === 'duplikat'): ?>
    <div class="msg msg-error">
        Karyawan ini sudah punya absensi di tanggal tersebut.
    </div>
<?php endif; ?>

<form action="absensi-proses.php" method="post">
    <input type="hidden" name="id" value="<?= $id ?>">

    <label>Karyawan</label>
    <select name="karyawan_id">
        <option value="0">-- Pilih Karyawan --</option>
        <?php foreach ($karyawan_list as $k): ?>
            <option value="<?= (int)$k['id'] ?>" <?= $k['id'] == $data['karyawan_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($k['nama']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Tanggal</label>
    <input type="date" name="tanggal" value="<?= htmlspecialchars($data['tanggal']) ?>">

    <label>Jam Masuk</label>
    <input type="time" name="jam_masuk" value="<?= htmlspecialchars($data['jam_masuk'] ?? '') ?>">

    <label>Jam Keluar</label>
    <input type="time" name="jam_keluar" value="<?= htmlspecialchars($data['jam_keluar'] ?? '') ?>">

    <label>Status</label>
    <select name="status">
        <?php foreach (['hadir', 'izin', 'sakit', 'alpha'] as $s): ?>
            <option value="<?= $s ?>" <?= $data['status'] === $s ? 'selected' : '' ?>>
                <?= ucfirst($s) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <p>
        <button type="submit" class="btn">Simpan</button>
        <a href="absensi.php?karyawan_id=<?= (int)$data['karyawan_id'] ?>" class="btn btn-secondary">Batal</a>
    </p>
</form>
</body>
</html>