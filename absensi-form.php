<?php
require_once 'auth.php';

$id          = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$karyawan_id = isset($_GET['karyawan_id']) ? (int)$_GET['karyawan_id'] : 0;

$data = [
        'karyawan_id' => $karyawan_id,
        'tanggal'     => date('Y-m-d'),
        'jam_masuk'   => '08:00',
        'jam_keluar'  => '',
        'status'      => 'hadir',
        'keterangan'  => '',
];

if ($id > 0) {
    $stmt = $conn->prepare(
            "SELECT karyawan_id, tanggal, jam_masuk, jam_keluar, status, keterangan
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $id ? 'Edit' : 'Catat' ?> Absensi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<h1><?= $id ? 'Edit' : 'Catat' ?> Absensi</h1>

<?php if ($pesan === 'lengkapi'): ?>
    <div class="msg msg-error">Karyawan dan tanggal wajib dipilih.</div>
<?php elseif ($pesan === 'duplikat'): ?>
    <div class="msg msg-error">Karyawan ini sudah memiliki catatan absensi pada tanggal tersebut.</div>
<?php elseif ($pesan === 'jam_invalid'): ?>
    <div class="msg msg-error">Jam keluar tidak boleh lebih awal dari jam masuk.</div>
<?php endif; ?>

<form action="absensi-proses.php" method="post" class="card-form">
    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
    <input type="hidden" name="id" value="<?= $id ?>">

    <label>Karyawan</label>
    <select name="karyawan_id" required>
        <option value="0">-- Pilih Karyawan --</option>
        <?php foreach ($karyawan_list as $k): ?>
            <option value="<?= (int)$k['id'] ?>" <?= $k['id'] == $data['karyawan_id'] ? 'selected' : '' ?>>
                <?= e($k['nama']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Tanggal</label>
    <input type="date" name="tanggal" value="<?= e($data['tanggal']) ?>" required>

    <label>Status Kehadiran</label>
    <select name="status" id="statusSelect" onchange="toggleJamFields()">
        <?php foreach (['hadir', 'izin', 'sakit', 'alpha'] as $s): ?>
            <option value="<?= $s ?>" <?= $data['status'] === $s ? 'selected' : '' ?>>
                <?= ucfirst($s) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <div id="jamContainer">
        <label>Jam Masuk</label>
        <input type="time" name="jam_masuk" id="jamMasuk" value="<?= e($data['jam_masuk'] ? substr($data['jam_masuk'], 0, 5) : '') ?>">

        <label>Jam Keluar</label>
        <input type="time" name="jam_keluar" id="jamKeluar" value="<?= e($data['jam_keluar'] ? substr($data['jam_keluar'], 0, 5) : '') ?>">
    </div>

    <label>Keterangan / Alasan (Opsional)</label>
    <input type="text" name="keterangan" placeholder="Contoh: Surat dokter, urusan keluarga, lembur, dll." value="<?= e($data['keterangan'] ?? '') ?>">

    <p>
        <button type="submit" class="btn">Simpan</button>
        <a href="absensi.php?karyawan_id=<?= (int)$data['karyawan_id'] ?>" class="btn btn-secondary">Batal</a>
    </p>
</form>

<script>
    function toggleJamFields() {
        const status = document.getElementById('statusSelect').value;
        const container = document.getElementById('jamContainer');
        if (status !== 'hadir') {
            container.style.opacity = '0.45';
            document.getElementById('jamMasuk').disabled = true;
            document.getElementById('jamKeluar').disabled = true;
        } else {
            container.style.opacity = '1';
            document.getElementById('jamMasuk').disabled = false;
            document.getElementById('jamKeluar').disabled = false;
        }
    }
    toggleJamFields();
</script>
</body>
</html>