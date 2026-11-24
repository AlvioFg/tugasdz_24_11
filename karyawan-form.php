<?php
require_once 'auth.php';

$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$data = ['nama' => '', 'jabatan' => '', 'departemen' => ''];

if ($id > 0) {
    $stmt = $conn->prepare("SELECT nama, jabatan, departemen FROM karyawan WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row) $data = $row;
    $stmt->close();
}

$pesan = $_GET['pesan'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $id ? 'Edit' : 'Tambah' ?> Karyawan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<h1><?= $id ? 'Edit' : 'Tambah' ?> Karyawan</h1>

<?php if ($pesan === 'wajib_lengkap'): ?>
    <div class="msg msg-error">Nama, jabatan, dan departemen wajib diisi semua.</div>
<?php endif; ?>

<form action="karyawan-proses.php" method="post" class="card-form">
    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
    <input type="hidden" name="id" value="<?= $id ?>">

    <label>Nama Lengkap</label>
    <input type="text" name="nama" value="<?= e($data['nama']) ?>" required>

    <label>Jabatan</label>
    <input type="text" name="jabatan" value="<?= e($data['jabatan']) ?>" required>

    <label>Departemen</label>
    <input type="text" name="departemen" value="<?= e($data['departemen']) ?>" required>

    <p>
        <button type="submit" class="btn">Simpan</button>
        <a href="index.php" class="btn btn-secondary">Batal</a>
    </p>
</form>
</body>
</html>